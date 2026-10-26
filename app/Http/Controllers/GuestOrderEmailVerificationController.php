<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\GuestOrderAccessService;
use Illuminate\Http\RedirectResponse;

class GuestOrderEmailVerificationController extends Controller
{
    public function __invoke(Order $order, string $token, GuestOrderAccessService $access): RedirectResponse
    {
        abort_unless($access->verifyEmail($order, $token), 404);

        return redirect()
            ->route('guest.orders.show', $order)
            ->with('status', 'E-mail confirmado. Agora você pode criar uma conta para acompanhar este pedido.');
    }
}
