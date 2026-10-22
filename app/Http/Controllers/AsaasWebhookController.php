<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAsaasWebhook;
use App\Models\Payment;
use App\Models\WebhookEvent;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsaasWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $expected = (string) config('services.asaas.webhook_token');
        $provided = (string) $request->header('asaas-access-token');
        abort_if($expected === '' || ! hash_equals($expected, $provided), 401);
        $data = $request->validate(['id' => ['required', 'string', 'max:255'], 'event' => ['required', 'string'], 'payment.id' => ['required', 'string']]);
        try {
            $event = WebhookEvent::create(['provider' => 'asaas', 'external_event_id' => $data['id'], 'payment_id' => Payment::where('provider_payment_id', data_get($data, 'payment.id'))->value('id'), 'event_type' => $data['event'], 'payload' => $request->all(), 'status' => 'pending']);
            ProcessAsaasWebhook::dispatch($event)->afterCommit();
        } catch (UniqueConstraintViolationException) {
            return response()->json(['received' => true, 'duplicate' => true]);
        }

        return response()->json(['received' => true]);
    }
}
