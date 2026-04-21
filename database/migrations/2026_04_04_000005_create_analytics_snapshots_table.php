<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained();
            $table->date('date');
            $table->unsignedInteger('users')->default(0);
            $table->unsignedInteger('new_users')->default(0);
            $table->unsignedInteger('sessions')->default(0);
            $table->unsignedInteger('page_views')->default(0);
            $table->unsignedSmallInteger('avg_session_duration')->default(0);
            $table->decimal('bounce_rate', 5, 2)->default(0);
            $table->unsignedInteger('organic_users')->default(0);
            $table->unsignedInteger('direct_users')->default(0);
            $table->unsignedInteger('referral_users')->default(0);
            $table->unsignedInteger('social_users')->default(0);
            $table->unsignedInteger('paid_users')->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->timestamps();

            $table->unique(['country_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_snapshots');
    }
};
