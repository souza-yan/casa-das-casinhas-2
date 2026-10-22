<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->role === Role::ADMIN || $order->user_id === $user->id;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }
}
