<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['unread_count' => 0, 'notifications' => []], 401);
            }
            return redirect()->route('admin.login');
        }

        // Return JSON for header dropdown
        if ($request->wantsJson() || $request->ajax()) {
            $notifications = $admin->notifications()->limit(15)->get()->map(function ($n) {
                return [
                    'id'         => $n->id,
                    'read'       => $n->read(),
                    'type'       => $n->data['type'] ?? 'general',
                    'title'      => $n->data['title'] ?? 'Notification',
                    'message'    => $n->data['message'] ?? '',
                    'url'        => $n->data['url'] ?? null,
                    'icon'       => $n->data['icon'] ?? 'bell',
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            });

            return response()->json([
                'unread_count'  => $admin->unreadNotifications()->count(),
                'notifications' => $notifications,
            ]);
        }

        // Full Page for Admin Notifications
        $query = $admin->notifications();

        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->status === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        if ($request->filled('type')) {
            $type = $request->type;
            $query->where('data', 'like', "%\"type\":\"%{$type}%\"%");
        }

        $notifications = $query->paginate(20)->withQueryString();
        $unreadCount = $admin->unreadNotifications()->count();
        $totalCount = $admin->notifications()->count();

        return view('admin.notifications.index', compact('notifications', 'unreadCount', 'totalCount'));
    }

    public function markAsRead(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $admin = auth('admin')->user();
        $notification = $admin?->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            $url = $notification->data['url'] ?? null;
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'redirect' => $url]);
            }
            if ($url) {
                return redirect()->to($url);
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $admin = auth('admin')->user();
        $admin?->unreadNotifications->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $admin = auth('admin')->user();
        $notification = $admin?->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->delete();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Notification deleted.']);
        }

        return back()->with('success', 'Notification deleted successfully.');
    }

    public function clear(Request $request): JsonResponse|RedirectResponse
    {
        $admin = auth('admin')->user();
        $admin?->notifications()->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'All notifications cleared.']);
        }

        return back()->with('success', 'All notifications cleared.');
    }
}
