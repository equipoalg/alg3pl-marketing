<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();
            $table->string('logo_url')->nullable();
            $table->json('branding')->nullable(); // {primary_color, secondary_color, font, favicon}
            $table->string('default_locale', 10)->default('es');
            $table->string('default_currency', 3)->default('USD');
            $table->string('timezone')->default('America/El_Salvador');
            $table->enum('plan', ['starter', 'professional', 'enterprise'])->default('professional');
            $table->enum('status', ['active', 'suspended', 'trial'])->default('trial');
            $table->date('trial_ends_at')->nullable();
            $table->json('settings')->nullable(); // extensible config
            $table->timestamps();
            $table->softDeletes();
        });

        // Add tenant_id to existing tables
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->boolean('is_super_admin')->default(false)->after('role');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('analytics_snapshots', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('search_console_data', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        $tables = ['users', 'countries', 'leads', 'campaigns', 'analytics_snapshots', 'search_console_data', 'contents'];
        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropConstrainedForeignId('tenant_id');
            });
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_super_admin');
        });
        Schema::dropIfExists('tenants');
    }
};
