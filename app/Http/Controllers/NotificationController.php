<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\School;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Determine the currently authenticated user across all guards.
     */
    private function getAuthUser()
    {
        if (Auth::guard('admin')->check()) {
            return Auth::guard('admin')->user();
        }
        if (Auth::guard('instructor')->check()) {
            return Auth::guard('instructor')->user();
        }
        if (Auth::guard('student')->check()) {
            return Auth::guard('student')->user();
        }
        return null;
    }

    /**
     * Get notifications for the notification bell dropdown (JSON).
     */
    public function index(School $school)
    {
        $user = $this->getAuthUser();
        if (!$user || $user->school_id !== $school->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notifications = Notification::forSchool($school->id)
            ->forNotifiable($user)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($notification) {
            return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
            'icon' => $notification->getIconEmoji(),
            'action_url' => $notification->action_url,
            'is_read' => $notification->isRead(),
            'time_ago' => $notification->getTimeAgo(),
            'created_at' => $notification->created_at->toISOString(),
            ];
        });

        $unreadCount = Notification::forSchool($school->id)
            ->forNotifiable($user)
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(School $school, Notification $notification)
    {
        $user = $this->getAuthUser();
        if (!$user || $user->school_id !== $school->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verify the notification belongs to this user
        if ($notification->notifiable_type !== get_class($user) || $notification->notifiable_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }
        catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to mark notification as read', [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to process request'], 500);
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(School $school)
    {
        $user = $this->getAuthUser();
        if (!$user || $user->school_id !== $school->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            Notification::forSchool($school->id)
                ->forNotifiable($user)
                ->unread()
                ->update(['read_at' => now()]);

            return response()->json(['success' => true]);
        }
        catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to mark all notifications as read', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to process request'], 500);
        }
    }
}
