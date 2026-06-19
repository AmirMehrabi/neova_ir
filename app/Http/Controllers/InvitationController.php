<?php

namespace App\Http\Controllers;

use App\Models\WorkspaceInvitation;
use App\Services\WorkspaceInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function show(Request $request, string $code): View|RedirectResponse
    {
        $invitation = WorkspaceInvitation::findByCode($code);

        abort_unless($invitation, 404);
        $invitation->markExpiredIfNeeded();

        if (! Auth::check()) {
            $request->session()->put('workspace_invitation_code', $code);

            return redirect()->route('auth');
        }

        return view('invitations.show', compact('invitation'));
    }

    public function accept(
        Request $request,
        WorkspaceInvitation $invitation,
        WorkspaceInvitationService $invitations,
    ): RedirectResponse {
        $invitations->accept($invitation->load(['workspace', 'inviter']), $request->user());
        $request->session()->forget('workspace_invitation_code');

        return redirect()->route('dashboard', ['workspace' => $invitation->workspace->slug])
            ->with('success', 'به فضای کاری پیوستید.');
    }

    public function decline(
        Request $request,
        WorkspaceInvitation $invitation,
        WorkspaceInvitationService $invitations,
    ): RedirectResponse {
        $invitations->decline($invitation->load(['workspace', 'inviter']), $request->user());
        $request->session()->forget('workspace_invitation_code');

        return redirect()->route('notifications.index')->with('success', 'دعوت‌نامه رد شد.');
    }
}
