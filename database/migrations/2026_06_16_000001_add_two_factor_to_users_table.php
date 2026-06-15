<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('two_factor_type')->nullable()->after('email');      // email | sms | totp
            $table->text('two_factor_secret')->nullable()->after('two_factor_type');
            $table->string('two_factor_phone')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['two_factor_type', 'two_factor_secret', 'two_factor_phone', 'two_factor_confirmed_at']);
        });
    }
};
