<?php

namespace App\Livewire\GuestOrders;

use App\Mail\GuestOrderAccessLink;
use App\Models\Order;
use App\Services\GuestOrderAccessService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.public')]
class Lookup extends Component
{
    public string $orderNumber = '';

    public string $email = '';

    public bool $requestCompleted = false;

    public function sendLink(GuestOrderAccessService $access): void
    {
        $this->orderNumber = ltrim(trim($this->orderNumber), '# ');
        $this->email = mb_strtolower(trim($this->email));

        $validated = $this->validate([
            'orderNumber' => ['required', 'integer', 'min:1'],
            'email' => ['required', 'email:rfc', 'max:255'],
        ], [
            'orderNumber.required' => 'Informe o número do pedido.',
            'orderNumber.integer' => 'Informe apenas o número do pedido.',
            'orderNumber.min' => 'Informe um número de pedido válido.',
            'email.required' => 'Informe o e-mail utilizado na compra.',
            'email.email' => 'Informe um endereço de e-mail válido.',
        ]);

        $requestKey = 'guest-order-lookup:'.hash('sha256', $validated['orderNumber'].'|'.$validated['email'].'|'.request()->ip());
        $ipKey = 'guest-order-lookup-ip:'.request()->ip();

        if (! RateLimiter::tooManyAttempts($requestKey, 1) && ! RateLimiter::tooManyAttempts($ipKey, 10)) {
            RateLimiter::hit($requestKey, 60);
            RateLimiter::hit($ipKey, 600);
            $this->emailAccessLink((int) $validated['orderNumber'], $validated['email'], $access);
        }

        $this->requestCompleted = true;
        $this->reset('orderNumber', 'email');
    }

    private function emailAccessLink(int $orderNumber, string $email, GuestOrderAccessService $access): void
    {
        $order = Order::query()
            ->whereKey($orderNumber)
            ->whereNull('user_id')
            ->whereRaw('LOWER(customer_email) = ?', [$email])
            ->first();

        if (! $order) {
            return;
        }

        $token = $access->makeToken();
        $previousHash = $order->guest_access_token_hash;
        $previousExpiration = $order->guest_access_expires_at;
        $order->update([
            'guest_access_token_hash' => hash('sha256', $token),
            'guest_access_expires_at' => now()->addDays(180),
        ]);

        try {
            Mail::to($order->customer_email)->send(new GuestOrderAccessLink($order, $token));
        } catch (Throwable $exception) {
            $order->update([
                'guest_access_token_hash' => $previousHash,
                'guest_access_expires_at' => $previousExpiration,
            ]);
            report($exception);
        }
    }

    public function render()
    {
        return view('livewire.guest-orders.lookup');
    }
}
