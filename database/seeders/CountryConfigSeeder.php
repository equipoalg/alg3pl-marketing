<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\CountryConfig;
use Illuminate\Database\Seeder;

/**
 * Seeds default CountryConfig rows for the 6 ALG3PL Centroamérica markets.
 *
 * Idempotent: uses updateOrCreate keyed on country_id, so re-running won't
 * duplicate. Safe to run on production AFTER editing the email values to
 * real team accounts (the placeholders here use @alg3pl.com domain).
 *
 * Once seeded:
 *   - LeadAssignmentService routes hot leads (score >= 70) → primary_manager
 *   - Cold leads round-robin across webhook_assignees
 *   - Edit per-country values via /admin/country-configs in Filament
 */
class CountryConfigSeeder extends Seeder
{
    public function run(): void
    {
        // Per-country defaults: monthly_lead_goal, primary manager email,
        // and round-robin assignee emails. EDIT THESE before running on prod.
        $defaults = [
            'sv' => [
                'goal'        => 80,
                'primary'     => 'manager.sv@alg3pl.com',
                'assignees'   => ['rep1.sv@alg3pl.com', 'rep2.sv@alg3pl.com'],
                'services'    => ['warehousing', 'last-mile', 'aduana'],
                'monthly_fee' => 350.00,
            ],
            'gt' => [
                'goal'        => 90,
                'primary'     => 'manager.gt@alg3pl.com',
                'assignees'   => ['rep1.gt@alg3pl.com', 'rep2.gt@alg3pl.com'],
                'services'    => ['warehousing', 'last-mile', 'aduana', 'transporte'],
                'monthly_fee' => 350.00,
            ],
            'hn' => [
                'goal'        => 60,
                'primary'     => 'manager.hn@alg3pl.com',
                'assignees'   => ['rep1.hn@alg3pl.com'],
                'services'    => ['warehousing', 'last-mile'],
                'monthly_fee' => 250.00,
            ],
            'ni' => [
                'goal'        => 40,
                'primary'     => 'manager.ni@alg3pl.com',
                'assignees'   => ['rep1.ni@alg3pl.com'],
                'services'    => ['warehousing', 'aduana'],
                'monthly_fee' => 200.00,
            ],
            'cr' => [
                'goal'        => 70,
                'primary'     => 'manager.cr@alg3pl.com',
                'assignees'   => ['rep1.cr@alg3pl.com', 'rep2.cr@alg3pl.com'],
                'services'    => ['warehousing', 'last-mile', 'transporte'],
                'monthly_fee' => 320.00,
            ],
            'pa' => [
                'goal'        => 60,
                'primary'     => 'manager.pa@alg3pl.com',
                'assignees'   => ['rep1.pa@alg3pl.com'],
                'services'    => ['warehousing', 'aduana', 'multimodal'],
                'monthly_fee' => 280.00,
            ],
        ];

        $seeded = 0;
        foreach ($defaults as $code => $cfg) {
            $country = Country::where('code', $code)->first();
            if (! $country) {
                $this->command?->warn("Country {$code} not found, skipping.");
                continue;
            }

            CountryConfig::updateOrCreate(
                ['country_id' => $country->id],
                [
                    'monthly_lead_goal'  => $cfg['goal'],
                    'primary_manager'    => $cfg['primary'],
                    'webhook_assignees'  => $cfg['assignees'],
                    'active_services'    => $cfg['services'],
                    'monthly_fee'        => $cfg['monthly_fee'],
                    'notes'              => 'Seeded ' . now()->toDateString() . '. Edit emails in /admin/country-configs.',
                ]
            );
            $seeded++;
        }

        $this->command?->info("CountryConfig seeded for {$seeded} countries.");
        $this->command?->warn("⚠ Update the placeholder @alg3pl.com emails to real team accounts before relying on auto-assignment.");
    }
}
