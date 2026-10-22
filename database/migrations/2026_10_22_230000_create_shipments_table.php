<?php

use App\Enums\ShipmentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->restrictOnDelete();
            $table->string('provider');
            $table->string('provider_shipment_id')->nullable();
            $table->string('service_id');
            $table->string('carrier');
            $table->decimal('price', 12, 2);
            $table->string('tracking_code')->nullable();
            $table->string('status')->default(ShipmentStatus::QUOTED->value)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
