<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateOrder
{
    public function execute(User $user, array $cart, array $address, string $phone, float $shippingTotal): Order
    {
        throw_if($cart === [], ValidationException::withMessages(['cart' => 'O carrinho está vazio.']));
        throw_if($shippingTotal < 0, ValidationException::withMessages(['shipping' => 'Frete inválido.']));

        return DB::transaction(function () use ($user, $cart, $address, $phone, $shippingTotal): Order {
            $products = Product::query()->whereIn('id', array_keys($cart))->where('is_active', true)->lockForUpdate()->get()->keyBy('id');
            throw_if($products->count() !== count($cart), ValidationException::withMessages(['cart' => 'Um produto não está mais disponível.']));
            $subtotal = $products->sum(fn (Product $p) => round((float) $p->price * max(1, (int) $cart[$p->id]), 2));
            $order = Order::create(['user_id' => $user->id, 'customer_name' => $user->name, 'customer_email' => $user->email, 'customer_phone' => $phone, 'subtotal' => $subtotal, 'shipping_total' => $shippingTotal, 'total' => round($subtotal + $shippingTotal, 2), 'status' => OrderStatus::PENDING_PAYMENT]);
            foreach ($products as $product) {
                $quantity = max(1, (int) $cart[$product->id]);
                $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => $quantity, 'unit_price' => $product->price, 'subtotal' => round((float) $product->price * $quantity, 2)]);
            }
            $order->address()->create($address);

            return $order->load(['items', 'address']);
        });
    }
}
