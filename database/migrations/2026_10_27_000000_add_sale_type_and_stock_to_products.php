<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sale_type')->default('on_demand');
            $table->unsignedInteger('stock_quantity')->default(0);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('sale_type')->default('on_demand');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn('sale_type'));
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn(['sale_type', 'stock_quantity']));
    }
};
