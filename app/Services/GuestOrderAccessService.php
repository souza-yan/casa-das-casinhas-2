<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class GuestOrderAccessService
{
    public function makeToken(): string
    {
        return Str::random(64);
    }

    public function grantSessionAccess(Order $order): void
    {
        session()->put($this->sessionKey($order), true);
    }

    public function hasSessionAccess(Order $order): bool
    {
        return (bool) session($this->sessionKey($order));
    }

    public function allowsToken(Order $order, string $token): bool
    {
        return $order->user_id === null
            && $order->guest_access_expires_at?->isFuture()
            && filled($order->guest_access_token_hash)
            && hash_equals($order->guest_access_token_hash, hash('sha256', $token));
    }

    private function sessionKey(Order $order): string
    {
        return 'guest-order-access.'.$order->getKey();
    }
}
