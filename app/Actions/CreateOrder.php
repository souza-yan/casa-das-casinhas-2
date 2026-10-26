<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\SaleType;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
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

                $lines = app(CartService::class)->lines($cart);
                $products = Product::query()->whereIn('id', collect($lines)->pluck('product_id')->unique())->where('is_active', true)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                throw_if($products->count() !== collect($lines)->pluck('product_id')->unique()->count(), ValidationException::withMessages(['cart' => 'Um produto não está mais disponível.']));
                foreach ($lines as $line) {
                    $product = $products->get($line['product_id']);
                    if ($product->is_customizable && blank($line['personalization_description'])) {
                        throw ValidationException::withMessages(['cart' => "Informe a personalização de {$product->name} antes de finalizar."]);
                    }
                }
                foreach (collect($lines)->groupBy('product_id') as $productId => $productLines) {
                    $product = $products->get($productId);
                    if ($product->isStockProduct()) {
                        $quantity = $productLines->sum('quantity');
                        if ($quantity > $product->stock_quantity) {
                            throw ValidationException::withMessages(['cart' => "Estoque insuficiente para {$product->name}. Disponível: {$product->stock_quantity} unidade(s)."]);
                        }
                        $product->decrement('stock_quantity', $quantity);
                    }
                }
                $subtotal = collect($lines)->sum(fn (array $line) => round((float) $products->get($line['product_id'])->price * $line['quantity'], 2));
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
                foreach ($lines as $line) {
                    $product = $products->get($line['product_id']);
                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $line['quantity'],
                        'unit_price' => $product->price,
                        'subtotal' => round((float) $product->price * $line['quantity'], 2),
                        'personalization_description' => $product->isStockProduct() ? null : $line['personalization_description'],
                        'reference_paths' => $product->isStockProduct() ? [] : $line['reference_paths'],
                        'production_days' => $product->production_days,
                        'sale_type' => $product->sale_type ?? SaleType::ON_DEMAND,
                    ]);
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
