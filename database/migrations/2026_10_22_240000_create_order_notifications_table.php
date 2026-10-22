<?php

use App\Enums\NotificationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->string('type');
            $table->string('channel');
            $table->string('recipient');
            $table->string('provider_message_id')->nullable()->index();
            $table->string('status')->default(NotificationStatus::PENDING->value)->index();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->unique(['order_id', 'type', 'channel', 'recipient'], 'order_notification_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_notifications');
    }
};
