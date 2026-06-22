<?php

namespace App\Http\Controllers;

use App\Models\WorkspaceInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $invitations = WorkspaceInvitation::query()
            ->with(['workspace', 'inviter'])
            ->where('phone', $request->user()->phone)
            ->latest()
            ->paginate(15);
        $invitations->getCollection()->each->markExpiredIfNeeded();

        $notifications = $request->user()->notifications()->latest()->paginate(20, ['*'], 'notifications');

        return view('notifications.index', compact('invitations', 'notifications'));
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }

    public function open(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless((int) $notification->notifiable_id === (int) $request->user()->id, 403);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('notifications.index'));
    }
}
