<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Services\WorkspaceInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkspaceManagementController extends Controller
{
    public function index(Request $request, string $workspace): View
    {
        $workspace = $this->workspace($request, $workspace);

        if (! $workspace->canManageMembers($request->user())) {
            abort(403);
        }

        $members = $workspace->members()->orderBy('name')->get();
        $projects = $workspace->projects()->with('members')->orderBy('name')->get();
        $invitations = $workspace->invitations()
            ->with('inviter')
            ->latest()
            ->limit(50)
            ->get()
            ->each->markExpiredIfNeeded();

        return view('workspaces.settings', [
            'workspace' => $workspace,
            'members' => $members,
            'projects' => $projects,
            'invitations' => $invitations,
            'actorRole' => $workspace->roleFor($request->user()),
        ]);
    }

    public function update(Request $request, string $workspace): RedirectResponse
    {
        $workspace = $this->workspace($request, $workspace);

        if (! $workspace->isOwnedBy($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $workspace->update(['name' => $validated['name']]);

        return back()->with('success', 'نام فضای کاری به‌روزرسانی شد.');
    }

    public function invite(
        Request $request,
        string $workspace,
        WorkspaceInvitationService $invitations,
    ): RedirectResponse {
        $workspace = $this->workspace($request, $workspace);
        $allowedRoles = $workspace->roleFor($request->user()) === 'owner'
            ? ['admin', 'user', 'viewer']
            : ['user', 'viewer'];

        if (! $workspace->canManageMembers($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'role' => ['required', Rule::in($allowedRoles)],
        ]);

        $invitations->create($workspace, $request->user(), $validated['phone'], $validated['role']);

        return back()->with('success', 'دعوت‌نامه ایجاد و برای ارسال پیامک در صف قرار گرفت.');
    }

    public function resend(
        Request $request,
        string $workspace,
        WorkspaceInvitation $invitation,
        WorkspaceInvitationService $invitations,
    ): RedirectResponse {
        $workspace = $this->workspace($request, $workspace);
        $this->ensureInvitationBelongsTo($workspace, $invitation);

        if (! $workspace->canManageMembers($request->user())
            || ! $workspace->canManageRole($request->user(), $invitation->role)) {
            abort(403);
        }

        $invitations->resend($invitation);

        return back()->with('success', 'دعوت‌نامه دوباره ارسال شد.');
    }

    public function revoke(
        Request $request,
        string $workspace,
        WorkspaceInvitation $invitation,
    ): RedirectResponse {
        $workspace = $this->workspace($request, $workspace);
        $this->ensureInvitationBelongsTo($workspace, $invitation);

        if (! $workspace->canManageMembers($request->user())
            || ! $workspace->canManageRole($request->user(), $invitation->role)) {
            abort(403);
        }

        if ($invitation->status === WorkspaceInvitation::STATUS_PENDING) {
            $invitation->update(['status' => WorkspaceInvitation::STATUS_REVOKED]);
        }

        return back()->with('success', 'دعوت‌نامه لغو شد.');
    }

    public function updateRole(Request $request, string $workspace, User $user): RedirectResponse
    {
        $workspace = $this->workspace($request, $workspace);
        $currentRole = $workspace->roleFor($user);

        if (! $currentRole || $currentRole === 'owner'
            || ! $workspace->canManageRole($request->user(), $currentRole)) {
            abort(403);
        }

        $allowedRoles = $workspace->roleFor($request->user()) === 'owner'
            ? ['admin', 'user', 'viewer']
            : ['user', 'viewer'];
        $validated = $request->validate(['role' => ['required', Rule::in($allowedRoles)]]);

        $workspace->members()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        return back()->with('success', 'نقش عضو به‌روزرسانی شد.');
    }

    public function removeMember(Request $request, string $workspace, User $user): RedirectResponse
    {
        $workspace = $this->workspace($request, $workspace);
        $targetRole = $workspace->roleFor($user);

        if (! $targetRole || $targetRole === 'owner'
            || ! $workspace->canManageRole($request->user(), $targetRole)) {
            abort(403);
        }

        $this->detachMember($workspace, $user);

        return back()->with('success', 'عضو از فضای کاری حذف شد.');
    }

    public function leave(Request $request, string $workspace): RedirectResponse
    {
        $workspace = $this->workspace($request, $workspace);

        if ($workspace->isOwnedBy($request->user())) {
            return back()->withErrors(['workspace' => 'مالک نمی‌تواند فضای کاری را ترک کند.']);
        }

        $this->detachMember($workspace, $request->user());

        return redirect()->route('dashboard')->with('success', 'از فضای کاری خارج شدید.');
    }

    public function addProjectMember(
        Request $request,
        string $workspace,
        Project $project,
    ): RedirectResponse {
        $workspace = $this->workspace($request, $workspace);
        $this->ensureProjectBelongsTo($workspace, $project);

        if (! $workspace->canManageMembers($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ]);
        $user = User::findOrFail($validated['user_id']);

        if (! $workspace->hasMember($user)) {
            return back()->withErrors(['user_id' => 'این کاربر عضو فضای کاری نیست.']);
        }

        $project->members()->syncWithoutDetaching([
            $user->id => ['added_by' => $request->user()->id],
        ]);

        return back()->with('success', 'عضو به تیم پروژه اضافه شد.');
    }

    public function removeProjectMember(
        Request $request,
        string $workspace,
        Project $project,
        User $user,
    ): RedirectResponse {
        $workspace = $this->workspace($request, $workspace);
        $this->ensureProjectBelongsTo($workspace, $project);

        if (! $workspace->canManageMembers($request->user())) {
            abort(403);
        }

        $project->members()->detach($user->id);

        return back()->with('success', 'عضو از تیم پروژه حذف شد.');
    }

    private function workspace(Request $request, string $slug): Workspace
    {
        $workspace = Workspace::where('slug', $slug)->firstOrFail();

        if (! $workspace->hasMember($request->user())) {
            abort(403);
        }

        return $workspace;
    }

    private function detachMember(Workspace $workspace, User $user): void
    {
        DB::transaction(function () use ($workspace, $user) {
            DB::table('project_members')
                ->where('user_id', $user->id)
                ->whereIn('project_id', $workspace->projects()->select('id'))
                ->delete();
            $workspace->members()->detach($user->id);
        });
    }

    private function ensureInvitationBelongsTo(
        Workspace $workspace,
        WorkspaceInvitation $invitation,
    ): void {
        abort_unless($invitation->workspace_id === $workspace->id, 404);
    }

    private function ensureProjectBelongsTo(Workspace $workspace, Project $project): void
    {
        abort_unless($project->workspace_id === $workspace->id, 404);
    }
}
