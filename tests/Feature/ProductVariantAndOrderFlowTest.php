<?php

namespace Tests\Feature;

use App\Mail\OrderConfirmationMail;
use App\Models\UserAddress;
use App\Models\Admin;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProductVariantAndOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name'     => 'Store Manager',
            'email'    => 'admin@beauty.com',
            'password' => Hash::make('password123'),
            'role'     => 'super_admin',
        ]);

        $this->user = User::factory()->create([
            'name'  => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+8801700000000',
        ]);

        UserAddress::create([
            'user_id'        => $this->user->id,
            'recipient_name' => 'Jane Doe',
            'phone'          => '+8801700000000',
            'address_line'   => 'House 12, Road 4, Banani',
            'city'           => 'Dhaka',
            'state'          => 'Dhaka',
            'postal_code'    => '1213',
            'country'        => 'BD',
            'is_primary'     => true,
        ]);

        $this->category = Category::create([
            'name' => 'Cosmetics',
            'slug' => 'cosmetics',
        ]);
    }

    public function test_admin_can_create_product_with_sizes_colors_shades_and_availability(): void
    {
        $payload = [
            'name'        => 'Luminous Foundation',
            'slug'        => 'luminous-foundation',
            'category_id' => $this->category->id,
            'sku'         => 'LUM-FDN-01',
            'price'       => 45.00,
            'stock'       => 100,
            'is_active'   => 1,
            'thumbnail'   => UploadedFile::fake()->image('thumb.jpg'),
            'sizes'       => [
                ['name' => '30ml', 'is_available' => '1'],
                ['name' => '50ml', 'is_available' => '0'], // unavailable
            ],
            'colors'      => [
                ['name' => 'Ivory Rose', 'code' => '#ffcccc', 'is_available' => '1'],
                ['name' => 'Golden Sand', 'code' => '#e6b800', 'is_available' => '0'], // unavailable
            ],
            'shades'      => [
                ['name' => 'Fair Warm', 'code' => '#f5deb3', 'is_available' => '1'],
            ],
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.products.store'), $payload);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('slug', 'luminous-foundation')->first();
        $this->assertNotNull($product);

        $this->assertDatabaseHas('product_options', [
            'product_id'   => $product->id,
            'type'         => 'size',
            'name'         => '30ml',
            'is_available' => true,
        ]);

        $this->assertDatabaseHas('product_options', [
            'product_id'   => $product->id,
            'type'         => 'size',
            'name'         => '50ml',
            'is_available' => false,
        ]);

        $this->assertDatabaseHas('product_options', [
            'product_id'   => $product->id,
            'type'         => 'color',
            'name'         => 'Ivory Rose',
            'code'         => '#ffcccc',
            'is_available' => true,
        ]);

        $this->assertDatabaseHas('product_options', [
            'product_id'   => $product->id,
            'type'         => 'color',
            'name'         => 'Golden Sand',
            'is_available' => false,
        ]);

        $this->assertDatabaseHas('product_options', [
            'product_id'   => $product->id,
            'type'         => 'shade',
            'name'         => 'Fair Warm',
            'is_available' => true,
        ]);
    }

    public function test_admin_can_update_product_variants_and_availability(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name'        => 'Matte Lipstick',
            'slug'        => 'matte-lipstick',
            'sku'         => 'LIP-MAT-01',
            'price'       => 20.00,
            'stock'       => 50,
            'is_active'   => true,
        ]);

        ProductOption::create([
            'product_id'   => $product->id,
            'type'         => 'shade',
            'name'         => 'Ruby Woo',
            'code'         => '#990000',
            'is_available' => true,
        ]);

        $updatePayload = [
            'name'        => 'Matte Lipstick',
            'slug'        => 'matte-lipstick',
            'category_id' => $this->category->id,
            'sku'         => 'LIP-MAT-01',
            'price'       => 22.00,
            'stock'       => 45,
            'is_active'   => 1,
            'shades'      => [
                ['name' => 'Ruby Woo', 'code' => '#990000', 'is_available' => '0'], // turned unavailable
                ['name' => 'Velvet Teddy', 'code' => '#cc8866', 'is_available' => '1'], // new available
            ],
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.products.update', $product), $updatePayload);

        $response->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('product_options', [
            'product_id'   => $product->id,
            'type'         => 'shade',
            'name'         => 'Ruby Woo',
            'is_available' => false,
        ]);

        $this->assertDatabaseHas('product_options', [
            'product_id'   => $product->id,
            'type'         => 'shade',
            'name'         => 'Velvet Teddy',
            'is_available' => true,
        ]);
    }

    public function test_product_detail_page_renders_variants_and_unavailable_indicators(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name'        => 'Hydra Glow Tint',
            'slug'        => 'hydra-glow-tint',
            'sku'         => 'HYD-01',
            'price'       => 30.00,
            'stock'       => 20,
            'is_active'   => true,
        ]);

        ProductOption::create([
            'product_id'   => $product->id,
            'type'         => 'size',
            'name'         => 'Travel Size',
            'is_available' => true,
        ]);

        ProductOption::create([
            'product_id'   => $product->id,
            'type'         => 'size',
            'name'         => 'Full Size',
            'is_available' => false, // unavailable
        ]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertStatus(200);
        $response->assertSee('Travel Size');
        $response->assertSee('Full Size');
        $response->assertSee('(Unavailable)');
    }

    public function test_cart_add_validation_requires_variant_selection(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name'        => 'Satin Primer',
            'slug'        => 'satin-primer',
            'sku'         => 'PRI-01',
            'price'       => 28.00,
            'stock'       => 30,
            'is_active'   => true,
        ]);

        ProductOption::create([
            'product_id'   => $product->id,
            'type'         => 'size',
            'name'         => '50ml',
            'is_available' => true,
        ]);

        // Attempting to add without size should fail
        $response = $this->actingAs($this->user)
            ->postJson(route('cart.add'), [
                'product_id' => $product->id,
                'quantity'   => 1,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['size']);
    }

    public function test_cart_add_validation_rejects_unavailable_variant(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name'        => 'Silk Concealer',
            'slug'        => 'silk-concealer',
            'sku'         => 'CON-01',
            'price'       => 18.00,
            'stock'       => 25,
            'is_active'   => true,
        ]);

        ProductOption::create([
            'product_id'   => $product->id,
            'type'         => 'shade',
            'name'         => 'Caramel',
            'is_available' => false, // unavailable
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('cart.add'), [
                'product_id' => $product->id,
                'quantity'   => 1,
                'shade'      => 'Caramel',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['shade']);
        $this->assertStringContainsString('unavailable', $response->json('message'));
    }

    public function test_cart_add_and_checkout_flow_saves_variants_sends_email_and_redirects(): void
    {
        Mail::fake();

        $product = Product::create([
            'category_id' => $this->category->id,
            'name'        => 'Glow Blush',
            'slug'        => 'glow-blush',
            'sku'         => 'BLU-01',
            'price'       => 24.00,
            'stock'       => 50,
            'is_active'   => true,
        ]);

        ProductOption::create([
            'product_id'   => $product->id,
            'type'         => 'size',
            'name'         => 'Standard',
            'is_available' => true,
        ]);

        ProductOption::create([
            'product_id'   => $product->id,
            'type'         => 'color',
            'name'         => 'Peach Blossom',
            'code'         => '#ff9999',
            'is_available' => true,
        ]);

        // Add to cart with valid options
        $cartResponse = $this->actingAs($this->user)
            ->postJson(route('cart.add'), [
                'product_id' => $product->id,
                'quantity'   => 2,
                'size'       => 'Standard',
                'color'      => 'Peach Blossom',
            ]);

        $cartResponse->assertStatus(200);
        $cartResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('cart_items', [
            'user_id'        => $this->user->id,
            'product_id'     => $product->id,
            'quantity'       => 2,
            'selected_size'  => 'Standard',
            'selected_color' => 'Peach Blossom',
        ]);

        // Proceed to place order
        $address = $this->user->addresses->first();

        $checkoutResponse = $this->actingAs($this->user)
            ->postJson(route('checkout.place'), [
                'address_id'         => $address->id,
                'payment_method'     => 'cod',
                'delivery_charge_id' => 'inside_dhaka',
            ]);

        $checkoutResponse->assertStatus(200);
        $checkoutResponse->assertJson(['success' => true]);

        $orderId = $checkoutResponse->json('order_id');
        $this->assertNotNull($orderId);

        $order = Order::with('items')->find($orderId);
        $this->assertNotNull($order);
        $this->assertCount(1, $order->items);

        $orderItem = $order->items->first();
        $this->assertEquals('Standard', $orderItem->selected_size);
        $this->assertEquals('Peach Blossom', $orderItem->selected_color);
        $this->assertStringContainsString('Size: Standard', $orderItem->options_summary);
        $this->assertStringContainsString('Color: Peach Blossom', $orderItem->options_summary);

        // Assert OrderConfirmationMail was sent to user
        Mail::assertSent(OrderConfirmationMail::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id &&
                   $mail->hasTo($order->shipping_email);
        });

        // Verify SEO Order URLs use order_number instead of id
        $this->assertStringContainsString('/orders/' . $order->order_number, route('user.orders.show', $order));
        $this->assertStringNotContainsString('/orders/' . $order->id, route('user.orders.show', $order));
        $this->assertStringContainsString('/checkout/success/' . $order->order_number, route('checkout.success', $order));

        // Test Order Success Page renders Track Order button and variants
        $successResponse = $this->actingAs($this->user)
            ->get(route('checkout.success', $order));

        $successResponse->assertStatus(200);
        $successResponse->assertSee('Track Order');
        $successResponse->assertSee(route('user.orders.show', $order));
        $successResponse->assertSee('Size: Standard');
        $successResponse->assertSee('Color: Peach Blossom');

        // Test Admin Order Details Page displays variant badges
        $adminOrderResponse = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.orders.show', $order));

        $adminOrderResponse->assertStatus(200);
        $adminOrderResponse->assertSee('Size: Standard');
        $adminOrderResponse->assertSee('Color: Peach Blossom');

        // Test User Order Details Page displays variant summary
        $userOrderDetailResponse = $this->actingAs($this->user)
            ->get(route('user.orders.show', $order));

        $userOrderDetailResponse->assertStatus(200);
        $userOrderDetailResponse->assertSee('Size:');
        $userOrderDetailResponse->assertSee('Standard');
        $userOrderDetailResponse->assertSee('Color:');
        $userOrderDetailResponse->assertSee('Peach Blossom');

        // Test User Orders List Page also displays chosen variants
        $userOrdersIndexResponse = $this->actingAs($this->user)
            ->get(route('user.orders'));

        $userOrdersIndexResponse->assertStatus(200);
        $userOrdersIndexResponse->assertSee('Size: Standard');
        $userOrdersIndexResponse->assertSee('Color: Peach Blossom');
    }

    public function test_buy_now_flow_with_variants_places_order_and_redirects_to_success(): void
    {
        Mail::fake();

        $product = Product::create([
            'category_id' => $this->category->id,
            'name'        => 'Velvet Lip Glaze',
            'slug'        => 'velvet-lip-glaze',
            'sku'         => 'GLZ-01',
            'price'       => 15.00,
            'stock'       => 20,
            'is_active'   => true,
        ]);

        ProductOption::create([
            'product_id'   => $product->id,
            'type'         => 'shade',
            'name'         => 'Cherry Pop',
            'code'         => '#cc0033',
            'is_available' => true,
        ]);

        $address = $this->user->addresses->first();

        // 1. Validation fails if Buy Now is attempted without shade
        $failResponse = $this->actingAs($this->user)
            ->postJson(route('checkout.place'), [
                'buy_now'            => 1,
                'product_id'         => $product->id,
                'quantity'           => 1,
                'address_id'         => $address->id,
                'payment_method'     => 'cod',
                'delivery_charge_id' => 'inside_dhaka',
            ]);

        $failResponse->assertStatus(422);
        $failResponse->assertJsonValidationErrors(['shade']);

        // 2. Buy Now succeeds when shade is provided
        $successResponse = $this->actingAs($this->user)
            ->postJson(route('checkout.place'), [
                'buy_now'            => 1,
                'product_id'         => $product->id,
                'quantity'           => 1,
                'shade'              => 'Cherry Pop',
                'address_id'         => $address->id,
                'payment_method'     => 'cod',
                'delivery_charge_id' => 'inside_dhaka',
            ]);

        $successResponse->assertStatus(200);
        $successResponse->assertJson(['success' => true]);

        $orderId = $successResponse->json('order_id');
        $order = Order::with('items')->find($orderId);
        $this->assertNotNull($order);
        $this->assertEquals('Cherry Pop', $order->items->first()->selected_shade);

        Mail::assertSent(OrderConfirmationMail::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id;
        });

        // Test User Order Details Page displays Shade badge and name
        $userOrderDetailResponse = $this->actingAs($this->user)
            ->get(route('user.orders.show', $order));
        $userOrderDetailResponse->assertStatus(200);
        $userOrderDetailResponse->assertSee('Shade:');
        $userOrderDetailResponse->assertSee('Cherry Pop');
    }
}
