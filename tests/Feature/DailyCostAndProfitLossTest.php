<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\DailyCost;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyCostAndProfitLossTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name'     => 'Finance Manager',
            'email'    => 'finance@beauty.test',
            'password' => bcrypt('password123'),
        ]);

        $category = Category::create([
            'name'      => 'Skincare',
            'slug'      => 'skincare',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name'        => 'Rose Hydrating Cream',
            'slug'        => 'rose-hydrating-cream',
            'sku'         => 'RHC-101',
            'price'       => 1500.00,
            'sale_price'  => 1200.00,
            'cost_price'  => 600.00,
            'stock'       => 100,
            'is_active'   => true,
        ]);
    }

    public function test_admin_can_view_daily_costs_page_with_kpi_cards_and_pl_summary(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.daily-costs.index'));

        $response->assertOk();
        $response->assertSee('Daily Costs & Profit/Loss', false);
        $response->assertSee('Simple Profit & Loss Statement', false);
        $response->assertSee('Product Sales (Revenue)', false);
        $response->assertSee('Total Recorded Costs', false);
        $response->assertSee('Net Profit / Loss', false);
        $response->assertSee('Daily Cost Entries & Inventory Log', false);
    }

    public function test_admin_can_store_daily_inventory_cost_and_auto_calculates_amount(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.daily-costs.store'), [
            'cost_date'    => now()->format('Y-m-d'),
            'title'        => 'Batch Restock Rose Cream 50 Units',
            'category'     => 'inventory',
            'product_id'   => $this->product->id,
            'quantity'     => 50,
            'unit_cost'    => 550.00,
            'amount'       => 27500.00, // 50 * 550
            'reference_no' => 'PO-2026-99',
            'notes'        => 'Direct delivery from lab',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('daily_costs', [
            'title'        => 'Batch Restock Rose Cream 50 Units',
            'category'     => 'inventory',
            'product_id'   => $this->product->id,
            'quantity'     => 50,
            'unit_cost'    => 550.00,
            'amount'       => 27500.00,
            'reference_no' => 'PO-2026-99',
            'created_by'   => 'Finance Manager',
        ]);
    }

    public function test_admin_can_update_and_delete_daily_cost(): void
    {
        $cost = DailyCost::create([
            'cost_date'    => now()->format('Y-m-d'),
            'title'        => 'Eco-friendly mailer boxes',
            'category'     => 'packaging',
            'amount'       => 4500.00,
            'reference_no' => 'BOX-01',
        ]);

        // Update
        $updateRes = $this->actingAs($this->admin, 'admin')->put(route('admin.daily-costs.update', $cost), [
            'cost_date'    => now()->format('Y-m-d'),
            'title'        => 'Eco-friendly mailer boxes 500 pcs',
            'category'     => 'packaging',
            'amount'       => 4800.00,
            'reference_no' => 'BOX-01-REVISED',
        ]);

        $updateRes->assertRedirect();
        $updateRes->assertSessionHas('success');

        $this->assertDatabaseHas('daily_costs', [
            'id'           => $cost->id,
            'title'        => 'Eco-friendly mailer boxes 500 pcs',
            'amount'       => 4800.00,
            'reference_no' => 'BOX-01-REVISED',
        ]);

        // Delete
        $deleteRes = $this->actingAs($this->admin, 'admin')->delete(route('admin.daily-costs.destroy', $cost));
        $deleteRes->assertRedirect();
        $deleteRes->assertSessionHas('success');

        $this->assertDatabaseMissing('daily_costs', ['id' => $cost->id]);
    }

    public function test_system_counts_sales_refunds_and_calculates_net_profit_and_loss_accurately(): void
    {
        $user = \App\Models\User::factory()->create();

        // 1. Fulfilled Order for today: 3 units sold @ 1200 = $3,600 sales
        $order = Order::create([
            'order_number'     => 'ORD-COST-PL-01',
            'user_id'          => $user->id,
            'status'           => 'completed',
            'subtotal'         => 3600.00,
            'discount'         => 100.00,
            'shipping_cost'    => 50.00,
            'total'            => 3550.00,
            'shipping_name'    => 'Customer One',
            'shipping_phone'   => '01700000000',
            'shipping_email'   => 'c1@beauty.test',
            'shipping_address' => 'Road 1',
            'shipping_city'    => 'Dhaka',
            'created_at'       => now(),
        ]);

        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku'  => $this->product->sku,
            'quantity'     => 3,
            'unit_price'   => 1200.00,
            'total'        => 3600.00,
        ]);

        // 2. Refunded Order for today: 1 unit refunded = $1,200 loss
        $refundOrder = Order::create([
            'order_number'     => 'ORD-REF-COST-01',
            'user_id'          => $user->id,
            'status'           => 'refunded',
            'refund_amount'    => 1200.00,
            'subtotal'         => 1200.00,
            'total'            => 1200.00,
            'shipping_name'    => 'Customer Two',
            'shipping_phone'   => '01700000001',
            'shipping_email'   => 'c2@beauty.test',
            'shipping_address' => 'Road 2',
            'shipping_city'    => 'Dhaka',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // 3. Daily Cost entry for today: $800 inventory restock
        DailyCost::create([
            'cost_date'  => now()->format('Y-m-d'),
            'title'      => 'Supplier Daily Restock',
            'category'   => 'inventory',
            'amount'     => 800.00,
            'created_by' => 'Finance Manager',
        ]);

        // Net Sales = 3600 - 100 - 1200 = 2300
        // Net Profit = (2300 + 50 shipping) - 800 cost = 1550 profit!

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.daily-costs.index', ['period' => 'today']));
        $response->assertOk();

        $response->assertSee('3,600.00'); // Gross sales
        $response->assertSee('1,200.00'); // Refund loss
        $response->assertSee('800.00');   // Daily recorded cost
        $response->assertSee('1,550.00'); // Net profit
        $response->assertSee('NET PROFIT');
    }

    public function test_period_filters_today_this_month_this_year_alltime_and_custom_range(): void
    {
        // Cost yesterday
        DailyCost::create([
            'cost_date' => now()->subDay()->format('Y-m-d'),
            'title'     => 'Yesterday Logistics',
            'category'  => 'shipping_logistics',
            'amount'    => 250.00,
        ]);

        // Cost today
        DailyCost::create([
            'cost_date' => now()->format('Y-m-d'),
            'title'     => 'Today Raw Materials',
            'category'  => 'inventory',
            'amount'    => 750.00,
        ]);

        // Today view should only show Today Raw Materials ($750)
        $todayRes = $this->actingAs($this->admin, 'admin')->get(route('admin.daily-costs.index', ['period' => 'today']));
        $todayRes->assertOk();
        $todayRes->assertSee('Today Raw Materials');
        $todayRes->assertDontSee('Yesterday Logistics');
        $todayRes->assertSee('750.00');

        // Monthly view should show both ($250 + $750 = $1,000)
        $monthRes = $this->actingAs($this->admin, 'admin')->get(route('admin.daily-costs.index', ['period' => 'monthly']));
        $monthRes->assertOk();
        $monthRes->assertSee('Today Raw Materials');
        $monthRes->assertSee('Yesterday Logistics');
        $monthRes->assertSee('1,000.00');

        // Custom range test
        $customRes = $this->actingAs($this->admin, 'admin')->get(route('admin.daily-costs.index', [
            'period'    => 'custom',
            'date_from' => now()->subDays(2)->format('Y-m-d'),
            'date_to'   => now()->subDay()->format('Y-m-d'),
        ]));
        $customRes->assertOk();
        $customRes->assertSee('Yesterday Logistics');
        $customRes->assertDontSee('Today Raw Materials');
    }

    public function test_admin_can_export_daily_costs_and_pl_csv(): void
    {
        DailyCost::create([
            'cost_date'    => now()->format('Y-m-d'),
            'title'        => 'Custom Bottle Caps Batch',
            'category'     => 'packaging',
            'amount'       => 1500.00,
            'reference_no' => 'CAP-99',
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.daily-costs.export', ['period' => 'monthly']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }

    public function test_unauthenticated_guests_cannot_access_or_modify_daily_costs(): void
    {
        $this->get(route('admin.daily-costs.index'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.daily-costs.store'), ['amount' => 100])->assertRedirect(route('admin.login'));
    }
}
