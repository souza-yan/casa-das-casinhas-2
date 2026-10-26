<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateOrder
{
    public function execute(User $user, array $cart, array $address, string $phone, float $shippingTotal): Order
    {
        return $this->create($user, $cart, [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $phone,
        ], $address, $shippingTotal);
    }

    public function create(?User $user, array $cart, array $customer, array $address, float $shippingTotal, ?string $checkoutToken = null, ?string $guestAccessToken = null, ?string $guestEmailVerificationToken = null): Order
    {
        throw_if($cart === [], ValidationException::withMessages(['cart' => 'O carrinho está vazio.']));
        throw_if($shippingTotal < 0, ValidationException::withMessages(['shipping' => 'Frete inválido.']));
        Validator::make($customer, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
        ])->validate();
        Validator::make($address, [
            'zip_code' => ['required', 'string', 'max:9'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
        ])->validate();
        throw_if($user === null && (blank($guestAccessToken) || blank($guestEmailVerificationToken)), ValidationException::withMessages(['checkout' => 'Não foi possível preparar o acesso seguro ao pedido.']));

        if ($checkoutToken && ($existing = Order::where('checkout_token', $checkoutToken)->first())) {
            return $existing->load(['items', 'address']);
        }

        try {
            return DB::transaction(function () use ($user, $cart, $customer, $address, $shippingTotal, $checkoutToken, $guestAccessToken, $guestEmailVerificationToken): Order {
                if ($checkoutToken && ($existing = Order::where('checkout_token', $checkoutToken)->lockForUpdate()->first())) {
                    return $existing->load(['items', 'address']);
                }

                $products = Product::query()->whereIn('id', array_keys($cart))->where('is_active', true)->lockForUpdate()->get()->keyBy('id');
                throw_if($products->count() !== count($cart), ValidationException::withMessages(['cart' => 'Um produto não está mais disponível.']));
                $subtotal = $products->sum(fn (Product $p) => round((float) $p->price * max(1, (int) $cart[$p->id]), 2));
                $order = Order::create([
                    'user_id' => $user?->id,
                    'customer_name' => $user?->name ?? $customer['name'],
                    'customer_email' => $user?->email ?? $customer['email'],
                    'customer_phone' => $customer['phone'],
                    'checkout_token' => $checkoutToken,
                    'guest_access_token_hash' => $user ? null : hash('sha256', $guestAccessToken),
                    'guest_access_expires_at' => $user ? null : now()->addDays(30),
                    'guest_email_verification_token_hash' => $user ? null : hash('sha256', $guestEmailVerificationToken),
                    'guest_email_verification_expires_at' => $user ? null : now()->addDays(7),
                    'subtotal' => $subtotal,
                    'shipping_total' => $shippingTotal,
                    'total' => round($subtotal + $shippingTotal, 2),
                    'status' => OrderStatus::PENDING_PAYMENT,
                ]);
                foreach ($products as $product) {
                    $quantity = max(1, (int) $cart[$product->id]);
                    $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => $quantity, 'unit_price' => $product->price, 'subtotal' => round((float) $product->price * $quantity, 2)]);
                }
                $order->address()->create($address);

                return $order->load(['items', 'address']);
            });
        } catch (UniqueConstraintViolationException $exception) {
            if ($checkoutToken && ($existing = Order::where('checkout_token', $checkoutToken)->first())) {
                return $existing->load(['items', 'address']);
            }

            throw $exception;
        }
    }
}
