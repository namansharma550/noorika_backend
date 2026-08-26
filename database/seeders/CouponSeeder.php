<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            ['code' => 'NOORIKA15', 'type' => 'percent', 'value' => 15, 'min_order_value' => 2000, 'max_uses' => 500],
            ['code' => 'WELCOME500', 'type' => 'flat', 'value' => 500, 'min_order_value' => 3000, 'max_uses' => 1000],
            ['code' => 'BRIDAL10', 'type' => 'percent', 'value' => 10, 'min_order_value' => 5000, 'max_uses' => null],
        ];

        foreach ($coupons as $c) {
            Coupon::updateOrCreate(
                ['code' => $c['code']],
                [
                    'type' => $c['type'],
                    'value' => $c['value'],
                    'min_order_value' => $c['min_order_value'],
                    'max_uses' => $c['max_uses'],
                    'expires_at' => now()->addMonths(3),
                    'status' => 'active',
                ]
            );
        }
    }
}
