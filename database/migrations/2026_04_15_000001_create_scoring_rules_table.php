<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scoring_rules', function (Blueprint $table) {
            $table->id();
            $table->string('factor'); // e.g. 'source_organic'
            $table->string('label');
            $table->integer('weight')->default(0); // 0-100
            $table->string('category'); // source|status|engagement|geography
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scoring_rules');
    }
};
