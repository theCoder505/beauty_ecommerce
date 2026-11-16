<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::withCount(['orders', 'reviews'])
            ->withSum('orders as total_spent', 'total');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'blocked') {
                $query->where('is_blocked', true);
            } elseif ($request->status === 'active') {
                $query->where('is_blocked', false);
            }
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total'   => User::count(),
            'active'  => User::where('is_blocked', false)->count(),
            'blocked' => User::where('is_blocked', true)->count(),
            'new_this_month' => User::whereMonth('created_at', now()->month)
                                    ->whereYear('created_at', now()->year)
                                    ->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function show(User $user): View
    {
        $user->load([
            'addresses',
            'orders' => fn ($q) => $q->latest()->limit(10),
            'reviews.product',
        ]);

        $totalSpent = $user->orders()->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered', 'completed'])->sum('total');

        return view('admin.users.show', compact('user', 'totalSpent'));
    }

    public function toggleBlock(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $isBlocked = !$user->is_blocked;
        $reason = $request->input('reason', $isBlocked ? 'Account blocked by administrator.' : null);

        $user->update([
            'is_blocked'     => $isBlocked,
            'blocked_at'     => $isBlocked ? now() : null,
            'blocked_reason' => $isBlocked ? $reason : null,
        ]);

        $statusText = $isBlocked ? 'blocked' : 'unblocked';
        $message = "User {$user->name} has been {$statusText} successfully.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'is_blocked' => $isBlocked,
                'message'    => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    public function destroy(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $name = $user->name;
        $user->delete();

        $message = "User {$name} was deleted successfully.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', $message);
    }
}
