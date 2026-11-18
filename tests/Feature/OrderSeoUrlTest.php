<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrderSeoUrlTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private User $user;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name'     => 'Admin User',
            'email'    => 'admin@beauty.com',
            'password' => Hash::make('password123'),
            'role'     => 'super_admin',
        ]);

        $this->user = User::factory()->create([
            'name'  => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $category = Category::create([
            'name' => 'Skincare',
            'slug' => 'skincare',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name'        => 'Glow Serum',
            'slug'        => 'glow-serum',
            'sku'         => 'GLOW-01',
            'price'       => 35.00,
            'stock'       => 50,
            'is_active'   => true,
        ]);

        $this->order = Order::create([
            'user_id'          => $this->user->id,
            'order_number'     => 'ORD-2026-TEST999',
            'status'           => 'pending',
            'subtotal'         => 35.00,
            'shipping_cost'    => 5.00,
            'total'            => 40.00,
            'shipping_name'    => 'John Doe',
            'shipping_email'   => 'john@example.com',
            'shipping_phone'   => '+123456789',
            'shipping_address' => '123 Main St',
            'shipping_city'    => 'Dhaka',
            'shipping_state'   => 'Dhaka',
            'shipping_zip'     => '1212',
            'shipping_country' => 'BD',
        ]);

        OrderItem::create([
            'order_id'       => $this->order->id,
            'product_id'     => $product->id,
            'product_name'   => 'Glow Serum',
            'product_sku'    => 'GLOW-01',
            'quantity'       => 1,
            'unit_price'     => 35.00,
            'total'          => 35.00,
            'selected_size'  => '30ml',
            'selected_color' => null,
            'selected_shade' => null,
        ]);
    }

    public function test_user_order_show_route_generates_seo_order_number_url(): void
    {
        $url = route('user.orders.show', $this->order);

        $this->assertStringContainsString('/orders/' . $this->order->order_number, $url);
        $this->assertStringNotContainsString('/orders/' . $this->order->id, $url);
        $this->assertStringNotContainsString('/dashboard/orders/', $url);
    }

    public function test_user_can_access_order_details_via_order_number_seo_url(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/orders/' . $this->order->order_number);

        $response->assertStatus(200);
        $response->assertSee($this->order->order_number);
        $response->assertSee('Glow Serum');
        $response->assertSee('30ml');
    }

    public function test_dashboard_orders_order_number_url_redirects_to_seo_url(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/dashboard/orders/' . $this->order->order_number);

        $response->assertRedirect('/orders/' . $this->order->order_number);
    }

    public function test_fallback_by_numeric_id_also_resolves_cleanly(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/orders/' . $this->order->id);

        $response->assertStatus(200);
        $response->assertSee($this->order->order_number);
    }

    public function test_admin_order_url_uses_order_number_seo_format(): void
    {
        $url = route('admin.orders.show', $this->order);

        $this->assertStringContainsString('/admin/orders/' . $this->order->order_number, $url);
        $this->assertStringNotContainsString('/admin/orders/' . $this->order->id, $url);

        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/orders/' . $this->order->order_number);

        $response->assertStatus(200);
        $response->assertSee($this->order->order_number);
        $response->assertSee('Glow Serum');
    }

    public function test_checkout_success_url_uses_order_number_seo_format(): void
    {
        $url = route('checkout.success', $this->order);

        $this->assertStringContainsString('/checkout/success/' . $this->order->order_number, $url);
        $this->assertStringNotContainsString('/checkout/success/' . $this->order->id, $url);

        $response = $this->actingAs($this->user)
            ->get('/checkout/success/' . $this->order->order_number);

        $response->assertStatus(200);
        $response->assertSee($this->order->order_number);
        $response->assertSee('Track Order');
        $response->assertSee(route('user.orders.show', $this->order));
    }
}
