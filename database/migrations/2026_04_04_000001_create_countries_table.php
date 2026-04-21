<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique();
            $table->string('name');
            $table->string('ga4_property_id')->nullable();
            $table->string('gsc_property_url')->nullable();
            $table->string('website_url');
            $table->string('timezone')->default('America/Guatemala');
            $table->string('currency', 3)->default('USD');
            $table->string('phone_prefix', 5)->nullable();
            $table->string('google_ads_account')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_regional')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
