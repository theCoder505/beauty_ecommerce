<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification to all administrators.
     */
    public static function notifyAdmins(
        string $type,
        string $title,
        string $message,
        ?string $url = null,
        ?string $icon = 'bell',
        array $extra = []
    ): void {
        try {
            $admins = Admin::all();
            if ($admins->isEmpty()) {
                return;
            }

            $notification = new SystemNotification($type, $title, $message, $url, $icon, $extra);
            foreach ($admins as $admin) {
                $admin->notify($notification);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed sending admin notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to a specific user.
     */
    public static function notifyUser(
        User|int $user,
        string $type,
        string $title,
        string $message,
        ?string $url = null,
        ?string $icon = 'bell',
        array $extra = []
    ): void {
        try {
            if (is_numeric($user)) {
                $user = User::find($user);
            }

            if (!$user) {
                return;
            }

            $user->notify(new SystemNotification($type, $title, $message, $url, $icon, $extra));
        } catch (\Throwable $e) {
            Log::warning('Failed sending user notification: ' . $e->getMessage());
        }
    }
}
