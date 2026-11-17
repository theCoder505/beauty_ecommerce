<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminOrderProductSkuTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private User $user;
    private Product $product;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@beautyecom.com',
            'password' => Hash::make('password'),
            'role'     => 'super_admin',
        ]);

        $this->user = User::factory()->create([
            'name'  => 'Test Customer',
            'email' => 'customer@beautyecom.com',
        ]);

        $category = Category::create([
            'name' => 'Skincare',
            'slug' => 'skincare',
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name'        => 'Velvet Lip Tint',
            'slug'        => 'velvet-lip-tint',
            'sku'         => 'SKU-VLT-999',
            'price'       => 25.00,
            'stock'       => 50,
            'is_active'   => true,
        ]);

        $this->order = Order::create([
            'user_id'          => $this->user->id,
            'order_number'     => 'ORD-20261118-001',
            'status'           => 'pending',
            'shipping_name'    => 'Test Customer',
            'shipping_email'   => 'customer@beautyecom.com',
            'shipping_phone'   => '+1234567890',
            'shipping_address' => '123 Beauty Blvd',
            'shipping_city'    => 'Dhaka',
            'shipping_state'   => 'Dhaka',
            'shipping_zip'     => '1212',
            'subtotal'         => 50.00,
            'shipping_cost'    => 5.00,
            'total'            => 55.00,
        ]);

        OrderItem::create([
            'order_id'     => $this->order->id,
            'product_id'   => $this->product->id,
            'product_name' => 'Velvet Lip Tint',
            'product_sku'  => 'SKU-VLT-999',
            'quantity'     => 2,
            'unit_price'   => 25.00,
            'total'        => 50.00,
        ]);
    }

    public function test_admin_order_details_displays_sku_in_bracket_next_to_product_name(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.orders.show', $this->order));

        $response->assertStatus(200);

        // Assert product name is followed by SKU in bracket
        $response->assertSee('Velvet Lip Tint');
        $response->assertSee('(SKU-VLT-999)');
    }

    public function test_admin_orders_index_lists_sku_in_bracket_for_order_items(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.orders.index'));

        $response->assertStatus(200);
        $response->assertSee('#' . $this->order->order_number);
        $response->assertSee('Velvet Lip Tint (SKU-VLT-999)');
    }
}
