<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LegalPagesRichTextEditorTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name'     => 'Legal Admin',
            'email'    => 'legal@beauty.test',
            'password' => bcrypt('password123'),
        ]);
    }

    /**
     * Test admin can access dedicated Terms & Conditions page with Quill RichTextEditor.
     */
    public function test_admin_can_view_dedicated_terms_editor_page(): void
    {
        Setting::create([
            'key' => 'terms_and_conditions',
            'value' => '<h2>1. Authentic Luxury</h2><p>All items guaranteed authentic.</p>',
            'group' => 'legal',
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.terms.edit'));

        $response->assertOk();
        $response->assertSee('Terms & Conditions Editor', false);
        $response->assertSee('id="quill-editor"', false);
        $response->assertSee('quill.snow.css', false);
        $response->assertSee('quill.js', false);
        $response->assertSee('Authentic Luxury', false);
    }

    /**
     * Test admin can update Terms & Conditions with rich HTML and inline images.
     */
    public function test_admin_can_update_terms_with_rich_html_and_image(): void
    {
        $richContent = '<h2>Official Store Agreement</h2><p>Welcome to our flagship store.</p><p><img src="https://example.com/badge.png" alt="Guarantee Badge"></p><ul><li>Safe shopping</li><li>Fast shipping</li></ul>';

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.terms.update'), [
            'content' => $richContent,
        ]);

        $response->assertRedirect(route('admin.terms.edit'));
        $response->assertSessionHas('success');

        $saved = Setting::where('key', 'terms_and_conditions')->value('value');
        $this->assertStringContainsString('Official Store Agreement', $saved);
        $this->assertStringContainsString('<img', $saved);
        $this->assertStringContainsString('https://example.com/badge.png', $saved);

        // Public page renders the rich HTML and image tags without escaping
        $publicRes = $this->get(route('terms'));
        $publicRes->assertOk();
        $publicRes->assertSee('<h2>Official Store Agreement</h2>', false);
        $publicRes->assertSee('<img src="https://example.com/badge.png"', false);
        $publicRes->assertSee('<li>Safe shopping</li>', false);
    }

    /**
     * Test admin can access dedicated Privacy Policy page with Quill RichTextEditor.
     */
    public function test_admin_can_view_dedicated_privacy_editor_page(): void
    {
        Setting::create([
            'key' => 'privacy_policy',
            'value' => '<h2>Data Safeguards</h2><p>Your privacy is strictly respected.</p>',
            'group' => 'legal',
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.privacy.edit'));

        $response->assertOk();
        $response->assertSee('Privacy Policy Editor', false);
        $response->assertSee('id="quill-editor"', false);
        $response->assertSee('quill.snow.css', false);
        $response->assertSee('quill.js', false);
        $response->assertSee('Data Safeguards', false);
    }

    /**
     * Test admin can update Privacy Policy with rich HTML and inline images.
     */
    public function test_admin_can_update_privacy_with_rich_html_and_image(): void
    {
        $richPrivacy = '<h2>GDPR & Customer Privacy</h2><p>We treat your personal info with top-tier encryption.</p><p><img src="https://example.com/security-shield.png" alt="Security Shield"></p>';

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.privacy.update'), [
            'content' => $richPrivacy,
        ]);

        $response->assertRedirect(route('admin.privacy.edit'));
        $response->assertSessionHas('success');

        $saved = Setting::where('key', 'privacy_policy')->value('value');
        $this->assertStringContainsString('GDPR &amp; Customer Privacy', $saved);
        $this->assertStringContainsString('<img', $saved);
        $this->assertStringContainsString('https://example.com/security-shield.png', $saved);

        // Public page renders the rich HTML and image tags without escaping
        $publicRes = $this->get(route('privacy'));
        $publicRes->assertOk();
        $publicRes->assertSee('GDPR &amp; Customer Privacy', false);
        $publicRes->assertSee('<img src="https://example.com/security-shield.png"', false);
    }

    /**
     * Test editor image upload endpoint stores file and returns image URL.
     */
    public function test_admin_can_upload_editor_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('policy-diagram.png', 800, 600);

        $response = $this->actingAs($this->admin, 'admin')->postJson(route('admin.editor.upload-image'), [
            'image' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['success', 'url', 'path']);
        $this->assertTrue($response->json('success'));

        $path = $response->json('path');
        Storage::disk('public')->assertExists($path);
    }

    /**
     * Test unauthenticated guests cannot upload images or update legal settings.
     */
    public function test_unauthenticated_guests_cannot_access_or_modify_legal_pages(): void
    {
        $this->get(route('admin.terms.edit'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.terms.update'), ['content' => 'hacked'])->assertRedirect(route('admin.login'));
        $this->get(route('admin.privacy.edit'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.privacy.update'), ['content' => 'hacked'])->assertRedirect(route('admin.login'));
        $this->postJson(route('admin.editor.upload-image'))->assertStatus(401);
    }

    /**
     * Test editor image upload endpoint accepts AVIF files.
     */
    public function test_admin_can_upload_avif_editor_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('policy-illustration.avif', 200, 'image/avif');

        $response = $this->actingAs($this->admin, 'admin')->postJson(route('admin.editor.upload-image'), [
            'image' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['success', 'url', 'path']);
        $this->assertTrue($response->json('success'));

        $path = $response->json('path');
        $this->assertStringEndsWith('.avif', $path);
        Storage::disk('public')->assertExists($path);
    }

    /**
     * Test admin can update privacy policy with display, margin, and border-radius styles without ErrorException.
     */
    public function test_admin_can_update_privacy_with_display_and_avif_image(): void
    {
        $styledHtml = '<h2>Privacy with AVIF</h2><p><img src="https://example.com/logo.avif" alt="Logo" style="display:block;margin-left:auto;margin-right:auto;width:300px;border-radius:8px" class="editor-img-center ql-align-center"></p>';

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.privacy.update'), [
            'content' => $styledHtml,
        ]);

        $response->assertRedirect(route('admin.privacy.edit'));
        $response->assertSessionHas('success');

        $saved = Setting::where('key', 'privacy_policy')->value('value');
        $this->assertStringContainsString('logo.avif', $saved);
        $this->assertStringContainsString('display:block', $saved);
        $this->assertStringContainsString('border-radius:8px', $saved);
        $this->assertStringContainsString('ql-align-center', $saved);
        $this->assertStringContainsString('editor-img-center', $saved);
    }

    /**
     * Test admin can save left and right aligned text and images, and surface page renders them properly.
     */
    public function test_left_and_right_alignment_preserved_and_rendered(): void
    {
        $content = '<p class="ql-align-right">Right aligned header text</p>'
            . '<p class="ql-align-left">Left aligned paragraph text</p>'
            . '<p><img src="https://example.com/left.jpg" alt="Left Image" class="editor-img-left ql-align-left" style="display:inline-block;float:left;margin-right:1.5rem;margin-bottom:1rem;margin-top:0.5rem;"></p>'
            . '<p>Flowing text next to left image</p>'
            . '<p><img src="https://example.com/right.jpg" alt="Right Image" class="editor-img-right ql-align-right" style="display:inline-block;float:right;margin-left:1.5rem;margin-bottom:1rem;margin-top:0.5rem;"></p>'
            . '<p>Flowing text next to right image</p>';

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.privacy.update'), [
            'content' => $content,
        ]);

        $response->assertRedirect(route('admin.privacy.edit'));

        $saved = Setting::where('key', 'privacy_policy')->value('value');
        $this->assertStringContainsString('ql-align-right', $saved);
        $this->assertStringContainsString('ql-align-left', $saved);
        $this->assertStringContainsString('editor-img-left', $saved);
        $this->assertStringContainsString('editor-img-right', $saved);
        $this->assertStringContainsString('float:left', $saved);
        $this->assertStringContainsString('float:right', $saved);

        $publicRes = $this->get(route('privacy'));
        $publicRes->assertOk();
        $publicRes->assertSee('ql-align-right', false);
        $publicRes->assertSee('ql-align-left', false);
        $publicRes->assertSee('editor-img-left', false);
        $publicRes->assertSee('editor-img-right', false);
        $publicRes->assertSee('float:left', false);
        $publicRes->assertSee('float:right', false);
    }

    /**
     * Test admin can update terms with data URI AVIF image without ErrorException.
     */
    public function test_admin_can_update_terms_with_data_uri_avif_image(): void
    {
        $dataUriHtml = '<p><img src="data:image/avif;base64,AAAAIGZ0eXBhdmlmAAAAAGF2aWZtaWYxbWlhZk1BMUIAAADybWV0YQ==" alt="Embedded AVIF" style="display:inline-block;float:left;margin-right:1.5rem"></p>';

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.terms.update'), [
            'content' => $dataUriHtml,
        ]);

        $response->assertRedirect(route('admin.terms.edit'));
        $response->assertSessionHas('success');

        $saved = Setting::where('key', 'terms_and_conditions')->value('value');
        $this->assertStringContainsString('data:image/avif;base64', $saved);
        $this->assertStringContainsString('display:inline-block', $saved);
    }

    /**
     * Test settings index page includes links to dedicated terms and privacy editor pages.
     */
    public function test_settings_index_includes_rich_editors_for_terms_and_privacy(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertSee(route('admin.terms.edit'), false);
        $response->assertSee(route('admin.privacy.edit'), false);
    }
}
