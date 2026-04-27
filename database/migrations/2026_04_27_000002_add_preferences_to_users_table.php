<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a JSON `preferences` column to the users table for per-user UI settings:
 * - variant (a/b layout density)
 * - theme (light/dark, light-only for now but column ready)
 * - notify_email (per-user notification toggle, placeholder)
 *
 * Used by /admin/settings to persist preferences across sessions and devices.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('preferences')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('preferences');
        });
    }
};
