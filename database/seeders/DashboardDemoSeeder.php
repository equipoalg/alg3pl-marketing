<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Country;
use App\Models\EmailCampaign;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Populates the dashboard with mock data matching the Claude Design bundle
 * (data.jsx) so /admin/dashboard renders like the reference PDF instead of
 * showing empty-state placeholders.
 *
 * Idempotent: safe to re-run, uses firstOrCreate keyed on stable fields.
 *
 * To seed:  php artisan db:seed --class=DashboardDemoSeeder
 */
class DashboardDemoSeeder extends Seeder
{
    public function run(): void
    {
        $countries = Country::pluck('id', 'code'); // ['sv' => 1, 'gt' => 2, ...]
        $admin = User::orderBy('id')->first();

        if (! $admin) {
            $this->command?->warn('No users found — create an admin first. Skipping.');
            return;
        }

        // ── 6 LEADS (per data.jsx:103-110) ──────────────────────────────
        $leadsMock = [
            ['name' => 'María Villalobos', 'email' => 'mvillalobos@cafedelvolcan.demo',  'company' => 'Café del Volcán S.A.', 'country' => 'gt', 'value' => 24800, 'status' => 'qualified',  'agoMin' => 12],
            ['name' => 'Carlos Mendoza',   'email' => 'cmendoza@industriasmendoza.demo', 'company' => 'Industrias Mendoza',  'country' => 'sv', 'value' => 8400,  'status' => 'contacted',  'agoMin' => 38],
            ['name' => 'Ana Recinos',      'email' => 'arecinos@textilex.demo',          'company' => 'TextileX Honduras',   'country' => 'hn', 'value' => 52000, 'status' => 'proposal',   'agoMin' => 60],
            ['name' => 'Diego Fernández',  'email' => 'dfernandez@pacificotrading.demo', 'company' => 'Pacífico Trading',    'country' => 'cr', 'value' => 16200, 'status' => 'qualified',  'agoMin' => 120],
            ['name' => 'Lucía Pineda',     'email' => 'lpineda@cosmeticamaya.demo',      'company' => 'Cosmética Maya',      'country' => 'mx', 'value' => 31500, 'status' => 'new',        'agoMin' => 180],
            ['name' => 'Roberto Salazar',  'email' => 'rsalazar@logitec.demo',           'company' => 'Logitec PTY',         'country' => 'pa', 'value' => 11800, 'status' => 'new',        'agoMin' => 240],
        ];

        foreach ($leadsMock as $row) {
            $countryId = $countries[$row['country']] ?? null;
            if (! $countryId) continue;

            Lead::firstOrCreate(
                ['email' => $row['email']],
                [
                    'name'             => $row['name'],
                    'company'          => $row['company'],
                    'country_id'       => $countryId,
                    'estimated_value'  => $row['value'],
                    'status'           => $row['status'],
                    'source'           => 'demo_seed',
                    'source_detail'    => 'DashboardDemoSeeder',
                    'score'            => $this->scoreForStatus($row['status']),
                    'created_at'       => now()->subMinutes($row['agoMin']),
                    'updated_at'       => now()->subMinutes($row['agoMin']),
                ]
            );
        }
        $this->command?->info('  · ' . count($leadsMock) . ' demo leads seeded');

        // ── 5 CAMPAIGNS (per data.jsx:112-118) ──────────────────────────
        $svId = $countries['sv'] ?? Country::orderBy('id')->value('id');

        $campaignsMock = [
            ['name' => 'Q2 — Centroamérica B2B',   'status' => 'active',    'budget' => 3200, 'sent' => 12480, 'open' => 5242, 'click' => 762],
            ['name' => 'Aduanas El Salvador',      'status' => 'active',    'budget' => 1100, 'sent' => 4820,  'open' => 1832, 'click' => 231],
            ['name' => 'Transporte multimodal MX', 'status' => 'scheduled', 'budget' => 2400, 'sent' => 0,     'open' => 0,    'click' => 0],
            ['name' => 'Reactivación leads 2025',  'status' => 'active',    'budget' => 890,  'sent' => 8940,  'open' => 4559, 'click' => 644],
            ['name' => 'Caso de éxito — TextileX', 'status' => 'paused',    'budget' => 420,  'sent' => 2110,  'open' => 717,  'click' => 110],
        ];

        foreach ($campaignsMock as $row) {
            $campaign = Campaign::firstOrCreate(
                ['name' => $row['name']],
                [
                    'country_id'  => $svId,
                    'created_by'  => $admin->id,
                    'type'        => 'email',
                    'status'      => $row['status'],
                    'budget'      => $row['budget'],
                    'description' => 'Demo campaign — DashboardDemoSeeder',
                ]
            );

            // Attach an EmailCampaign with the rendered metrics so the
            // dashboard's Campañas card shows non-zero send/open/CTR.
            EmailCampaign::firstOrCreate(
                ['campaign_id' => $campaign->id, 'subject' => $row['name']],
                [
                    'body'        => '<p>Demo body for ' . e($row['name']) . '</p>',
                    'sent_count'  => $row['sent'],
                    'open_count'  => $row['open'],
                    'click_count' => $row['click'],
                    'sent_at'     => $row['sent'] > 0 ? now()->subDays(rand(1, 28)) : null,
                ]
            );
        }
        $this->command?->info('  · ' . count($campaignsMock) . ' demo campaigns seeded');

        // ── 6 LEAD ACTIVITY ENTRIES (per data.jsx:120-127) ──────────────
        // Anchor activities to specific leads where the design references them.
        $cafeLead    = Lead::where('email', 'mvillalobos@cafedelvolcan.demo')->first();
        $textilexLead= Lead::where('email', 'arecinos@textilex.demo')->first();
        $pacificoLead= Lead::where('email', 'dfernandez@pacificotrading.demo')->first();
        // lead_activities.lead_id is NOT NULL, so anchor "system" activities
        // to the first demo lead (purely so the row insert succeeds).
        $anchor = Lead::where('source', 'demo_seed')->orderBy('id')->first() ?? Lead::orderBy('id')->first();

        $activitiesMock = [
            ['lead' => $anchor,       'user' => null,    'desc' => 'sincronizó 18 nuevos leads desde HubSpot', 'when' => now()->setTime(10, 42)],
            ['lead' => $cafeLead,     'user' => $admin,  'desc' => "movió 'Café del Volcán' a Propuesta",      'when' => now()->setTime(10, 18)],
            ['lead' => $anchor,       'user' => null,    'desc' => "envió campaña 'Q2 — Centroamérica B2B'",   'when' => now()->setTime(9, 55)],
            ['lead' => $textilexLead, 'user' => $admin,  'desc' => "agregó nota a 'TextileX Honduras'",        'when' => now()->setTime(9, 31)],
            ['lead' => $anchor,       'user' => null,    'desc' => 'actualizó posiciones Search Console',     'when' => now()->setTime(8, 0)],
            ['lead' => $pacificoLead, 'user' => $admin,  'desc' => "creó cuenta 'Pacífico Trading'",           'when' => now()->subDay()->setTime(17, 30)],
        ];

        foreach ($activitiesMock as $row) {
            if (! $row['lead']) continue; // skip if no anchor available
            // Use description+lead_id as a soft uniqueness key
            $exists = LeadActivity::where('description', $row['desc'])
                ->where('lead_id', $row['lead']->id)
                ->exists();
            if ($exists) continue;

            LeadActivity::create([
                'lead_id'     => $row['lead']->id,
                'user_id'     => $row['user']?->id,
                'type'        => 'note',
                'description' => $row['desc'],
                'created_at'  => $row['when'],
                'updated_at'  => $row['when'],
            ]);
        }
        $this->command?->info('  · ' . count($activitiesMock) . ' demo activities seeded');

        $this->command?->info('Demo dashboard data ready. Open /admin/dashboard.');
    }

    private function scoreForStatus(string $status): int
    {
        return match ($status) {
            'won'         => 100,
            'negotiation' => 85,
            'proposal'    => 78,
            'qualified'   => 65,
            'contacted'   => 40,
            'new'         => 25,
            'lost'        => 0,
            default       => 30,
        };
    }
}
