<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // General
            'app_name'          => config('app.name', 'The Rhythm'),
            'app_description'   => '',
            'app_timezone'      => config('app.timezone', 'UTC'),
            'date_format'       => 'Y-m-d',

            // Email
            'mail_from_name'    => config('mail.from.name', ''),
            'mail_from_address' => config('mail.from.address', ''),

            // Security & access
            'allow_registration'  => '1',
            'require_2fa'         => '0',
            'session_lifetime'    => '120',

            // Appearance
            'site_logo' => null,
            'login_bg'  => null,
        ];

        foreach ($defaults as $key => $value) {
            SiteSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
