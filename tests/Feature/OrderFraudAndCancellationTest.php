<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\FraudDetectionService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFraudAndCancellationTest extends TestCase
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
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'phone' => '01711223344',
            'created_at' => now()->subMonths(3),
        ]);

        $this->admin = Admin::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@beauty.test',
            'password' => bcrypt('password123'),
        ]);

        $this->category = Category::create([
            'name'      => 'Skincare',
            'slug'      => 'skincare',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name'        => 'Hydrating Rose Serum',
            'slug'        => 'hydrating-rose-serum',
            'sku'         => 'HRS-001',
            'price'       => 1500,
            'cost_price'  => 900,
            'stock'       => 50,
            'is_active'   => true,
            'track_stock' => true,
        ]);
    }

    protected function createOrder(array $attributes = []): Order
    {
        static $seq = 1000;
        $seq++;
        return Order::create(array_merge([
            'order_number'     => 'ORD-TEST-' . $seq,
            'user_id'          => $this->user->id,
            'status'           => 'pending',
            'subtotal'         => 1500.00,
            'shipping_cost'    => 60.00,
            'total'            => 1560.00,
            'shipping_name'    => 'Jane Customer',
            'shipping_phone'   => '01711223344',
            'shipping_email'   => 'jane@example.com',
            'shipping_address' => 'House 12, Road 4, Dhanmondi',
            'shipping_city'    => 'Dhaka',
        ], $attributes));
    }

    protected function createOrderItem(Order $order, array $attributes = []): OrderItem
    {
        return OrderItem::create(array_merge([
            'order_id'     => $order->id,
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku'  => $this->product->sku ?? 'HRS-001',
            'quantity'     => 1,
            'unit_price'   => 1500,
            'price'        => 1500,
            'total'        => 1500,
            'subtotal'     => 1500,
        ], $attributes));
    }

    /**
     * Test FraudDetectionService identifies low risk for standard orders.
     */
    public function test_fraud_service_evaluates_low_risk_for_normal_order(): void
    {
        $order = $this->createOrder([
            'user_id' => $this->user->id,
            'total' => 2500,
            'status' => 'pending',
            'shipping_name' => 'Jane Customer',
            'shipping_email' => 'jane@example.com',
            'shipping_phone' => '01711223344',
            'shipping_address' => 'House 12, Road 4, Dhanmondi',
        ]);

        $this->createOrderItem($order, [
            'quantity' => 2,
            'unit_price' => 1250,
            'price' => 1250,
            'total' => 2500,
            'subtotal' => 2500,
        ]);

        $service = new FraudDetectionService();
        $analysis = $service->evaluate($order);

        $this->assertEquals('low', $analysis['risk_level']);
        $this->assertLessThan(30, $analysis['risk_score']);
        $this->assertIsArray($analysis['flags']);
    }

    /**
     * Test FraudDetectionService flags high value, bulk quantities, and suspicious patterns.
     */
    public function test_fraud_service_detects_high_risk_order(): void
    {
        $newUser = User::factory()->create([
            'created_at' => now()->subMinutes(10), // brand new
            'email' => 'tester@tempmail.com', // disposable domain
        ]);

        // Place 3 orders in last 24h to simulate velocity
        for ($i = 0; $i < 3; $i++) {
            $this->createOrder([
                'user_id' => $newUser->id,
                'created_at' => now()->subHours(2),
            ]);
        }

        $suspiciousOrder = $this->createOrder([
            'user_id' => $newUser->id,
            'total' => 35000, // Very high total
            'status' => 'pending',
            'shipping_name' => 'Someone Completely Different',
            'shipping_email' => 'tester@mailinator.com', // disposable domain
            'shipping_phone' => '00000000000', // invalid pattern
            'shipping_address' => 'test test test placeholder',
        ]);

        $this->createOrderItem($suspiciousOrder, [
            'quantity' => 25, // Bulk quantity
            'unit_price' => 1400,
            'price' => 1400,
            'total' => 35000,
            'subtotal' => 35000,
        ]);

        $service = new FraudDetectionService();
        $analysis = $service->evaluate($suspiciousOrder);

        $this->assertGreaterThanOrEqual(60, $analysis['risk_score']);
        $this->assertEquals('high', $analysis['risk_level']);
        $this->assertNotEmpty($analysis['flags']);
    }

    /**
     * Test Admin order index lists fraud risk level and supports filtering.
     */
    public function test_admin_orders_index_displays_fraud_indicators_and_filters(): void
    {
        $lowOrder = $this->createOrder([
            'user_id' => $this->user->id,
            'total' => 1200,
            'fraud_risk_level' => 'low',
            'fraud_risk_score' => 10,
        ]);

        $highOrder = $this->createOrder([
            'user_id' => $this->user->id,
            'total' => 45000,
            'fraud_risk_level' => 'high',
            'fraud_risk_score' => 75,
            'fraud_flags' => ['High order total (> 15,000 BDT)'],
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.orders.index'));
        $response->assertStatus(200);
        $response->assertSee('Fraud Risk');
        $response->assertSee('High (75)');

        // Filter high risk
        $filterResponse = $this->actingAs($this->admin, 'admin')->get(route('admin.orders.index', ['fraud_risk' => 'high']));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee($highOrder->order_number);
    }

    /**
     * Test Admin can cancel order with notice and the customer sees it.
     */
    public function test_admin_can_cancel_order_with_notice_and_customer_sees_it(): void
    {
        $initialStock = $this->product->stock;

        $order = $this->createOrder([
            'user_id' => $this->user->id,
            'total' => 4500,
            'status' => 'confirmed',
        ]);

        $this->createOrderItem($order, [
            'quantity' => 3,
            'unit_price' => 1500,
            'price' => 1500,
            'total' => 4500,
            'subtotal' => 4500,
        ]);

        // Deduct 3 stock as if ordered
        $this->product->decrement('stock', 3);
        $this->assertEquals($initialStock - 3, $this->product->fresh()->stock);

        $cancelNotice = 'Flagged by fraud detection: recipient phone number could not be verified by our verification team.';

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.orders.cancel-notice', $order), [
            'cancellation_reason' => $cancelNotice,
        ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertStringStartsWith('admin', $order->cancelled_by);
        $this->assertEquals($cancelNotice, $order->cancellation_reason);
        $this->assertNotNull($order->cancelled_at);

        // Stock must be restocked
        $this->assertEquals($initialStock, $this->product->fresh()->stock);

        // Verify customer sees the cancellation notice on their order page
        $userResponse = $this->actingAs($this->user)->get(route('user.orders.show', $order));
        $userResponse->assertStatus(200);
        $userResponse->assertSee('This Order Has Been Cancelled');
        $userResponse->assertSee('Notice from Store:');
        $userResponse->assertSee($cancelNotice);
    }

    /**
     * Test Customer can cancel order when status is pending or confirmed.
     */
    public function test_customer_can_cancel_order_when_pending_or_confirmed(): void
    {
        $initialStock = $this->product->stock;

        $order = $this->createOrder([
            'user_id' => $this->user->id,
            'total' => 3000,
            'status' => 'pending',
        ]);

        $this->createOrderItem($order, [
            'quantity' => 2,
            'unit_price' => 1500,
            'price' => 1500,
            'total' => 3000,
            'subtotal' => 3000,
        ]);

        $this->product->decrement('stock', 2);
        $this->assertTrue($order->canBeCancelledByUser());

        $customerReason = 'Ordered duplicate items by mistake';

        $response = $this->actingAs($this->user)->post(route('user.orders.cancel', $order), [
            'cancellation_reason' => $customerReason,
        ]);

        $response->assertRedirect(route('user.orders.show', $order));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertStringStartsWith('user', $order->cancelled_by);
        $this->assertEquals($customerReason, $order->cancellation_reason);
        $this->assertNotNull($order->cancelled_at);

        // Restocked
        $this->assertEquals($initialStock, $this->product->fresh()->stock);
    }

    /**
     * Test Customer CANNOT cancel order once status reaches processing or later.
     */
    public function test_customer_cannot_cancel_order_once_status_is_processing(): void
    {
        $order = $this->createOrder([
            'user_id' => $this->user->id,
            'total' => 3000,
            'status' => 'processing',
        ]);

        $this->assertFalse($order->canBeCancelledByUser());

        $response = $this->actingAs($this->user)->post(route('user.orders.cancel', $order), [
            'cancellation_reason' => 'I want to cancel now',
        ]);

        $response->assertSessionHas('error');
        $order->refresh();
        $this->assertEquals('processing', $order->status);
        $this->assertNull($order->cancelled_at);
    }

    /**
     * Test Customer cannot cancel an order belonging to another customer.
     */
    public function test_customer_cannot_cancel_another_users_order(): void
    {
        $otherUser = User::factory()->create();
        $order = $this->createOrder([
            'user_id' => $otherUser->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->post(route('user.orders.cancel', $order), [
            'cancellation_reason' => 'Malicious cancel attempt',
        ]);

        $response->assertStatus(403);
        $this->assertEquals('pending', $order->fresh()->status);
    }
}
