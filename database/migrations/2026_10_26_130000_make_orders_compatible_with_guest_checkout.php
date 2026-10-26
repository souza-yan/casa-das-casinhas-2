<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('asaas_customer_id')->nullable()->after('customer_phone');
            $table->string('checkout_token', 64)->nullable()->unique()->after('asaas_customer_id');
            $table->string('guest_access_token_hash', 64)->nullable()->unique()->after('checkout_token');
            $table->timestamp('guest_access_expires_at')->nullable()->after('guest_access_token_hash');
            $table->string('guest_email_verification_token_hash', 64)->nullable()->unique()->after('guest_access_expires_at');
            $table->timestamp('guest_email_verification_expires_at')->nullable()->after('guest_email_verification_token_hash');
            $table->timestamp('guest_email_verified_at')->nullable()->after('guest_email_verification_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['checkout_token']);
            $table->dropUnique(['guest_access_token_hash']);
            $table->dropUnique(['guest_email_verification_token_hash']);
            $table->dropColumn([
                'asaas_customer_id',
                'checkout_token',
                'guest_access_token_hash',
                'guest_access_expires_at',
                'guest_email_verification_token_hash',
                'guest_email_verification_expires_at',
                'guest_email_verified_at',
            ]);
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
