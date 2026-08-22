<?php

namespace App\Http\Controllers;

use App\Models\WorkspaceInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;
use App\Services\WorkspaceContext;

class NotificationController extends Controller
{
    public function index(Request $request, WorkspaceContext $context): View|RedirectResponse
    {
        $workspace = $context->resolve($request->user());
        if (! $workspace) {
            return redirect()->route('dashboard');
        }
        $invitations = WorkspaceInvitation::query()
            ->with(['workspace', 'inviter'])
            ->where('phone', $request->user()->phone)
            ->latest()
            ->paginate(15);
        $invitations->getCollection()->each->markExpiredIfNeeded();

        $notifications = $request->user()->notifications()
            ->when($request->string('filter')->toString() === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->latest()
            ->paginate(20, ['*'], 'notifications')
            ->withQueryString();

        return view('notifications.index', compact('invitations', 'notifications', 'workspace'));
    }

    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'unread_count' => 0,
            ]);
        }

        return back();
    }

    public function markRead(Request $request, DatabaseNotification $notification): JsonResponse|RedirectResponse
    {
        abort_unless((int) $notification->notifiable_id === (int) $request->user()->id, 403);

        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ]);
        }

        return back();
    }

    public function open(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless((int) $notification->notifiable_id === (int) $request->user()->id, 403);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('notifications.index'));
    }
}
