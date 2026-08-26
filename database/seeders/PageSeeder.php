<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'About Noorika',
                'slug' => 'about-noorika',
                'content' => '<p>Noorika is a royal Rajasthani jewellery brand, handcrafted in Jaipur by Chitra & Komal. We specialize in Kundan, Meenakari and heirloom silver for the modern bride.</p>',
            ],
            [
                'title' => 'Our Craft',
                'slug' => 'our-craft',
                'content' => "<p>Every Noorika piece passes through the hands of third-generation artisans in the walled city of Jaipur — uncut stones set in gold foil, enamel fired at a thousand degrees, then finished by hand.</p>",
            ],
            [
                'title' => 'Shipping & Returns',
                'slug' => 'shipping-returns',
                'content' => '<p>Free shipping across India. 7-day easy exchange on all pieces. Orders are delivered safely, insured in transit.</p>',
            ],
            [
                'title' => 'Jewellery Care',
                'slug' => 'jewellery-care',
                'content' => '<p>Store your Noorika pieces in a dry box away from direct sunlight. Avoid contact with perfume, water and sweat to preserve the Meenakari finish.</p>',
            ],
            [
                'title' => 'Size Guide',
                'slug' => 'size-guide',
                'content' => '<p>Refer to our bangle and ring sizing chart to find your perfect fit before ordering.</p>',
            ],
            [
                'title' => 'FAQs',
                'slug' => 'faqs',
                'content' => '<p>Frequently asked questions about orders, shipping, returns and jewellery care.</p>',
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => '<p>Reach us at hello@noorika.com or via our store in Jaipur, Rajasthan.</p>',
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'content' => $page['content'],
                    'status' => 'published',
                ]
            );
        }
    }
}
