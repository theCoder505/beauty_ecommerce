<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Skincare',
                'description' => 'Nourishing serums, moisturizers, and cleansers for radiant skin.',
                'products'    => [
                    ['name' => 'Vitamin C Brightening Serum',    'price' => 1850, 'sale_price' => 1499, 'stock' => 85, 'featured' => true],
                    ['name' => 'Hydra-Boost Moisturizer',         'price' => 1200, 'sale_price' => null,  'stock' => 60, 'featured' => false],
                    ['name' => 'Rose Water Toner',                'price' => 750,  'sale_price' => 650,  'stock' => 120, 'featured' => false],
                    ['name' => 'Retinol Night Cream',             'price' => 2200, 'sale_price' => 1900, 'stock' => 45, 'featured' => true],
                    ['name' => 'Micellar Cleansing Water 400ml',  'price' => 890,  'sale_price' => null,  'stock' => 95, 'featured' => false],
                ],
            ],
            [
                'name'        => 'Foundation & Concealer',
                'description' => 'Flawless coverage foundations and concealers for every skin tone.',
                'products'    => [
                    ['name' => 'HD Matte Foundation SPF20',    'price' => 2400, 'sale_price' => 1999, 'stock' => 70, 'featured' => true],
                    ['name' => 'Full Coverage Concealer',       'price' => 980,  'sale_price' => null,  'stock' => 110, 'featured' => false],
                    ['name' => 'Luminous Skin Tint',            'price' => 1650, 'sale_price' => 1450, 'stock' => 55, 'featured' => false],
                    ['name' => 'Creamy Loose Setting Powder',   'price' => 1100, 'sale_price' => null,  'stock' => 80, 'featured' => false],
                    ['name' => 'Color Correcting Primer',       'price' => 1350, 'sale_price' => 1150, 'stock' => 40, 'featured' => true],
                ],
            ],
            [
                'name'        => 'Lip Color',
                'description' => 'Bold lipsticks, glosses, and tinted balms for every mood.',
                'products'    => [
                    ['name' => 'Velvet Matte Lipstick – Ruby Red',   'price' => 750,  'sale_price' => null, 'stock' => 150, 'featured' => true],
                    ['name' => 'Glossy Plumping Lip Gloss',          'price' => 650,  'sale_price' => 550, 'stock' => 130, 'featured' => false],
                    ['name' => 'Satin Lip Liner – Nude Pack',        'price' => 580,  'sale_price' => null, 'stock' => 90,  'featured' => false],
                    ['name' => 'Moisture Bomb Tinted Lip Balm',      'price' => 480,  'sale_price' => 420, 'stock' => 200, 'featured' => false],
                    ['name' => 'Long-Wear Liquid Lipstick – Rosewood','price' => 890, 'sale_price' => null, 'stock' => 75,  'featured' => true],
                ],
            ],
            [
                'name'        => 'Eye Makeup',
                'description' => 'Eyeshadow palettes, mascaras, liners, and brow products.',
                'products'    => [
                    ['name' => 'Smoky Nude Eyeshadow Palette 15 Pan', 'price' => 2800, 'sale_price' => 2399, 'stock' => 50, 'featured' => true],
                    ['name' => 'Lash Extreme Volume Mascara',          'price' => 1100, 'sale_price' => null,  'stock' => 110, 'featured' => false],
                    ['name' => 'Precision Liquid Eyeliner Black',      'price' => 650,  'sale_price' => 580,  'stock' => 180, 'featured' => false],
                    ['name' => 'Micro Brow Pencil – Dark Brown',       'price' => 780,  'sale_price' => null,  'stock' => 95,  'featured' => false],
                    ['name' => 'Glitter Eye Topper Duo',               'price' => 950,  'sale_price' => 820,  'stock' => 60,  'featured' => true],
                ],
            ],
            [
                'name'        => 'Blush & Highlighter',
                'description' => 'Cheek colors and luminizers to sculpt and glow.',
                'products'    => [
                    ['name' => 'Peach Flush Blush Powder',       'price' => 1200, 'sale_price' => null,  'stock' => 70, 'featured' => true],
                    ['name' => 'Liquid Highlighter – Champagne',  'price' => 1450, 'sale_price' => 1250, 'stock' => 55, 'featured' => false],
                    ['name' => 'Contour & Blush Duo Palette',     'price' => 1800, 'sale_price' => null,  'stock' => 45, 'featured' => false],
                    ['name' => 'Pressed Shimmer Highlighter',     'price' => 980,  'sale_price' => 850,  'stock' => 90, 'featured' => false],
                    ['name' => 'Cream Blush Stick – Coral',       'price' => 870,  'sale_price' => null,  'stock' => 100, 'featured' => true],
                ],
            ],
            [
                'name'        => 'Body Care',
                'description' => 'Luxurious body lotions, scrubs, and oils for silky skin.',
                'products'    => [
                    ['name' => 'Shea & Mango Body Butter',    'price' => 1350, 'sale_price' => 1150, 'stock' => 85, 'featured' => true],
                    ['name' => 'Coffee & Coconut Body Scrub',  'price' => 980,  'sale_price' => null,  'stock' => 70, 'featured' => false],
                    ['name' => 'Firming Body Lotion SPF15',   'price' => 1200, 'sale_price' => 1000, 'stock' => 60, 'featured' => false],
                    ['name' => 'Rose Hip Body Oil 100ml',     'price' => 1600, 'sale_price' => null,  'stock' => 40, 'featured' => false],
                    ['name' => 'Cooling Peppermint Foot Cream','price' => 750,  'sale_price' => 650,  'stock' => 120, 'featured' => true],
                ],
            ],
            [
                'name'        => 'Hair Care',
                'description' => 'Shampoos, conditioners, masks, and oils for healthy hair.',
                'products'    => [
                    ['name' => 'Argan Oil Repair Shampoo 400ml',    'price' => 1100, 'sale_price' => 950,  'stock' => 80, 'featured' => true],
                    ['name' => 'Deep Moisture Hair Mask 300g',       'price' => 1300, 'sale_price' => null,  'stock' => 55, 'featured' => false],
                    ['name' => 'Keratin Smoothing Conditioner',      'price' => 980,  'sale_price' => 880,  'stock' => 70, 'featured' => false],
                    ['name' => 'Lightweight Hair Growth Serum',      'price' => 1800, 'sale_price' => 1550, 'stock' => 35, 'featured' => false],
                    ['name' => 'Frizz Control Hair Mist 150ml',      'price' => 750,  'sale_price' => null,  'stock' => 110, 'featured' => true],
                ],
            ],
            [
                'name'        => 'Fragrances',
                'description' => 'Captivating perfumes and body mists for every personality.',
                'products'    => [
                    ['name' => 'Oud & Rose Eau de Parfum 50ml',   'price' => 3500, 'sale_price' => 2999, 'stock' => 30, 'featured' => true],
                    ['name' => 'Floral Bloom Body Mist 200ml',     'price' => 1200, 'sale_price' => null,  'stock' => 90, 'featured' => false],
                    ['name' => 'Vanilla & Sandalwood EDP 30ml',    'price' => 2800, 'sale_price' => 2450, 'stock' => 25, 'featured' => false],
                    ['name' => 'Fresh Citrus Cologne 100ml',        'price' => 1800, 'sale_price' => null,  'stock' => 50, 'featured' => false],
                    ['name' => 'Black Musk Parfum 10ml Travel',    'price' => 950,  'sale_price' => 820,  'stock' => 75, 'featured' => true],
                ],
            ],
            [
                'name'        => 'Sun Care',
                'description' => 'Sun protection sprays, sticks, and after-sun care.',
                'products'    => [
                    ['name' => 'Ultra-Light Sunscreen SPF50 50ml',  'price' => 1450, 'sale_price' => 1250, 'stock' => 100, 'featured' => true],
                    ['name' => 'Invisible Sunscreen Spray SPF30',    'price' => 1200, 'sale_price' => null,  'stock' => 80,  'featured' => false],
                    ['name' => 'Tinted Mineral Sunscreen SPF40',    'price' => 1650, 'sale_price' => 1420, 'stock' => 55,  'featured' => false],
                    ['name' => 'Soothing After-Sun Gel 200ml',      'price' => 850,  'sale_price' => null,  'stock' => 70,  'featured' => false],
                    ['name' => 'Kids Safe Sunblock SPF60 100ml',    'price' => 1300, 'sale_price' => 1100, 'stock' => 45,  'featured' => true],
                ],
            ],
            [
                'name'        => 'Nail Care',
                'description' => 'Polishes, treatments, and tools for perfect nails.',
                'products'    => [
                    ['name' => 'Gel-Effect Nail Polish – Midnight',  'price' => 480, 'sale_price' => null, 'stock' => 200, 'featured' => true],
                    ['name' => 'Strengthening Base Coat',             'price' => 420, 'sale_price' => 380, 'stock' => 150, 'featured' => false],
                    ['name' => 'Quick-Dry Top Coat',                  'price' => 420, 'sale_price' => null, 'stock' => 160, 'featured' => false],
                    ['name' => 'Cuticle Nourishing Oil 15ml',        'price' => 580, 'sale_price' => 520, 'stock' => 120, 'featured' => false],
                    ['name' => 'Nail Polish Remover Pads Pack 50',   'price' => 350, 'sale_price' => null, 'stock' => 250, 'featured' => true],
                ],
            ],
        ];

        foreach ($categories as $catData) {
            $products = $catData['products'];
            unset($catData['products']);

            $catData['slug']      = Str::slug($catData['name']);
            $catData['is_active'] = true;
            $catData['meta_title'] = $catData['name'] . ' – GlowHaus Beauty';

            $category = Category::updateOrCreate(['slug' => $catData['slug']], $catData);

            $skuPrefix = strtoupper(substr(Str::slug($catData['name']), 0, 3));
            $skuIndex  = 1;

            foreach ($products as $prodData) {
                $slug = Str::slug($prodData['name']);
                // Ensure unique slug
                $slugBase  = $slug;
                $slugCount = 1;
                while (Product::where('slug', $slug)->where('category_id', '!=', $category->id)->exists()) {
                    $slug = $slugBase . '-' . $slugCount++;
                }

                $sku = $skuPrefix . '-' . str_pad($skuIndex++, 3, '0', STR_PAD_LEFT);

                $desc = "<p><strong>{$prodData['name']}</strong> is a premium beauty product from our {$catData['name']} collection. "
                    . "Crafted with high-quality ingredients to deliver outstanding results. "
                    . "Suitable for all skin types. Apply daily for best results.</p>"
                    . "<ul><li>Premium quality formulation</li><li>Dermatologically tested</li>"
                    . "<li>Free from harmful chemicals</li><li>Cruelty-free & vegan</li></ul>";

                Product::updateOrCreate(['sku' => $sku], [
                    'category_id' => $category->id,
                    'name'        => $prodData['name'],
                    'slug'        => $slug,
                    'sku'         => $sku,
                    'price'       => $prodData['price'],
                    'sale_price'  => $prodData['sale_price'],
                    'stock'       => $prodData['stock'],
                    'description' => $desc,
                    'is_active'   => true,
                    'is_featured' => $prodData['featured'],
                    'meta_title'  => $prodData['name'] . ' – GlowHaus Beauty',
                    'meta_description' => "Buy {$prodData['name']} online at GlowHaus Beauty. Premium cosmetics at great prices.",
                ]);
            }
        }
    }
}

