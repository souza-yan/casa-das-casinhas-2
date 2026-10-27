<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['guest_email_verification_token_hash']);
            $table->dropColumn([
                'guest_email_verification_token_hash',
                'guest_email_verification_expires_at',
                'guest_email_verified_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('guest_email_verification_token_hash', 64)->nullable()->unique()->after('guest_access_expires_at');
            $table->timestamp('guest_email_verification_expires_at')->nullable()->after('guest_email_verification_token_hash');
            $table->timestamp('guest_email_verified_at')->nullable()->after('guest_email_verification_expires_at');
        });
    }
};
