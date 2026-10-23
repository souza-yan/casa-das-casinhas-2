<?php

namespace App\Http\Responses;

use App\Enums\Role;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $route = $request->user()->role === Role::ADMIN
            ? 'admin.dashboard'
            : 'home';

        return redirect()->route($route);
    }
}
