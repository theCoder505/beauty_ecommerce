<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserNotificationsReadUnreadTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name'  => 'Test Customer',
            'email' => 'customer@example.com',
        ]);
    }

    public function test_user_notifications_page_renders_with_counts_and_tabs(): void
    {
        // Create 2 notifications: 1 read, 1 unread
        NotificationService::notifyUser(
            $this->user,
            'order_placed',
            'Order Placed #1',
            'Your order has been placed.',
            '/orders/ORD-001',
            'order'
        );

        NotificationService::notifyUser(
            $this->user,
            'order_status',
            'Order Shipped #2',
            'Your order has been shipped.',
            '/orders/ORD-002',
            'order'
        );

        // Mark one as read
        $readNotif = $this->user->notifications()->first();
        $readNotif->markAsRead();

        $response = $this->actingAs($this->user)->get('/notifications');

        $response->assertStatus(200);
        $response->assertSee('Notifications');
        $response->assertSee('All (2)');
        $response->assertSee('Unread (1)');
        $response->assertSee('Read (1)');
        $response->assertSee('Order Placed #1');
        $response->assertSee('Order Shipped #2');
        $response->assertSee('dark:bg-slate-800');
    }

    public function test_user_can_filter_unread_notifications(): void
    {
        NotificationService::notifyUser(
            $this->user,
            'order_placed',
            'Unread Notification',
            'This is unread.',
            '/orders/ORD-UNREAD'
        );

        NotificationService::notifyUser(
            $this->user,
            'order_status',
            'Read Notification',
            'This is read.',
            '/orders/ORD-READ'
        );

        $readNotif = $this->user->notifications()->where('data->title', 'Read Notification')->first();
        $readNotif->markAsRead();

        $response = $this->actingAs($this->user)->get('/notifications?status=unread');

        $response->assertStatus(200);
        $response->assertSee('Unread Notification');
        $response->assertDontSee('Read Notification');
    }

    public function test_user_can_filter_read_notifications(): void
    {
        NotificationService::notifyUser(
            $this->user,
            'order_placed',
            'Unread Notification',
            'This is unread.',
            '/orders/ORD-UNREAD'
        );

        NotificationService::notifyUser(
            $this->user,
            'order_status',
            'Read Notification',
            'This is read.',
            '/orders/ORD-READ'
        );

        $readNotif = $this->user->notifications()->where('data->title', 'Read Notification')->first();
        $readNotif->markAsRead();

        $response = $this->actingAs($this->user)->get('/notifications?status=read');

        $response->assertStatus(200);
        $response->assertSee('Read Notification');
        $response->assertDontSee('Unread Notification');
    }

    public function test_user_can_mark_notification_as_read_and_unread(): void
    {
        NotificationService::notifyUser(
            $this->user,
            'order_placed',
            'Test Order Alert',
            'Your order is confirmed.',
            '/orders/ORD-ALERT'
        );

        $notif = $this->user->unreadNotifications()->first();
        $this->assertNotNull($notif);

        // 1. Mark as Read
        $readResponse = $this->actingAs($this->user)
            ->post(route('notifications.read', $notif->id));

        $readResponse->assertSessionHas('success', 'Notification marked as read.');
        $this->assertNotNull($notif->fresh()->read_at);
        $this->assertEquals(0, $this->user->unreadNotifications()->count());

        // 2. Mark as Unread
        $unreadResponse = $this->actingAs($this->user)
            ->post(route('notifications.unread', $notif->id));

        $unreadResponse->assertSessionHas('success', 'Notification marked as unread.');
        $this->assertNull($notif->fresh()->read_at);
        $this->assertEquals(1, $this->user->unreadNotifications()->count());
    }

    public function test_view_details_marks_notification_as_read_and_redirects(): void
    {
        NotificationService::notifyUser(
            $this->user,
            'order_placed',
            'Order Delivery',
            'Your order has arrived.',
            '/orders/ORD-ARRIVED'
        );

        $notif = $this->user->unreadNotifications()->first();

        $response = $this->actingAs($this->user)
            ->post(route('notifications.read', $notif->id), [
                'redirect' => 1,
            ]);

        $response->assertRedirect('/orders/ORD-ARRIVED');
        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_user_can_mark_all_read_and_mark_all_unread(): void
    {
        NotificationService::notifyUser($this->user, 'test', 'N1', 'Msg 1');
        NotificationService::notifyUser($this->user, 'test', 'N2', 'Msg 2');

        $this->assertEquals(2, $this->user->unreadNotifications()->count());

        // Mark All Read
        $this->actingAs($this->user)->post(route('notifications.mark-all-read'));
        $this->assertEquals(0, $this->user->unreadNotifications()->count());
        $this->assertEquals(2, $this->user->readNotifications()->count());

        // Mark All Unread
        $this->actingAs($this->user)->post(route('notifications.mark-all-unread'));
        $this->assertEquals(2, $this->user->unreadNotifications()->count());
        $this->assertEquals(0, $this->user->readNotifications()->count());
    }

    public function test_user_header_ajax_mark_all_read_marks_all_notifications_as_read(): void
    {
        NotificationService::notifyUser($this->user, 'test', 'Header Alert 1', 'Msg 1');
        NotificationService::notifyUser($this->user, 'test', 'Header Alert 2', 'Msg 2');

        $this->assertEquals(2, $this->user->unreadNotifications()->count());

        // Header clicks "Mark all read" via AJAX fetch
        $response = $this->actingAs($this->user)->postJson(route('notifications.mark-all-read'));

        $response->assertStatus(200);
        $response->assertJson([
            'success'      => true,
            'message'      => 'All notifications marked as read.',
            'unread_count' => 0,
        ]);

        $this->assertEquals(0, $this->user->unreadNotifications()->count());
        $this->assertEquals(2, $this->user->readNotifications()->count());

        // Verify notification page template includes the reactive event listener
        $pageResponse = $this->actingAs($this->user)->get('/notifications');
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('notifications-marked-all-read');
    }

    public function test_dashboard_notifications_loads_notifications_page(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard/notifications');
        $response->assertStatus(200);
        $response->assertSee('Notifications');
    }
}
