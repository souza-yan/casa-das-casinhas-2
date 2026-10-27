<?php

namespace Tests\Feature;

use App\Actions\CreateOrder;
use App\Actions\CreatePayment;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Jobs\ProcessAsaasWebhook;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AsaasIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.asaas.token' => 'sandbox-token', 'services.asaas.webhook_token' => 'webhook-secret']);
    }

    public function test_charge_is_created_with_server_total_and_reused(): void
    {
        Http::fake([
            '*/v3/customers' => Http::response(['id' => 'cus_1']),
            '*/v3/payments*' => Http::response(['id' => 'pay_1', 'status' => 'PENDING', 'billingType' => 'UNDEFINED', 'invoiceUrl' => 'https://sandbox.example/pay']),
        ]);
        $order = $this->order();
        $first = app(CreatePayment::class)->execute($order);
        $second = app(CreatePayment::class)->execute($order);
        $this->assertTrue($first->is($second));
        $this->assertSame('pay_1', $first->provider_payment_id);
        Http::assertSent(fn ($request) => $request->url() === config('services.asaas.url').'/v3/customers'
            && $request['cpfCnpj'] === '24971563792');
        Http::assertSent(fn ($request) => $request->url() === config('services.asaas.url').'/v3/payments' && $request['value'] === (float) $order->total);
    }

    public function test_webhook_rejects_invalid_token_and_queues_valid_event_once(): void
    {
        Queue::fake();
        $payment = app(CreatePayment::class);
        Http::fake(['*/v3/customers' => Http::response(['id' => 'cus_1']), '*/v3/payments' => Http::response(['id' => 'pay_1'])]);
        $stored = $payment->execute($this->order());
        $payload = ['id' => 'evt_1', 'event' => 'PAYMENT_CONFIRMED', 'payment' => ['id' => $stored->provider_payment_id, 'value' => (float) $stored->amount]];
        $this->postJson(route('webhooks.asaas'), $payload)->assertUnauthorized();
        $this->withHeader('asaas-access-token', 'webhook-secret')->postJson(route('webhooks.asaas'), $payload)->assertOk();
        $this->withHeader('asaas-access-token', 'webhook-secret')->postJson(route('webhooks.asaas'), $payload)->assertOk()->assertJson(['duplicate' => true]);
        Queue::assertPushed(ProcessAsaasWebhook::class, 1);
    }

    public function test_fixed_boleto_is_replaced_by_a_payment_with_customer_choice(): void
    {
        $order = $this->order();
        $oldPayment = $order->payments()->create([
            'provider' => 'asaas',
            'provider_payment_id' => 'pay_boleto',
            'method' => 'hosted',
            'amount' => $order->total,
            'status' => PaymentStatus::PENDING,
            'payment_url' => 'https://sandbox.asaas.com/boleto-antigo',
        ]);

        Http::fake(function ($request) {
            if ($request->method() === 'GET' && str_ends_with($request->url(), '/v3/payments/pay_boleto')) {
                return Http::response([
                    'id' => 'pay_boleto',
                    'status' => 'PENDING',
                    'billingType' => 'BOLETO',
                    'invoiceUrl' => 'https://sandbox.asaas.com/boleto-antigo',
                ]);
            }

            if (str_ends_with($request->url(), '/v3/customers')) {
                return Http::response(['id' => 'cus_1']);
            }

            return Http::response([
                'id' => 'pay_flexivel',
                'billingType' => 'UNDEFINED',
                'invoiceUrl' => 'https://sandbox.asaas.com/fatura-flexivel',
            ]);
        });

        $newPayment = app(CreatePayment::class)->execute($order);

        $this->assertSame(PaymentStatus::FAILED, $oldPayment->refresh()->status);
        $this->assertSame('pay_flexivel', $newPayment->provider_payment_id);
        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && str_ends_with($request->url(), '/v3/payments/pay_boleto'));
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/v3/payments')
            && $request['billingType'] === 'UNDEFINED');
    }

    public function test_confirmation_is_idempotent_and_refund_is_recorded(): void
    {
        Http::fake(['*/v3/customers' => Http::response(['id' => 'cus_1']), '*/v3/payments' => Http::response(['id' => 'pay_1'])]);
        $payment = app(CreatePayment::class)->execute($this->order());
        $event = $payment->webhookEvents()->create(['provider' => 'asaas', 'external_event_id' => 'evt_confirm', 'event_type' => 'PAYMENT_CONFIRMED', 'payload' => ['payment' => ['id' => 'pay_1', 'value' => (float) $payment->amount]], 'status' => 'pending']);
        (new ProcessAsaasWebhook($event))->handle();
        (new ProcessAsaasWebhook($event))->handle();
        $this->assertSame(PaymentStatus::CONFIRMED, $payment->refresh()->status);
        $this->assertSame(OrderStatus::PAID, $payment->order->refresh()->status);
    }

    private function order()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50]);

        return app(CreateOrder::class)->execute($user, [$product->id => 1], ['zip_code' => '01001-000', 'street' => 'Rua A', 'number' => '1', 'district' => 'Centro', 'city' => 'São Paulo', 'state' => 'SP', 'document' => '24971563792'], '11999999999', 10);
    }
}
