<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('mfa_enabled')->default(false)->after('password');
            $table->string('mfa_secret', 64)->nullable()->after('mfa_enabled');
            $table->string('mfa_otp_code', 6)->nullable()->after('mfa_secret');
            $table->timestamp('mfa_otp_expires_at')->nullable()->after('mfa_otp_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['mfa_enabled', 'mfa_secret', 'mfa_otp_code', 'mfa_otp_expires_at']);
        });
    }
};
