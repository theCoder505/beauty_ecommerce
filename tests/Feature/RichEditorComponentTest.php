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

    /**
     * Test full roundtrip: Quill features written in admin (terms, privacy, product, blog)
     * are set in database without stripping and rendered accurately on surface pages.
     */
    public function test_terms_privacy_product_and_blog_preserve_and_render_all_quill_features_on_surface(): void
    {
        $complexQuillHtml = '<h2 class="ql-align-center" style="text-align: center;">Centered Luxury Title</h2>'
            . '<p class="ql-align-right" style="text-align: right;">Right Aligned Subheading</p>'
            . '<p class="ql-indent-1">Indented paragraph level 1</p>'
            . '<p><span style="color: rgb(220, 38, 38); background-color: rgb(254, 240, 138);">Highlighted &amp; colored phrase</span></p>'
            . '<p><span class="ql-size-large">Large emphasis</span>, <u>underlined luxury</u>, <s>discounted</s>, and H<sub>2</sub>O formula</p>'
            . '<ol>'
            . '<li data-list="bullet" class="ql-indent-1"><span class="ql-ui"></span>Prestige Bullet Item</li>'
            . '</ol>'
            . '<blockquote class="ql-align-center" style="text-align: center;">Verified craftsmanship quote</blockquote>'
            . '<p><img src="https://example.com/artisan.avif" alt="Artisan Craft" style="display: block; margin-left: auto; margin-right: auto; width: 350px; border-radius: 12px;" class="editor-img-center ql-align-center"></p>'
            . '<table style="width: 100%; border: 1px solid #e5e7eb;"><thead><tr><th>Attribute</th><th>Specification</th></tr></thead><tbody><tr><td>Origin</td><td>Grasse, France</td></tr></tbody></table>';

        // 1. Terms roundtrip: Admin updates terms -> Verify saved -> Verify surface page
        $this->actingAs($this->admin, 'admin')->post(route('admin.terms.update'), ['content' => $complexQuillHtml])->assertRedirect(route('admin.terms.edit'));
        $savedTerms = Setting::where('key', 'terms_and_conditions')->value('value');
        $this->assertStringContainsString('Centered Luxury Title', $savedTerms);
        $this->assertStringContainsString('ql-align-center', $savedTerms);
        $this->assertStringContainsString('ql-align-right', $savedTerms);
        $this->assertStringContainsString('data-list="bullet"', $savedTerms);
        $this->assertStringContainsString('ql-indent-1', $savedTerms);
        $this->assertStringContainsString('artisan.avif', $savedTerms);
        $this->assertStringContainsString('display:block', $savedTerms);
        $this->assertStringContainsString('border-radius:12px', $savedTerms);

        $termsSurface = $this->get(route('terms'));
        $termsSurface->assertOk();
        $termsSurface->assertSee('Centered Luxury Title', false);
        $termsSurface->assertSee('ql-align-center', false);
        $termsSurface->assertSee('data-list="bullet"', false);
        $termsSurface->assertSee('artisan.avif', false);
        $termsSurface->assertSee('Origin', false);

        // 2. Privacy roundtrip: Admin updates privacy -> Verify saved -> Verify surface page
        $this->actingAs($this->admin, 'admin')->post(route('admin.privacy.update'), ['content' => $complexQuillHtml])->assertRedirect(route('admin.privacy.edit'));
        $savedPrivacy = Setting::where('key', 'privacy_policy')->value('value');
        $this->assertStringContainsString('Centered Luxury Title', $savedPrivacy);
        $this->assertStringContainsString('data-list="bullet"', $savedPrivacy);

        $privacySurface = $this->get(route('privacy'));
        $privacySurface->assertOk();
        $privacySurface->assertSee('Centered Luxury Title', false);
        $privacySurface->assertSee('ql-align-center', false);
        $privacySurface->assertSee('data-list="bullet"', false);
        $privacySurface->assertSee('artisan.avif', false);

        // 3. Blog roundtrip: Admin creates blog post -> Verify saved -> Verify surface page
        $blogCat = BlogCategory::create(['name' => 'High Perfumery', 'slug' => 'high-perfumery', 'is_active' => true]);
        $this->actingAs($this->admin, 'admin')->post(route('admin.blog-posts.store'), [
            'title' => 'Mastering Olfactory Notes',
            'blog_category_id' => $blogCat->id,
            'content' => $complexQuillHtml,
            'is_published' => true,
        ])->assertRedirect(route('admin.blog-posts.index'));

        $savedBlog = BlogPost::where('slug', 'mastering-olfactory-notes')->firstOrFail();
        $this->assertStringContainsString('Centered Luxury Title', $savedBlog->content);
        $this->assertStringContainsString('data-list="bullet"', $savedBlog->content);
        $this->assertStringContainsString('ql-align-center', $savedBlog->content);

        $blogSurface = $this->get(route('blog.show', $savedBlog->slug));
        $blogSurface->assertOk();
        $blogSurface->assertSee('Centered Luxury Title', false);
        $blogSurface->assertSee('ql-align-center', false);
        $blogSurface->assertSee('data-list="bullet"', false);
        $blogSurface->assertSee('artisan.avif', false);

        // 4. Product roundtrip: Admin creates product -> Verify saved -> Verify surface page
        $prodCat = Category::create(['name' => 'Parfums', 'slug' => 'parfums', 'is_active' => true]);
        $this->actingAs($this->admin, 'admin')->post(route('admin.products.store'), [
            'name' => 'Rose Noir Absolu',
            'sku' => 'RN-001',
            'category_id' => $prodCat->id,
            'price' => 220.00,
            'stock' => 12,
            'thumbnail' => \Illuminate\Http\UploadedFile::fake()->image('thumb.jpg'),
            'description' => $complexQuillHtml,
            'is_active' => true,
        ])->assertRedirect(route('admin.products.index'));

        $savedProduct = Product::where('sku', 'RN-001')->firstOrFail();
        $this->assertStringContainsString('Centered Luxury Title', $savedProduct->description);
        $this->assertStringContainsString('data-list="bullet"', $savedProduct->description);
        $this->assertStringContainsString('ql-align-center', $savedProduct->description);

        $productSurface = $this->get(route('products.show', $savedProduct->slug));
        $productSurface->assertOk();
        $productSurface->assertSee('Centered Luxury Title', false);
        $productSurface->assertSee('ql-align-center', false);
        $productSurface->assertSee('data-list="bullet"', false);
        $productSurface->assertSee('artisan.avif', false);
    }

    /**
     * Test image left/right auto-fit wrapping and center next-line behavior across all 4 surfaces.
     */
    public function test_image_left_right_autofit_and_center_next_line_on_all_surfaces(): void
    {
        $content = '<h2>Left Aligned Section</h2>'
            . '<p><img src="https://example.com/left.avif" alt="Left" class="editor-img-left ql-align-left" style="width:300px;float:left;margin:0.5rem 1.5rem 0.75rem 0;"></p>'
            . '<p>This text auto-fits on the right side of the left-floated image.</p>'
            . '<h2>Right Aligned Section</h2>'
            . '<p><img src="https://example.com/right.avif" alt="Right" class="editor-img-right ql-align-right" style="width:300px;float:right;margin:0.5rem 0 0.75rem 1.5rem;"></p>'
            . '<p>This text auto-fits on the left side of the right-floated image.</p>'
            . '<h2>Centered Section</h2>'
            . '<p class="ql-align-center"><img src="https://example.com/center.avif" alt="Center" class="editor-img-center ql-align-center" style="width:300px;display:block;margin:1rem auto;float:none;"></p>'
            . '<p>This text appears simply on the next line below the centered image.</p>';

        // 1. Terms
        $this->actingAs($this->admin, 'admin')->post(route('admin.terms.update'), ['content' => $content]);
        $res = $this->get(route('terms'));
        $res->assertOk();
        $res->assertSee('editor-img-left', false);
        $res->assertSee('editor-img-right', false);
        $res->assertSee('editor-img-center', false);
        $res->assertSee('left.avif', false);
        $res->assertSee('right.avif', false);
        $res->assertSee('center.avif', false);

        // 2. Privacy
        $this->actingAs($this->admin, 'admin')->post(route('admin.privacy.update'), ['content' => $content]);
        $res = $this->get(route('privacy'));
        $res->assertOk();
        $res->assertSee('editor-img-left', false);
        $res->assertSee('editor-img-right', false);
        $res->assertSee('editor-img-center', false);

        // 3. Blog
        $blogCat = BlogCategory::first() ?? BlogCategory::create(['name' => 'Skin Tips', 'slug' => 'skin-tips', 'is_active' => true]);
        $blog = BlogPost::create([
            'title' => 'Alignment Demonstration',
            'slug' => 'alignment-demonstration',
            'blog_category_id' => $blogCat->id,
            'content' => $content,
            'is_published' => true,
        ]);
        $res = $this->get(route('blog.show', $blog->slug));
        $res->assertOk();
        $res->assertSee('editor-img-left', false);
        $res->assertSee('editor-img-right', false);
        $res->assertSee('editor-img-center', false);

        // 4. Product
        $prodCat = Category::first() ?? Category::create(['name' => 'Serums', 'slug' => 'serums', 'is_active' => true]);
        $prod = Product::create([
            'name' => 'Luminous Glow Serum',
            'slug' => 'luminous-glow-serum',
            'sku' => 'LGS-001',
            'category_id' => $prodCat->id,
            'price' => 85.00,
            'stock' => 20,
            'description' => $content,
            'is_active' => true,
        ]);
        $res = $this->get(route('products.show', $prod->slug));
        $res->assertOk();
        $res->assertSee('editor-img-left', false);
        $res->assertSee('editor-img-right', false);
        $res->assertSee('editor-img-center', false);
    }
}
