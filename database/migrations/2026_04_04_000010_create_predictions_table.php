<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained();
            $table->string('metric');
            $table->string('period');
            $table->date('target_date');
            $table->decimal('predicted_value', 12, 2);
            $table->decimal('confidence', 5, 2)->default(0);
            $table->decimal('actual_value', 12, 2)->nullable();
            $table->string('model_version')->default('v1');
            $table->timestamps();

            $table->index(['country_id', 'metric', 'target_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
