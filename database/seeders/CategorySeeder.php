<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Necklace Sets',
            'Kundan Jewellery',
            'Meenakari Jewellery',
            'Bridal Sets',
            'Jhumkas & Earrings',
            'Maang Tikka',
            'Bangles & Kadas',
            'Nose Pins',
            'Anklets (Payal)',
        ];

        foreach ($categories as $index => $name) {
            $slug = Str::slug($name);

            Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'image' => "categories/{$slug}.jpg",
                    'display_order' => $index,
                    'status' => 'active',
                ]
            );
        }
    }
}
