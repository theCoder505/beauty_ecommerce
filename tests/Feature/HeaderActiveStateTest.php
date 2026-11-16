<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeaderActiveStateTest extends TestCase
{
    use RefreshDatabase;
    public function test_home_page_shows_active_in_header(): void
    {
        $res = $this->get(route('home'));
        $res->assertOk();
        $res->assertSee('Home</span>', false);
        $res->assertSee('bg-brand-700 dark:bg-brand-400 rounded-full', false);
    }

    public function test_shop_page_shows_active_in_header(): void
    {
        $res = $this->get(route('shop.index'));
        $res->assertOk();
        $res->assertSee('Shop</span>', false);
    }

    public function test_blog_page_shows_active_in_header(): void
    {
        $res = $this->get(route('blog.index'));
        $res->assertOk();
        $res->assertSee('Blog</span>', false);
    }

    public function test_contact_page_shows_active_in_header(): void
    {
        $res = $this->get(route('contact'));
        $res->assertOk();
        $res->assertSee('Contact</span>', false);
    }
}
