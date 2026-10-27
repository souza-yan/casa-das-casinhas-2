<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessSuperFreteWebhook;
use App\Models\WebhookEvent;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuperFreteWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = (string) config('services.superfrete.webhook_secret');
        $provided = preg_replace('/^sha256=/i', '', (string) $request->header('X-ME-Signature'));
        $expected = hash_hmac('sha256', $request->getContent(), $secret);
        abort_if($secret === '' || $provided === '' || ! hash_equals($expected, $provided), 401);

        $data = $request->validate([
            'event' => ['required', 'string', 'in:order.created,order.released,order.generated,order.posted,order.delivered,order.cancelled'],
            'data.id' => ['required', 'string', 'max:255'],
            'data.status' => ['nullable', 'string', 'max:100'],
            'data.tracking' => ['nullable', 'string', 'max:255'],
            'data.tracking_url' => ['nullable', 'string', 'max:2048'],
        ]);
        $eventId = hash('sha256', implode('|', [
            $data['event'],
            data_get($data, 'data.id'),
            data_get($data, 'data.updated_at', data_get($data, 'data.delivered_at', data_get($data, 'data.posted_at', data_get($data, 'data.generated_at', '')))),
        ]));

        try {
            $event = WebhookEvent::create([
                'provider' => 'superfrete',
                'external_event_id' => $eventId,
                'event_type' => $data['event'],
                'payload' => $request->all(),
                'status' => 'pending',
            ]);
            ProcessSuperFreteWebhook::dispatch($event)->afterCommit();
        } catch (UniqueConstraintViolationException) {
            return response()->json(['received' => true, 'duplicate' => true]);
        }

        return response()->json(['received' => true]);
    }
}
