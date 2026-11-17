<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScrollToTopTest extends TestCase
{
    use RefreshDatabase;

    public function test_scroll_to_top_is_rendered_on_surface_home_page(): void
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('id="scroll-to-top-btn"', false);
        $response->assertSee('aria-label="Scroll to top"', false);
        $response->assertSee("scrollToTop()", false);
    }

    public function test_scroll_to_top_is_rendered_on_surface_shop_page(): void
    {
        $response = $this->get(route('shop.index'));
        $response->assertStatus(200);
        $response->assertSee('id="scroll-to-top-btn"', false);
    }

    public function test_scroll_to_top_is_rendered_on_surface_contact_page(): void
    {
        $response = $this->get(route('contact'));
        $response->assertStatus(200);
        $response->assertSee('id="scroll-to-top-btn"', false);
    }
}
