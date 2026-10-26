<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Storage;

class AdminOrderItemReferenceController extends Controller
{
    public function __invoke(Order $order, OrderItem $item, int $reference)
    {
        abort_unless(auth()->user()?->role === Role::ADMIN, 403);
        abort_unless($item->order_id === $order->id, 404);

        $path = $item->reference_paths[$reference] ?? null;
        abort_unless(is_string($path) && str_starts_with($path, 'personalizations/') && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
