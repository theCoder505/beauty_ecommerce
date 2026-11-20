<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MoreBlogPostsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = BlogCategory::all();
        if ($categories->isEmpty()) {
            return;
        }

        $posts = [
            [
                'title' => 'The Power of Hyaluronic Acid: Why Your Skin Needs It Now',
                'excerpt' => 'Dive deep into how hyaluronic acid acts as a moisture magnet, plumping fine lines and reviving dry, tired skin.',
                'cat' => 'Skincare Guides',
                'image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'title' => 'Summer Glow: Top Sunscreens That Leave Zero White Cast',
                'excerpt' => 'Our beauty editors tested dozens of daily broad-spectrum sunscreens to find the ones that melt seamlessly into all skin tones.',
                'cat' => 'Skincare Guides',
                'image' => 'https://images.unsplash.com/photo-1598440947619-2c35fc9aa908?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'title' => 'Lip Care Secrets: How to Keep Your Lips Plump & Hydrated',
                'excerpt' => 'Say goodbye to chapped lips with our dermatologist-approved nighttime lip treatment routine and gentle exfoliators.',
                'cat' => 'Beauty Tips',
                'image' => 'https://images.unsplash.com/photo-1586495777744-4413f21062fa?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'title' => 'Mastering the 10-Step Korean Glass Skin Routine at Home',
                'excerpt' => 'Everything you need to know about double cleansing, essence layering, and achieving that ultra-radiant glass skin finish.',
                'cat' => 'Skincare Guides',
                'image' => 'https://images.unsplash.com/photo-1512290900672-1f487e59670d?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'title' => 'The Complete Guide to Hair Oiling for Scalp Health & Volume',
                'excerpt' => 'Discover the ancient ritual of scalp massaging with rosemary, argan, and jojoba oils to stimulate healthy hair growth.',
                'cat' => 'Beauty Tips',
                'image' => 'https://images.unsplash.com/photo-1527799820374-dcf8d9d4a388?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'title' => 'Everyday Natural Makeup Tutorial: Fresh & Youthful in Minutes',
                'excerpt' => 'Learn how to accentuate your best features with tinted moisturizers, soft cream blushes, and feathery brows.',
                'cat' => 'Makeup Tutorials',
                'image' => 'https://images.unsplash.com/photo-1512496015851-a90fb38ba796?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'title' => 'Retinol vs Bakuchiol: Which Youth-Boosting Active Is Right for You?',
                'excerpt' => 'A clinical comparison between the gold standard anti-aging vitamin A and its gentle plant-derived counterpart.',
                'cat' => 'Skincare Guides',
                'image' => 'https://images.unsplash.com/photo-1608248597359-24225574514c?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'title' => 'How to Prep Your Skin for a Big Event: A 3-Day Countdown',
                'excerpt' => 'Expert facialists share their 72-hour pre-event timeline for calm, glowing, photogenic complexion without redness.',
                'cat' => 'Beauty Tips',
                'image' => 'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'title' => 'Smoky Eyes Made Simple: Pro Tricks for Long-Lasting Shadow',
                'excerpt' => 'Step-by-step blending techniques and primer secrets to achieve effortless smoldering eyes without fallout.',
                'cat' => 'Makeup Tutorials',
                'image' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'title' => 'Body Care as Self-Care: Exfoliating, Moisturization & Barrier Care',
                'excerpt' => 'Why your body deserves the same active ingredients as your face, from AHA body washes to ceramide rich creams.',
                'cat' => 'Beauty Tips',
                'image' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'title' => 'Niacinamide Benefits: Pore Minimizing, Oil Balance & Calming',
                'excerpt' => 'Meet the all-in-one vitamin B3 multitasking ingredient that soothes redness, tightens pores, and strengthens resilience.',
                'cat' => 'Skincare Guides',
                'image' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=600&q=80',
            ],
        ];

        foreach ($posts as $idx => $p) {
            $cat = $categories->firstWhere('name', $p['cat']) ?? $categories->first();
            BlogPost::updateOrCreate(
                ['slug' => Str::slug($p['title'])],
                [
                    'blog_category_id' => $cat->id,
                    'title' => $p['title'],
                    'excerpt' => $p['excerpt'],
                    'content' => '<p>' . $p['excerpt'] . '</p><p>Experience the ultimate in modern beauty and evidence-based self-care rituals tailored for your daily routine.</p>',
                    'cover_image' => $p['image'],
                    'is_published' => true,
                    'published_at' => now()->subDays($idx + 1),
                    'views_count' => rand(40, 250),
                ]
            );
        }
    }
}
