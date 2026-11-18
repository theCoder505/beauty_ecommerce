<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RichEditorComponentTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name'     => 'Editor Admin',
            'email'    => 'editor@beauty.test',
            'password' => bcrypt('password123'),
        ]);
    }

    /**
     * Test admin blog post create & edit views contain the reusable rich editor component.
     */
    public function test_blog_posts_admin_pages_use_rich_editor_component(): void
    {
        $category = BlogCategory::create(['name' => 'Skincare Rituals', 'slug' => 'skincare-rituals', 'is_active' => true]);

        // Create page
        $res = $this->actingAs($this->admin, 'admin')->get(route('admin.blog-posts.create'));
        $res->assertOk();
        $res->assertSee('rich-editor-wrapper', false);
        $res->assertSee('rich-editor-img-overlay', false);
        $res->assertSee('data-action="align-center"', false);
        $res->assertSee('data-action="align-left"', false);
        $res->assertSee('data-action="align-right"', false);
        $res->assertSee('handle-nw', false);
        $res->assertSee('name="content"', false);

        // Edit page
        $post = BlogPost::create([
            'title' => 'Botanical Alchemy',
            'slug' => 'botanical-alchemy',
            'blog_category_id' => $category->id,
            'content' => '<h2>Sublime Scents</h2><p>Infused with <u>organic essence</u>.</p>',
            'is_published' => true,
        ]);

        $resEdit = $this->actingAs($this->admin, 'admin')->get(route('admin.blog-posts.edit', $post));
        $resEdit->assertOk();
        $resEdit->assertSee('rich-editor-wrapper', false);
        $resEdit->assertSee('rich-editor-img-overlay', false);
        $resEdit->assertSee('Botanical Alchemy', false);
        $resEdit->assertSee('Sublime Scents', false);
    }

    /**
     * Test admin products create & edit views contain the reusable rich editor component.
     */
    public function test_products_admin_pages_use_rich_editor_component(): void
    {
        $category = Category::create(['name' => 'Perfumes', 'slug' => 'perfumes', 'is_active' => true]);

        // Create page
        $res = $this->actingAs($this->admin, 'admin')->get(route('admin.products.create'));
        $res->assertOk();
        $res->assertSee('rich-editor-wrapper', false);
        $res->assertSee('rich-editor-img-overlay', false);
        $res->assertSee('name="description"', false);

        // Edit page
        $product = Product::create([
            'name' => 'Ambre Royal Fragrance',
            'slug' => 'ambre-royal-fragrance',
            'sku' => 'TRF-AMB-01',
            'category_id' => $category->id,
            'price' => 145.00,
            'stock' => 10,
            'description' => '<h3>Olfactory Symphony</h3><p>Warm amber and <u>Bourbon vanilla</u>.</p>',
            'is_active' => true,
        ]);

        $resEdit = $this->actingAs($this->admin, 'admin')->get(route('admin.products.edit', $product));
        $resEdit->assertOk();
        $resEdit->assertSee('rich-editor-wrapper', false);
        $resEdit->assertSee('rich-editor-img-overlay', false);
        $resEdit->assertSee('Ambre Royal Fragrance', false);
        $resEdit->assertSee('Olfactory Symphony', false);
    }

    /**
     * Test admin legal pages (terms & privacy) contain the reusable rich editor component.
     */
    public function test_terms_and_privacy_admin_pages_use_rich_editor_component(): void
    {
        // Terms
        $termsRes = $this->actingAs($this->admin, 'admin')->get(route('admin.terms.edit'));
        $termsRes->assertOk();
        $termsRes->assertSee('rich-editor-wrapper', false);
        $termsRes->assertSee('rich-editor-img-overlay', false);
        $termsRes->assertSee('data-action="align-center"', false);

        // Privacy
        $privacyRes = $this->actingAs($this->admin, 'admin')->get(route('admin.privacy.edit'));
        $privacyRes->assertOk();
        $privacyRes->assertSee('rich-editor-wrapper', false);
        $privacyRes->assertSee('rich-editor-img-overlay', false);
        $privacyRes->assertSee('data-action="align-center"', false);
    }

    /**
     * Test frontend blog detail page renders rich formatted HTML with headings, underlines, and images.
     */
    public function test_frontend_blog_page_renders_rich_content(): void
    {
        $category = BlogCategory::create(['name' => 'Fragrance Notes', 'slug' => 'fragrance-notes', 'is_active' => true]);

        $post = BlogPost::create([
            'title' => 'The Art of Scent Layering',
            'slug' => 'art-of-scent-layering',
            'blog_category_id' => $category->id,
            'content' => '<h1>Masterclass</h1><p>Learn how to apply <u>bergamot and cedar</u>.</p><p class="ql-align-center"><img src="https://example.com/bottle.jpg" class="ql-align-center editor-img-center" style="width: 400px; display: block; margin: 1rem auto;" alt="Perfume Bottle"></p>',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $res = $this->get(route('blog.show', $post->slug));
        $res->assertOk();
        $res->assertSee('rich-content', false);
        $res->assertSee('prose', false);
        $res->assertSee('<h1>Masterclass</h1>', false);
        $res->assertSee('<u>bergamot and cedar</u>', false);
        $res->assertSee('https://example.com/bottle.jpg', false);
        $res->assertSee('ql-align-center', false);
    }

    /**
     * Test frontend product detail page renders rich formatted HTML with headings, underlines, and images.
     */
    public function test_frontend_product_page_renders_rich_content(): void
    {
        $category = Category::create(['name' => 'Elixirs', 'slug' => 'elixirs', 'is_active' => true]);

        $product = Product::create([
            'name' => 'Velvet Rose Eau de Parfum',
            'slug' => 'velvet-rose-edp',
            'sku' => 'TRF-VLV-02',
            'category_id' => $category->id,
            'price' => 195.00,
            'stock' => 15,
            'description' => '<h2>Fragrance Notes</h2><p>Infused with <u>Turkish rose petals</u> and sparkling mandarin.</p><p><img src="https://example.com/rose-note.jpg" style="width: 300px; float: left; margin: 0.5rem 1.5rem 1rem 0;" class="editor-img-left ql-align-left" alt="Rose Note"></p>',
            'is_active' => true,
        ]);

        $res = $this->get(route('products.show', $product->slug));
        $res->assertOk();
        $res->assertSee('rich-content', false);
        $res->assertSee('prose', false);
        $res->assertSee('<h2>Fragrance Notes</h2>', false);
        $res->assertSee('<u>Turkish rose petals</u>', false);
        $res->assertSee('https://example.com/rose-note.jpg', false);
        $res->assertSee('editor-img-left', false);
    }

    /**
     * Test frontend terms and privacy pages render rich content with proper styles.
     */
    public function test_frontend_terms_and_privacy_render_rich_content(): void
    {
        Setting::create([
            'key' => 'terms_and_conditions',
            'value' => '<h1>Terms of Service</h1><p>By using our boutique, you agree to our <u>luxury guarantee</u>.</p><p class="ql-align-center"><img src="https://example.com/seal.png" class="editor-img-center ql-align-center" style="display: block; margin: 1rem auto; width: 250px;" alt="Boutique Seal"></p>',
            'group' => 'legal',
        ]);

        Setting::create([
            'key' => 'privacy_policy',
            'value' => '<h1>Customer Data Pledge</h1><p>We believe in <u>absolute transparency</u> regarding customer security.</p><p><img src="https://example.com/lock.png" class="editor-img-right ql-align-right" style="float: right; margin: 0.5rem 0 1rem 1.5rem; width: 150px;" alt="Data Shield"></p>',
            'group' => 'legal',
        ]);

        // Terms public page
        $termsRes = $this->get(route('terms'));
        $termsRes->assertOk();
        $termsRes->assertSee('rich-content', false);
        $termsRes->assertSee('prose', false);
        $termsRes->assertSee('<h1>Terms of Service</h1>', false);
        $termsRes->assertSee('<u>luxury guarantee</u>', false);
        $termsRes->assertSee('https://example.com/seal.png', false);

        // Privacy public page
        $privacyRes = $this->get(route('privacy'));
        $privacyRes->assertOk();
        $privacyRes->assertSee('rich-content', false);
        $privacyRes->assertSee('prose', false);
        $privacyRes->assertSee('<h1>Customer Data Pledge</h1>', false);
        $privacyRes->assertSee('<u>absolute transparency</u>', false);
        $privacyRes->assertSee('https://example.com/lock.png', false);
    }
}
