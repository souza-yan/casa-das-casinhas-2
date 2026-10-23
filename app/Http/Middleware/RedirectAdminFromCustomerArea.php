<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAdminFromCustomerArea
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role === Role::ADMIN) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
