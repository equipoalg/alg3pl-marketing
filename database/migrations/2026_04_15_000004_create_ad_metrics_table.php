<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('platform'); // google | meta | linkedin
            $table->string('campaign_name');
            $table->date('period_start');
            $table->date('period_end');
            $table->bigInteger('impressions')->default(0);
            $table->bigInteger('clicks')->default(0);
            $table->decimal('spend', 10, 2)->default(0);
            $table->integer('leads_generated')->default(0);
            $table->decimal('cost_per_lead', 10, 2)->nullable();
            $table->decimal('roas', 8, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['country_id', 'platform', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_metrics');
    }
};
