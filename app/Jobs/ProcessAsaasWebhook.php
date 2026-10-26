<?php

namespace App\Jobs;

use App\Enums\NotificationStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\OrderNotification;
use App\Models\Payment;
use App\Models\WebhookEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProcessAsaasWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public WebhookEvent $webhookEvent) {}

    public function handle(): void
    {
        DB::transaction(function (): void {
            $event = WebhookEvent::lockForUpdate()->findOrFail($this->webhookEvent->id);
            if ($event->status === 'processed') {
                return;
            }
            $payment = Payment::where('provider', 'asaas')->where('provider_payment_id', data_get($event->payload, 'payment.id'))->lockForUpdate()->firstOrFail();
            $amount = (float) data_get($event->payload, 'payment.value');
            throw_unless(abs($amount - (float) $payment->amount) < 0.01, RuntimeException::class, 'Valor do pagamento divergente.');
            $status = match ($event->event_type) {
                'PAYMENT_CONFIRMED', 'PAYMENT_RECEIVED' => PaymentStatus::CONFIRMED,
                'PAYMENT_OVERDUE' => PaymentStatus::OVERDUE,
                'PAYMENT_REFUNDED' => PaymentStatus::REFUNDED,
                'PAYMENT_CHARGEBACK_REQUESTED' => PaymentStatus::CHARGEBACK,
                default => null,
            };
            if ($status) {
                $terminal = in_array($payment->status, [PaymentStatus::REFUNDED, PaymentStatus::CHARGEBACK], true);
                if (! ($terminal && $status === PaymentStatus::CONFIRMED)) {
                    $payment->update(['status' => $status, 'confirmed_at' => $status === PaymentStatus::CONFIRMED ? ($payment->confirmed_at ?? now()) : $payment->confirmed_at]);
                }
                if (! $terminal && $status === PaymentStatus::CONFIRMED && $payment->order->status === OrderStatus::PENDING_PAYMENT) {
                    $payment->order->load('items');
                    $payment->order->update([
                        'status' => $payment->order->requiresPersonalizationApproval()
                            ? OrderStatus::AWAITING_PERSONALIZATION_APPROVAL
                            : OrderStatus::PAID,
                        'paid_at' => now(),
                    ]);
                    $recipient = (string) config('services.whatsapp.recipient');
                    if ($recipient !== '') {
                        $notification = OrderNotification::firstOrCreate(
                            ['order_id' => $payment->order_id, 'type' => 'paid_order', 'channel' => 'whatsapp', 'recipient' => $recipient],
                            ['status' => NotificationStatus::PENDING],
                        );
                        if ($notification->wasRecentlyCreated) {
                            SendWhatsAppNotification::dispatch($notification)->afterCommit();
                        }
                    }
                }
            }
            $event->update(['payment_id' => $payment->id, 'status' => 'processed', 'processed_at' => now()]);
        });
    }
}
