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

        if ($request->filled('fraud_risk')) {
            $query->where('fraud_risk_level', $request->fraud_risk);
        }

        $orders = $query->orderByDesc('created_at')->get();

        // Evaluate fraud analysis on loaded orders if missing
        foreach ($orders as $ord) {
            if (is_null($ord->fraud_risk_score)) {
                app(\App\Services\FraudDetectionService::class)->evaluateAndSave($ord);
            }
        }

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
        $fraudAnalysis = $order->fraud_analysis;

        return view('admin.orders.show', compact('order', 'allowedTransitions', 'allStatuses', 'deliveryCharges', 'fraudAnalysis'));
    }

    public function changeStatus(Request $request, Order $order): RedirectResponse
    {
        $validStatuses = array_keys(OrderService::allStatuses());

        $request->validate([
            'status'              => ['required', 'string', 'in:' . implode(',', $validStatuses)],
            'delivery_option'     => ['nullable', 'string', 'max:255'],
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $changedBy = 'admin:' . auth('admin')->id() . ':' . auth('admin')->user()->name;

        $note = null;
        if ($request->filled('delivery_option')) {
            $note = 'Delivery Option: ' . $request->delivery_option;
            $order->update(['shipping_title' => $request->delivery_option]);
        }

        if ($request->status === 'cancelled') {
            $reason = $request->input('cancellation_reason') ?? 'Order cancelled by store administrator.';
            $order->update([
                'cancellation_reason' => $reason,
                'cancelled_by'        => $changedBy,
                'cancelled_at'        => now(),
            ]);
            $note = ($note ? $note . '. ' : '') . 'Notice: ' . $reason;
        }

        $this->orderService->changeStatus($order, $request->status, $changedBy, $note);

        return back()->with('success', 'Order status updated to ' . ucfirst(str_replace('-', ' ', $request->status)) . '.');
    }

    public function cancelWithNotice(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $changedBy = 'admin:' . auth('admin')->id() . ':' . auth('admin')->user()->name;

        $order->update([
            'cancellation_reason' => $data['cancellation_reason'],
            'cancelled_by'        => $changedBy,
            'cancelled_at'        => now(),
        ]);

        $this->orderService->changeStatus(
            $order,
            'cancelled',
            $changedBy,
            'Cancelled by Admin with customer notice: ' . $data['cancellation_reason']
        );

        if ($order->user_id) {
            \App\Services\NotificationService::notifyUser(
                $order->user_id,
                'order_status',
                'Order Cancelled by Store',
                "Your order #{$order->order_number} has been cancelled by store administration. Notice: {$data['cancellation_reason']}",
                route('user.orders.show', $order),
                'status'
            );
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', "Order #{$order->order_number} has been cancelled and customer notified with notice.");
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
