<?php

namespace Tests\Feature;

use App\Actions\CreateOrder;
use App\Enums\OrderStatus;
use App\Enums\SaleType;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StockSalesTest extends TestCase
{
    use RefreshDatabase;

    private function stock(int $quantity = 3): Product
    {
        return Product::factory()->create(['sale_type' => SaleType::STOCK, 'stock_quantity' => $quantity]);
    }

    private function order(array $cart, ?string $token = null): Order
    {
        $user = User::factory()->create();

        return app(CreateOrder::class)->create($user, $cart, [
            'name' => $user->name, 'email' => $user->email, 'phone' => '12988961502',
        ], [
            'zip_code' => '01001-000', 'street' => 'Rua A', 'number' => '1',
            'district' => 'Centro', 'city' => 'São Paulo', 'state' => 'SP',
        ], 0, $token);
    }

    public function test_checkout_reserves_stock_and_preserves_sale_type_and_zero_production_time(): void
    {
        $product = $this->stock();
        $order = $this->order([$product->id => 2], 'same-checkout');
        $this->assertSame(1, $product->fresh()->stock_quantity);
        $this->assertSame(SaleType::STOCK, $order->items->sole()->sale_type);
        $this->assertSame(0, $order->productionDays());
        $this->assertSame($order->id, $this->order([$product->id => 2], 'same-checkout')->id);
        $this->assertSame(1, $product->fresh()->stock_quantity);
    }

    public function test_checkout_aggregates_separate_lines_and_rolls_back_when_stock_is_insufficient(): void
    {
        $product = $this->stock(1);
        try {
            $this->order([
                'a' => ['product_id' => $product->id, 'quantity' => 1],
                'b' => ['product_id' => $product->id, 'quantity' => 1],
            ]);
            $this->fail('Expected insufficient stock.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('cart', $exception->errors());
        }
        $this->assertSame(1, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_a_second_order_cannot_buy_the_last_reserved_unit(): void
    {
        $product = $this->stock(1);
        $this->order([$product->id => 1]);
        $this->expectException(ValidationException::class);
        $this->order([$product->id => 1]);
    }

    public function test_failed_mixed_checkout_rolls_back_all_stock_changes(): void
    {
        $first = $this->stock(4);
        $second = $this->stock(0);
        try {
            $this->order([$first->id => 2, $second->id => 1]);
            $this->fail('Expected insufficient stock.');
        } catch (ValidationException) {
            $this->assertSame(4, $first->fresh()->stock_quantity);
            $this->assertDatabaseCount('orders', 0);
        }
    }

    public function test_adding_a_sold_out_product_rejects_stale_product_data(): void
    {
        $product = $this->stock(1);
        Product::whereKey($product->id)->update(['stock_quantity' => 0]);
        $this->expectException(ValidationException::class);
        app(CartService::class)->add($product);
    }

    public function test_cancelled_order_returns_stock_once_even_after_product_changes(): void
    {
        $product = $this->stock();
        $order = $this->order([$product->id => 2]);
        $product->update(['sale_type' => SaleType::ON_DEMAND]);
        $product->delete();
        $order->transitionTo(OrderStatus::CANCELLED);
        $this->assertSame(3, Product::withTrashed()->find($product->id)->stock_quantity);
        try {
            $order->transitionTo(OrderStatus::CANCELLED);
            $this->fail('Repeated cancellation must not return stock again.');
        } catch (\DomainException) {
            $this->assertSame(3, Product::withTrashed()->find($product->id)->stock_quantity);
        }
    }

    public function test_on_demand_order_does_not_consume_stock_and_mixed_order_keeps_production_time(): void
    {
        $stock = $this->stock();
        $demand = Product::factory()->create();
        $order = $this->order([$stock->id => 1, $demand->id => 4]);
        $this->assertSame(2, $stock->fresh()->stock_quantity);
        $this->assertSame(0, $demand->fresh()->stock_quantity);
        $this->assertSame(15, $order->productionDays());
        $order->transitionTo(OrderStatus::CANCELLED);
        $this->assertSame(3, $stock->fresh()->stock_quantity);
        $this->assertSame(0, $demand->fresh()->stock_quantity);
    }

    public function test_cart_limits_the_total_across_personalized_lines(): void
    {
        $product = $this->stock(1);
        $cart = app(CartService::class);
        $cart->add($product, 1, 'Azul');
        try {
            $cart->add($product, 1, 'Verde');
            $this->fail('Expected insufficient stock.');
        } catch (ValidationException) {
            $this->assertCount(1, $cart->raw());
        }
        $this->assertSame(1, $product->fresh()->stock_quantity);
    }

    public function test_cart_update_cannot_exceed_stock(): void
    {
        $product = $this->stock(1);
        $cart = app(CartService::class);
        $cart->add($product);
        $this->expectException(ValidationException::class);
        $cart->update($product->id, 2);
    }

    public function test_stock_order_can_skip_production_only_after_payment(): void
    {
        $product = $this->stock();
        $order = $this->order([$product->id => 1]);
        $this->assertFalse($order->canTransitionTo(OrderStatus::READY_TO_SHIP));
        $order->transitionTo(OrderStatus::PAID);
        $this->assertTrue($order->canTransitionTo(OrderStatus::READY_TO_SHIP));
        $order->transitionTo(OrderStatus::READY_TO_SHIP);
        $this->assertSame(OrderStatus::READY_TO_SHIP, $order->status);

        $demand = Product::factory()->create();
        $order = $this->order([$demand->id => 1]);
        $order->transitionTo(OrderStatus::PAID);
        $this->assertFalse($order->canTransitionTo(OrderStatus::READY_TO_SHIP));
        $this->expectException(\DomainException::class);
        $order->transitionTo(OrderStatus::READY_TO_SHIP);
    }

    public function test_sold_out_product_has_no_purchase_button(): void
    {
        $product = $this->stock(0);
        $this->get(route('products.show', $product))->assertOk()
            ->assertSee('Produto esgotado.')->assertDontSee('wire:click="add"', false);
    }

    public function test_stock_products_never_offer_personalization_even_with_a_legacy_flag(): void
    {
        $product = $this->stock();
        Product::whereKey($product->id)->update(['is_customizable' => true]);
        $this->assertFalse($product->fresh()->is_customizable);
        $this->get(route('products.show', $product))->assertOk()
            ->assertDontSee('wire:model="personalization"', false)
            ->assertSee('wire:click="add"', false);

        $product->refresh();
        $product->is_customizable = true;
        $product->save();
        $this->assertSame(0, (int) $product->fresh()->getRawOriginal('is_customizable'));
    }

    public function test_stock_order_ignores_personalization_from_an_old_cart(): void
    {
        $product = $this->stock();
        $cart = app(CartService::class);
        $cart->add($product, 1, 'Instruções antigas', ['personalizations/old.png']);
        $this->assertNull($cart->items()->sole()['personalization_description']);
        $this->assertSame([], $cart->items()->sole()['reference_paths']);

        $order = $this->order(['legacy' => [
            'product_id' => $product->id, 'quantity' => 1,
            'personalization_description' => 'Instruções antigas',
            'reference_paths' => ['personalizations/old.png'],
        ]]);
        $this->assertNull($order->items->sole()->personalization_description);
        $this->assertSame([], $order->items->sole()->reference_paths);
        $this->assertFalse($order->requiresPersonalizationApproval());
    }
}
