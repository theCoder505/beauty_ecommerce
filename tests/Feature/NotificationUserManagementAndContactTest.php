<?php

namespace Tests\Feature;

use App\Mail\ContactReplyMail;
use App\Mail\OtpMail;
use App\Models\Admin;
use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\OrderService;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationUserManagementAndContactTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@beautyecom.com',
            'password' => Hash::make('password'),
        ]);

        $this->user = User::create([
            'name'       => 'Jane Doe',
            'email'      => 'jane@example.com',
            'password'   => Hash::make('password123'),
            'is_active'  => true,
            'is_blocked' => false,
        ]);
    }

    // =========================================================================
    // 1. NOTIFICATIONS TESTS
    // =========================================================================

    public function test_admin_and_user_notifications_endpoints(): void
    {
        NotificationService::notifyAdmins(
            'order_placed',
            'New Order Received',
            'Order #ORD-1001 placed.',
            '/admin/orders/1',
            'order'
        );

        NotificationService::notifyUser(
            $this->user,
            'order_status',
            'Order Status Updated',
            'Your order is now Shipped.',
            '/dashboard/orders/1',
            'status'
        );

        // Check Admin Notifications API
        $adminRes = $this->actingAs($this->admin, 'admin')->getJson(route('admin.notifications.index'));
        $adminRes->assertOk();
        $adminRes->assertJsonFragment(['title' => 'New Order Received']);
        $this->assertEquals(1, $adminRes->json('unread_count'));

        // Mark admin notification read
        $notifId = $adminRes->json('notifications.0.id');
        $this->actingAs($this->admin, 'admin')->postJson(route('admin.notifications.read', $notifId))
            ->assertOk();
        $this->assertEquals(0, $this->admin->unreadNotifications()->count());

        // Check User Notifications API
        $userRes = $this->actingAs($this->user)->getJson(route('notifications.index'));
        $userRes->assertOk();
        $userRes->assertJsonFragment(['title' => 'Order Status Updated']);
        $this->assertEquals(1, $userRes->json('unread_count'));

        // Mark all user notifications read
        $this->actingAs($this->user)->postJson(route('notifications.mark-all-read'))
            ->assertOk();
        $this->assertEquals(0, $this->user->unreadNotifications()->count());
    }

    public function test_order_status_change_notifies_user(): void
    {
        $order = Order::create([
            'order_number'    => 'ORD-2001',
            'user_id'         => $this->user->id,
            'status'          => 'pending',
            'subtotal'        => 500,
            'shipping_cost'   => 60,
            'total'           => 560,
            'shipping_name'   => 'Jane Doe',
            'shipping_phone'  => '01700000000',
            'shipping_email'  => 'jane@example.com',
            'shipping_address'=> 'House 1, Road 2',
            'shipping_city'   => 'Dhaka',
        ]);

        $orderService = app(OrderService::class);
        $orderService->changeStatus($order, 'shipped', 'admin:1:Super Admin');

        $this->assertEquals(1, $this->user->unreadNotifications()->count());
        $notification = $this->user->unreadNotifications()->first();
        $this->assertEquals('Order Status Updated', $notification->data['title']);
        $this->assertStringContainsString('Shipped', $notification->data['message']);
    }

    public function test_blog_like_and_comments_trigger_correct_notifications(): void
    {
        $cat = BlogCategory::create(['name' => 'Skincare', 'slug' => 'skincare']);
        $post = BlogPost::create([
            'blog_category_id' => $cat->id,
            'title'            => 'Glow Rituals',
            'slug'             => 'glow-rituals',
            'body'             => 'Content of glow rituals',
            'is_published'     => true,
            'published_at'     => now(),
        ]);

        // User likes the post -> Admin notified
        $this->actingAs($this->user)->postJson(route('blog.like', $post->slug))
            ->assertOk()
            ->assertJson(['liked' => true]);

        $this->assertEquals(1, $this->admin->unreadNotifications()->count());
        $this->assertEquals('New Post Like', $this->admin->unreadNotifications()->first()->data['title']);

        // User posts a top-level comment -> Admin notified
        $this->actingAs($this->user)->postJson(route('blog.comment', $post->slug), [
            'body' => 'I loved this article!',
        ])->assertOk();

        $this->assertEquals(2, $this->admin->unreadNotifications()->count());

        $comment = BlogComment::first();

        // Second user replies to first user's comment -> First user notified & Admin notified
        $user2 = User::create([
            'name'       => 'Alice Smith',
            'email'      => 'alice@example.com',
            'password'   => Hash::make('password123'),
            'is_active'  => true,
            'is_blocked' => false,
        ]);

        $this->actingAs($user2)->postJson(route('blog.comment', $post->slug), [
            'body'      => 'Totally agree with you!',
            'parent_id' => $comment->id,
        ])->assertOk();

        // First user should have 1 notification about the reply
        $this->assertEquals(1, $this->user->unreadNotifications()->count());
        $this->assertEquals('New Reply to Your Comment', $this->user->unreadNotifications()->first()->data['title']);

        // Second user likes first user's comment -> First user gets like notification
        $this->actingAs($user2)->postJson(route('blog.comment.like', $comment->id))
            ->assertOk()
            ->assertJson(['liked' => true]);

        $this->assertEquals(2, $this->user->unreadNotifications()->count());
        $hasLikeNotif = $this->user->unreadNotifications->contains(fn ($n) => str_contains($n->data['message'], 'liked your comment'));
        $this->assertTrue($hasLikeNotif);
    }

    public function test_newsletter_subscribe_notifies_admin(): void
    {
        $res = $this->postJson(route('newsletter.subscribe'), [
            'email' => 'subscriber@test.com',
        ]);
        $res->assertOk();

        $this->assertEquals(1, $this->admin->unreadNotifications()->count());
        $this->assertEquals('New Subscriber', $this->admin->unreadNotifications()->first()->data['title']);
    }

    public function test_review_submission_notifies_admin(): void
    {
        $category = Category::create(['name' => 'Serums', 'slug' => 'serums']);
        $product = Product::create([
            'category_id' => $category->id,
            'name'        => 'Glow Face Serum',
            'slug'        => 'glow-face-serum',
            'sku'         => 'SERUM-01',
            'price'       => 1200,
            'stock'       => 50,
            'is_active'   => true,
        ]);

        $order = Order::create([
            'order_number'    => 'ORD-REVIEW-01',
            'user_id'         => $this->user->id,
            'status'          => 'delivered',
            'subtotal'        => 1200,
            'shipping_cost'   => 0,
            'total'           => 1200,
            'shipping_name'   => 'Jane Doe',
            'shipping_phone'  => '01700000000',
            'shipping_email'  => 'jane@example.com',
            'shipping_address'=> 'House 1, Road 2',
            'shipping_city'   => 'Dhaka',
        ]);

        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'product_sku'  => $product->sku,
            'quantity'     => 1,
            'unit_price'   => 1200,
            'total'        => 1200,
        ]);

        $res = $this->actingAs($this->user)->postJson(route('reviews.store'), [
            'product_id' => $product->id,
            'order_id'   => $order->id,
            'rating'     => 5,
            'body'       => 'Absolutely loved this serum!',
        ]);

        $res->assertOk();
        $this->assertEquals(1, $this->admin->unreadNotifications()->count());
        $notification = $this->admin->unreadNotifications()->first();
        $this->assertEquals('New Product Review', $notification->data['title']);
        $this->assertStringContainsString('5★ review for "Glow Face Serum"', $notification->data['message']);
    }

    // =========================================================================
    // 2. USER MANAGEMENT & BLOCK TESTS
    // =========================================================================

    public function test_admin_can_view_users_list_and_details(): void
    {
        $res = $this->actingAs($this->admin, 'admin')->get(route('admin.users.index'));
        $res->assertOk();
        $res->assertSee('Jane Doe');
        $res->assertSee('jane@example.com');

        $showRes = $this->actingAs($this->admin, 'admin')->get(route('admin.users.show', $this->user->id));
        $showRes->assertOk();
        $showRes->assertSee('Jane Doe');
    }

    public function test_admin_can_block_and_unblock_user(): void
    {
        // Block user
        $blockRes = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.users.toggle-block', $this->user->id), [
                'reason' => 'Violation of community terms.',
            ]);
        $blockRes->assertRedirect();

        $this->user->refresh();
        $this->assertTrue($this->user->is_blocked);
        $this->assertNotNull($this->user->blocked_at);
        $this->assertEquals('Violation of community terms.', $this->user->blocked_reason);

        // Unblock user
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.users.toggle-block', $this->user->id));

        $this->user->refresh();
        $this->assertFalse($this->user->is_blocked);
        $this->assertNull($this->user->blocked_at);
    }

    public function test_blocked_user_is_logged_out_when_accessing_system(): void
    {
        // User is currently authenticated
        $this->actingAs($this->user);

        // Admin blocks user in DB
        $this->user->update(['is_blocked' => true]);

        // Next request by user to dashboard should hit CheckUserBlockedMiddleware, log them out, and redirect to login
        $res = $this->get(route('user.dashboard'));
        $res->assertRedirect(route('login'));
        $res->assertSessionHas('error');

        // Confirm user is no longer authenticated
        $this->assertGuest();
    }

    public function test_blocked_user_cannot_login(): void
    {
        $this->user->update(['is_blocked' => true]);

        $res = $this->post(route('login.post'), [
            'email'    => 'jane@example.com',
            'password' => 'password123',
        ]);

        $res->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_remove_user(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.users.destroy', $this->user->id))
            ->assertRedirect(route('admin.users.index'));

        $this->assertSoftDeleted('users', ['id' => $this->user->id]);
    }

    // =========================================================================
    // 3. CONTACT PAGE, EMAIL OTP, INQUIRY & ADMIN REPLY TESTS
    // =========================================================================

    public function test_guest_can_view_contact_page_with_faqs(): void
    {
        Faq::create([
            'question'   => 'How do I return an item?',
            'answer'     => 'Returns are accepted within 7 days.',
            'category'   => 'Returns',
            'sort_order' => 1,
            'is_active'  => true,
        ]);

        $res = $this->get(route('contact'));
        $res->assertOk();
        $res->assertSee('How do I return an item?');
        $res->assertSee('Returns are accepted within 7 days.');
    }

    public function test_contact_form_requires_email_otp_verification(): void
    {
        Mail::fake();

        // 1. Request OTP
        $otpRes = $this->postJson(route('contact.send-otp'), [
            'name'    => 'Visitor Sam',
            'email'   => 'sam@example.com',
            'subject' => 'Account Block Assistance',
            'message' => 'My account was blocked and I need help unlocking it.',
        ]);

        $otpRes->assertOk();
        $otpRes->assertJson(['success' => true]);

        Mail::assertSent(OtpMail::class, function ($mail) {
            return $mail->hasTo('sam@example.com') && strlen($mail->otp) === 6;
        });

        // 2. Attempt submit with wrong OTP -> Rejected
        $badRes = $this->postJson(route('contact.submit'), [
            'otp' => '000000',
        ]);
        $badRes->assertStatus(422);
        $this->assertDatabaseMissing('contact_messages', ['email' => 'sam@example.com']);

        // 3. Retrieve valid OTP from session and submit
        $otpService = app(OtpService::class);
        $sessionData = $otpService->getSessionData('contact');
        $this->assertNotNull($sessionData);

        // Generate known OTP to test successful verification
        $validOtp = '123456';
        $otpService->store('contact', $validOtp, $sessionData);

        $goodRes = $this->postJson(route('contact.submit'), [
            'otp' => $validOtp,
        ]);
        $goodRes->assertOk();
        $goodRes->assertJson(['success' => true]);

        // Message stored in database
        $this->assertDatabaseHas('contact_messages', [
            'name'    => 'Visitor Sam',
            'email'   => 'sam@example.com',
            'subject' => 'Account Block Assistance',
            'status'  => 'pending',
        ]);

        // Admin received a notification in admin panel
        $this->assertEquals(1, $this->admin->unreadNotifications()->count());
        $this->assertEquals('New Contact Message', $this->admin->unreadNotifications()->first()->data['title']);
    }

    public function test_admin_can_view_inquiries_and_reply_via_email(): void
    {
        Mail::fake();

        $inquiry = ContactMessage::create([
            'name'    => 'Mark Taylor',
            'email'   => 'mark@example.com',
            'subject' => 'Question about shipment',
            'message' => 'When will my package arrive?',
            'status'  => 'pending',
        ]);

        // Admin views inquiry
        $viewRes = $this->actingAs($this->admin, 'admin')->get(route('admin.contact-messages.show', $inquiry->id));
        $viewRes->assertOk();
        $viewRes->assertSee('When will my package arrive?');

        // Admin sends reply
        $replyRes = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.contact-messages.reply', $inquiry->id), [
                'reply' => 'Hello Mark, your shipment is en route and will be delivered by tomorrow afternoon.',
            ]);

        $replyRes->assertRedirect();

        // Email was dispatched to the visitor
        Mail::assertSent(ContactReplyMail::class, function ($mail) {
            return $mail->hasTo('mark@example.com');
        });

        // Database updated with reply details
        $inquiry->refresh();
        $this->assertEquals('replied', $inquiry->status);
        $this->assertNotNull($inquiry->replied_at);
        $this->assertEquals($this->admin->id, $inquiry->replied_by);
        $this->assertStringContainsString('delivered by tomorrow', $inquiry->admin_reply);
    }

    // =========================================================================
    // 4. FAQS MANAGEMENT TESTS
    // =========================================================================

    public function test_admin_faq_crud_operations(): void
    {
        // Create
        $this->actingAs($this->admin, 'admin')->post(route('admin.faqs.store'), [
            'question'   => 'Do you ship internationally?',
            'answer'     => 'Currently we only ship inside Bangladesh.',
            'category'   => 'Shipping',
            'sort_order' => 5,
            'is_active'  => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('faqs', ['question' => 'Do you ship internationally?']);

        $faq = Faq::where('question', 'Do you ship internationally?')->first();

        // Toggle Active
        $this->actingAs($this->admin, 'admin')->postJson(route('admin.faqs.toggle', $faq->id))
            ->assertOk()
            ->assertJson(['is_active' => false]);

        $this->assertFalse($faq->fresh()->is_active);

        // Update
        $this->actingAs($this->admin, 'admin')->put(route('admin.faqs.update', $faq->id), [
            'question'   => 'Do you ship globally?',
            'answer'     => 'Worldwide shipping coming soon.',
            'category'   => 'Shipping',
            'sort_order' => 1,
            'is_active'  => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('faqs', ['question' => 'Do you ship globally?']);

        // Delete
        $this->actingAs($this->admin, 'admin')->delete(route('admin.faqs.destroy', $faq->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    public function test_admin_separate_notifications_page(): void
    {
        NotificationService::notifyAdmins(
            'order_placed',
            'Order Notification Test',
            'Order #ORD-PAGE-1 placed.',
            '/admin/orders/1'
        );

        $res = $this->actingAs($this->admin, 'admin')->get(route('admin.notifications.index'));
        $res->assertOk();
        $res->assertSee('Notifications Center');
        $res->assertSee('Order Notification Test');

        $notif = $this->admin->notifications()->first();

        // Delete single notification
        $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.notifications.destroy', $notif->id))
            ->assertRedirect();

        $this->assertEquals(0, $this->admin->notifications()->count());
    }

    public function test_user_separate_notifications_page(): void
    {
        NotificationService::notifyUser(
            $this->user,
            'order_status',
            'Your Order is Ready',
            'Package is dispatched.',
            '/dashboard/orders/1'
        );

        $res = $this->actingAs($this->user)->get(route('user.notifications'));
        $res->assertOk();
        $res->assertSee('Notifications');
        $res->assertSee('Your Order is Ready');

        $notif = $this->user->notifications()->first();

        // Delete single notification
        $this->actingAs($this->user)
            ->delete(route('notifications.destroy', $notif->id))
            ->assertRedirect();

        $this->assertEquals(0, $this->user->notifications()->count());
    }
}
