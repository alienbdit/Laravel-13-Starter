<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_gateway_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('provider')->default('custom');   // twilio | vonage | aws_sns | custom
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->string('from_number')->nullable();
            $table->string('endpoint_url')->nullable();      // custom provider endpoint
            $table->json('extra_params')->nullable();        // provider-specific extra fields
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_gateway_settings');
    }
};
