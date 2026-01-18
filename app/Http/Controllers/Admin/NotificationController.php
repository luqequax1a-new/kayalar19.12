<?php

namespace FleetCart\Http\Controllers\Admin;

use FleetCart\Models\AdminNotification;
use FleetCart\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NotificationController extends Controller
{
    /**
     * Get recent notifications
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $period = $request->get('period', 'all');

        $query = AdminNotification::recent($limit);

        if ($period === 'today') {
            $query->where('created_at', '>=', now()->startOfDay());
        } elseif ($period === 'weekly') {
            $query->where('created_at', '>=', now()->subDays(7)->startOfDay());
        }

        $notifications = $query->get();
        $unreadCount = NotificationService::getUnreadCount();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = AdminNotification::findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => NotificationService::getUnreadCount(),
        ]);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead()
    {
        NotificationService::markAllAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        $notification = AdminNotification::findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'unread_count' => NotificationService::getUnreadCount(),
        ]);
    }

    /**
     * Get unread count
     */
    public function unreadCount()
    {
        return response()->json([
            'count' => NotificationService::getUnreadCount(),
        ]);
    }
}
