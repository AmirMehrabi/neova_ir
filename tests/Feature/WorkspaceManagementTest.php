<?php

namespace Tests\Feature;

use App\Jobs\SendWorkspaceInvitationSms;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Services\WorkspaceInvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WorkspaceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_invite_and_user_can_accept_idempotently(): void
    {
        Queue::fake();
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['phone' => '09123456789']);
        $workspace = Workspace::create(['owner_id' => $owner->id, 'name' => 'تیم محصول']);

        $invitation = app(WorkspaceInvitationService::class)
            ->create($workspace, $owner, $invitee->phone, 'user');

        Queue::assertPushed(SendWorkspaceInvitationSms::class);
        $this->assertDatabaseHas('workspace_invitations', [
            'id' => $invitation->id,
            'status' => WorkspaceInvitation::STATUS_PENDING,
        ]);

        app(WorkspaceInvitationService::class)->accept($invitation, $invitee);
        app(WorkspaceInvitationService::class)->accept($invitation->refresh(), $invitee);

        $this->assertDatabaseHas('workspace_members', [
            'workspace_id' => $workspace->id,
            'user_id' => $invitee->id,
            'role' => 'user',
        ]);
        $this->assertDatabaseCount('workspace_members', 1);
    }

    public function test_admin_cannot_manage_another_admin(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $otherAdmin = User::factory()->create();
        $workspace = Workspace::create(['owner_id' => $owner->id, 'name' => 'تیم محصول']);
        $workspace->members()->attach($admin->id, ['role' => 'admin']);
        $workspace->members()->attach($otherAdmin->id, ['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('workspaces.members.role', [$workspace->slug, $otherAdmin]), ['role' => 'user'])
            ->assertForbidden();
    }

    public function test_guest_invitation_link_resumes_after_authentication(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['phone' => '09123456789']);
        $workspace = Workspace::create(['owner_id' => $owner->id, 'name' => 'تیم محصول']);
        $code = 'guest-invite-code';
        $invitation = WorkspaceInvitation::create([
            'workspace_id' => $workspace->id,
            'invited_by' => $owner->id,
            'phone' => $invitee->phone,
            'role' => 'viewer',
            'code_hash' => hash('sha256', $code),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $this->get(route('invitations.show', $code))
            ->assertRedirect(route('auth'))
            ->assertSessionHas('workspace_invitation_code', $code);

        $this->actingAs($invitee)
            ->get(route('invitations.show', $code))
            ->assertOk()
            ->assertSee($workspace->name);

        $this->actingAs($invitee)
            ->post(route('invitations.accept', $invitation))
            ->assertRedirect(route('dashboard', ['workspace' => $workspace->slug]));

        $this->assertDatabaseHas('workspace_members', [
            'workspace_id' => $workspace->id,
            'user_id' => $invitee->id,
            'role' => 'viewer',
        ]);
    }

    public function test_project_team_does_not_limit_workspace_project_visibility(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = Workspace::create(['owner_id' => $owner->id, 'name' => 'تیم محصول']);
        $workspace->members()->attach($member->id, ['role' => 'user']);
        $project = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'پروژه اول',
            'key' => 'PRJ',
        ]);
        $project->columns()->create(['title' => 'پس‌زمینه', 'position' => 0]);

        $this->actingAs($member)
            ->get(route('board', [$workspace->slug, $project->slug]))
            ->assertOk();
    }

    public function test_viewer_can_view_board_but_cannot_create_tasks(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $workspace = Workspace::create(['owner_id' => $owner->id, 'name' => 'تیم محصول']);
        $workspace->members()->attach($viewer->id, ['role' => 'viewer']);
        $project = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'پروژه اول',
            'key' => 'PRJ',
        ]);
        $column = $project->columns()->create(['title' => 'پس‌زمینه', 'position' => 0]);

        $this->actingAs($viewer)
            ->get(route('board', [$workspace->slug, $project->slug]))
            ->assertOk()
            ->assertSee('فقط مشاهده');

        $this->actingAs($viewer)
            ->postJson(route('board.task.store', [$workspace->slug, $project->slug]), [
                'column_id' => $column->id,
                'title' => 'کار جدید',
            ])
            ->assertForbidden();
    }
}
