<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Rani Haar Kundan Set', 'category' => 'Bridal Sets', 'price' => 8499, 'mrp' => 11999, 'badge' => 'Bestseller'],
            ['name' => 'Meenakari Peacock Jhumkas', 'category' => 'Jhumkas & Earrings', 'price' => 2199, 'mrp' => 2999, 'badge' => null],
            ['name' => 'Polki Choker Necklace Set', 'category' => 'Kundan Jewellery', 'price' => 6799, 'mrp' => 8999, 'badge' => 'Trending'],
            ['name' => 'Borla Maang Tikka', 'category' => 'Maang Tikka', 'price' => 1899, 'mrp' => 2499, 'badge' => null],
            ['name' => 'Gulabi Meenakari Bangles', 'category' => 'Bangles & Kadas', 'price' => 3299, 'mrp' => 3799, 'badge' => null],
            ['name' => 'Temple Kundan Nath', 'category' => 'Nose Pins', 'price' => 1499, 'mrp' => 1899, 'badge' => null],
            ['name' => 'Layered Rajwadi Payal', 'category' => 'Anklets (Payal)', 'price' => 2099, 'mrp' => 2599, 'badge' => null],
            ['name' => 'Antique Gold Kada Pair', 'category' => 'Bangles & Kadas', 'price' => 4599, 'mrp' => 5299, 'badge' => 'New'],
            ['name' => 'Kundan Choker Bridal Set', 'category' => 'Bridal Sets', 'price' => 9999, 'mrp' => 13499, 'badge' => 'Bestseller'],
            ['name' => 'Meenakari Drop Earrings', 'category' => 'Jhumkas & Earrings', 'price' => 1799, 'mrp' => 2299, 'badge' => null],
            ['name' => 'Silver Oxidised Payal', 'category' => 'Anklets (Payal)', 'price' => 1599, 'mrp' => 1999, 'badge' => null],
            ['name' => 'Rajwadi Nath with Chain', 'category' => 'Nose Pins', 'price' => 1699, 'mrp' => 2099, 'badge' => 'New'],

            // Necklace Sets
            ['name' => 'Heritage Kundan Necklace', 'category' => 'Necklace Sets', 'price' => 7299, 'mrp' => 9499, 'badge' => null],
            ['name' => 'Rajwadi Choker Set', 'category' => 'Necklace Sets', 'price' => 5899, 'mrp' => 7599, 'badge' => 'Trending'],
            ['name' => 'Statement Kundan Set', 'category' => 'Necklace Sets', 'price' => 8899, 'mrp' => 11499, 'badge' => null],

            // Kundan Jewellery
            ['name' => 'Kundan Borla Set', 'category' => 'Kundan Jewellery', 'price' => 4299, 'mrp' => 5499, 'badge' => 'New'],
            ['name' => 'Bridal Polki Necklace', 'category' => 'Kundan Jewellery', 'price' => 9599, 'mrp' => 12999, 'badge' => 'Bestseller'],

            // Meenakari Jewellery
            ['name' => 'Meenakari Chandbali Earrings', 'category' => 'Meenakari Jewellery', 'price' => 2599, 'mrp' => 3299, 'badge' => null],
            ['name' => 'Meenakari Kada Pair', 'category' => 'Meenakari Jewellery', 'price' => 3899, 'mrp' => 4699, 'badge' => 'Trending'],

            // Bridal Sets
            ['name' => 'Complete Bridal Trousseau', 'category' => 'Bridal Sets', 'price' => 15999, 'mrp' => 21999, 'badge' => 'Bestseller'],

            // Jhumkas & Earrings
            ['name' => 'Temple Silver Jhumkas', 'category' => 'Jhumkas & Earrings', 'price' => 1999, 'mrp' => 2499, 'badge' => null],

            // Maang Tikka
            ['name' => 'Kundan Maang Tikka Set', 'category' => 'Maang Tikka', 'price' => 2299, 'mrp' => 2899, 'badge' => 'New'],

            // Bangles & Kadas
            ['name' => 'Rajwadi Gold Bangles', 'category' => 'Bangles & Kadas', 'price' => 3699, 'mrp' => 4399, 'badge' => null],
            ['name' => 'Silver Charm Bracelet', 'category' => 'Bangles & Kadas', 'price' => 1899, 'mrp' => 2299, 'badge' => null],

            // Nose Pins
            ['name' => 'Oxidised Silver Nath', 'category' => 'Nose Pins', 'price' => 1299, 'mrp' => 1599, 'badge' => null],
            ['name' => 'Gold Polki Nose Pin', 'category' => 'Nose Pins', 'price' => 1899, 'mrp' => 2399, 'badge' => 'Trending'],

            // Anklets (Payal)
            ['name' => 'Silver Anklet Pair', 'category' => 'Anklets (Payal)', 'price' => 1799, 'mrp' => 2199, 'badge' => null],
        ];

        foreach ($products as $index => $p) {
            $category = Category::where('name', $p['category'])->first();
            $slug = Str::slug($p['name']);

            $product = Product::updateOrCreate(
                ['sku' => 'NRK-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'name' => $p['name'],
                    'slug' => $slug,
                    'description' => "Handcrafted {$p['name']} — part of the Noorika Rajasthani jewellery collection, made by third-generation Jaipur artisans.",
                    'category_id' => $category?->id,
                    'price' => $p['price'],
                    'mrp' => $p['mrp'],
                    'stock_qty' => rand(10, 60),
                    'status' => 'active',
                    'badge' => $p['badge'],
                ]
            );

            $product->images()->updateOrCreate(
                ['sort_order' => 0],
                ['image_path' => "product-images/{$slug}.jpg"]
            );
        }
    }
}
