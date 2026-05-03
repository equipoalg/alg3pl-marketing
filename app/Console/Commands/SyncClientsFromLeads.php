<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Country;
use App\Models\Lead;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Backfill the `clients` table from existing `leads`.
 *
 * For each unique (company, country_id) combo found in leads where company is
 * non-empty, firstOrCreate a Client row. Picks primary contact info from the
 * latest lead. Promotes the client to 'active' if any of the company's leads
 * is in 'won' status; otherwise leaves it as 'prospect'.
 *
 * Idempotent — safe to re-run. Reports created vs already-present counts.
 *
 * Usage:
 *   php artisan clients:sync-from-leads
 *   php artisan clients:sync-from-leads --dry-run
 */
class SyncClientsFromLeads extends Command
{
    protected $signature = 'clients:sync-from-leads {--dry-run : Print what would be created without writing}';

    protected $description = 'Mirror unique companies from leads into the clients (Cuentas) table';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // Resolve a fallback tenant_id (single-tenant install today).
        $fallbackTenantId = Tenant::query()->orderBy('id')->value('id');
        if ($fallbackTenantId === null) {
            $this->error('No Tenant rows found — cannot satisfy clients.tenant_id FK.');
            return self::FAILURE;
        }

        // Pre-load country → tenant_id map (countries.tenant_id is set by TenantSeeder)
        $countryTenantMap = Country::query()->pluck('tenant_id', 'id')->all();

        // Group leads by (company, country_id), capturing the latest contact
        // info and whether any lead in the group is 'won'.
        $rows = Lead::query()
            ->whereNotNull('company')
            ->where('company', '!=', '')
            ->whereNotNull('country_id')
            ->selectRaw("
                TRIM(company) as company_name,
                country_id,
                MAX(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as has_won,
                MAX(name) as latest_name,
                MAX(email) as latest_email,
                MAX(phone) as latest_phone,
                COUNT(*) as lead_count
            ")
            ->groupBy(DB::raw('TRIM(company)'), 'country_id')
            ->get();

        $this->info("Found {$rows->count()} unique (company × country) combinations across leads.");

        if ($dryRun) {
            $this->warn('DRY RUN — no records will be written.');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $byCountry = [];

        foreach ($rows as $row) {
            $companyName = trim((string) $row->company_name);
            if ($companyName === '') {
                $skipped++;
                continue;
            }

            $tenantId = $countryTenantMap[$row->country_id] ?? $fallbackTenantId;

            if ($dryRun) {
                $this->line("  · {$companyName} (country_id={$row->country_id}, tenant_id={$tenantId}, leads={$row->lead_count}" . ($row->has_won ? ', WON' : '') . ")");
                $byCountry[$row->country_id] = ($byCountry[$row->country_id] ?? 0) + 1;
                continue;
            }

            // Skip BelongsToTenant scope — we set tenant_id explicitly.
            $client = Client::withoutGlobalScopes()->firstOrCreate(
                [
                    'tenant_id'    => $tenantId,
                    'country_id'   => $row->country_id,
                    'company_name' => $companyName,
                ],
                [
                    'status'                 => $row->has_won ? 'active' : 'prospect',
                    'tier'                   => 'smb',
                    'health_score'           => 50,
                    'primary_contact_name'   => $row->latest_name,
                    'primary_contact_email'  => $row->latest_email,
                    'primary_contact_phone'  => $row->latest_phone,
                    'contract_start'         => $row->has_won ? now() : null,
                ]
            );

            if ($client->wasRecentlyCreated) {
                $created++;
            } else {
                // Backfill missing primary contact fields if the existing client has blanks
                $dirty = false;
                foreach ([
                    'primary_contact_name'  => $row->latest_name,
                    'primary_contact_email' => $row->latest_email,
                    'primary_contact_phone' => $row->latest_phone,
                ] as $field => $value) {
                    if (! $client->{$field} && $value) {
                        $client->{$field} = $value;
                        $dirty = true;
                    }
                }
                if ($row->has_won && $client->status !== 'active') {
                    $client->status = 'active';
                    if (! $client->contract_start) {
                        $client->contract_start = now();
                    }
                    $dirty = true;
                }
                if ($dirty) {
                    $client->save();
                    $updated++;
                }
            }

            $byCountry[$row->country_id] = ($byCountry[$row->country_id] ?? 0) + 1;
        }

        $this->newLine();
        $this->info("✓ Created: {$created}");
        $this->info("✓ Updated: {$updated}");
        if ($skipped) {
            $this->warn("Skipped (empty company): {$skipped}");
        }

        if (! empty($byCountry)) {
            $this->newLine();
            $this->info('By country:');
            $countryNames = Country::query()->pluck('name', 'id')->all();
            foreach ($byCountry as $cid => $count) {
                $name = $countryNames[$cid] ?? "country_id={$cid}";
                $this->line("  {$name}: {$count}");
            }
        }

        return self::SUCCESS;
    }
}
