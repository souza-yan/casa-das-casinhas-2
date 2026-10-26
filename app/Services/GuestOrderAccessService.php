<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
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

    public function verifyEmail(Order $order, string $token): bool
    {
        $isValid = $order->user_id === null
            && $order->guest_email_verification_expires_at?->isFuture()
            && filled($order->guest_email_verification_token_hash)
            && hash_equals($order->guest_email_verification_token_hash, hash('sha256', $token));

        if (! $isValid) {
            return false;
        }

        $order->update(['guest_email_verified_at' => $order->guest_email_verified_at ?? now()]);
        $this->grantSessionAccess($order);
        session()->put('guest-order-claim', $order->getKey());

        return true;
    }

    public function claimVerifiedOrderFor(User $user): void
    {
        $orderId = session('guest-order-claim');
        if (! $orderId) {
            return;
        }

        $order = Order::find($orderId);
        if ($order
            && $order->user_id === null
            && $order->guest_email_verified_at
            && Str::lower($order->customer_email) === Str::lower($user->email)) {
            $order->update(['user_id' => $user->id]);
        }

        session()->forget('guest-order-claim');
    }

    private function sessionKey(Order $order): string
    {
        return 'guest-order-access.'.$order->getKey();
    }
}
