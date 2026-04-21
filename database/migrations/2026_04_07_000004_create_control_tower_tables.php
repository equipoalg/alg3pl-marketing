<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->date('period_date');
            $table->enum('period_type', ['daily', 'weekly', 'monthly'])->default('daily');

            // OTIF - On Time In Full
            $table->integer('total_shipments')->default(0);
            $table->integer('on_time_shipments')->default(0);
            $table->integer('in_full_shipments')->default(0);
            $table->integer('otif_shipments')->default(0);
            $table->decimal('otif_percentage', 5, 2)->default(0);

            // Cost metrics
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('cost_to_serve', 15, 2)->default(0);
            $table->decimal('gross_margin', 5, 2)->default(0);

            // Volume
            $table->decimal('total_weight_kg', 12, 2)->default(0);
            $table->decimal('total_cbm', 10, 2)->default(0);
            $table->integer('total_teus')->default(0);

            // Mode breakdown
            $table->json('mode_breakdown')->nullable(); // {ocean:40, air:10, ground:50}

            $table->timestamps();

            $table->unique(['tenant_id', 'country_id', 'period_date', 'period_type', 'client_id'], 'shipment_metrics_unique');
            $table->index(['tenant_id', 'period_date']);
        });

        Schema::create('sales_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->date('period_date');
            $table->enum('period_type', ['daily', 'weekly', 'monthly'])->default('monthly');

            // Pipeline
            $table->integer('new_leads')->default(0);
            $table->integer('qualified_leads')->default(0);
            $table->integer('proposals_sent')->default(0);
            $table->integer('deals_won')->default(0);
            $table->integer('deals_lost')->default(0);
            $table->decimal('pipeline_value', 15, 2)->default(0);
            $table->decimal('closed_value', 15, 2)->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);

            // Retention
            $table->integer('active_clients')->default(0);
            $table->integer('churned_clients')->default(0);
            $table->decimal('churn_rate', 5, 2)->default(0);
            $table->decimal('nps_score', 4, 1)->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'country_id', 'period_date', 'period_type'], 'sales_metrics_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_metrics');
        Schema::dropIfExists('shipment_metrics');
    }
};
