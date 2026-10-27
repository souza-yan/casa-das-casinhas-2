<?php

namespace App\Jobs;

use App\Enums\NotificationStatus;
use App\Mail\OrderTrackingAvailable;
use App\Models\OrderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendOrderTrackingEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(public OrderNotification $notification) {}

    public function handle(): void
    {
        $notification = $this->notification->fresh();
        if ($notification->status === NotificationStatus::SENT) {
            return;
        }

        $notification->increment('attempts');

        try {
            $order = $notification->order()->with('shipment')->firstOrFail();
            Mail::to($notification->recipient)->send(new OrderTrackingAvailable($order));
            $notification->update(['status' => NotificationStatus::SENT, 'sent_at' => now(), 'last_error' => null]);
        } catch (Throwable $exception) {
            $notification->update(['status' => NotificationStatus::FAILED, 'last_error' => mb_substr($exception->getMessage(), 0, 2000)]);
            throw $exception;
        }
    }
}
