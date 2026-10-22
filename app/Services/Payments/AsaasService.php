<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AsaasService
{
    public function createCharge(Order $order): array
    {
        $customer = $this->client()->post('/v3/customers', ['name' => $order->customer_name, 'email' => $order->customer_email, 'externalReference' => 'user:'.$order->user_id])->throw()->json();

        return $this->client()->withHeaders(['asaas-idempotency-key' => 'order-'.$order->id])->post('/v3/payments', [
            'customer' => $customer['id'], 'billingType' => 'UNDEFINED', 'value' => (float) $order->total,
            'dueDate' => now()->addDays(3)->toDateString(), 'description' => 'Pedido #'.$order->id,
            'externalReference' => (string) $order->id,
        ])->throw()->json();
    }

    public function findCharge(string $id): array
    {
        return $this->client()->get('/v3/payments/'.$id)->throw()->json();
    }

    private function client(): PendingRequest
    {
        $token = config('services.asaas.token');
        throw_if(blank($token), RuntimeException::class, 'Credencial do Asaas Sandbox não configurada.');

        return Http::baseUrl(rtrim(config('services.asaas.url'), '/'))->acceptJson()->withHeader('access_token', $token)->timeout(15)->retry(2, 250);
    }
}
