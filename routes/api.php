<?php

use App\Http\Controllers\AsaasWebhookController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['webhook.json', 'throttle:60,1'])->group(function (): void {
    Route::post('/webhooks/asaas', AsaasWebhookController::class)->name('webhooks.asaas');
    Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'update'])->name('webhooks.whatsapp.update');
});

Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify'])->name('webhooks.whatsapp.verify');
