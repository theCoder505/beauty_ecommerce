<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\HeroSlide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HeroCarouselAndLandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin@beautyecom.test',
            'password' => Hash::make('secret123'),
        ]);
    }

    public function test_landing_page_renders_hero_section_and_carousel(): void
    {
        HeroSlide::create([
            'badge_text' => '✦ Radiate Confidence Every Day',
            'title' => 'Beauty & Wellness for a Better You',
            'subtitle' => 'Discover premium health & beauty products for healthy skin, hair & body.',
            'primary_button_text' => 'Shop Now',
            'primary_button_url' => '/shop',
            'secondary_button_text' => 'Explore Categories',
            'secondary_button_url' => '#categories',
            'image' => 'hero-slides/slide-1.jpg',
            'floating_badge_text' => '100% Original Products',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Radiate Confidence Every Day');
        $response->assertSee('Beauty &amp; Wellness for a Better You', false);
        $response->assertSee('Discover premium health &amp; beauty products', false);
        $response->assertSee('Shop Now');
        $response->assertSee('Explore Categories');
        $response->assertSee('100% Original');
        $response->assertSee('Expert Approved');
        $response->assertSee('Fast Delivery');
        $response->assertSee('Easy Returns');
        $response->assertSee('Shop by Category');
        $response->assertSee('Best Sellers');
        $response->assertSee('Secure Payments');
    }

    public function test_admin_can_view_hero_slides_list(): void
    {
        $admin = $this->createAdmin();

        HeroSlide::create([
            'title' => 'First Slide Title',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.hero-slides.index'));

        $response->assertStatus(200);
        $response->assertSee('Landing Page Hero Carousel');
        $response->assertSee('First Slide Title');
    }

    public function test_admin_can_create_new_hero_slide(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.hero-slides.store'), [
            'badge_text' => '✦ Natural Radiance',
            'title' => 'Pure Organic Skincare',
            'subtitle' => 'Handcrafted with organic botanicals for glowing skin.',
            'primary_button_text' => 'Shop Now',
            'primary_button_url' => '/shop',
            'secondary_button_text' => 'Explore',
            'secondary_button_url' => '#categories',
            'floating_badge_text' => '100% Vegan',
            'sort_order' => 2,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.hero-slides.index'));
        $this->assertDatabaseHas('hero_slides', [
            'title' => 'Pure Organic Skincare',
            'badge_text' => '✦ Natural Radiance',
        ]);
    }

    public function test_admin_can_update_hero_slide(): void
    {
        $admin = $this->createAdmin();

        $slide = HeroSlide::create([
            'title' => 'Old Title',
            'subtitle' => 'Old Subtitle',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.hero-slides.update', $slide), [
            'title' => 'Updated Glorious Title',
            'subtitle' => 'Updated Subtitle Text',
            'primary_button_text' => 'Shop Collection',
            'primary_button_url' => '/shop',
            'sort_order' => 5,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.hero-slides.index'));
        $this->assertDatabaseHas('hero_slides', [
            'id' => $slide->id,
            'title' => 'Updated Glorious Title',
            'sort_order' => 5,
        ]);
    }

    public function test_admin_cannot_delete_when_only_one_item_exists(): void
    {
        $admin = $this->createAdmin();

        $onlySlide = HeroSlide::create([
            'title' => 'The Sole Slide',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->assertEquals(1, HeroSlide::count());

        $response = $this->actingAs($admin, 'admin')->delete(route('admin.hero-slides.destroy', $onlySlide));

        $response->assertRedirect(route('admin.hero-slides.index'));
        $response->assertSessionHas('error');
        $this->assertEquals(1, HeroSlide::count());
        $this->assertDatabaseHas('hero_slides', ['id' => $onlySlide->id]);
    }

    public function test_admin_can_delete_when_multiple_items_exist(): void
    {
        $admin = $this->createAdmin();

        $slide1 = HeroSlide::create(['title' => 'Slide One', 'sort_order' => 1]);
        $slide2 = HeroSlide::create(['title' => 'Slide Two', 'sort_order' => 2]);

        $this->assertEquals(2, HeroSlide::count());

        $response = $this->actingAs($admin, 'admin')->delete(route('admin.hero-slides.destroy', $slide2));

        $response->assertRedirect(route('admin.hero-slides.index'));
        $response->assertSessionHas('success');
        $this->assertEquals(1, HeroSlide::count());
        $this->assertDatabaseMissing('hero_slides', ['id' => $slide2->id]);
        $this->assertDatabaseHas('hero_slides', ['id' => $slide1->id]);
    }

    public function test_admin_can_toggle_hero_slide_status(): void
    {
        $admin = $this->createAdmin();

        $slide = HeroSlide::create([
            'title' => 'Toggled Slide',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')->post(route('admin.hero-slides.toggle', $slide));
        $this->assertFalse($slide->fresh()->is_active);

        $this->actingAs($admin, 'admin')->post(route('admin.hero-slides.toggle', $slide));
        $this->assertTrue($slide->fresh()->is_active);
    }
}
