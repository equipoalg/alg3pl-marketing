<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Expands leads.source ENUM to include 'fluent_forms' and 'form' so that
 * inbound webhooks can stamp the lead with their actual source category.
 *
 * Original ENUM: organic, direct, referral, social, paid, whatsapp, email, other
 * After:        organic, direct, referral, social, paid, whatsapp, email, other,
 *               form, fluent_forms
 *
 * Without this, WebhookInboundController fails with
 * "Data truncated for column 'source'" because MySQL strict mode rejects
 * values not in the ENUM list.
 */
return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE leads MODIFY source ENUM(
                'organic', 'direct', 'referral', 'social', 'paid',
                'whatsapp', 'email', 'other',
                'form', 'fluent_forms'
            ) NOT NULL DEFAULT 'other'
        ");
    }

    public function down(): void
    {
        // Map any rows using new values back to 'other' before shrinking the ENUM
        DB::statement("UPDATE leads SET source = 'other' WHERE source IN ('form', 'fluent_forms')");

        DB::statement("
            ALTER TABLE leads MODIFY source ENUM(
                'organic', 'direct', 'referral', 'social', 'paid',
                'whatsapp', 'email', 'other'
            ) NOT NULL DEFAULT 'other'
        ");
    }
};
