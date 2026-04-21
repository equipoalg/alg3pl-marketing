<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            [
                'code' => 'sv',
                'name' => 'El Salvador',
                'ga4_property_id' => '447141041',
                'gsc_property_url' => 'https://alg3pl.com/sv/',
                'website_url' => 'https://alg3pl.com/sv/',
                'timezone' => 'America/El_Salvador',
                'currency' => 'USD',
                'phone_prefix' => '+503',
            ],
            [
                'code' => 'gt',
                'name' => 'Guatemala',
                'ga4_property_id' => '503847442',
                'gsc_property_url' => 'https://alg3pl.com/gt/',
                'website_url' => 'https://alg3pl.com/gt/',
                'timezone' => 'America/Guatemala',
                'currency' => 'GTQ',
                'phone_prefix' => '+502',
                'google_ads_account' => '137-125-3878',
            ],
            [
                'code' => 'hn',
                'name' => 'Honduras',
                'ga4_property_id' => '452527002',
                'gsc_property_url' => 'https://alg3pl.com/hn/',
                'website_url' => 'https://alg3pl.com/hn/',
                'timezone' => 'America/Tegucigalpa',
                'currency' => 'HNL',
                'phone_prefix' => '+504',
            ],
            [
                'code' => 'ni',
                'name' => 'Nicaragua',
                'ga4_property_id' => '450300127',
                'gsc_property_url' => 'https://alg3pl.com/ni/',
                'website_url' => 'https://alg3pl.com/ni/',
                'timezone' => 'America/Managua',
                'currency' => 'NIO',
                'phone_prefix' => '+505',
            ],
            [
                'code' => 'cr',
                'name' => 'Costa Rica',
                'ga4_property_id' => '484011151',
                'gsc_property_url' => 'https://alg3pl.com/cr/',
                'website_url' => 'https://alg3pl.com/cr/',
                'timezone' => 'America/Costa_Rica',
                'currency' => 'CRC',
                'phone_prefix' => '+506',
            ],
            [
                'code' => 'pa',
                'name' => 'Panama',
                'ga4_property_id' => '453664557',
                'gsc_property_url' => 'https://alg3pl.com/pa/',
                'website_url' => 'https://alg3pl.com/pa/',
                'timezone' => 'America/Panama',
                'currency' => 'USD',
                'phone_prefix' => '+507',
            ],
            [
                'code' => 'us',
                'name' => 'USA / Miami',
                'ga4_property_id' => '450284783',
                'gsc_property_url' => 'https://alg3pl.com/',
                'website_url' => 'https://alg3pl.com/us/',
                'timezone' => 'America/New_York',
                'currency' => 'USD',
                'phone_prefix' => '+1',
                'is_regional' => true,
            ],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['code' => $country['code']],
                $country
            );
        }
    }
}
