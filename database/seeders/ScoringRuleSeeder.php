<?php

namespace Database\Seeders;

use App\Models\ScoringRule;
use Illuminate\Database\Seeder;

class ScoringRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            // --- Source rules (mirrors old $sourceScores array) ---
            ['factor' => 'source_organic',  'label' => 'Organic Search',  'weight' => 25, 'category' => 'source'],
            ['factor' => 'source_whatsapp', 'label' => 'WhatsApp',        'weight' => 22, 'category' => 'source'],
            ['factor' => 'source_referral', 'label' => 'Referral',        'weight' => 20, 'category' => 'source'],
            ['factor' => 'source_email',    'label' => 'Email Campaign',   'weight' => 18, 'category' => 'source'],
            ['factor' => 'source_direct',   'label' => 'Direct',           'weight' => 15, 'category' => 'source'],
            ['factor' => 'source_social',   'label' => 'Social Media',     'weight' => 12, 'category' => 'source'],
            ['factor' => 'source_paid',     'label' => 'Paid Ads',         'weight' => 10, 'category' => 'source'],

            // --- Status rules (mirrors old $statusScores array) ---
            ['factor' => 'status_new',         'label' => 'New Lead',     'weight' => 0,   'category' => 'status'],
            ['factor' => 'status_contacted',   'label' => 'Contacted',    'weight' => 10,  'category' => 'status'],
            ['factor' => 'status_qualified',   'label' => 'Qualified',    'weight' => 25,  'category' => 'status'],
            ['factor' => 'status_proposal',    'label' => 'Proposal',     'weight' => 40,  'category' => 'status'],
            ['factor' => 'status_negotiation', 'label' => 'Negotiation',  'weight' => 55,  'category' => 'status'],
            ['factor' => 'status_won',         'label' => 'Won',          'weight' => 100, 'category' => 'status'],
            ['factor' => 'status_lost',        'label' => 'Lost',         'weight' => 0,   'category' => 'status'],
        ];

        foreach ($rules as $rule) {
            ScoringRule::updateOrCreate(
                ['factor' => $rule['factor']],
                array_merge($rule, ['is_active' => true])
            );
        }

        $this->command->info('ScoringRuleSeeder: ' . count($rules) . ' rules seeded.');
    }
}
