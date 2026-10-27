<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class AsaasService
{
    public function createCharge(Order $order, ?string $successUrl = null): array
    {
        try {
            $order->loadMissing(['user', 'address']);
            $document = preg_replace('/\D/', '', (string) $order->address?->document);
            throw_if(! in_array(strlen($document), [11, 14], true), RuntimeException::class, 'CPF ou CNPJ do cliente é obrigatório para criar a cobrança no Asaas.');

            $customerId = $order->user?->asaas_customer_id ?? $order->asaas_customer_id;
            if (! $customerId) {
                $customer = $this->client()->post('/v3/customers', [
                    'name' => $order->customer_name,
                    'email' => $order->customer_email,
                    'cpfCnpj' => $document,
                    'externalReference' => 'order:'.$order->id,
                ])->throw()->json();
                $customerId = $customer['id'];
                if ($order->user) {
                    $order->user->update(['asaas_customer_id' => $customerId]);
                } else {
                    $order->update(['asaas_customer_id' => $customerId]);
                }
            } else {
                // Pedidos criados antes desta validação podem apontar para um cliente sem CPF/CNPJ no Asaas.
                $this->client()->put('/v3/customers/'.$customerId, ['cpfCnpj' => $document])->throw();
            }

            $payload = [
                'customer' => $customerId, 'billingType' => 'UNDEFINED', 'value' => (float) $order->total,
                'dueDate' => now()->addDays(3)->toDateString(), 'description' => 'Pedido #'.$order->id,
                'externalReference' => (string) $order->id,
            ];

            if (filled($successUrl)) {
                $payload['callback'] = ['successUrl' => $successUrl, 'autoRedirect' => true];
            }

            try {
                return $this->postCharge($order, $payload);
            } catch (RequestException $exception) {
                // O callback é opcional. Em Sandbox, o Asaas rejeita a cobrança caso o
                // domínio do successUrl ainda não tenha sido cadastrado na conta.
                // Neste caso a fatura hospedada continua segura e pode ser aberta sem ele.
                if (! isset($payload['callback']) || ! $this->isUnconfiguredDomainError($exception)) {
                    throw $exception;
                }

                unset($payload['callback']);

                Log::warning('Cobrança do Asaas criada sem callback por domínio não configurado.', [
                    'order_id' => $order->id,
                    'http_status' => $exception->response->status(),
                ]);

                return $this->postCharge($order, $payload);
            }
        } catch (Throwable $exception) {
            $this->logFailure($order, $exception);

            throw $exception;
        }
    }

    public function findCharge(string $id): array
    {
        return $this->client()->get('/v3/payments/'.$id)->throw()->json();
    }

    public function deleteCharge(string $id): void
    {
        $this->client()->delete('/v3/payments/'.$id)->throw();
    }

    public function logFailure(Order $order, Throwable $exception): void
    {
        $errors = $exception instanceof RequestException
            ? collect($exception->response->json('errors', []))->map(fn (array $error) => [
                'code' => $error['code'] ?? null,
                'description' => $error['description'] ?? null,
            ])->values()->all()
            : [];

        Log::warning('Falha na cobrança do Asaas.', [
            'order_id' => $order->id,
            'http_status' => $exception instanceof RequestException ? $exception->response->status() : null,
            'errors' => $errors,
            'communication_error' => $exception instanceof ConnectionException,
        ]);
    }

    public static function clientMessage(Throwable $exception): string
    {
        if ($exception instanceof ConnectionException || ($exception instanceof RequestException && $exception->response->serverError())) {
            return 'Não foi possível comunicar com o Asaas. Tente novamente em instantes.';
        }

        $descriptions = $exception instanceof RequestException
            ? strtolower(collect($exception->response->json('errors', []))->pluck('description')->implode(' '))
            : strtolower($exception->getMessage());

        if (str_contains($descriptions, 'cpf') || str_contains($descriptions, 'cnpj') || str_contains($descriptions, 'documento')) {
            return 'Informe um CPF ou CNPJ válido.';
        }

        if (str_contains($descriptions, 'valor') && (str_contains($descriptions, 'mínimo') || str_contains($descriptions, 'minimo'))) {
            return 'O valor do pedido precisa ser de pelo menos R$ 5,00.';
        }

        return 'Não foi possível abrir o pagamento agora. Tente novamente.';
    }

    private function client(): PendingRequest
    {
        $token = config('services.asaas.token');
        throw_if(blank($token), RuntimeException::class, 'Credencial do Asaas Sandbox não configurada.');

        return Http::baseUrl(rtrim(config('services.asaas.url'), '/'))->acceptJson()->withHeader('access_token', $token)->withUserAgent(config('services.asaas.user_agent'))->timeout(15)->retry(2, 250);
    }

    private function postCharge(Order $order, array $payload): array
    {
        return $this->client()
            ->withHeaders(['asaas-idempotency-key' => 'order-'.$order->id])
            ->post('/v3/payments', $payload)
            ->throw()
            ->json();
    }

    private function isUnconfiguredDomainError(RequestException $exception): bool
    {
        return $exception->response->status() === 400
            && str_contains(
                strtolower((string) collect($exception->response->json('errors', []))->pluck('description')->implode(' ')),
                'domínio configurado'
            );
    }
}
