<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): View
    {
        $query = Order::with(['user', 'payment'])
            ->withCount('items');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('payment_method')) {
            $query->whereHas('payment', fn ($q) => $q->where('method', $request->payment_method));
        }

        $orders = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $statuses = [
            'pending', 'confirmed', 'processing', 'shipped',
            'out-for-delivery', 'delivered', 'completed', 'cancelled', 'returned', 'refunded',
        ];

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    public function show(Order $order): View
    {
        $order->load([
            'user',
            'items.product.productImages',
            'payment',
            'statusHistories' => fn ($q) => $q->orderByDesc('created_at'),
        ]);

        $allowedTransitions = $this->orderService->allowedTransitions($order->status);

        return view('admin.orders.show', compact('order', 'allowedTransitions'));
    }

    public function changeStatus(Request $request, Order $order): RedirectResponse
    {
        $allowed = $this->orderService->allowedTransitions($order->status);

        $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', $allowed)],
            'note'   => ['nullable', 'string', 'max:500'],
        ]);

        $changedBy = 'admin:' . auth('admin')->id() . ':' . auth('admin')->user()->name;

        $this->orderService->changeStatus($order, $request->status, $changedBy, $request->note);

        return back()->with('success', 'Order status updated to ' . ucfirst($request->status) . '.');
    }

    public function verifyPayment(Request $request, Order $order): RedirectResponse
    {
        $payment = $order->payment;

        abort_if(!$payment, 404, 'No payment record found for this order.');

        $payment->update([
            'status'      => 'verified',
            'verified_by' => auth('admin')->id(),
            'verified_at' => now(),
        ]);

        // Auto-confirm COD/manual orders that were still pending
        if (in_array($payment->method, ['cod', 'manual', 'bank_transfer'])
            && $order->status === 'pending') {
            $changedBy = 'admin:' . auth('admin')->id() . ':' . auth('admin')->user()->name;
            $this->orderService->changeStatus($order, 'confirmed', $changedBy, 'Payment verified — order confirmed.');
        }

        return back()->with('success', 'Payment verified successfully.');
    }

    public function rejectPayment(Request $request, Order $order): RedirectResponse
    {
        $payment = $order->payment;

        abort_if(!$payment, 404, 'No payment record found for this order.');

        $request->validate([
            'admin_note' => ['required', 'string', 'max:500'],
        ]);

        $payment->update([
            'status'     => 'rejected',
            'admin_note' => $request->admin_note,
        ]);

        return back()->with('success', 'Payment rejected and note saved.');
    }
}
