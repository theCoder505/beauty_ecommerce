<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderReviewLegalReturnsAndPLTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Admin $admin;
    protected Category $category;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email'    => 'customer@example.com',
            'name'     => 'Alice Green',
            'password' => bcrypt('password123'),
        ]);

        $this->admin = Admin::create([
            'name'     => 'Admin Manager',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->category = Category::create([
            'name'      => 'Skincare',
            'slug'      => 'skincare',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name'        => 'Velvet Rose Face Oil',
            'slug'        => 'velvet-rose-face-oil',
            'sku'         => 'VR-001',
            'price'       => 1500.00,
            'sale_price'  => 1200.00,
            'cost_price'  => 600.00,
            'stock'       => 50,
            'is_active'   => true,
            'thumbnail'   => 'products/vr-thumb.jpg',
        ]);
    }

    // =========================================================================
    // REQUIREMENT 1: REVIEWS SUBMITTED FROM ORDER PAGE, SHOWCASED ON PRODUCT
    // =========================================================================

    public function test_product_detail_page_has_no_review_submission_form_directly()
    {
        $response = $this->actingAs($this->user)->get(route('products.show', $this->product->slug));
        $response->assertOk();

        // The product page should showcase reviews, not have the review submission form
        $response->assertSee('Customer Reviews');
        $response->assertDontSee('form action="' . route('reviews.store') . '"', false);
    }

    public function test_user_can_submit_review_from_completed_order_and_it_showcases_on_product_page()
    {
        // 1. Create a completed order
        $order = Order::create([
            'order_number'     => 'ORD-COMPLETED-101',
            'user_id'          => $this->user->id,
            'status'           => 'completed',
            'subtotal'         => 1200.00,
            'shipping_cost'    => 60.00,
            'total'            => 1260.00,
            'shipping_name'    => 'Alice Green',
            'shipping_phone'   => '01711111111',
            'shipping_email'   => 'alice@example.com',
            'shipping_address' => '12 Gulshan Ave',
            'shipping_city'    => 'Dhaka',
        ]);

        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku'  => $this->product->sku,
            'quantity'     => 1,
            'unit_price'   => 1200.00,
            'total'        => 1200.00,
        ]);

        // Order detail page shows the review button for completed order
        $orderPage = $this->actingAs($this->user)->get(route('user.orders.show', $order));
        $orderPage->assertOk();
        $orderPage->assertSee('Write Review');

        // 2. Submit the review
        $res = $this->actingAs($this->user)->post(route('reviews.store'), [
            'order_id'   => $order->id,
            'product_id' => $this->product->id,
            'rating'     => 5,
            'body'       => 'Exquisite velvety texture, my skin glows beautifully!',
        ]);

        $res->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
            'order_id'   => $order->id,
            'rating'     => 5,
            'body'       => 'Exquisite velvety texture, my skin glows beautifully!',
            'status'     => 'approved',
        ]);

        // 3. Review showcases on product detail page
        $productPage = $this->get(route('products.show', $this->product->slug));
        $productPage->assertOk();
        $productPage->assertSee('Exquisite velvety texture, my skin glows beautifully!');
        $productPage->assertSee('Alice Green');
    }

    public function test_user_cannot_review_product_from_non_completed_order()
    {
        $order = Order::create([
            'order_number'     => 'ORD-PENDING-102',
            'user_id'          => $this->user->id,
            'status'           => 'pending',
            'subtotal'         => 1200.00,
            'shipping_cost'    => 60.00,
            'total'            => 1260.00,
            'shipping_name'    => 'Alice Green',
            'shipping_phone'   => '01711111111',
            'shipping_email'   => 'alice@example.com',
            'shipping_address' => '12 Gulshan Ave',
            'shipping_city'    => 'Dhaka',
        ]);

        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku'  => $this->product->sku,
            'quantity'     => 1,
            'unit_price'   => 1200.00,
            'total'        => 1200.00,
        ]);

        $res = $this->actingAs($this->user)->postJson(route('reviews.store'), [
            'order_id'   => $order->id,
            'product_id' => $this->product->id,
            'rating'     => 5,
            'body'       => 'Should not be allowed yet',
        ]);

        $res->assertStatus(422);
        $this->assertDatabaseMissing('reviews', ['order_id' => $order->id]);
    }

    // =========================================================================
    // REQUIREMENT 2: TERMS & CONDITIONS AND PRIVACY POLICY ADMIN & PUBLIC
    // =========================================================================

    public function test_admin_can_set_terms_and_privacy_and_they_show_on_public_pages_and_footer()
    {
        $customTerms = "1. GlowHaus Official Terms.\nAll products are certified luxury items.";
        $customPrivacy = "1. Customer Privacy First.\nWe never disclose client details.";

        // Admin updates settings
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.settings.update'), [
            'terms_and_conditions' => $customTerms,
            'privacy_policy'       => $customPrivacy,
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        // Public Terms Page displays custom terms
        $termsRes = $this->get(route('terms'));
        $termsRes->assertOk();
        $termsRes->assertSee('GlowHaus Official Terms.');
        $termsRes->assertSee('All products are certified luxury items.');

        // Public Privacy Policy Page displays custom privacy
        $privacyRes = $this->get(route('privacy'));
        $privacyRes->assertOk();
        $privacyRes->assertSee('Customer Privacy First.');
        $privacyRes->assertSee('We never disclose client details.');

        // Footer links both pages
        $homeRes = $this->get(route('home'));
        $homeRes->assertOk();
        $homeRes->assertSee('/terms-and-conditions');
        $homeRes->assertSee('/privacy-policy');
        $homeRes->assertSee('Terms & Conditions', false);
        $homeRes->assertSee('Privacy Policy', false);
    }

    // =========================================================================
    // REQUIREMENT 3: ORDER RETURN OR REFUND WITH REASON & DASHBOARD STATS
    // =========================================================================

    public function test_user_can_request_return_or_refund_when_order_is_completed()
    {
        $order = Order::create([
            'order_number'     => 'ORD-RET-01',
            'user_id'          => $this->user->id,
            'status'           => 'completed',
            'subtotal'         => 1200.00,
            'shipping_cost'    => 60.00,
            'total'            => 1260.00,
            'shipping_name'    => 'Alice Green',
            'shipping_phone'   => '01711111111',
            'shipping_email'   => 'alice@example.com',
            'shipping_address' => '12 Gulshan Ave',
            'shipping_city'    => 'Dhaka',
        ]);

        // Submit refund request with reason
        $res = $this->actingAs($this->user)->post(route('user.orders.return-request', $order), [
            'request_type'    => 'refund',
            'reason_category' => 'Damaged or Defective Item',
            'reason'          => 'The bottle dropper arrived shattered during transit.',
        ]);

        $res->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('requested', $order->return_status);
        $this->assertEquals('refund', $order->return_request_type);
        $this->assertStringContainsString('The bottle dropper arrived shattered', $order->return_reason);
        $this->assertNotNull($order->return_requested_at);

        // User order detail page now reflects under review banner
        $detailRes = $this->actingAs($this->user)->get(route('user.orders.show', $order));
        $detailRes->assertOk();
        $detailRes->assertSee('Refund Request Under Review');
        $detailRes->assertSee('The bottle dropper arrived shattered');
    }

    public function test_admin_can_approve_refund_and_sets_status_to_refunded()
    {
        $order = Order::create([
            'order_number'        => 'ORD-REFUND-APPROVE',
            'user_id'             => $this->user->id,
            'status'              => 'completed',
            'return_request_type' => 'refund',
            'return_reason'       => 'Wrong item delivered',
            'return_status'       => 'requested',
            'return_requested_at' => now(),
            'subtotal'            => 1200.00,
            'shipping_cost'       => 60.00,
            'total'               => 1260.00,
            'shipping_name'       => 'Alice Green',
            'shipping_phone'      => '01711111111',
            'shipping_email'      => 'alice@example.com',
            'shipping_address'    => '12 Gulshan Ave',
            'shipping_city'       => 'Dhaka',
        ]);

        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku'  => $this->product->sku,
            'quantity'     => 1,
            'unit_price'   => 1200.00,
            'total'        => 1200.00,
        ]);

        // Stock before refund approval
        $stockBefore = $this->product->fresh()->stock;

        // Admin approves refund
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.returns.approve', $order), [
            'action_type'   => 'refund',
            'refund_amount' => 1260.00,
            'admin_note'    => 'Refund accepted and sent back to original card.',
        ]);

        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('refunded', $order->status);
        $this->assertEquals('approved', $order->return_status);
        $this->assertEquals(1260.00, (float) $order->refund_amount);

        // Product stock automatically restocked
        $this->assertEquals($stockBefore + 1, $this->product->fresh()->stock);

        // Admin dashboard reflects returned & refunded stat
        $dashRes = $this->actingAs($this->admin, 'admin')->get(route('admin.dashboard'));
        $dashRes->assertOk();
        $dashRes->assertSee('Returns & Refunds');
        $dashRes->assertSee('1,260');

        // Returns & Refunds admin page shows the record
        $returnsPage = $this->actingAs($this->admin, 'admin')->get(route('admin.returns.index'));
        $returnsPage->assertOk();
        $returnsPage->assertSee($order->order_number);
        $returnsPage->assertSee('Refunded');
    }

    // =========================================================================
    // REQUIREMENT 4: PROFIT & LOSS (P&L) REPORT WITH REFUNDED GOODS AS LOSS
    // =========================================================================

    public function test_profit_and_loss_report_calculates_cogs_and_deducts_refunds_as_loss()
    {
        // 1. Fulfilled Order: 2 units sold ($2,400 sales, COGS $1,200)
        $fulfilledOrder = Order::create([
            'order_number'     => 'ORD-PL-01',
            'user_id'          => $this->user->id,
            'status'           => 'completed',
            'subtotal'         => 2400.00,
            'discount'         => 100.00,
            'shipping_cost'    => 60.00,
            'total'            => 2360.00,
            'shipping_name'    => 'Alice Green',
            'shipping_phone'   => '01711111111',
            'shipping_email'   => 'alice@example.com',
            'shipping_address' => '12 Gulshan Ave',
            'shipping_city'    => 'Dhaka',
        ]);

        OrderItem::create([
            'order_id'     => $fulfilledOrder->id,
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku'  => $this->product->sku,
            'quantity'     => 2,
            'unit_price'   => 1200.00,
            'total'        => 2400.00,
        ]);

        // 2. Refunded Order: 1 unit refunded ($1,200 refund loss)
        $refundedOrder = Order::create([
            'order_number'     => 'ORD-PL-02-REFUND',
            'user_id'          => $this->user->id,
            'status'           => 'refunded',
            'refund_amount'    => 1200.00,
            'subtotal'         => 1200.00,
            'discount'         => 0.00,
            'shipping_cost'    => 0.00,
            'total'            => 1200.00,
            'shipping_name'    => 'Alice Green',
            'shipping_phone'   => '01711111111',
            'shipping_email'   => 'alice@example.com',
            'shipping_address' => '12 Gulshan Ave',
            'shipping_city'    => 'Dhaka',
        ]);

        OrderItem::create([
            'order_id'     => $refundedOrder->id,
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku'  => $this->product->sku,
            'quantity'     => 1,
            'unit_price'   => 1200.00,
            'total'        => 1200.00,
        ]);

        // P&L Page loads
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.reports.profit-loss', ['period' => 'alltime']));
        $response->assertOk();

        // Check report contents
        $response->assertSee('Profit & Loss Report', false);
        $response->assertSee('Gross Sales');
        $response->assertSee('2,400.00'); // Gross sales
        $response->assertSee('Refunds (Loss)');
        $response->assertSee('1,200.00'); // Refund loss deducted
        $response->assertSee('COGS (Product Cost)');
        $response->assertSee('1,200.00'); // 2 units * $600 cost_price = $1,200
        $response->assertSee('Net Profit');

        // Test CSV export endpoint
        $csvRes = $this->actingAs($this->admin, 'admin')->get(route('admin.reports.profit-loss.export', ['period' => 'alltime']));
        $csvRes->assertOk();
        $this->assertStringContainsString('text/csv', $csvRes->headers->get('content-type'));
    }
}
