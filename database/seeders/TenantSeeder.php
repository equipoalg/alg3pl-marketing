<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Create ALG3PL as the first tenant
        $tenant = Tenant::updateOrCreate(
            ['slug' => 'alg3pl'],
            [
                'name' => 'ALG 3PL',
                'domain' => 'marketing.alg3pl.com',
                'branding' => [
                    'primary_color' => '#1E3A5F',
                    'secondary_color' => '#3B82F6',
                    'font' => 'Inter',
                ],
                'default_locale' => 'es',
                'default_currency' => 'USD',
                'timezone' => 'America/El_Salvador',
                'plan' => 'enterprise',
                'status' => 'active',
            ]
        );

        // Assign tenant to all existing users and countries
        User::whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);
        \App\Models\Country::whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);

        // Make first user super admin
        User::where('email', 'roberto@diaztercero.com')->update([
            'is_super_admin' => true,
            'tenant_id' => $tenant->id,
        ]);

        // Create default roles
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full access to all modules',
                'permissions' => ['*'],
                'is_default' => false,
            ],
            [
                'name' => 'manager',
                'display_name' => 'Country Manager',
                'description' => 'Manage country-level operations',
                'permissions' => [
                    'dashboard.view', 'leads.view', 'leads.edit', 'leads.create',
                    'clients.view', 'clients.edit', 'clients.create',
                    'campaigns.view', 'campaigns.edit', 'campaigns.create',
                    'analytics.view', 'reports.view', 'reports.export',
                    'funnels.view', 'funnels.edit',
                ],
            ],
            [
                'name' => 'sales',
                'display_name' => 'Sales Representative',
                'description' => 'Manage leads and client interactions',
                'permissions' => [
                    'dashboard.view', 'leads.view', 'leads.edit', 'leads.create',
                    'clients.view', 'interactions.create',
                ],
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Viewer',
                'description' => 'Read-only access to dashboards and reports',
                'permissions' => [
                    'dashboard.view', 'analytics.view', 'reports.view',
                ],
                'is_default' => true,
            ],
            [
                'name' => 'api_consumer',
                'display_name' => 'API Consumer',
                'description' => 'External system integration',
                'permissions' => [
                    'api.leads.read', 'api.leads.write',
                    'api.metrics.read', 'api.metrics.write',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $roleData['name']],
                $roleData
            );
        }
    }
}
