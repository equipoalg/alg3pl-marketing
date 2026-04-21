<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funnels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'active', 'paused', 'archived'])->default('draft');
            $table->enum('trigger_type', ['page_visit', 'form_submit', 'api_event', 'manual']);
            $table->json('trigger_config')->nullable();
            $table->json('audience_rules')->nullable(); // {country:['SV','GT'], source:['organic']}
            $table->integer('total_entries')->default(0);
            $table->integer('total_conversions')->default(0);
            $table->timestamps();
        });

        Schema::create('funnel_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funnel_id')->constrained()->cascadeOnDelete();
            $table->integer('order');
            $table->string('name');
            $table->enum('action_type', [
                'send_email', 'send_whatsapp', 'wait_delay',
                'wait_condition', 'assign_score', 'assign_tag',
                'notify_sales', 'create_task', 'webhook'
            ]);
            $table->json('action_config')->nullable();
            $table->integer('delay_hours')->default(0);
            $table->json('condition')->nullable(); // for branching logic
            $table->integer('entries_count')->default(0);
            $table->integer('completions_count')->default(0);
            $table->timestamps();

            $table->unique(['funnel_id', 'order']);
        });

        Schema::create('funnel_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funnel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('current_step')->default(1);
            $table->enum('status', ['active', 'completed', 'dropped', 'paused'])->default('active');
            $table->timestamp('enrolled_at');
            $table->timestamp('completed_at')->nullable();
            $table->json('step_history')->nullable();
            $table->timestamps();

            $table->index(['funnel_id', 'status']);
        });

        Schema::create('behavior_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable();
            $table->string('event_type'); // page_view, form_submit, download, click, scroll_depth
            $table->string('page_url')->nullable();
            $table->string('element')->nullable();
            $table->json('properties')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('source')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('device_type')->nullable();
            $table->timestamp('created_at');

            $table->index(['tenant_id', 'lead_id']);
            $table->index(['tenant_id', 'event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behavior_events');
        Schema::dropIfExists('funnel_enrollments');
        Schema::dropIfExists('funnel_steps');
        Schema::dropIfExists('funnels');
    }
};
