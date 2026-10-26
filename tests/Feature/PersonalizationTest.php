<?php

namespace Tests\Feature;

use App\Actions\CreateOrder;
use App\Enums\OrderStatus;
use App\Livewire\Cart;
use App\Livewire\ProductDetail;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PersonalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customizable_product_requires_a_description_before_adding_to_cart(): void
    {
        $product = Product::factory()->create(['is_customizable' => true]);

        Livewire::test(ProductDetail::class, ['product' => $product])
            ->set('quantity', 1)
            ->set('personalization', '')
            ->call('add')
            ->assertHasErrors(['personalization']);
    }

    public function test_product_without_personalization_can_be_added_without_instructions(): void
    {
        $product = Product::factory()->create(['is_customizable' => false]);

        Livewire::test(ProductDetail::class, ['product' => $product])
            ->call('add')
            ->assertHasNoErrors();

        $this->assertSame(1, app(CartService::class)->items()->count());
        $this->assertNull(app(CartService::class)->items()->first()['personalization_description']);
    }

    public function test_same_product_can_be_kept_in_separate_cart_lines_with_distinct_personalizations(): void
    {
        $product = Product::factory()->create(['is_customizable' => true, 'price' => 50]);
        $cart = app(CartService::class);
        $cart->add($product, 1, 'Pintar em azul');
        $cart->add($product, 1, 'Pintar em verde');

        $items = $cart->items();

        $this->assertCount(2, $items);
        $this->assertNotSame($items[0]['key'], $items[1]['key']);
        $this->assertSame(['Pintar em azul', 'Pintar em verde'], $items->pluck('personalization_description')->all());
    }

    public function test_customer_can_edit_personalization_in_cart(): void
    {
        $product = Product::factory()->create(['is_customizable' => true]);
        $cart = app(CartService::class);
        $cart->add($product, 1, 'Cor original');
        $key = $cart->items()->first()['key'];

        Livewire::test(Cart::class)
            ->set("personalizations.$key", 'Usar cor terracota')
            ->call('savePersonalization', $key)
            ->assertHasNoErrors();

        $this->assertSame('Usar cor terracota', app(CartService::class)->items()->first()['personalization_description']);
    }

    public function test_personalization_and_private_references_are_snapshotted_on_order_item(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('personalizations/referencia.png', 'imagem privada');
        $product = Product::factory()->create(['is_customizable' => true, 'price' => 100]);
        $cart = app(CartService::class);
        $cart->add($product, 1, 'Adicionar uma janela amarela', ['personalizations/referencia.png']);

        $order = $this->createOrder($cart);
        $item = $order->items->sole();
        $product->update(['name' => 'Produto alterado', 'price' => 200]);

        $this->assertSame('Adicionar uma janela amarela', $item->personalization_description);
        $this->assertSame(['personalizations/referencia.png'], $item->reference_paths);
        $this->assertSame('100.00', $item->unit_price);
        $this->assertSame(OrderStatus::AWAITING_PERSONALIZATION_APPROVAL, $this->nextStatusFor($order));
    }

    public function test_private_reference_is_never_available_to_a_customer_or_another_order(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('personalizations/referencia.png', 'imagem privada');
        $product = Product::factory()->create(['is_customizable' => true]);
        $order = $this->createOrder(app(CartService::class), $product, ['personalizations/referencia.png']);
        $item = $order->items->sole();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.orders.items.references.show', ['order' => $order, 'item' => $item, 'reference' => 0]))
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.orders.items.references.show', ['order' => $order, 'item' => $item, 'reference' => 0]))
            ->assertOk();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Adicionar uma janela amarela');
    }

    public function test_checkout_shows_production_and_transport_as_separate_estimates(): void
    {
        $product = Product::factory()->create(['is_customizable' => false]);

        $this->withSession(['cart.items' => [$product->id => 1]])
            ->get(route('checkout'))
            ->assertOk()
            ->assertSee('Produção')
            ->assertSee('Transporte')
            ->assertSee('Até 15 dias úteis');
    }

    private function createOrder(CartService $cart, ?Product $product = null, array $references = [])
    {
        $product ??= Product::factory()->create(['is_customizable' => true, 'price' => 100]);
        if ($cart->items()->isEmpty()) {
            $cart->add($product, 1, 'Adicionar uma janela amarela', $references);
        }

        return app(CreateOrder::class)->create(
            User::factory()->create(),
            $cart->raw(),
            ['name' => 'Cliente', 'email' => 'cliente@example.com', 'phone' => '11999999999'],
            ['zip_code' => '01001-000', 'street' => 'Rua A', 'number' => '1', 'district' => 'Centro', 'city' => 'São Paulo', 'state' => 'SP'],
            10,
        );
    }

    private function nextStatusFor($order): OrderStatus
    {
        return $order->requiresPersonalizationApproval()
            ? OrderStatus::AWAITING_PERSONALIZATION_APPROVAL
            : OrderStatus::PAID;
    }
}
