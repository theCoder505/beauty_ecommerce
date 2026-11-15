<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $cats = [
            ['name' => 'Beauty Tips', 'slug' => 'beauty-tips', 'is_active' => true],
            ['name' => 'Skincare Guides', 'slug' => 'skincare-guides', 'is_active' => true],
            ['name' => 'Makeup Tutorials', 'slug' => 'makeup-tutorials', 'is_active' => true],
        ];

        $catModels = [];
        foreach ($cats as $cat) {
            $catModels[$cat['slug']] = BlogCategory::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        $posts = [
            [
                'category' => 'beauty-tips',
                'title'    => '10 Morning Skincare Habits That Will Transform Your Skin',
                'excerpt'  => 'Starting your day with the right skincare routine can make a world of difference. Here are 10 habits to adopt this year.',
                'content'  => '<p>A consistent morning routine is the foundation of healthy, glowing skin. Here are our top 10 habits...</p><h2>1. Cleanse Gently</h2><p>Use a gentle, pH-balanced cleanser to remove overnight buildup without stripping your skin barrier.</p><h2>2. Tone & Prep</h2><p>A hydrating toner preps your skin to absorb the next products better.</p><h2>3. Vitamin C Serum</h2><p>Apply a vitamin C serum to brighten, protect from UV damage, and boost collagen production.</p><h2>4. Moisturize</h2><p>Even oily skin needs moisture. Choose a lightweight, non-comedogenic formula.</p><h2>5. Never Skip SPF</h2><p>Broad-spectrum SPF 30+ is non-negotiable, rain or shine.</p>',
            ],
            [
                'category' => 'skincare-guides',
                'title'    => 'The Ultimate Guide to Building a Skincare Routine for Beginners',
                'excerpt'  => 'Starting with skincare can feel overwhelming. This beginner-friendly guide breaks it down step by step.',
                'content'  => '<p>Skincare doesn\'t have to be complicated. For beginners, the golden rule is: Cleanse, Moisturize, SPF. Once you\'ve mastered those three, you can layer in actives like retinol, niacinamide, and exfoliants over time.</p><h2>Step 1: Know Your Skin Type</h2><p>Understanding whether you have dry, oily, combination, or sensitive skin is the first step to choosing the right products.</p><h2>Step 2: Start Simple</h2><p>A 3-step routine (cleanser, moisturizer, SPF) done consistently beats a 10-step routine done twice a week.</p>',
            ],
            [
                'category' => 'makeup-tutorials',
                'title'    => 'How to Achieve a Flawless Base in 5 Minutes',
                'excerpt'  => 'A perfect foundation base is the key to any makeup look. Master this in 5 minutes with our expert tips.',
                'content'  => '<p>A flawless base starts long before foundation — it starts with prep. Here\'s how to nail it quickly.</p><h2>Prep Your Skin</h2><p>Always start with moisturized, primed skin. A hydrating primer helps foundation grip and last longer.</p><h2>Choose the Right Formula</h2><p>Dewy finishes suit dry skin; matte or satin suits oily or combination skin.</p><h2>Application Technique</h2><p>Apply with a damp beauty sponge for a natural, skin-like finish. Build coverage only where needed.</p>',
            ],
            [
                'category' => 'beauty-tips',
                'title'    => 'The Best Ingredients for Anti-Aging Skincare',
                'excerpt'  => 'Science-backed ingredients that actually work for reducing fine lines and wrinkles.',
                'content'  => '<p>When it comes to anti-aging, not all ingredients are created equal. These are the ones with the most research behind them.</p><h2>Retinol</h2><p>The gold standard of anti-aging. It speeds cell turnover and boosts collagen production.</p><h2>Hyaluronic Acid</h2><p>Plumps and hydrates the skin, temporarily reducing the appearance of fine lines.</p><h2>Peptides</h2><p>Signal the skin to produce more collagen and elastin.</p>',
            ],
            [
                'category' => 'skincare-guides',
                'title'    => 'How to Layer Skincare Products in the Right Order',
                'excerpt'  => 'The order you apply skincare products matters as much as what you use. Here\'s the definitive guide.',
                'content'  => '<p>Applying products in the correct order ensures maximum absorption and effectiveness. The golden rule: thinnest to thickest consistency.</p><ol><li>Cleanser</li><li>Toner</li><li>Essence or face mist</li><li>Serum (or multiple, lightest first)</li><li>Eye cream</li><li>Moisturizer</li><li>Face oil (AM: skip; PM: optional)</li><li>Sunscreen (AM only)</li></ol>',
            ],
        ];

        foreach ($posts as $index => $post) {
            $slug    = Str::slug($post['title']);
            $catId   = $catModels[$post['category']]->id ?? null;
            BlogPost::updateOrCreate(['slug' => $slug], [
                'blog_category_id' => $catId,
                'title'            => $post['title'],
                'slug'             => $slug,
                'excerpt'          => $post['excerpt'],
                'content'          => $post['content'],
                'is_published'     => true,
                'published_at'     => now()->subDays(($index + 1) * 3),
                'meta_title'       => $post['title'] . ' | GlowHaus Beauty Blog',
                'meta_description' => $post['excerpt'],
            ]);
        }
    }
}

