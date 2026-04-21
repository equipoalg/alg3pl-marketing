<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_console_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained();
            $table->date('date');
            $table->string('query');
            $table->string('page')->nullable();
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('impressions')->default(0);
            $table->decimal('ctr', 5, 2)->default(0);
            $table->decimal('position', 5, 1)->default(0);
            $table->timestamps();

            $table->index(['country_id', 'date']);
            $table->index('query');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_console_data');
    }
};
