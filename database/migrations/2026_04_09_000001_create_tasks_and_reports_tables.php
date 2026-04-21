<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', ['seo', 'technical', 'content', 'ux', 'marketing', 'analytics'])->default('seo');
            $table->enum('priority', ['P0', 'P1', 'P2', 'P3'])->default('P1');
            $table->enum('effort', ['1d', '3d', '1w', '2w', '3w', '4w', '6w'])->nullable();
            $table->enum('impact', ['+', '++', '+++'])->default('+');
            $table->enum('status', ['pending', 'in_progress', 'done', 'blocked'])->default('pending');
            $table->string('assignee')->nullable();
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('source_file')->nullable();
            $table->timestamps();

            $table->index(['country_id', 'priority', 'status']);
        });

        Schema::create('country_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('period');
            $table->string('type')->default('seo');
            $table->json('kpis')->nullable();
            $table->json('findings')->nullable();
            $table->json('opportunities')->nullable();
            $table->json('ga4_data')->nullable();
            $table->json('gsc_data')->nullable();
            $table->text('summary')->nullable();
            $table->string('source_file')->nullable();
            $table->timestamps();

            $table->unique(['country_id', 'period', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_reports');
        Schema::dropIfExists('tasks');
    }
};
