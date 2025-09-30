<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Get all notifications
     */
    public function index(Request $request)
    {
        $query = Notification::where('user_id', $request->user()->id);

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Get single notification
     */
    public function show($id)
    {
        $notification = Notification::find($id);

        if (!$notification || $notification->user_id !== request()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        // Mark as read
        if ($notification->isUnread()) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'data' => $notification
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::find($id);

        if (!$notification || $notification->user_id !== request()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->where('status', 'unread')
            ->update([
                'status' => 'read',
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Get notification statistics
     */
    public function statistics(Request $request)
    {
        $userId = $request->user()->id;
        
        $stats = [
            'total_notifications' => Notification::where('user_id', $userId)->count(),
            'unread_notifications' => Notification::where('user_id', $userId)
                ->where('status', 'unread')
                ->count(),
            'read_notifications' => Notification::where('user_id', $userId)
                ->where('status', 'read')
                ->count(),
            'by_type' => Notification::where('user_id', $userId)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}