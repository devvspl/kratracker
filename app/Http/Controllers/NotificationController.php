<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** API: unread count + latest 15 for bell dropdown */
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

    /** Full page: all notifications paginated */
    public function all(Request $request)
    {
        $filter = $request->get('filter', 'all'); // all | unread

        $query = Notification::where('user_id', auth()->id())->latest();

        if ($filter === 'unread') {
            $query->where('is_read', false);
        }

        $notifications = $query->paginate(20)->withQueryString();
        $unreadCount   = Notification::where('user_id', auth()->id())->where('is_read', false)->count();

        return view('notifications.index', compact('notifications', 'unreadCount', 'filter'));
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

    /** Mark all read via web (form post, redirect back) */
    public function markAllReadWeb()
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
        return back()->with('status', 'All notifications marked as read.');
    }
}
