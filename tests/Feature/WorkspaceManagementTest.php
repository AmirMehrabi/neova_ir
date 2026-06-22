<?php

namespace Tests\Feature;

use App\Jobs\SendWorkspaceInvitationSms;
use App\Models\Project;
use App\Models\Task;
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

    public function test_workspace_editor_can_move_a_task_between_project_columns(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create(['owner_id' => $owner->id, 'name' => 'Product Team']);
        $project = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'First Project',
            'key' => 'PRJ',
        ]);
        $source = $project->columns()->create(['title' => 'پس‌زمینه', 'position' => 0]);
        $target = $project->columns()->create(['title' => 'در حال انجام', 'position' => 1]);
        $task = Task::create([
            'column_id' => $source->id,
            'title' => 'PRJ-001 کار نمونه',
            'priority' => 'متوسط',
            'position' => 1,
        ]);

        $this->actingAs($owner)
            ->postJson(route('board.task.move', [$workspace->slug, $project->slug, $task->id]), [
                'column_id' => $target->id,
                'position' => 0,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'column_id' => $target->id,
            'position' => 1,
        ]);
    }

    public function test_project_manager_can_manage_members_from_board_routes(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = Workspace::create(['owner_id' => $owner->id, 'name' => 'Product Team']);
        $workspace->members()->attach($member->id, ['role' => 'user']);
        $project = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'First Project',
            'key' => 'PRJ',
        ]);

        $this->actingAs($owner)
            ->postJson(route('board.project.members.store', [$workspace->slug, $project->slug]), [
                'user_id' => $member->id,
            ])
            ->assertOk()
            ->assertJsonPath('member.id', $member->id);

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id' => $member->id,
        ]);

        $this->actingAs($owner)
            ->deleteJson(route('board.project.members.destroy', [$workspace->slug, $project->slug, $member]))
            ->assertOk();

        $this->assertDatabaseMissing('project_members', [
            'project_id' => $project->id,
            'user_id' => $member->id,
        ]);

        $this->actingAs($owner)
            ->patchJson(route('board.project.update', [$workspace->slug, $project->slug]), [
                'name' => 'Renamed Project',
                'key' => 'NEW',
                'description' => 'Updated description',
            ])
            ->assertOk()
            ->assertJsonPath('project.name', 'Renamed Project');
    }

    public function test_task_assignment_update_and_mention_create_human_readable_notifications(): void
    {
        $owner = User::factory()->create(['first_name' => 'مالک', 'last_name' => 'پروژه']);
        $member = User::factory()->create(['first_name' => 'عضو', 'last_name' => 'تیم']);
        $workspace = Workspace::create(['owner_id' => $owner->id, 'name' => 'Product Team']);
        $workspace->members()->attach($member->id, ['role' => 'user']);
        $project = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'First Project',
            'key' => 'PRJ',
        ]);
        $project->members()->attach($member->id, ['added_by' => $owner->id]);
        $column = $project->columns()->create(['title' => 'پس‌زمینه', 'position' => 0]);

        $response = $this->actingAs($owner)
            ->postJson(route('board.task.store', [$workspace->slug, $project->slug]), [
                'column_id' => $column->id,
                'title' => 'کار جدید',
                'assignees' => [$member->full_name],
            ])
            ->assertOk();

        $task = Task::findOrFail($response->json('id'));
        $this->assertStringContainsString('شما را به وظیفه', $member->notifications()->latest()->first()->data['message']);

        $this->actingAs($owner)
            ->putJson(route('board.task.update', [$workspace->slug, $project->slug, $task->id]), [
                'title' => $task->title,
                'column_id' => $column->id,
                'priority' => 'بالا',
                'assignees' => [$member->full_name],
                'description' => "@[{$member->full_name}](user:{$member->id}) لطفاً بررسی کنید.",
            ])
            ->assertOk();

        $this->actingAs($owner)
            ->postJson(route('board.task.comments.store', [$workspace->slug, $project->slug, $task->id]), [
                'text' => "@[{$member->full_name}](user:{$member->id}) نظر شما چیست؟",
                'mention_ids' => [$member->id],
            ])
            ->assertOk()
            ->assertJsonPath('comment.mention_ids.0', $member->id);

        $messages = $member->notifications()->latest()->take(3)->get()->pluck('data.message')->implode(' ');
        $this->assertStringContainsString('از شما نام برد', $messages);
    }
}
