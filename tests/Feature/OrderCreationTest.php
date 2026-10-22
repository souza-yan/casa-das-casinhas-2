<?php

namespace Tests\Feature;

use App\Actions\CreateOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_prices_are_recalculated_from_database(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 25.50]);
        $order = app(CreateOrder::class)->execute($user, [$product->id => 2], $this->address(), '11999999999', 12.40);

        $this->assertSame('51.00', $order->subtotal);
        $this->assertSame('63.40', $order->total);
        $this->assertSame('25.50', $order->items->first()->unit_price);
        $this->assertSame($product->name, $order->items->first()->product_name);
    }

    public function test_user_can_only_view_own_order(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->create();
        $order = app(CreateOrder::class)->execute($owner, [$product->id => 1], $this->address(), '11999999999', 0);
        $this->assertTrue($owner->can('view', $order));
        $this->assertFalse($other->can('view', $order));
        $this->assertTrue(User::factory()->admin()->create()->can('view', $order));
    }

    private function address(): array
    {
        return ['zip_code' => '01001-000', 'street' => 'Praça da Sé', 'number' => '1', 'complement' => null, 'district' => 'Sé', 'city' => 'São Paulo', 'state' => 'SP'];
    }
}
