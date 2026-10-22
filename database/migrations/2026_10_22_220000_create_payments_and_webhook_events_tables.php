<?php

use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->string('provider');
            $table->string('provider_payment_id')->nullable();
            $table->string('method');
            $table->decimal('amount', 12, 2);
            $table->string('status')->default(PaymentStatus::PENDING->value)->index();
            $table->text('payment_url')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_payment_id']);
            $table->index(['order_id', 'status']);
        });
        Schema::create('webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->string('provider');
            $table->string('external_event_id');
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type');
            $table->json('payload');
            $table->string('status')->default('pending')->index();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'external_event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('payments');
    }
};
