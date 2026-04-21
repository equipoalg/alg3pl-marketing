<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('country_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->integer('monthly_lead_goal')->default(50);
            $table->string('primary_manager')->nullable();
            $table->json('webhook_assignees')->nullable();
            $table->json('active_services')->nullable();
            $table->decimal('monthly_fee', 8, 2)->default(150.00);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('country_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_configs');
    }
};
