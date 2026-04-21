<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user
        User::factory()->create([
            'name' => 'Roberto Diaz Tercero',
            'email' => 'roberto@diaztercero.com',
            'is_super_admin' => true,
        ]);

        $this->call([
            CountrySeeder::class,
            TenantSeeder::class,
        ]);
    }
}
