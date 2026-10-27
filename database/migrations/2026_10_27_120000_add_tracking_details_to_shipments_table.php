<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table): void {
            $table->text('label_url')->nullable()->after('tracking_code');
            $table->text('tracking_url')->nullable()->after('label_url');
            $table->string('provider_status')->nullable()->index()->after('tracking_url');
            $table->timestamp('tracking_synced_at')->nullable()->after('provider_status');
            $table->timestamp('posted_at')->nullable()->after('tracking_synced_at');
            $table->timestamp('delivered_at')->nullable()->after('posted_at');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table): void {
            $table->dropIndex(['provider_status']);
            $table->dropColumn(['label_url', 'tracking_url', 'provider_status', 'tracking_synced_at', 'posted_at', 'delivered_at']);
        });
    }
};
