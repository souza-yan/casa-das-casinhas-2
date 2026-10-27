<?php

namespace Tests\Feature;

use App\Actions\CreateOrder;
use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Jobs\SyncSuperFreteTracking;
use App\Mail\OrderTrackingAvailable;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Shipping\SuperFreteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
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

    public function test_tracking_code_can_be_synchronized_after_label_processing(): void
    {
        Mail::fake();
        Http::fake(['*/api/v0/order/info/label_1' => Http::response(['tracking' => 'AB123456789BR'])]);
        $shipment = $this->shipment(['provider_shipment_id' => 'label_1']);

        $trackingCode = app(SuperFreteService::class)->syncTracking($shipment);

        $this->assertSame('AB123456789BR', $trackingCode);
        $this->assertSame('AB123456789BR', $shipment->refresh()->tracking_code);
        Mail::assertSent(OrderTrackingAvailable::class, fn (OrderTrackingAvailable $mail) => $mail->order->is($shipment->order));
    }

    public function test_print_url_is_requested_and_saved_for_the_admin(): void
    {
        Http::fake(['*/api/v0/tag/print' => Http::response(['url' => 'https://sandbox.superfrete.com/label/test.pdf'])]);
        $shipment = $this->shipment(['provider_shipment_id' => 'label_1']);

        $url = app(SuperFreteService::class)->syncLabelUrl($shipment);

        $this->assertSame('https://sandbox.superfrete.com/label/test.pdf', $url);
        $this->assertSame($url, $shipment->refresh()->label_url);
    }

    public function test_provider_status_updates_shipping_and_order_timeline(): void
    {
        Mail::fake();
        $shipment = $this->shipment(['provider_shipment_id' => 'label_1']);
        $shipment->order->update(['status' => OrderStatus::READY_TO_SHIP]);

        app(SuperFreteService::class)->applyProviderData($shipment, [
            'status' => 'posted',
            'tracking' => 'AB123456789BR',
            'tracking_url' => 'rastreio.superfrete.com/#/tracking/1',
            'posted_at' => now()->toIso8601String(),
        ]);

        $this->assertSame(ShipmentStatus::SHIPPED, $shipment->refresh()->status);
        $this->assertSame(OrderStatus::SHIPPED, $shipment->order->refresh()->status);
        $this->assertNotNull($shipment->posted_at);
    }

    public function test_hourly_command_queues_shipments_still_waiting_for_tracking(): void
    {
        Queue::fake();
        $shipment = $this->shipment(['provider_shipment_id' => 'label_1']);

        $this->artisan('shipping:sync-tracking')->assertSuccessful();

        Queue::assertPushed(SyncSuperFreteTracking::class, fn ($job) => $job->shipmentId === $shipment->id);
    }

    private function shipment(array $attributes = []): Shipment
    {
        $order = app(CreateOrder::class)->execute(
            User::factory()->create(),
            [Product::factory()->create()->id => 1],
            ['zip_code' => '01001-000', 'street' => 'Rua A', 'number' => '1', 'district' => 'Centro', 'city' => 'São Paulo', 'state' => 'SP'],
            '11999999999',
            10,
        );

        return $order->shipment()->create(array_merge([
            'provider' => 'superfrete',
            'service_id' => '1',
            'carrier' => 'Correios',
            'price' => 10,
            'status' => ShipmentStatus::LABEL_PURCHASED,
        ], $attributes));
    }
}
