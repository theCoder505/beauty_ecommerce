<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * JSON feed for top header notification bell & dropdown.
     */
    public function feed(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['unread_count' => 0, 'notifications' => []], 401);
        }

        $notifications = $user->notifications()->limit(15)->get()->map(function ($n) {
            return [
                'id'         => $n->id,
                'read'       => !is_null($n->read_at),
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

    public function index(Request $request): View|JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        if (!$user) {
            if ($request->wantsJson() || $request->ajax() || $request->query('format') === 'json') {
                return response()->json(['unread_count' => 0, 'notifications' => []], 401);
            }
            return redirect()->route('login');
        }

        // Return JSON for header dropdown
        if ($request->wantsJson() || $request->ajax() || $request->query('format') === 'json') {
            return $this->feed($request);
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

        $notifications = $query->latest()->paginate(15)->withQueryString();
        $unreadCount = $user->unreadNotifications()->count();
        $readCount = $user->readNotifications()->count();
        $totalCount = $user->notifications()->count();

        return view('users.pages.notifications', compact('notifications', 'unreadCount', 'readCount', 'totalCount'));
    }

    public function markAsRead(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        $notification = $user?->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            $url = $notification->data['url'] ?? null;

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'redirect'     => $url,
                    'unread_count' => $user->unreadNotifications()->count(),
                ]);
            }

            // Redirect only if specifically requested (e.g. "View Details" button click)
            if ($request->boolean('redirect') && $url) {
                return redirect()->to($url);
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'unread_count' => $user?->unreadNotifications()->count() ?? 0,
            ]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAsUnread(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        $notification = $user?->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->update(['read_at' => null]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'unread_count' => $user?->unreadNotifications()->count() ?? 0,
            ]);
        }

        return back()->with('success', 'Notification marked as unread.');
    }

    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        $user?->unreadNotifications->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'message'      => 'All notifications marked as read.',
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    public function markAllAsUnread(Request $request): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        $user?->readNotifications()->update(['read_at' => null]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'message'      => 'All notifications marked as unread.',
                'unread_count' => $user?->unreadNotifications()->count() ?? 0,
            ]);
        }

        return back()->with('success', 'All notifications marked as unread.');
    }

    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        $notification = $user?->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->delete();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Notification deleted.',
                'unread_count' => $user?->unreadNotifications()->count() ?? 0,
            ]);
        }

        return back()->with('success', 'Notification deleted successfully.');
    }

    public function clear(Request $request): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        $user?->notifications()->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'message'      => 'All notifications cleared.',
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'All notifications cleared.');
    }
}
