<?php

namespace Tests\Feature;

use App\Actions\CreateOrder;
use App\Actions\CreatePayment;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SaleType;
use App\Jobs\ProcessAsaasWebhook;
use App\Livewire\Checkout;
use App\Livewire\GuestOrders\Lookup;
use App\Mail\GuestOrderAccessLink;
use App\Mail\GuestOrderConfirmation;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\GuestOrderAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GuestCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.superfrete.token' => 'sandbox-token',
            'services.superfrete.origin.postal_code' => '01310-100',
            'services.asaas.token' => 'sandbox-token',
        ]);
    }

    public static function saleTypes(): array
    {
        return ['sob demanda' => ['on_demand'], 'em estoque' => ['stock']];
    }

    #[DataProvider('saleTypes')]
    public function test_guest_can_calculate_shipping_and_create_an_order_without_an_account(string $saleType): void
    {
        Mail::fake();
        Http::fake([
            'viacep.com.br/ws/*/json/' => Http::response(['logradouro' => 'Praça da Sé', 'bairro' => 'Sé', 'localidade' => 'São Paulo', 'uf' => 'SP']),
            '*/api/v0/calculator' => Http::response([['id' => 1, 'name' => 'PAC', 'price' => '20.00', 'delivery_time' => 5, 'company' => ['name' => 'Correios']]]),
            '*/v3/customers' => Http::response(['id' => 'cus_guest_1']),
            '*/v3/payments' => Http::response(['id' => 'pay_guest_1', 'invoiceUrl' => 'https://sandbox.example/pay']),
        ]);
        $product = Product::factory()->create(['price' => 100, 'sale_type' => $saleType, 'stock_quantity' => $saleType === 'stock' ? 2 : 0]);
        $this->withSession(['cart.items' => [$product->id => 1]]);

        Livewire::test(Checkout::class)
            ->set('name', 'Maria Visitante')
            ->set('email', 'maria@example.com')
            ->set('phone', '11999999999')
            ->set('document', '24971563792')
            ->set('zip_code', '01001-000')
            ->set('street', 'Praça da Sé')
            ->set('number', '1')
            ->set('district', 'Sé')
            ->set('city', 'São Paulo')
            ->set('state', 'SP')
            ->assertSet('shippingOptions.0.id', '1')
            ->set('shippingId', '1')
            ->call('place')
            ->assertHasNoErrors()
            ->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertNull($order->user_id);
        $this->assertSame('Maria Visitante', $order->customer_name);
        $this->assertSame('120.00', $order->total);
        $this->assertSame($saleType === 'stock' ? 1 : 0, $product->fresh()->stock_quantity);
        $this->assertSame($saleType === 'stock' ? 0 : 15, $order->productionDays());
        $this->assertSame(SaleType::from($saleType), $order->items->sole()->sale_type);
        $this->assertNotNull($order->guest_access_token_hash);
        $this->assertNotNull($order->payments()->first()?->provider_payment_id);
        Mail::assertSent(GuestOrderConfirmation::class, fn (GuestOrderConfirmation $mail) => $mail->order->is($order));
    }

    public function test_checkout_token_prevents_duplicate_guest_orders(): void
    {
        $product = Product::factory()->create();
        $access = app(GuestOrderAccessService::class);
        $checkoutToken = bin2hex(random_bytes(32));
        $customer = ['name' => 'Maria Visitante', 'email' => 'maria@example.com', 'phone' => '11999999999'];
        $address = ['zip_code' => '01001-000', 'street' => 'Rua A', 'number' => '1', 'district' => 'Centro', 'city' => 'São Paulo', 'state' => 'SP'];

        $first = app(CreateOrder::class)->create(null, [$product->id => 1], $customer, $address, 10, $checkoutToken, $access->makeToken());
        $second = app(CreateOrder::class)->create(null, [$product->id => 1], $customer, $address, 10, $checkoutToken, $access->makeToken());

        $this->assertTrue($first->is($second));
        $this->assertSame(1, Order::count());
    }

    public function test_confirmed_guest_payment_updates_the_order_without_a_user(): void
    {
        Http::fake([
            '*/v3/customers' => Http::response(['id' => 'cus_guest_1']),
            '*/v3/payments' => Http::response(['id' => 'pay_guest_1', 'invoiceUrl' => 'https://sandbox.example/pay']),
        ]);
        [$order] = $this->guestOrder();
        $payment = app(CreatePayment::class)->execute($order);
        $event = $payment->webhookEvents()->create([
            'provider' => 'asaas',
            'external_event_id' => 'evt_guest_confirmed',
            'event_type' => 'PAYMENT_CONFIRMED',
            'payload' => ['payment' => ['id' => $payment->provider_payment_id, 'value' => (float) $payment->amount]],
            'status' => 'pending',
        ]);

        (new ProcessAsaasWebhook($event))->handle();

        $this->assertNull($order->user_id);
        $this->assertSame(PaymentStatus::CONFIRMED, $payment->refresh()->status);
        $this->assertSame(OrderStatus::PAID, $order->refresh()->status);
    }

    public function test_guest_order_cannot_be_accessed_without_its_secret_token(): void
    {
        [$order, $accessToken] = $this->guestOrder();

        $this->get(route('guest.orders.show', $order))->assertForbidden();
        $this->get(route('guest.orders.show', ['order' => $order, 'token' => $accessToken]))->assertOk();
    }

    public function test_guest_can_request_a_new_private_order_access_link(): void
    {
        Mail::fake();
        [$order, $oldAccessToken] = $this->guestOrder(['email' => 'consulta@example.com']);
        $oldHash = $order->guest_access_token_hash;

        Livewire::test(Lookup::class)
            ->set('orderNumber', '#'.$order->id)
            ->set('email', 'CONSULTA@example.com')
            ->call('sendLink')
            ->assertHasNoErrors()
            ->assertSet('requestCompleted', true)
            ->assertSee('Se os dados informados corresponderem');

        $order->refresh();
        $this->assertNotSame($oldHash, $order->guest_access_token_hash);
        $this->assertFalse(app(GuestOrderAccessService::class)->allowsToken($order, $oldAccessToken));
        $this->assertTrue($order->guest_access_expires_at->isAfter(now()->addDays(179)));
        Mail::assertSent(GuestOrderAccessLink::class, function (GuestOrderAccessLink $mail) use ($order): bool {
            return $mail->order->is($order)
                && app(GuestOrderAccessService::class)->allowsToken($order, $mail->accessToken);
        });
    }

    public function test_order_lookup_does_not_reveal_when_the_data_does_not_match(): void
    {
        Mail::fake();
        [$order] = $this->guestOrder(['email' => 'correto@example.com']);

        Livewire::test(Lookup::class)
            ->set('orderNumber', (string) $order->id)
            ->set('email', 'incorreto@example.com')
            ->call('sendLink')
            ->assertHasNoErrors()
            ->assertSet('requestCompleted', true)
            ->assertSee('Se os dados informados corresponderem');

        Mail::assertNothingSent();
    }

    public function test_guest_can_return_to_the_order_from_another_session_after_two_days(): void
    {
        [$order, $accessToken] = $this->guestOrder();

        $this->travel(2)->days();
        $this->flushSession();

        $this->get(route('guest.orders.show', ['order' => $order, 'token' => $accessToken]))
            ->assertOk()
            ->assertSee('Pedido #'.$order->id);
    }

    public function test_payment_return_shows_standard_completion_screen_and_email_has_tracking_access(): void
    {
        [$order, $accessToken] = $this->guestOrder();

        $this->get(route('guest.orders.show', [
            'order' => $order,
            'token' => $accessToken,
            'payment_returned' => 1,
        ]))->assertOk()
            ->assertSee('Pedido concluído')
            ->assertSee($order->customer_email)
            ->assertDontSee('Itens do pedido')
            ->assertDontSee('Aguardando aprovação da personalização');

        $html = (new GuestOrderConfirmation($order, $accessToken))->render();

        $this->assertStringContainsString('Resumo do pedido', $html);
        $this->assertStringContainsString('Acompanhar pedido', $html);
        $this->assertStringContainsString('código de rastreio', $html);
    }

    public function test_completed_guest_order_uses_success_screen_but_email_link_opens_details(): void
    {
        [$order, $accessToken] = $this->guestOrder();
        $order->update(['status' => OrderStatus::AWAITING_PERSONALIZATION_APPROVAL]);

        $baseParameters = ['order' => $order, 'token' => $accessToken];

        $this->get(route('guest.orders.show', $baseParameters))
            ->assertOk()
            ->assertSee('Pedido concluído')
            ->assertDontSee('Aguardando aprovação da personalização');

        $this->get(route('guest.orders.show', [...$baseParameters, 'details' => 1]))
            ->assertOk()
            ->assertSee('Itens do pedido')
            ->assertSee('Aguardando aprovação da personalização');
    }

    public function test_cart_is_preserved_when_guest_logs_in(): void
    {
        $customer = User::factory()->create(['password' => 'password']);
        $product = Product::factory()->create();

        $this->withSession(['cart.items' => [$product->id => 2]])
            ->post(route('login.store'), ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect(route('home'))
            ->assertSessionHas('cart.items.'.$product->id, 2);
    }

    private function guestOrder(array $customer = []): array
    {
        $product = Product::factory()->create(['price' => 50]);
        $access = app(GuestOrderAccessService::class);
        $accessToken = $access->makeToken();
        $customer = array_merge(['name' => 'Maria Visitante', 'email' => 'visitante'.fake()->unique()->numberBetween(1, 999999).'@example.com', 'phone' => '11999999999'], $customer);

        $order = app(CreateOrder::class)->create(
            null,
            [$product->id => 1],
            $customer,
            ['zip_code' => '01001-000', 'street' => 'Rua A', 'number' => '1', 'district' => 'Centro', 'city' => 'São Paulo', 'state' => 'SP', 'document' => '24971563792'],
            10,
            bin2hex(random_bytes(32)),
            $accessToken,
        );

        return [$order, $accessToken];
    }
}
