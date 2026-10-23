<?php

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Http\Controllers\AsaasWebhookController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Livewire\Admin\Orders\Index as AdminOrdersIndex;
use App\Livewire\Admin\Orders\Show as AdminOrderShow;
use App\Livewire\Admin\Products\Index as ProductsIndex;
use App\Livewire\Cart;
use App\Livewire\Checkout;
use App\Livewire\Orders\Index as OrdersIndex;
use App\Livewire\Orders\Payment as OrderPayment;
use App\Livewire\Orders\Show as OrderShow;
use App\Livewire\ProductDetail;
use App\Livewire\Store;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'featuredProducts' => Product::where('is_active', true)->latest()->take(4)->get(),
        'heroProduct' => Product::where('is_active', true)->where('slug', 'casas-da-vila')->first(),
    ]);
})->name('home');

Route::get('/loja', Store::class)->name('store');
Route::get('/produtos/{product:slug}', ProductDetail::class)->name('products.show');
Route::get('/carrinho', Cart::class)->middleware('customer-area')->name('cart');
Route::middleware(['auth', 'customer-area'])->group(function (): void {
    Route::get('/checkout', Checkout::class)->name('checkout');
    Route::get('/pedidos', OrdersIndex::class)->name('orders.index');
    Route::get('/pedidos/{order}', OrderShow::class)->name('orders.show');
    Route::get('/pedidos/{order}/pagamento', OrderPayment::class)->name('orders.payment');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->role === Role::ADMIN) {
        return redirect()->route('admin.dashboard');
    }

    return view('dashboard', [
        'ordersCount' => $user->orders()->count(),
        'latestOrders' => $user->orders()->latest()->take(3)->get(),
    ]);
})->middleware('auth')->name('dashboard');

Route::get('/admin', fn () => view('admin.dashboard', ['indicators' => [
    'products' => Product::where('is_active', true)->count(),
    'orders' => Order::count(),
    'pending' => Order::where('status', OrderStatus::PENDING_PAYMENT)->count(),
]]))->middleware(['auth', 'admin'])->name('admin.dashboard');

Route::get('/admin/produtos', ProductsIndex::class)
    ->middleware(['auth', 'admin'])
    ->name('admin.products.index');
Route::get('/admin/pedidos', AdminOrdersIndex::class)->middleware(['auth', 'admin'])->name('admin.orders.index');
Route::get('/admin/pedidos/{order}', AdminOrderShow::class)->middleware(['auth', 'admin'])->name('admin.orders.show');

Route::post('/webhooks/asaas', AsaasWebhookController::class)
    ->middleware('throttle:60,1')
    ->withoutMiddleware(ValidateCsrfToken::class)
    ->name('webhooks.asaas');

Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify'])->name('webhooks.whatsapp.verify');
Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'update'])
    ->withoutMiddleware(ValidateCsrfToken::class)
    ->name('webhooks.whatsapp.update');
