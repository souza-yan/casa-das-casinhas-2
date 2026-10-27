<?php

namespace Tests\Feature;

use App\Actions\CreateOrder;
use App\Enums\OrderStatus;
use App\Models\Product;
use App\Models\User;
use App\Services\Shipping\SuperFreteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class SuperFreteServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.superfrete.token' => 'sandbox', 'services.superfrete.origin' => [
            'name' => 'Casa das Casinhas', 'postal_code' => '01310-100', 'address' => 'Rua A', 'number' => '1',
            'district' => 'Centro', 'city' => 'São Paulo', 'state_abbr' => 'SP',
        ]]);
    }

    public function test_quote_uses_weight_and_largest_dimensions(): void
    {
        Http::fake(['*/api/v0/calculator' => Http::response([['id' => 1, 'name' => 'PAC', 'price' => '22.50', 'delivery_time' => 5, 'company' => ['name' => 'Correios']]])]);
        $small = Product::factory()->create(['weight_kg' => 0.5, 'height_cm' => 10, 'width_cm' => 15, 'length_cm' => 20]);
        $large = Product::factory()->create(['weight_kg' => 1, 'height_cm' => 30, 'width_cm' => 25, 'length_cm' => 40]);

        $options = app(SuperFreteService::class)->quote('01001-000', collect([['product' => $small, 'quantity' => 2], ['product' => $large, 'quantity' => 1]]));

        $this->assertSame(22.5, $options[0]['price']);
        Http::assertSent(fn ($request) => $request['products'][0]['weight'] === 0.5 && $request['products'][0]['quantity'] === 2 && $request['products'][1]['height'] === 30.0 && $request['products'][1]['length'] === 40.0);
    }

    public function test_label_cannot_be_bought_before_order_is_ready(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();
        $order = app(CreateOrder::class)->execute($user, [$product->id => 1], ['zip_code' => '01001-000', 'street' => 'Rua A', 'number' => '1', 'district' => 'Centro', 'city' => 'São Paulo', 'state' => 'SP'], '11999999999', 10);

        $this->assertSame(OrderStatus::PENDING_PAYMENT, $order->status);
        $this->expectException(RuntimeException::class);
        app(SuperFreteService::class)->buyLabel($order);
    }
}
