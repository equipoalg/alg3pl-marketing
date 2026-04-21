<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sustainability_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->date('period_date');
            $table->enum('period_type', ['daily', 'monthly', 'quarterly', 'annual'])->default('monthly');

            // Carbon footprint
            $table->decimal('co2_emissions_kg', 12, 2)->default(0); // total kg CO2e
            $table->decimal('co2_per_shipment', 8, 2)->default(0);
            $table->decimal('co2_per_ton_km', 8, 4)->default(0);

            // Mode-specific emissions
            $table->decimal('ocean_emissions_kg', 12, 2)->default(0);
            $table->decimal('air_emissions_kg', 12, 2)->default(0);
            $table->decimal('ground_emissions_kg', 12, 2)->default(0);

            // Consolidation efficiency
            $table->integer('total_shipments')->default(0);
            $table->integer('consolidated_shipments')->default(0);
            $table->decimal('consolidation_rate', 5, 2)->default(0);
            $table->decimal('avg_container_utilization', 5, 2)->default(0); // % fill

            // Waste & packaging
            $table->decimal('packaging_waste_kg', 10, 2)->default(0);
            $table->decimal('recycled_packaging_pct', 5, 2)->default(0);

            $table->json('extra_metrics')->nullable(); // extensible
            $table->timestamps();

            $table->unique(['tenant_id', 'country_id', 'period_date', 'period_type'], 'sustainability_unique');
        });

        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->json('scopes')->nullable(); // ['leads.read','metrics.write']
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['token', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
        Schema::dropIfExists('sustainability_metrics');
    }
};
