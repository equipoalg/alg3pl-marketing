<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tags for contact segmentation
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('color', 7)->default('#6366F1'); // hex
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        // Lead-Tag pivot
        Schema::create('lead_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->string('source')->nullable(); // manual, funnel, smartlink, import
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['lead_id', 'tag_id']);
        });

        // Client-Tag pivot
        Schema::create('client_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['client_id', 'tag_id']);
        });

        // Email Templates
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('subject');
            $table->text('body_html');
            $table->text('body_text')->nullable();
            $table->enum('category', ['welcome', 'follow_up', 'nurturing', 'quote', 'newsletter', 'notification', 'custom'])->default('custom');
            $table->json('variables')->nullable(); // [{key:'nombre', default:'Cliente'}]
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();
        });

        // Smart Segments
        Schema::create('segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['static', 'dynamic'])->default('dynamic');
            $table->json('rules')->nullable(); // {conditions:[{field:'country_id',op:'in',value:[1,2]},{field:'score',op:'>=',value:60}], logic:'and'}
            $table->unsignedInteger('cached_count')->default(0);
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();
        });

        // Static segment members (for static segments)
        Schema::create('segment_lead', function (Blueprint $table) {
            $table->id();
            $table->foreignId('segment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['segment_id', 'lead_id']);
        });

        // Smartlinks (auto-tag on click)
        Schema::create('smartlinks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('destination_url');
            $table->string('name');
            $table->json('tags_to_apply')->nullable(); // [tag_id, tag_id]
            $table->integer('score_adjustment')->default(0); // +5, +10 etc
            $table->unsignedInteger('click_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Smartlink clicks tracking
        Schema::create('smartlink_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smartlink_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->timestamp('clicked_at')->useCurrent();

            $table->index(['smartlink_id', 'clicked_at']);
        });

        // A/B Test variants for email campaigns
        Schema::create('ab_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('test_percentage')->default(20); // % of audience for testing
            $table->enum('test_variable', ['subject', 'body', 'send_time'])->default('subject');
            $table->enum('winning_metric', ['open_rate', 'click_rate'])->default('open_rate');
            $table->enum('status', ['testing', 'winner_selected', 'completed'])->default('testing');
            $table->unsignedTinyInteger('winning_variant')->nullable(); // 0=A, 1=B
            $table->timestamp('test_end_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ab_test_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ab_test_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('variant_index'); // 0=A, 1=B
            $table->string('subject')->nullable();
            $table->text('body_html')->nullable();
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('open_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamps();

            $table->unique(['ab_test_id', 'variant_index']);
        });

        // Email bounce tracking
        Schema::create('email_bounces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->enum('bounce_type', ['hard', 'soft', 'complaint'])->default('soft');
            $table->string('reason')->nullable();
            $table->string('diagnostic_code')->nullable();
            $table->unsignedTinyInteger('bounce_count')->default(1);
            $table->timestamp('first_bounced_at')->nullable();
            $table->timestamp('last_bounced_at')->nullable();
            $table->timestamps();

            $table->index('email');
        });

        // Webhooks (bidirectional)
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('direction', ['inbound', 'outbound']);
            $table->string('url')->nullable(); // for outbound
            $table->string('secret')->nullable(); // for verification
            $table->json('events')->nullable(); // ['lead.created','lead.status_changed']
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failure_count')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->string('event');
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->boolean('success')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['webhook_id', 'created_at']);
        });

        // Add email_verified_at and unsubscribed_at to leads for double opt-in
        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('verification_token')->nullable()->after('email_verified_at');
            $table->timestamp('unsubscribed_at')->nullable()->after('verification_token');
        });

        // Add A/B test fields to email_campaigns
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->foreignId('template_id')->nullable()->after('campaign_id');
            $table->foreignId('ab_test_id')->nullable()->after('template_id');
            $table->enum('variant', ['original', 'a', 'b'])->default('original')->after('ab_test_id');
        });

        // Add webhook_url to campaigns for external integrations
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('webhook_url')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', fn (Blueprint $t) => $t->dropColumn('webhook_url'));
        Schema::table('email_campaigns', fn (Blueprint $t) => $t->dropColumn(['template_id', 'ab_test_id', 'variant']));
        Schema::table('leads', fn (Blueprint $t) => $t->dropColumn(['email_verified_at', 'verification_token', 'unsubscribed_at']));
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('email_bounces');
        Schema::dropIfExists('ab_test_variants');
        Schema::dropIfExists('ab_tests');
        Schema::dropIfExists('smartlink_clicks');
        Schema::dropIfExists('smartlinks');
        Schema::dropIfExists('segment_lead');
        Schema::dropIfExists('segments');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('client_tag');
        Schema::dropIfExists('lead_tag');
        Schema::dropIfExists('tags');
    }
};
