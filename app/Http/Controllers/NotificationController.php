<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** Return unread count + latest 10 notifications */
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->latest()
            ->limit(15)
            ->get(['id', 'type', 'message', 'is_read', 'created_at', 'data']);

        return response()->json([
            'unread' => $notifications->where('is_read', false)->count(),
            'items'  => $notifications,
        ]);
    }

    /** Mark one as read */
    public function markRead(Notification $notification)
    {
        abort_if($notification->user_id !== auth()->id(), 403);
        $notification->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    /** Mark all as read */
    public function markAllRead()
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }
}
