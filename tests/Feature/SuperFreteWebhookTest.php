<?php

namespace Tests\Feature;

use App\Actions\CreateOrder;
use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperFreteWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_posted_event_updates_the_order_and_duplicate_is_idempotent(): void
    {
        config(['services.superfrete.webhook_secret' => 'webhook-secret']);
        $order = app(CreateOrder::class)->execute(
            User::factory()->create(),
            [Product::factory()->create()->id => 1],
            ['zip_code' => '01001-000', 'street' => 'Rua A', 'number' => '1', 'district' => 'Centro', 'city' => 'São Paulo', 'state' => 'SP'],
            '11999999999',
            10,
        );
        $order->update(['status' => OrderStatus::READY_TO_SHIP]);
        $order->shipment()->create([
            'provider' => 'superfrete',
            'provider_shipment_id' => 'label_1',
            'service_id' => '1',
            'carrier' => 'Correios',
            'price' => 10,
            'status' => ShipmentStatus::LABEL_PURCHASED,
        ]);
        $payload = [
            'event' => 'order.posted',
            'data' => [
                'id' => 'label_1',
                'status' => 'posted',
                'tracking' => 'AB123456789BR',
                'posted_at' => '2026-10-27T12:00:00+00:00',
            ],
        ];
        $signature = hash_hmac('sha256', json_encode($payload), 'webhook-secret');

        $this->withHeader('X-ME-Signature', $signature)->postJson(route('webhooks.superfrete'), $payload)->assertOk();
        $this->withHeader('X-ME-Signature', $signature)->postJson(route('webhooks.superfrete'), $payload)->assertOk()->assertJson(['duplicate' => true]);

        $this->assertSame(OrderStatus::SHIPPED, $order->refresh()->status);
        $this->assertSame(ShipmentStatus::SHIPPED, $order->shipment->refresh()->status);
        $this->assertDatabaseCount('webhook_events', 1);
    }

    public function test_webhook_rejects_an_invalid_signature(): void
    {
        config(['services.superfrete.webhook_secret' => 'webhook-secret']);

        $this->withHeader('X-ME-Signature', 'invalid')
            ->postJson(route('webhooks.superfrete'), ['event' => 'order.posted', 'data' => ['id' => 'label_1']])
            ->assertUnauthorized();
    }
}
