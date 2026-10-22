<?php

namespace App\Http\Controllers;

use App\Enums\NotificationStatus;
use App\Models\OrderNotification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request): Response
    {
        abort_unless($request->query('hub.mode') === 'subscribe' && hash_equals((string) config('services.whatsapp.verify_token'), (string) $request->query('hub.verify_token')), 403);

        return response((string) $request->query('hub.challenge'));
    }

    public function update(Request $request): Response
    {
        $secret = (string) config('services.whatsapp.app_secret');
        $signature = (string) $request->header('X-Hub-Signature-256');
        abort_if($secret === '' || ! hash_equals('sha256='.hash_hmac('sha256', $request->getContent(), $secret), $signature), 401);
        foreach ((array) data_get($request->all(), 'entry.0.changes.0.value.statuses', []) as $status) {
            $notification = OrderNotification::where('provider_message_id', $status['id'] ?? null)->first();
            if (! $notification) {
                continue;
            }
            if (($status['status'] ?? null) === 'delivered') {
                $notification->update(['status' => NotificationStatus::DELIVERED, 'delivered_at' => now()]);
            }
            if (($status['status'] ?? null) === 'failed') {
                $notification->update(['status' => NotificationStatus::FAILED, 'last_error' => data_get($status, 'errors.0.title', 'Falha informada pela Meta')]);
            }
        }

        return response('EVENT_RECEIVED');
    }
}
