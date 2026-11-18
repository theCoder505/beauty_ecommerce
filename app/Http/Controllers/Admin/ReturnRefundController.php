<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\NotificationService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnRefundController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): View
    {
        $query = Order::with(['user', 'payment', 'items.product'])
            ->where(function ($q) {
                $q->whereNotNull('return_status')
                  ->orWhereIn('status', ['refunded', 'returned']);
            });

        // Filter by tab
        $tab = $request->get('tab', 'all');
        if ($tab === 'requested') {
            $query->where('return_status', 'requested');
        } elseif ($tab === 'refunded') {
            $query->where('status', 'refunded');
        } elseif ($tab === 'returned') {
            $query->where('status', 'returned');
        } elseif ($tab === 'rejected') {
            $query->where('return_status', 'rejected');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('return_reason', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        // Calculate KPI metrics
        $refundedAmount = (float) Order::where('status', 'refunded')->sum('refund_amount');
        if ($refundedAmount <= 0) {
            $refundedAmount = (float) Order::where('status', 'refunded')->sum('total');
        }

        $stats = [
            'total_refunded_amount' => $refundedAmount,
            'refunded_count'        => Order::where('status', 'refunded')->count(),
            'returned_count'        => Order::where('status', 'returned')->count(),
            'pending_count'         => Order::where('return_status', 'requested')->count(),
            'rejected_count'        => Order::where('return_status', 'rejected')->count(),
            'total_items_refunded'  => (int) \App\Models\OrderItem::whereHas('order', fn($q) => $q->where('status', 'refunded'))->sum('quantity'),
        ];

        return view('admin.returns.index', compact('orders', 'stats', 'tab'));
    }

    public function approve(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'action_type'   => ['required', 'string', 'in:refund,return'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
            'admin_note'    => ['nullable', 'string', 'max:1000'],
        ]);

        $newStatus = $data['action_type'] === 'refund' ? 'refunded' : 'returned';
        $refundAmount = $data['refund_amount'] !== null && $data['refund_amount'] !== ''
            ? (float) $data['refund_amount']
            : (float) $order->total;

        $adminNote = $data['admin_note'] ?? null;
        $adminIdentifier = 'admin:' . auth('admin')->id() . ':' . auth('admin')->user()->name;

        $order->update([
            'return_status'      => 'approved',
            'return_actioned_at' => now(),
            'return_admin_note'  => $adminNote,
            'refund_amount'      => $newStatus === 'refunded' ? $refundAmount : 0,
        ]);

        $this->orderService->changeStatus(
            $order,
            $newStatus,
            $adminIdentifier,
            "Accepted {$data['action_type']}. " . ($adminNote ? "Note: {$adminNote}" : '')
        );

        return back()->with('success', "Order #{$order->order_number} has been set to " . ucfirst($newStatus) . ' successfully.');
    }

    public function reject(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'admin_note' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $adminIdentifier = 'admin:' . auth('admin')->id() . ':' . auth('admin')->user()->name;

        $order->update([
            'return_status'      => 'rejected',
            'return_actioned_at' => now(),
            'return_admin_note'  => $data['admin_note'],
        ]);

        OrderStatusHistory::create([
            'order_id'   => $order->id,
            'status'     => $order->status,
            'changed_by' => $adminIdentifier,
            'note'       => 'Return/Refund request declined. Reason: ' . $data['admin_note'],
        ]);

        if ($order->user_id) {
            NotificationService::notifyUser(
                $order->user_id,
                'order_status',
                'Return / Refund Request Update',
                "Your return/refund request for Order #{$order->order_number} was declined. Reason: {$data['admin_note']}",
                route('user.orders.show', $order),
                'status'
            );
        }

        return back()->with('success', "Return/Refund request for Order #{$order->order_number} has been declined.");
    }
}
