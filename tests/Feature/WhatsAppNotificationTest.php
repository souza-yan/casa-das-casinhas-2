<?php

namespace Tests\Feature;

use App\Actions\CreateOrder;
use App\Enums\NotificationStatus;
use App\Jobs\SendWhatsAppNotification;
use App\Models\OrderNotification;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_sends_approved_template_without_marking_as_delivered(): void
    {
        config(['services.whatsapp.token' => 'token', 'services.whatsapp.phone_number_id' => 'phone', 'services.whatsapp.recipient' => '5511999999999']);
        Http::fake(['*/phone/messages' => Http::response(['messages' => [['id' => 'wamid.1']]])]);
        $order = $this->orderForNotification();
        $notification = OrderNotification::create(['order_id' => $order->id, 'type' => 'paid_order', 'channel' => 'whatsapp', 'recipient' => '5511999999999', 'status' => NotificationStatus::PENDING]);
        app()->call([new SendWhatsAppNotification($notification), 'handle']);
        $notification->refresh();
        $this->assertSame(NotificationStatus::SENT, $notification->status);
        $this->assertNull($notification->delivered_at);
        $this->assertSame('wamid.1', $notification->provider_message_id);
    }

    private function orderForNotification()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        return app(CreateOrder::class)->execute($user, [$product->id => 1], ['zip_code' => '01001-000', 'street' => 'Rua A', 'number' => '1', 'district' => 'Centro', 'city' => 'São Paulo', 'state' => 'SP'], '11999999999', 10);
    }
}
