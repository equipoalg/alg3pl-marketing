<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add snoozed_until column to leads.
 *
 * Powers the "Snooze / follow-up reminder" feature in /admin/leads:
 * the operator marks a lead "remind me Tuesday" → snoozed_until = ese
 * datetime → el lead desaparece de Bandeja/Contactos hasta que la fecha
 * pase, y aparece en una folder dedicada "Snoozed" cuando vence.
 *
 * Indexed because we filter by `snoozed_until <= now()` en getViewData.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('snoozed_until')->nullable()->after('assigned_to');
            $table->index('snoozed_until');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['snoozed_until']);
            $table->dropColumn('snoozed_until');
        });
    }
};
