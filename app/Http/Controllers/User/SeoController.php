<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\BlogPost;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $products   = Product::where('is_active', true)->select('slug', 'updated_at')->get();
        $categories = Category::where('is_active', true)->select('slug', 'updated_at')->get();
        $posts      = BlogPost::where('is_published', true)->select('slug', 'updated_at')->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Static pages
        $statics = [url('/'), url('/shop'), url('/blog')];
        foreach ($statics as $url) {
            $xml .= "<url><loc>{$url}</loc><changefreq>weekly</changefreq><priority>1.0</priority></url>\n";
        }

        foreach ($categories as $cat) {
            $url = route('shop.category', $cat->slug);
            $xml .= "<url><loc>{$url}</loc><lastmod>{$cat->updated_at->toDateString()}</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>\n";
        }

        foreach ($products as $product) {
            $url = route('products.show', $product->slug);
            $xml .= "<url><loc>{$url}</loc><lastmod>{$product->updated_at->toDateString()}</lastmod><changefreq>weekly</changefreq><priority>0.9</priority></url>\n";
        }

        foreach ($posts as $post) {
            $url = route('blog.show', $post->slug);
            $xml .= "<url><loc>{$url}</loc><lastmod>{$post->updated_at->toDateString()}</lastmod><changefreq>monthly</changefreq><priority>0.7</priority></url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots(): Response
    {
        $content = "User-agent: *\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /dashboard/\n";
        $content .= "Allow: /\n\n";
        $content .= "Sitemap: " . url('/sitemap.xml') . "\n";

        return response($content, 200, ['Content-Type' => 'text/plain']);
    }
}

