<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
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
            $product = Product::with('images')->findOrFail($request->integer('product_id'));
            $quantity = max(1, min((int) $request->integer('quantity', 1), max(1, (int)$product->stock)));

            if ($product->stock < 1) {
                return redirect()->route('products.show', $product->slug)
                    ->with('error', 'This product is currently out of stock.');
            }

            $lineTotal = $product->current_price * $quantity;
            $buyNowItem = (object) [
                'id'         => null,
                'product_id' => $product->id,
                'product'    => $product,
                'quantity'   => $quantity,
                'line_total' => $lineTotal,
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
        $cartItems      = $items;
        $cartTotal      = $total;
        $cartCount      = $this->cartService->count();

        return view('users.pages.checkout', compact('items', 'cartItems', 'paymentMethods', 'user', 'total', 'cartTotal', 'isBuyNow', 'cartCount'));
    }

    public function place(Request $request): RedirectResponse|\Illuminate\Http\Response
    {
        $data = $request->validate([
            'shipping_name'    => ['required', 'string', 'max:255'],
            'shipping_email'   => ['required', 'email', 'max:255'],
            'shipping_phone'   => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'shipping_city'    => ['required', 'string', 'max:100'],
            'shipping_state'   => ['nullable', 'string', 'max:100'],
            'shipping_zip'     => ['nullable', 'string', 'max:20'],
            'shipping_country' => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'payment_method'   => ['required', 'string'],
            'transaction_id'   => ['nullable', 'string', 'max:255'],
            'buy_now'          => ['nullable', 'boolean'],
            'product_id'       => ['nullable', 'integer', 'exists:products,id'],
            'quantity'         => ['nullable', 'integer', 'min:1'],
        ]);

        $enabledMethods = $this->getEnabledPaymentMethods();

        if (! in_array($data['payment_method'], $enabledMethods)) {
            return back()->withErrors(['payment_method' => 'Selected payment method is not available.']);
        }

        $isBuyNow = !empty($data['buy_now']) && !empty($data['product_id']);
        $customItems = null;

        if ($isBuyNow) {
            $product = Product::findOrFail($data['product_id']);
            $quantity = max(1, (int) ($data['quantity'] ?? 1));
            $customItems = collect([
                (object) [
                    'id'         => null,
                    'product_id' => $product->id,
                    'product'    => $product,
                    'quantity'   => $quantity,
                    'line_total' => $product->current_price * $quantity,
                ]
            ]);
        }

        // Build idempotency key to prevent duplicate orders
        $idempotencyKey = session()->get('checkout_idempotency_key')
            ?? session()->put('checkout_idempotency_key', md5(Auth::id() . now()->toDateString() . json_encode($data)));

        $data['idempotency_key'] = session('checkout_idempotency_key', md5(Auth::id() . now()->toDateString()));

        if ($data['payment_method'] === 'stripe') {
            return $this->handleStripeCheckout($data, $customItems, $isBuyNow);
        }

        // COD / Manual bank transfer / other methods
        $order = $this->checkoutService->placeOrder($data, $data['payment_method'], $customItems, !$isBuyNow);

        try {
            $order->load('items');
            if (!empty($order->shipping_email)) {
                Mail::to($order->shipping_email)->send(new OrderConfirmationMail($order));
            }
        } catch (\Throwable $e) {
            Log::warning('Order confirmation email failed: ' . $e->getMessage());
        }

        session()->forget('checkout_idempotency_key');

        return redirect()->route('checkout.success', $order->id)
            ->with('success', 'Order placed successfully!');
    }

    public function success(Order $order): View|RedirectResponse
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items.product', 'payment']);

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

            return redirect()->route('checkout.success', $order->id)
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

    private function getEnabledPaymentMethods(): array
    {
        $methods = [];

        if ($this->settingService->get('payment_cod_enabled')) {
            $methods[] = 'cod';
        }
        if ($this->settingService->get('payment_stripe_enabled')) {
            $methods[] = 'stripe';
        }
        if ($this->settingService->get('payment_bank_transfer_enabled')) {
            $methods[] = 'bank_transfer';
        }

        return $methods;
    }

    private function handleStripeCheckout(array $data, ?\Illuminate\Support\Collection $customItems = null, bool $isBuyNow = false): RedirectResponse
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

        return redirect()->away($stripeSession->url);
    }
}
