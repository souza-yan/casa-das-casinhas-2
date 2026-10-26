<?php

namespace App\Http\Responses;

use App\Enums\Role;
use App\Services\GuestOrderAccessService;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        app(GuestOrderAccessService::class)->claimVerifiedOrderFor($request->user());

        $route = $request->user()->role === Role::ADMIN
            ? 'admin.dashboard'
            : 'home';

        return redirect()->route($route);
    }
}
