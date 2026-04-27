<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webhooks', function (Blueprint $table) {
            // Tag inbound webhooks with the country whose website POSTs to them.
            // Used by WebhookInboundController to stamp the resulting Lead with country_id.
            $table->foreignId('country_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete();

            // Source identifier (e.g. "fluent_forms", "tally", "typeform"). Tells the
            // inbound handler which payload shape to parse.
            $table->string('source', 32)->nullable()->after('direction');

            // Field mapping JSON: { "name_field": "names", "email_field": "email", ... }
            // Lets the user remap form fields without code changes.
            $table->json('field_mapping')->nullable()->after('events');
        });
    }

    public function down(): void
    {
        Schema::table('webhooks', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn(['country_id', 'source', 'field_mapping']);
        });
    }
};
