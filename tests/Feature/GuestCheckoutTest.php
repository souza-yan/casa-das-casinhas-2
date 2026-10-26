<?php

namespace Tests\Feature;

use App\Actions\CreateOrder;
use App\Actions\CreatePayment;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Enums\SaleType;
use App\Jobs\ProcessAsaasWebhook;
use App\Livewire\Checkout;
use App\Livewire\GuestOrders\CreateAccount;
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
            'services.melhor_envio.token' => 'sandbox-token',
            'services.melhor_envio.origin_zip_code' => '01310-100',
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
            '*/api/v2/me/shipment/calculate' => Http::response([['id' => 1, 'name' => 'PAC', 'price' => '20.00', 'delivery_time' => 5, 'company' => ['name' => 'Correios']]]),
            '*/v3/customers' => Http::response(['id' => 'cus_guest_1']),
            '*/v3/payments' => Http::response(['id' => 'pay_guest_1', 'invoiceUrl' => 'https://sandbox.example/pay']),
        ]);
        $product = Product::factory()->create(['price' => 100, 'sale_type' => $saleType, 'stock_quantity' => $saleType === 'stock' ? 2 : 0]);
        $this->withSession(['cart.items' => [$product->id => 1]]);

        Livewire::test(Checkout::class)
            ->set('name', 'Maria Visitante')
            ->set('email', 'maria@example.com')
            ->set('phone', '11999999999')
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

        $first = app(CreateOrder::class)->create(null, [$product->id => 1], $customer, $address, 10, $checkoutToken, $access->makeToken(), $access->makeToken());
        $second = app(CreateOrder::class)->create(null, [$product->id => 1], $customer, $address, 10, $checkoutToken, $access->makeToken(), $access->makeToken());

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

    public function test_guest_can_verify_email_and_create_account_without_linking_other_orders(): void
    {
        [$order, , $verificationToken] = $this->guestOrder(['email' => 'maria@example.com']);
        [$otherOrder] = $this->guestOrder(['email' => 'maria@example.com']);

        $this->get(route('guest.orders.verify', ['order' => $order, 'token' => $verificationToken]))->assertRedirect(route('guest.orders.show', $order));

        Livewire::test(CreateAccount::class, ['order' => $order])
            ->set('password', 'senha-segura')
            ->set('password_confirmation', 'senha-segura')
            ->call('createAccount')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $user = User::where('email', 'maria@example.com')->firstOrFail();
        $this->assertSame(Role::CUSTOMER, $user->role);
        $this->assertSame($user->id, $order->refresh()->user_id);
        $this->assertNull($otherOrder->refresh()->user_id);
    }

    public function test_existing_account_is_not_duplicated_when_guest_email_is_verified(): void
    {
        $existing = User::factory()->create(['email' => 'existente@example.com']);
        [$order, , $verificationToken] = $this->guestOrder(['email' => $existing->email]);

        $this->get(route('guest.orders.verify', ['order' => $order, 'token' => $verificationToken]));

        Livewire::test(CreateAccount::class, ['order' => $order])
            ->assertSet('existingAccount', true)
            ->assertSee('Já existe uma conta com este e-mail');

        $this->assertSame(1, User::where('email', $existing->email)->count());
    }

    public function test_cart_is_preserved_when_guest_logs_in(): void
    {
        $customer = User::factory()->create(['password' => 'password']);
        $product = Product::factory()->create();

        $this->withSession(['cart.items' => [$product->id => 2]])
            ->post(route('login.store'), ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect(route('home'));

        $this->assertSessionHas('cart.items.'.$product->id, 2);
    }

    private function guestOrder(array $customer = []): array
    {
        $product = Product::factory()->create(['price' => 50]);
        $access = app(GuestOrderAccessService::class);
        $accessToken = $access->makeToken();
        $verificationToken = $access->makeToken();
        $customer = array_merge(['name' => 'Maria Visitante', 'email' => 'visitante'.fake()->unique()->numberBetween(1, 999999).'@example.com', 'phone' => '11999999999'], $customer);

        $order = app(CreateOrder::class)->create(
            null,
            [$product->id => 1],
            $customer,
            ['zip_code' => '01001-000', 'street' => 'Rua A', 'number' => '1', 'district' => 'Centro', 'city' => 'São Paulo', 'state' => 'SP'],
            10,
            bin2hex(random_bytes(32)),
            $accessToken,
            $verificationToken,
        );

        return [$order, $accessToken, $verificationToken];
    }
}
