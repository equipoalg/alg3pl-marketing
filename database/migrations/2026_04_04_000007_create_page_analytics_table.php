<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained();
            $table->date('date');
            $table->string('page_path');
            $table->string('page_title')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('users')->default(0);
            $table->unsignedSmallInteger('avg_time')->default(0);
            $table->decimal('bounce_rate', 5, 2)->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->timestamps();

            $table->index(['country_id', 'date']);
            $table->index('page_path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_analytics');
    }
};
