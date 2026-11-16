<?php

use App\Models\Admin;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createDashboardAdmin(): Admin
{
    return Admin::create([
        'name'     => 'Super Admin',
        'email'    => 'admin@beauty.com',
        'password' => Hash::make('password123'),
    ]);
}

test('guest cannot view dashboard', function () {
    $response = $this->get(route('admin.dashboard'));
    $response->assertRedirect(route('admin.login'));
});

test('admin can view comprehensive dashboard with charts, geo stats, device stats, datatable, and clickable products', function () {
    $admin = createDashboardAdmin();

    Setting::create([
        'key'          => 'currency_symbol',
        'group'        => 'general',
        'value'        => '৳',
        'is_encrypted' => false,
    ]);

    $category = Category::create([
        'name'      => 'Skincare',
        'slug'      => 'skincare',
        'is_active' => true,
    ]);

    $productLowStock = Product::create([
        'category_id' => $category->id,
        'name'        => 'Hydrating Rose Cream',
        'slug'        => 'hydrating-rose-cream',
        'sku'         => 'HRC-001',
        'price'       => 45.00,
        'stock'       => 3,
        'is_active'   => true,
        'views_count' => 150,
    ]);

    $productTopSelling = Product::create([
        'category_id' => $category->id,
        'name'        => 'Vitamin C Glow Serum',
        'slug'        => 'vitamin-c-glow-serum',
        'sku'         => 'VCS-002',
        'price'       => 65.00,
        'stock'       => 50,
        'is_active'   => true,
        'views_count' => 320,
    ]);

    $user = User::create([
        'name'     => 'Alice Johnson',
        'email'    => 'alice@example.com',
        'password' => Hash::make('password123'),
    ]);

    // Create an order
    $order = Order::create([
        'user_id'           => $user->id,
        'order_number'      => 'ORD-TEST-001',
        'status'            => 'completed',
        'subtotal'          => 130.00,
        'shipping_cost'     => 10.00,
        'discount'          => 0.00,
        'total'             => 140.00,
        'shipping_name'     => 'Alice Johnson',
        'shipping_email'    => 'alice@example.com',
        'shipping_phone'    => '1234567890',
        'shipping_address'  => '123 Main St',
        'shipping_city'     => 'New York',
        'shipping_country'  => 'US',
    ]);

    OrderItem::create([
        'order_id'     => $order->id,
        'product_id'   => $productTopSelling->id,
        'product_name' => $productTopSelling->name,
        'product_sku'  => $productTopSelling->sku,
        'quantity'     => 2,
        'unit_price'   => 65.00,
        'total'        => 130.00,
    ]);

    // Create visitor log
    $visit = SiteVisit::create([
        'session_id'   => 'test_sess_1',
        'ip_address'   => '64.233.160.1',
        'url'          => 'http://localhost/shop',
        'device'       => 'Desktop',
        'platform'     => 'macOS',
        'browser'      => 'Chrome',
        'country'      => 'United States',
        'country_code' => 'US',
        'city'         => 'Mountain View',
        'visited_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'));

    $response->assertOk();

    // Verify key sections
    $response->assertSee('Orders & Revenue Analytics', false);
    $response->assertSee('Visitor Geographic Distribution (World Map)');
    $response->assertSee('Device Types');
    $response->assertSee('Recent Visitor Activity Logs');
    $response->assertSee('Low Stock Alert');
    $response->assertSee('Low Stock Inventory Manager');

    // Verify currency symbol from settings is rendered
    $response->assertSee('৳140.00');

    // Verify World Map container height (h-[460px]) & minimize controls
    $response->assertSee('h-[460px]');
    $response->assertSee('mapZoomOut()', false);
    $response->assertSee('toggleMapHeight()', false);

    // Verify Visitor Logs DataTable and AJAX delete buttons
    $response->assertSee('id="visitor-logs-datatable"', false);
    $response->assertSee('deleteVisitorLogAjax', false);
    $response->assertSee('bulkDeleteVisitorLogsAjax', false);

    // Verify clickable products
    $response->assertSee(route('admin.products.edit', $productLowStock));
    $response->assertSee(route('admin.products.edit', $productTopSelling));
    $response->assertSee('Hydrating Rose Cream');
    $response->assertSee('Vitamin C Glow Serum');

    // Verify view data contains periods
    $orderCharts = $response->viewData('orderCharts');
    expect($orderCharts)->toHaveKeys(['today', 'month', 'year', 'all_time']);
    expect($orderCharts['today'])->toHaveKeys(['labels', 'counts', 'revenues', 'status_breakdown', 'summary']);
    expect($orderCharts['month'])->toHaveKeys(['labels', 'counts', 'revenues', 'status_breakdown', 'summary']);
    expect($orderCharts['year'])->toHaveKeys(['labels', 'counts', 'revenues', 'status_breakdown', 'summary']);
    expect($orderCharts['all_time'])->toHaveKeys(['labels', 'counts', 'revenues', 'status_breakdown', 'summary']);

    // Verify country and device stats
    $countryStats = $response->viewData('countryStats');
    expect($countryStats)->toHaveKeys(['total_visitors', 'total_countries', 'map_data', 'country_list']);

    $deviceStats = $response->viewData('deviceStats');
    expect($deviceStats)->toHaveKeys(['devices', 'browsers', 'platforms']);
});

test('admin can quickly update product stock from dashboard', function () {
    $admin = createDashboardAdmin();

    $category = Category::create([
        'name'      => 'Fragrance',
        'slug'      => 'fragrance',
        'is_active' => true,
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name'        => 'Velvet Amber Perfume',
        'slug'        => 'velvet-amber-perfume',
        'sku'         => 'VAP-001',
        'price'       => 95.00,
        'stock'       => 2,
        'is_active'   => true,
    ]);

    $response = $this->actingAs($admin, 'admin')->patchJson(route('admin.products.update-stock', $product), [
        'stock' => 25,
    ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'product' => [
            'id'    => $product->id,
            'stock' => 25,
        ],
    ]);

    expect($product->fresh()->stock)->toBe(25);
});

test('admin can delete a single visitor log via ajax json', function () {
    $admin = createDashboardAdmin();

    $visit = SiteVisit::create([
        'session_id'   => 'del_sess_1',
        'ip_address'   => '192.168.1.50',
        'url'          => 'http://localhost/products',
        'device'       => 'Mobile',
        'platform'     => 'Android',
        'browser'      => 'Chrome',
        'country'      => 'Bangladesh',
        'country_code' => 'BD',
        'visited_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($admin, 'admin')->deleteJson(route('admin.visitor-logs.destroy', $visit));

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Visitor log removed successfully.',
    ]);
    $this->assertDatabaseMissing('site_visits', ['id' => $visit->id]);
});

test('admin can bulk delete multiple visitor logs via ajax json', function () {
    $admin = createDashboardAdmin();

    $v1 = SiteVisit::create([
        'session_id'   => 'bulk_1',
        'ip_address'   => '1.1.1.1',
        'url'          => 'http://localhost/',
        'visited_date' => now()->toDateString(),
    ]);

    $v2 = SiteVisit::create([
        'session_id'   => 'bulk_2',
        'ip_address'   => '2.2.2.2',
        'url'          => 'http://localhost/shop',
        'visited_date' => now()->toDateString(),
    ]);

    $v3 = SiteVisit::create([
        'session_id'   => 'bulk_3',
        'ip_address'   => '3.3.3.3',
        'url'          => 'http://localhost/blog',
        'visited_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($admin, 'admin')->postJson(route('admin.visitor-logs.bulk-delete'), [
        'ids' => [$v1->id, $v2->id],
    ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'count'   => 2,
    ]);

    $this->assertDatabaseMissing('site_visits', ['id' => $v1->id]);
    $this->assertDatabaseMissing('site_visits', ['id' => $v2->id]);
    $this->assertDatabaseHas('site_visits', ['id' => $v3->id]);
});

test('bulk delete visitor logs requires valid ids', function () {
    $admin = createDashboardAdmin();

    $response = $this->actingAs($admin, 'admin')->postJson(route('admin.visitor-logs.bulk-delete'), [
        'ids' => [],
    ]);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['ids']);

    $responseInvalid = $this->actingAs($admin, 'admin')->postJson(route('admin.visitor-logs.bulk-delete'), [
        'ids' => [999999],
    ]);
    $responseInvalid->assertStatus(422);
    $responseInvalid->assertJsonValidationErrors(['ids.0']);
});
