<?php

use App\Http\Controllers\AsaasWebhookController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Livewire\Admin\Orders\Index as AdminOrdersIndex;
use App\Livewire\Admin\Products\Index as ProductsIndex;
use App\Livewire\Cart;
use App\Livewire\Checkout;
use App\Livewire\Orders\Index as OrdersIndex;
use App\Livewire\Orders\Show as OrderShow;
use App\Livewire\ProductDetail;
use App\Livewire\Store;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/loja', Store::class)->name('store');
Route::get('/produtos/{product:slug}', ProductDetail::class)->name('products.show');
Route::get('/carrinho', Cart::class)->name('cart');
Route::middleware('auth')->group(function (): void {
    Route::get('/checkout', Checkout::class)->name('checkout');
    Route::get('/pedidos', OrdersIndex::class)->name('orders.index');
    Route::get('/pedidos/{order}', OrderShow::class)->name('orders.show');
});

Route::view('/dashboard', 'dashboard')
    ->middleware('auth')
    ->name('dashboard');

Route::view('/admin', 'admin.dashboard')
    ->middleware(['auth', 'admin'])
    ->name('admin.dashboard');

Route::get('/admin/produtos', ProductsIndex::class)
    ->middleware(['auth', 'admin'])
    ->name('admin.products.index');
Route::get('/admin/pedidos', AdminOrdersIndex::class)->middleware(['auth', 'admin'])->name('admin.orders.index');

Route::post('/webhooks/asaas', AsaasWebhookController::class)
    ->middleware('throttle:60,1')
    ->withoutMiddleware(ValidateCsrfToken::class)
    ->name('webhooks.asaas');

Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify'])->name('webhooks.whatsapp.verify');
Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'update'])
    ->withoutMiddleware(ValidateCsrfToken::class)
    ->name('webhooks.whatsapp.update');
