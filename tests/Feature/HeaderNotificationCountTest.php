<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HeaderNotificationCountTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'client@beautyecom.com',
        ]);

        $this->admin = Admin::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@beautyecom.com',
            'password' => Hash::make('password'),
            'role'     => 'super_admin',
        ]);
    }

    public function test_user_header_and_sidebar_both_render_unread_count_when_notification_exists(): void
    {
        NotificationService::notifyUser(
            $this->user,
            'order_status',
            'Order Confirmed',
            'Your order has been confirmed.',
            '/dashboard/orders',
            'package'
        );

        $this->assertEquals(1, $this->user->unreadNotifications()->count());

        // Visit user dashboard
        $response = $this->actingAs($this->user)->get(route('user.dashboard'));
        $response->assertStatus(200);

        // Sidebar should contain badge with '1'
        $response->assertSee('Notifications');
        $response->assertSee('bg-brand-100 text-brand-800 dark:bg-brand-900/50 dark:text-brand-300', false);

        // Header notification bell should initialize unreadCount: 1 and render 1 in the badge span
        $response->assertSee('unreadCount: 1', false);
        $response->assertSee('px-1.5 py-0.2 rounded-full min-w-4 text-center leading-tight ">1</span>', false);

        // Header notification bell should initialize unreadCount: 1 and render 1 in the badge span
        $response->assertSee('unreadCount: 1', false);
        $response->assertSee('px-1.5 py-0.2 rounded-full min-w-4 text-center leading-tight ">1</span>', false);
    }

    public function test_user_notifications_feed_returns_json_with_notifications_and_count(): void
    {
        NotificationService::notifyUser(
            $this->user,
            'order_status',
            'Order Shipped',
            'Your order is on the way.',
            '/dashboard/orders/1',
            'truck'
        );

        $response = $this->actingAs($this->user)->getJson(route('notifications.feed'));
        $response->assertStatus(200);
        $response->assertJson([
            'unread_count' => 1,
        ]);
        $response->assertJsonPath('notifications.0.title', 'Order Shipped');
        $response->assertJsonPath('notifications.0.read', false);
    }

    public function test_user_notifications_index_with_json_format_returns_feed(): void
    {
        NotificationService::notifyUser(
            $this->user,
            'order_status',
            'Order Delivered',
            'Your package arrived.',
            '/dashboard/orders/1'
        );

        $response = $this->actingAs($this->user)->get(route('notifications.index', ['format' => 'json']));
        $response->assertStatus(200);
        $response->assertJson([
            'unread_count' => 1,
        ]);
    }

    public function test_admin_notifications_feed_and_header_count(): void
    {
        NotificationService::notifyAdmins(
            'new_order',
            'New Order Received',
            'Order #1001 placed.',
            '/admin/orders/1'
        );

        // Feed endpoint
        $response = $this->actingAs($this->admin, 'admin')->getJson(route('admin.notifications.feed'));
        $response->assertStatus(200);
        $response->assertJson([
            'unread_count' => 1,
        ]);

        // Admin dashboard page should pre-render badge and state
        $pageResponse = $this->actingAs($this->admin, 'admin')->get(route('admin.dashboard'));
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('unreadCount: 1', false);
        $pageResponse->assertSee('px-1.5 py-0.2 rounded-full min-w-4 text-center leading-tight ">1</span>', false);
    }

    public function test_user_notification_bell_is_active_when_on_notification_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('notifications.index'));
        $response->assertStatus(200);

        // Header notification bell icon should have active classes
        $response->assertSee('text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/60 ring-1 ring-brand-500/20', false);
    }

    public function test_admin_notification_bell_is_active_when_on_admin_notification_page(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.notifications.index'));
        $response->assertStatus(200);

        // Admin notification bell icon should have active classes
        $response->assertSee('text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/40 ring-1 ring-brand-500/20', false);
    }
}
