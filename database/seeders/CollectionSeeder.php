<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    public function run(): void
    {
        $collection = Collection::updateOrCreate(
            ['slug' => 'rajwadi-bridal-trousseau'],
            [
                'name' => 'The Rajwadi Bridal Trousseau',
                'description' => 'Layered Rani Haars, temple jhumkas, borla maang tikkas and kada sets — curated as complete bridal sets, so every ritual has its shringar.',
                'banner_image' => 'collections/rajwadi-bridal-trousseau.jpg',
            ]
        );

        $bridalProductIds = Product::whereHas('category', fn ($q) => $q->where('name', 'Bridal Sets'))
            ->pluck('id');

        $collection->products()->sync($bridalProductIds);
    }
}
