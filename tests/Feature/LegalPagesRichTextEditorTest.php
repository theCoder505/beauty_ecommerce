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
     * Test settings index page includes rich text editors for terms and privacy under Tab 7.
     */
    public function test_settings_index_includes_rich_editors_for_terms_and_privacy(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertSee('id="terms-quill-editor"', false);
        $response->assertSee('id="privacy-quill-editor"', false);
        $response->assertSee('hidden-terms-content', false);
        $response->assertSee('hidden-privacy-content', false);
    }
}
