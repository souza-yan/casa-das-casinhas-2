<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->boolean('is_customizable')->default(false)->after('production_days');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->text('personalization_description')->nullable()->after('subtotal');
            $table->json('reference_paths')->nullable()->after('personalization_description');
            $table->unsignedSmallInteger('production_days')->default(15)->after('reference_paths');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn(['personalization_description', 'reference_paths', 'production_days']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('is_customizable');
        });
    }
};
