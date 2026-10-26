<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 30)->nullable()->after('asaas_customer_id');
            $table->string('zip_code', 9)->nullable()->after('phone');
            $table->string('street')->nullable()->after('zip_code');
            $table->string('number', 30)->nullable()->after('street');
            $table->string('complement')->nullable()->after('number');
            $table->string('district')->nullable()->after('complement');
            $table->string('city')->nullable()->after('district');
            $table->char('state', 2)->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'phone',
                'zip_code',
                'street',
                'number',
                'complement',
                'district',
                'city',
                'state',
            ]);
        });
    }
};
