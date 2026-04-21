<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->string('name');
            $table->enum('type', ['email', 'whatsapp', 'social', 'multi']);
            $table->enum('status', ['draft', 'scheduled', 'active', 'paused', 'completed'])->default('draft');
            $table->json('audience_filter')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['country_id', 'status']);
        });

        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->text('body');
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('open_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            $table->unsignedInteger('bounce_count')->default(0);
            $table->unsignedInteger('unsubscribe_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('direction', ['in', 'out']);
            $table->text('message');
            $table->string('template_name')->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending');
            $table->string('phone_number');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('lead_id');
        });

        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained();
            $table->enum('platform', ['facebook', 'instagram', 'linkedin', 'twitter']);
            $table->text('content');
            $table->string('media_url')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'published', 'failed'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('reach')->default(0);
            $table->unsignedInteger('engagement')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_posts');
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('email_campaigns');
        Schema::dropIfExists('campaigns');
    }
};
