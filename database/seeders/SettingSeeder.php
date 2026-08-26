<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'store_name' => ['name' => 'Noorika'],
            'store_contact' => ['email' => 'hello@noorika.com', 'phone' => ''],
            'shipping' => ['flat_rate' => 79, 'free_above' => 999],
            'tax' => ['gst_percent' => 3],
            'social_links' => ['instagram' => '', 'facebook' => ''],
            'payment_razorpay' => ['key_id' => '', 'key_secret' => '', 'mode' => 'test'],
            'payment_stripe' => ['publishable_key' => '', 'secret_key' => '', 'mode' => 'test'],
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Admin-managed once created — re-running the seeder must never clobber an
        // uploaded logo/favicon with the placeholder default.
        Setting::firstOrCreate(
            ['key' => 'site_branding'],
            ['value' => ['logo' => 'site/noorika-logo.png', 'favicon' => 'site/noorika-logo.png']]
        );

        // Manual UPI/QR payment — admin uploads a QR image; enabled only once one is set.
        Setting::firstOrCreate(
            ['key' => 'payment_qr'],
            ['value' => ['qr_image' => '', 'upi_id' => '', 'payee_name' => '']]
        );
    }
}
