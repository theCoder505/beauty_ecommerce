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
        $query = Order::with(['user', 'payment', 'items.product'])
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

        $orders = $query->orderByDesc('created_at')->get();

        $statuses = array_keys(OrderService::allStatuses());

        $statusCounts = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        $totalOrdersCount = Order::count();

        return view('admin.orders.index', compact('orders', 'statuses', 'statusCounts', 'totalOrdersCount'));
    }

    public function show(Order $order): View
    {
        $order->load([
            'user.addresses',
            'items.product.productImages',
            'payment',
            'statusHistories' => fn ($q) => $q->orderBy('created_at', 'asc'),
        ]);

        $allowedTransitions = $this->orderService->allowedTransitions($order->status);
        $allStatuses = OrderService::allStatuses();
        $deliveryCharges = app(\App\Services\SettingService::class)->getDeliveryCharges();

        return view('admin.orders.show', compact('order', 'allowedTransitions', 'allStatuses', 'deliveryCharges'));
    }

    public function changeStatus(Request $request, Order $order): RedirectResponse
    {
        $validStatuses = array_keys(OrderService::allStatuses());

        $request->validate([
            'status'          => ['required', 'string', 'in:' . implode(',', $validStatuses)],
            'delivery_option' => ['nullable', 'string', 'max:255'],
        ]);

        $changedBy = 'admin:' . auth('admin')->id() . ':' . auth('admin')->user()->name;

        $note = null;
        if ($request->filled('delivery_option')) {
            $note = 'Delivery Option: ' . $request->delivery_option;
            $order->update(['shipping_title' => $request->delivery_option]);
        }

        $this->orderService->changeStatus($order, $request->status, $changedBy, $note);

        return back()->with('success', 'Order status updated to ' . ucfirst(str_replace('-', ' ', $request->status)) . '.');
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
