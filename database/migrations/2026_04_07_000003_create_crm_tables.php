<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('company_name');
            $table->string('trade_name')->nullable();
            $table->string('tax_id')->nullable(); // NIT, RUC, RTN, etc.
            $table->string('industry')->nullable();
            $table->enum('tier', ['enterprise', 'mid_market', 'smb'])->default('smb');
            $table->enum('status', ['prospect', 'active', 'inactive', 'churned'])->default('prospect');
            $table->string('primary_contact_name')->nullable();
            $table->string('primary_contact_email')->nullable();
            $table->string('primary_contact_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->decimal('annual_revenue', 15, 2)->nullable();
            $table->decimal('monthly_volume', 12, 2)->nullable(); // shipments or CBM
            $table->json('services_contracted')->nullable(); // ['ocean_freight','customs','warehousing']
            $table->json('lanes')->nullable(); // [{origin:'SV', dest:'US', mode:'ocean'}]
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->integer('health_score')->default(50); // 0-100
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'country_id']);
        });

        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', [
                'call', 'email', 'meeting', 'whatsapp', 'visit',
                'quote_sent', 'quote_accepted', 'quote_rejected',
                'complaint', 'support_ticket', 'note'
            ]);
            $table->string('subject');
            $table->text('body')->nullable();
            $table->enum('outcome', ['positive', 'neutral', 'negative', 'pending'])->default('pending');
            $table->datetime('scheduled_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'client_id', 'created_at']);
        });

        Schema::create('scheduled_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['rate_review', 'contract_renewal', 'service_review', 'quarterly_meeting', 'annual_review']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'overdue'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->date('completed_at')->nullable();
            $table->text('outcome_notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'due_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_maintenances');
        Schema::dropIfExists('interactions');
        Schema::dropIfExists('clients');
    }
};
