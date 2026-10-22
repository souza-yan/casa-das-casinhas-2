<?php

namespace App\Jobs;

use App\Enums\NotificationStatus;
use App\Models\OrderNotification;
use App\Services\Notifications\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendWhatsAppNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(public OrderNotification $notification) {}

    public function handle(WhatsAppService $whatsApp): void
    {
        $notification = $this->notification->fresh();
        if ($notification->status === NotificationStatus::DELIVERED) {
            return;
        }
        $notification->increment('attempts');
        try {
            $messageId = $whatsApp->sendPaidOrder($notification->order()->with('items.product')->firstOrFail(), $notification->recipient);
            $notification->update(['provider_message_id' => $messageId, 'status' => NotificationStatus::SENT, 'sent_at' => now(), 'last_error' => null]);
        } catch (Throwable $exception) {
            $notification->update(['status' => NotificationStatus::FAILED, 'last_error' => mb_substr($exception->getMessage(), 0, 2000)]);
            throw $exception;
        }
    }
}
