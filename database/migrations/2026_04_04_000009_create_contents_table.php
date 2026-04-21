<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->enum('type', ['landing_page', 'blog_post', 'email_template', 'social_post', 'meta_content']);
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->json('keywords')->nullable();
            $table->enum('status', ['draft', 'review', 'approved', 'published'])->default('draft');
            $table->unsignedTinyInteger('performance_score')->default(0);
            $table->string('url')->nullable();
            $table->string('wp_post_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
