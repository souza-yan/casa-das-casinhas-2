<?php

use App\Http\Controllers\AsaasWebhookController;
use App\Livewire\Admin\Products\Index as ProductsIndex;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/dashboard', 'dashboard')
    ->middleware('auth')
    ->name('dashboard');

Route::view('/admin', 'admin.dashboard')
    ->middleware(['auth', 'admin'])
    ->name('admin.dashboard');

Route::get('/admin/produtos', ProductsIndex::class)
    ->middleware(['auth', 'admin'])
    ->name('admin.products.index');

Route::post('/webhooks/asaas', AsaasWebhookController::class)
    ->middleware('throttle:60,1')
    ->withoutMiddleware(ValidateCsrfToken::class)
    ->name('webhooks.asaas');
