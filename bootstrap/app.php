<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureWebhookJsonResponse;
use App\Http\Middleware\RedirectAdminFromCustomerArea;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: '',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'customer-area' => RedirectAdminFromCustomerArea::class,
            'webhook.json' => EnsureWebhookJsonResponse::class,
        ]);

        // Nesta fase do bootstrap o repositório de configuração ainda não está disponível.
        if (env('APP_ENV') === 'local') {
            $middleware->trustProxies(
                at: '*',
                headers: Request::HEADER_X_FORWARDED_FOR
                    | Request::HEADER_X_FORWARDED_HOST
                    | Request::HEADER_X_FORWARDED_PROTO
                    | Request::HEADER_X_FORWARDED_PORT,
            );
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
