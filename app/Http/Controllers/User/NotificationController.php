<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View|JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        if (!$user) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['unread_count' => 0, 'notifications' => []], 401);
            }
            return redirect()->route('login');
        }

        // Return JSON for header dropdown
        if ($request->wantsJson() || $request->ajax()) {
            $notifications = $user->notifications()->limit(15)->get()->map(function ($n) {
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
                'unread_count'  => $user->unreadNotifications()->count(),
                'notifications' => $notifications,
            ]);
        }

        // Full Page for User Notifications
        $query = $user->notifications();

        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->status === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        $notifications = $query->paginate(15)->withQueryString();
        $unreadCount = $user->unreadNotifications()->count();
        $totalCount = $user->notifications()->count();

        return view('users.pages.notifications', compact('notifications', 'unreadCount', 'totalCount'));
    }

    public function markAsRead(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        $notification = $user?->notifications()->where('id', $id)->first();

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
        $user = auth()->user();
        $user?->unreadNotifications->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        $notification = $user?->notifications()->where('id', $id)->first();

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
        $user = auth()->user();
        $user?->notifications()->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'All notifications cleared.']);
        }

        return back()->with('success', 'All notifications cleared.');
    }
}
