<?php

namespace Tests\Feature;

use App\Actions\CreateOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_store_and_cart_are_accessible(): void
    {
        $this->get(route('home'))->assertOk();
        $this->get(route('store'))->assertOk();
        $this->get(route('cart'))->assertOk();
    }

    public function test_checkout_and_orders_require_authentication(): void
    {
        $this->get(route('checkout'))->assertRedirect(route('login'));
        $this->get(route('orders.index'))->assertRedirect(route('login'));
    }

    public function test_customer_profile_shows_account_and_order_summary(): void
    {
        $customer = User::factory()->create(['name' => 'Maria Cliente']);
        $order = $this->createOrderFor($customer, Product::factory()->create());

        $this->actingAs($customer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Maria Cliente')
            ->assertSee('Pedidos recentes')
            ->assertSee('#'.$order->id);
    }

    public function test_customer_cannot_access_admin_order_details(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();
        $order = $this->createOrderFor($customer, $product);

        $this->actingAs($customer)->get(route('admin.orders.show', $order))->assertForbidden();
    }

    public function test_admin_can_access_order_details(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrderFor($customer, Product::factory()->create());

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Pedido #'.$order->id);
    }

    private function createOrderFor(User $user, Product $product): Order
    {
        return app(CreateOrder::class)->execute($user, [$product->id => 1], [
            'zip_code' => '01001-000', 'street' => 'Rua A', 'number' => '1',
            'district' => 'Centro', 'city' => 'São Paulo', 'state' => 'SP',
        ], '11999999999', 10);
    }
}
