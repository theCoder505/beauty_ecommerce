<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PromoCode;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService     $cartService,
        private readonly CheckoutService $checkoutService,
        private readonly SettingService  $settingService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $isBuyNow = $request->boolean('buy_now') && $request->filled('product_id');

        if ($isBuyNow) {
            $product = Product::with(['images', 'options'])->findOrFail($request->integer('product_id'));
            $quantity = max(1, min((int) $request->integer('quantity', 1), max(1, (int)$product->stock)));

            if ($product->stock < 1) {
                return redirect()->route('products.show', $product->slug)
                    ->with('error', 'This product is currently out of stock.');
            }

            $selectedSize  = $request->query('size');
            $selectedColor = $request->query('color');
            $selectedShade = $request->query('shade');

            $parts = [];
            if (!empty($selectedSize)) $parts[] = 'Size: ' . $selectedSize;
            if (!empty($selectedColor)) $parts[] = 'Color: ' . $selectedColor;
            if (!empty($selectedShade)) $parts[] = 'Shade: ' . $selectedShade;
            $optionsSummary = implode(' · ', $parts);

            $lineTotal = $product->current_price * $quantity;
            $buyNowItem = (object) [
                'id'              => null,
                'product_id'      => $product->id,
                'product'         => $product,
                'quantity'        => $quantity,
                'selected_size'   => $selectedSize,
                'selected_color'  => $selectedColor,
                'selected_shade'  => $selectedShade,
                'options_summary' => $optionsSummary,
                'line_total'      => $lineTotal,
            ];

            $items = collect([$buyNowItem]);
            $total = $lineTotal;
        } else {
            $items = $this->cartService->items();

            if ($items->isEmpty()) {
                return redirect()->route('cart.index')
                    ->with('error', 'Your cart is empty. Add some products before checking out.');
            }

            $total = $this->cartService->total();
        }

        $paymentMethods = $this->getEnabledPaymentMethods();
        $user           = Auth::user();
        if ($user) {
            $user->load('addresses');
        }
        $addresses      = $user ? $user->addresses : collect();
        $primaryAddress = $addresses->firstWhere('is_primary', true) ?? $addresses->first();
        $cartItems      = $items;
        $cartTotal      = $total;
        $cartCount      = $this->cartService->count();

        $deliveryCharges = $this->settingService->getDeliveryCharges();
        $defaultDelivery = collect($deliveryCharges)->firstWhere('is_default', true) ?? ($deliveryCharges[0] ?? ['id' => 'inside_dhaka', 'title' => 'Inside Dhaka', 'amount' => 60]);

        return view('users.pages.checkout', compact(
            'items',
            'cartItems',
            'paymentMethods',
            'user',
            'total',
            'cartTotal',
            'isBuyNow',
            'cartCount',
            'addresses',
            'primaryAddress',
            'deliveryCharges',
            'defaultDelivery'
        ));
    }

    public function applyCoupon(Request $request): \Illuminate\Http\JsonResponse
    {
        $code = strtoupper(trim((string) $request->input('code', '')));
        if (empty($code)) {
            return response()->json(['success' => false, 'message' => 'Please enter a promo code.'], 422);
        }

        $promo = PromoCode::where('code', $code)->first();
        if (!$promo) {
            return response()->json(['success' => false, 'message' => 'Invalid promo code.'], 422);
        }

        $isBuyNow = $request->boolean('buy_now') && $request->filled('product_id');
        if ($isBuyNow) {
            $product = Product::findOrFail($request->integer('product_id'));
            $quantity = max(1, (int) $request->integer('quantity', 1));
            $items = collect([(object) [
                'product_id'    => $product->id,
                'product'       => $product,
                'quantity'      => $quantity,
                'current_price' => $product->current_price,
            ]]);
            $subtotal = $product->current_price * $quantity;
        } else {
            $items = $this->cartService->items();
            if ($items->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Your cart is empty.'], 422);
            }
            $subtotal = $this->cartService->total();
        }

        [$valid, $discount, $message] = $promo->computeDiscount($items, $subtotal);

        if (!$valid) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        $currency = $this->settingService->get('currency_symbol', '৳');

        return response()->json([
            'success'            => true,
            'code'               => $promo->code,
            'discount'           => (float) $discount,
            'formatted_discount' => $currency . number_format($discount),
            'message'            => $message,
            'type'               => $promo->type,
            'value'              => (float) $promo->value,
            'new_subtotal'       => max(0, $subtotal - $discount),
        ]);
    }

    public function place(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'address_id'         => ['required', 'integer'],
            'delivery_charge_id' => ['required', 'string'],
            'payment_method'     => ['required', 'string'],
            'transaction_id'     => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'buy_now'            => ['nullable', 'boolean'],
            'product_id'         => ['nullable', 'integer', 'exists:products,id'],
            'quantity'           => ['nullable', 'integer', 'min:1'],
            'coupon_code'        => ['nullable', 'string', 'max:50'],
            'size'               => ['nullable', 'string', 'max:100'],
            'color'              => ['nullable', 'string', 'max:100'],
            'shade'              => ['nullable', 'string', 'max:100'],
        ]);

        // Securely retrieve the user's saved address
        $userAddress = $user->addresses()->find($data['address_id'])
            ?? $user->primaryAddress
            ?? $user->addresses()->first();

        if (!$userAddress) {
            $msg = 'Please add a delivery address in your profile before checking out.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                    'errors'  => ['address_id' => [$msg]],
                ], 422);
            }
            return back()->withErrors(['address_id' => $msg]);
        }

        // Auto-fill all shipping snapshot fields securely from verified user & address models
        $data['shipping_name']      = $userAddress->recipient_name ?: $user->name;
        $data['shipping_email']     = $user->email;
        $data['shipping_phone']     = $userAddress->phone ?: ($user->phone ?: 'N/A');
        $data['shipping_address']   = $userAddress->address_line;
        $data['shipping_city']      = $userAddress->city ?: 'Dhaka';
        $data['shipping_state']     = $userAddress->state;
        $data['shipping_zip']       = $userAddress->postal_code;
        $data['shipping_country']   = $userAddress->country ?: 'BD';
        $data['shipping_latitude']  = $userAddress->latitude;
        $data['shipping_longitude'] = $userAddress->longitude;

        // Check transaction ID if manual / bank transfer
        if (in_array($data['payment_method'], ['manual', 'bank_transfer']) && empty(trim((string) ($data['transaction_id'] ?? '')))) {
            $msg = 'Please enter the Transaction ID / Reference Number for your mobile banking or bank transfer.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                    'errors'  => ['transaction_id' => [$msg]],
                ], 422);
            }
            return back()->withErrors(['transaction_id' => $msg]);
        }

        // Resolve Delivery Charge chosen by user
        $deliveryCharges = $this->settingService->getDeliveryCharges();
        $selectedCharge  = collect($deliveryCharges)->firstWhere('id', $data['delivery_charge_id'])
            ?? collect($deliveryCharges)->firstWhere('title', $data['delivery_charge_id'])
            ?? ($deliveryCharges[0] ?? ['title' => 'Standard Delivery', 'amount' => 60]);

        $data['shipping_cost']  = (float) $selectedCharge['amount'];
        $data['shipping_title'] = $selectedCharge['title'];

        $enabledMethods = $this->getEnabledPaymentMethods();

        if (! in_array($data['payment_method'], $enabledMethods)) {
            $msg = 'Selected payment method is not available.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                    'errors'  => ['payment_method' => [$msg]],
                ], 422);
            }
            return back()->withErrors(['payment_method' => $msg]);
        }

        $isBuyNow = !empty($data['buy_now']) && !empty($data['product_id']);
        $customItems = null;

        if ($isBuyNow) {
            $product = Product::with('options')->findOrFail($data['product_id']);
            $quantity = max(1, (int) ($data['quantity'] ?? 1));

            // Enforce required option selections if product has them
            if ($product->hasSizes() && empty($data['size'])) {
                $msg = 'Please select a size.';
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg, 'errors' => ['size' => [$msg]]], 422);
                }
                return back()->withErrors(['size' => $msg]);
            }

            if ($product->hasColors() && empty($data['color'])) {
                $msg = 'Please select a color.';
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg, 'errors' => ['color' => [$msg]]], 422);
                }
                return back()->withErrors(['color' => $msg]);
            }

            if ($product->hasShades() && empty($data['shade'])) {
                $msg = 'Please select a shade.';
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg, 'errors' => ['shade' => [$msg]]], 422);
                }
                return back()->withErrors(['shade' => $msg]);
            }

            // Validate availability of selected options
            if (!empty($data['size'])) {
                $opt = $product->sizes()->where('name', $data['size'])->first();
                if ($opt && !$opt->is_available) {
                    $msg = "Size '{$data['size']}' is currently unavailable.";
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => $msg, 'errors' => ['size' => [$msg]]], 422);
                    }
                    return back()->withErrors(['size' => $msg]);
                }
            }

            if (!empty($data['color'])) {
                $opt = $product->colors()->where('name', $data['color'])->first();
                if ($opt && !$opt->is_available) {
                    $msg = "Color '{$data['color']}' is currently unavailable.";
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => $msg, 'errors' => ['color' => [$msg]]], 422);
                    }
                    return back()->withErrors(['color' => $msg]);
                }
            }

            if (!empty($data['shade'])) {
                $opt = $product->shades()->where('name', $data['shade'])->first();
                if ($opt && !$opt->is_available) {
                    $msg = "Shade '{$data['shade']}' is currently unavailable.";
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => $msg, 'errors' => ['shade' => [$msg]]], 422);
                    }
                    return back()->withErrors(['shade' => $msg]);
                }
            }

            $customItems = collect([
                (object) [
                    'id'             => null,
                    'product_id'     => $product->id,
                    'product'        => $product,
                    'quantity'       => $quantity,
                    'selected_size'  => $data['size'] ?? null,
                    'selected_color' => $data['color'] ?? null,
                    'selected_shade' => $data['shade'] ?? null,
                    'line_total'     => $product->current_price * $quantity,
                    'current_price'  => $product->current_price,
                ]
            ]);
        }

        // Validate and apply promo code on server side
        $discount = 0;
        $appliedCoupon = null;
        if (!empty($data['coupon_code'])) {
            $promo = PromoCode::where('code', strtoupper(trim($data['coupon_code'])))->first();
            if ($promo) {
                $evalItems = $customItems ?? $this->cartService->items();
                $evalSubtotal = $isBuyNow ? $customItems->sum('line_total') : $this->cartService->total();
                [$valid, $calcDiscount] = $promo->computeDiscount($evalItems, $evalSubtotal);
                if ($valid) {
                    $discount = (float) $calcDiscount;
                    $appliedCoupon = $promo->code;
                    $promo->incrementUsage();
                }
            }
        }
        $data['discount']    = $discount;
        $data['coupon_code'] = $appliedCoupon;

        // Build idempotency key to prevent duplicate orders
        $idempotencyKey = session()->get('checkout_idempotency_key')
            ?? session()->put('checkout_idempotency_key', md5(Auth::id() . now()->toDateString() . json_encode($data)));

        $data['idempotency_key'] = session('checkout_idempotency_key', md5(Auth::id() . now()->toDateString()));

        if ($data['payment_method'] === 'stripe') {
            return $this->handleStripeCheckout($data, $customItems, $isBuyNow);
        }

        // COD / Manual bank transfer / other methods
        try {
            $order = $this->checkoutService->placeOrder($data, $data['payment_method'], $customItems, !$isBuyNow);
        } catch (\RuntimeException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors'  => ['general' => [$e->getMessage()]],
                ], 422);
            }
            return back()->withErrors(['general' => $e->getMessage()]);
        }

        try {
            $order->load('items');
            if (!empty($order->shipping_email)) {
                Mail::to($order->shipping_email)->send(new OrderConfirmationMail($order));
            }
        } catch (\Throwable $e) {
            Log::warning('Order confirmation email failed: ' . $e->getMessage());
        }

        session()->forget('checkout_idempotency_key');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'redirect_url' => route('checkout.success', $order),
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'message'      => 'Order placed successfully!',
            ]);
        }

        return redirect()->route('checkout.success', $order)
            ->with('success', 'Order placed successfully!');
    }

    public function success(Order $order): View|RedirectResponse
    {
        if ($order->user_id && Auth::check() && $order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items.product', 'payment', 'statusHistories']);

        return view('users.pages.order-success', compact('order'));
    }

    public function stripeSuccess(Request $request): RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return redirect()->route('checkout.index')
                ->with('error', 'Invalid Stripe session.');
        }

        try {
            Stripe::setApiKey($this->settingService->get('stripe_secret_key'));
            $stripeSession = StripeSession::retrieve($sessionId);

            $order = Order::whereHas('payment', function ($q) use ($sessionId) {
                $q->where('transaction_id', $sessionId);
            })->with('payment')->firstOrFail();

            if ($order->user_id !== Auth::id()) {
                abort(403);
            }

            if ($stripeSession->payment_status === 'paid') {
                $order->payment()->update(['status' => 'verified']);
                $order->update(['status' => 'confirmed']);

                try {
                    $order->load('items');
                    if (!empty($order->shipping_email)) {
                        Mail::to($order->shipping_email)->send(new OrderConfirmationMail($order));
                    }
                } catch (\Throwable $e) {
                    Log::warning('Stripe order confirmation email failed: ' . $e->getMessage());
                }
            }

            session()->forget('checkout_idempotency_key');

            return redirect()->route('checkout.success', $order)
                ->with('success', 'Payment confirmed! Your order has been placed.');
        } catch (\Exception $e) {
            return redirect()->route('checkout.index')
                ->with('error', 'Could not verify your payment. Please contact support.');
        }
    }

    public function stripeCancel(): RedirectResponse
    {
        return redirect()->route('checkout.index')
            ->with('error', 'Payment was cancelled. Your cart items are still saved.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function getEnabledPaymentMethods(): array
    {
        $methods = [];

        $cod = $this->settingService->get('cod_enabled');
        if ($cod === null || $cod == '1' || $this->settingService->get('payment_cod_enabled')) {
            $methods[] = 'cod';
        }

        $manual = $this->settingService->get('manual_payment_enabled');
        if ($manual === null || $manual == '1' || $this->settingService->get('payment_bank_transfer_enabled')) {
            $methods[] = 'manual';
            $methods[] = 'bank_transfer';
        }

        $stripe = $this->settingService->get('stripe_enabled');
        if ($stripe == '1' || $this->settingService->get('payment_stripe_enabled')) {
            $methods[] = 'stripe';
        }

        return $methods;
    }

    private function handleStripeCheckout(array $data, ?\Illuminate\Support\Collection $customItems = null, bool $isBuyNow = false): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        Stripe::setApiKey($this->settingService->get('stripe_secret_key'));

        $items     = $customItems ?? $this->cartService->items();
        $lineItems = [];

        foreach ($items as $item) {
            $product     = $item->product;
            $unitAmount  = (int) round(($product->current_price ?? $product->price) * 100);

            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'usd',
                    'unit_amount'  => $unitAmount,
                    'product_data' => [
                        'name' => $product->name,
                    ],
                ],
                'quantity' => $item->quantity,
            ];
        }

        // Place a pending order first so we can track by idempotency key
        $order = $this->checkoutService->placeOrder($data, 'stripe', $customItems, !$isBuyNow);

        $stripeSession = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => route('checkout.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => route('checkout.stripe.cancel'),
            'metadata'             => [
                'order_id' => $order->id,
                'user_id'  => Auth::id(),
            ],
        ]);

        // Store Stripe session ID as transaction_id on the payment record
        $order->payment()->update(['transaction_id' => $stripeSession->id]);

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success'      => true,
                'redirect_url' => $stripeSession->url,
                'order_id'     => $order->id,
            ]);
        }

        return redirect()->away($stripeSession->url);
    }
}
