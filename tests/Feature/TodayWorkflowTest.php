<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\ProjectActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TodayWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function context(): array
    {
        $user = User::factory()->create();
        $workspace = Workspace::create(['owner_id' => $user->id, 'name' => 'تیم محصول']);
        $project = Project::create(['workspace_id' => $workspace->id, 'name' => 'وب‌سایت', 'key' => 'WEB']);
        $backlog = $project->columns()->create(['title' => 'پس‌زمینه', 'position' => 0, 'workflow_role' => 'backlog']);
        $doing = $project->columns()->create(['title' => 'در حال انجام', 'position' => 1, 'workflow_role' => 'active']);
        $done = $project->columns()->create(['title' => 'انجام شده', 'position' => 2, 'workflow_role' => 'done']);
        $task = Task::create(['column_id' => $backlog->id, 'task_number' => 1, 'title' => 'رفع خطا', 'due_date' => today(), 'position' => 1]);
        $task->assignedUsers()->attach($user->id);

        return compact('user', 'workspace', 'project', 'backlog', 'doing', 'done', 'task');
    }

    public function test_due_date_does_not_schedule_a_task_for_today(): void
    {
        extract($this->context());

        $this->actingAs($user)->get(route('today', $workspace->slug))
            ->assertOk()->assertSee('هنوز کاری برای امروز انتخاب نکرده‌اید');

        $this->assertDatabaseCount('task_plans', 0);
    }

    public function test_assignee_can_plan_and_remove_a_task_for_today(): void
    {
        extract($this->context());
        $date = now($workspace->timezone)->toDateString();

        $this->actingAs($user)->putJson(route('today.task.plan', [$workspace->slug, $project->slug, $task]), [
            'planned_for' => $date,
            'bucket' => 'must',
        ])->assertOk()->assertJsonPath('task.plan.bucket', 'must');

        $this->assertDatabaseHas('task_plans', ['task_id' => $task->id, 'user_id' => $user->id, 'planned_for' => $date.' 00:00:00']);
        $this->actingAs($user)->get(route('today', $workspace->slug))->assertSee('رفع خطا');

        $this->actingAs($user)->deleteJson(route('today.task.unplan', [$workspace->slug, $project->slug, $task]), [
            'planned_for' => $date,
        ])->assertOk();
        $this->assertDatabaseCount('task_plans', 0);
    }

    public function test_task_can_be_moved_to_tomorrow_atomically(): void
    {
        extract($this->context());
        $today = now($workspace->timezone)->startOfDay();

        $this->actingAs($user)->putJson(route('today.task.plan', [$workspace->slug, $project->slug, $task]), [
            'planned_for' => $today->toDateString(),
            'bucket' => 'must',
        ])->assertOk();

        $this->actingAs($user)->patchJson(route('today.task.tomorrow', [$workspace->slug, $project->slug, $task]))
            ->assertOk()
            ->assertJsonPath('planned_for', $today->copy()->addDay()->toDateString());

        $this->assertDatabaseMissing('task_plans', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'planned_for' => $today->toDateString().' 00:00:00',
        ]);
        $this->assertDatabaseHas('task_plans', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'planned_for' => $today->copy()->addDay()->toDateString().' 00:00:00',
        ]);
    }

    public function test_today_tasks_can_be_reordered_and_normalized_into_one_priority_list(): void
    {
        extract($this->context());
        $second = Task::create(['column_id' => $backlog->id, 'task_number' => 2, 'title' => 'کار دوم', 'position' => 2]);
        $second->assignedUsers()->attach($user->id);
        $date = now($workspace->timezone)->toDateString();

        $this->actingAs($user)->putJson(route('today.task.plan', [$workspace->slug, $project->slug, $task]), [
            'planned_for' => $date, 'bucket' => 'optional', 'position' => 1,
        ])->assertOk();
        $this->actingAs($user)->putJson(route('today.task.plan', [$workspace->slug, $project->slug, $second]), [
            'planned_for' => $date, 'bucket' => 'must', 'position' => 1,
        ])->assertOk();

        $this->actingAs($user)->patchJson(route('today.tasks.reorder', $workspace->slug), [
            'task_ids' => [$task->id, $second->id],
        ])->assertOk()->assertJsonPath('task_ids', [$task->id, $second->id]);

        $this->assertDatabaseHas('task_plans', ['task_id' => $task->id, 'bucket' => 'must', 'position' => 1]);
        $this->assertDatabaseHas('task_plans', ['task_id' => $second->id, 'bucket' => 'must', 'position' => 2]);
    }

    public function test_reorder_rejects_tasks_that_are_not_in_the_active_today_list(): void
    {
        extract($this->context());

        $this->actingAs($user)->patchJson(route('today.tasks.reorder', $workspace->slug), [
            'task_ids' => [$task->id],
        ])->assertUnprocessable();
    }

    public function test_completing_from_today_moves_task_to_done_and_clears_block(): void
    {
        extract($this->context());
        $task->update(['is_blocked' => true, 'blocked_reason' => 'منتظر سرویس']);

        $this->actingAs($user)->patchJson(route('today.task.state', [$workspace->slug, $project->slug, $task]), [
            'action' => 'complete',
        ])->assertOk()->assertJsonPath('task.column.role', 'done');

        $task->refresh();
        $this->assertSame($done->id, $task->column_id);
        $this->assertNotNull($task->completed_at);
        $this->assertFalse($task->is_blocked);
        $this->assertNull($task->blocked_reason);
    }

    public function test_completed_today_task_can_be_reopened_without_losing_its_plan(): void
    {
        extract($this->context());
        $date = now($workspace->timezone)->toDateString();

        $this->actingAs($user)->putJson(route('today.task.plan', [$workspace->slug, $project->slug, $task]), [
            'planned_for' => $date,
            'bucket' => 'must',
        ])->assertOk();

        $this->actingAs($user)->patchJson(route('today.task.state', [$workspace->slug, $project->slug, $task]), [
            'action' => 'complete',
        ])->assertOk();

        $this->actingAs($user)->patchJson(route('today.task.state', [$workspace->slug, $project->slug, $task]), [
            'action' => 'reopen',
        ])->assertOk()->assertJsonPath('task.column.role', 'backlog');

        $task->refresh();
        $this->assertSame($backlog->id, $task->column_id);
        $this->assertNull($task->completed_at);
        $this->assertDatabaseHas('task_plans', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'planned_for' => $date.' 00:00:00',
        ]);
    }

    public function test_blocking_preserves_column_and_requires_reason(): void
    {
        extract($this->context());

        $this->actingAs($user)->patchJson(route('today.task.state', [$workspace->slug, $project->slug, $task]), [
            'action' => 'block',
        ])->assertStatus(422);

        $this->actingAs($user)->patchJson(route('today.task.state', [$workspace->slug, $project->slug, $task]), [
            'action' => 'block', 'reason' => 'منتظر پاسخ مشتری',
        ])->assertOk()->assertJsonPath('task.isBlocked', true);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'column_id' => $backlog->id, 'is_blocked' => true]);
    }

    public function test_task_cannot_be_planned_through_another_workspace(): void
    {
        extract($this->context());
        $other = Workspace::create(['owner_id' => $user->id, 'name' => 'فضای دیگر']);
        $otherProject = Project::create(['workspace_id' => $other->id, 'name' => 'دیگر', 'key' => 'OTH']);
        $otherProject->columns()->create(['title' => 'پس‌زمینه', 'position' => 0, 'workflow_role' => 'backlog']);

        $this->actingAs($user)->putJson(route('today.task.plan', [$other->slug, $otherProject->slug, $task]), [
            'planned_for' => today()->toDateString(), 'bucket' => 'must',
        ])->assertNotFound();
    }

    public function test_viewer_cannot_mutate_today(): void
    {
        extract($this->context());
        $viewer = User::factory()->create();
        $workspace->members()->attach($viewer->id, ['role' => 'viewer']);

        $this->actingAs($viewer)->putJson(route('today.task.plan', [$workspace->slug, $project->slug, $task]), [
            'planned_for' => today()->toDateString(), 'bucket' => 'must',
        ])->assertForbidden();
    }

    public function test_quick_create_for_today_is_assigned_planned_and_visible_on_board(): void
    {
        extract($this->context());

        $response = $this->actingAs($user)->postJson(route('today.tasks.store', $workspace->slug), [
            'project_id' => $project->id,
            'title' => 'وظیفه سریع',
            'when' => 'today',
            'bucket' => 'must',
        ])->assertCreated()->assertJsonPath('task.title', 'وظیفه سریع');

        $created = Task::findOrFail($response->json('task.dbId'));
        $this->assertSame($backlog->id, $created->column_id);
        $this->assertTrue($created->assignedUsers()->whereKey($user->id)->exists());
        $this->assertTrue($created->plans()->where('user_id', $user->id)->whereDate('planned_for', now($workspace->timezone)->toDateString())->exists());

        $this->actingAs($user)->get(route('today', $workspace->slug))->assertSee('وظیفه سریع');
        $this->actingAs($user)->get(route('board', [$workspace->slug, $project->slug]))
            ->assertOk()
            ->assertDontSee('[..', false);
    }

    public function test_owner_can_add_existing_task_to_member_today_without_replacing_assignees(): void
    {
        Notification::fake();
        extract($this->context());
        $member = User::factory()->create(['first_name' => 'همکار', 'last_name' => 'تیم']);
        $workspace->members()->attach($member->id, ['role' => 'user']);
        $date = now($workspace->timezone)->toDateString();

        $this->actingAs($user)->putJson(route('today.task.plan', [$workspace->slug, $project->slug, $task]), [
            'planned_for' => $date,
            'bucket' => 'must',
            'user_id' => $member->id,
        ])->assertOk()->assertJsonPath('task.plan.bucket', 'must');

        $this->assertEqualsCanonicalizing([$user->id, $member->id], $task->assignedUsers()->pluck('users.id')->all());
        $this->assertDatabaseHas('task_plans', ['task_id' => $task->id, 'user_id' => $member->id, 'planned_for' => $date.' 00:00:00']);
        $this->assertDatabaseHas('project_activities', ['project_id' => $project->id, 'task_id' => $task->id, 'actor_id' => $user->id, 'kind' => 'task_planned']);
        Notification::assertSentToTimes($member, ProjectActivityNotification::class, 1);
        Notification::assertSentTo($member, ProjectActivityNotification::class, fn ($notification) => $notification->kind === 'today_planned');
    }

    public function test_regular_user_cannot_manage_another_members_today_plan(): void
    {
        extract($this->context());
        $actor = User::factory()->create();
        $target = User::factory()->create();
        $workspace->members()->attach($actor->id, ['role' => 'user']);
        $workspace->members()->attach($target->id, ['role' => 'user']);

        $this->actingAs($actor)->putJson(route('today.task.plan', [$workspace->slug, $project->slug, $task]), [
            'planned_for' => now($workspace->timezone)->toDateString(),
            'bucket' => 'must',
            'user_id' => $target->id,
        ])->assertForbidden();

        $this->assertDatabaseMissing('task_plans', ['task_id' => $task->id, 'user_id' => $target->id]);
    }

    public function test_moving_teammate_task_to_tomorrow_does_not_move_managers_plan(): void
    {
        Notification::fake();
        extract($this->context());
        $member = User::factory()->create();
        $workspace->members()->attach($member->id, ['role' => 'user']);
        $task->assignedUsers()->attach($member->id);
        $today = now($workspace->timezone)->startOfDay();

        foreach ([$user, $member] as $target) {
            $this->actingAs($user)->putJson(route('today.task.plan', [$workspace->slug, $project->slug, $task]), [
                'planned_for' => $today->toDateString(), 'bucket' => 'must', 'user_id' => $target->id,
            ])->assertOk();
        }

        $this->actingAs($user)->patchJson(route('today.task.tomorrow', [$workspace->slug, $project->slug, $task]), [
            'user_id' => $member->id,
        ])->assertOk();

        $this->assertDatabaseHas('task_plans', ['task_id' => $task->id, 'user_id' => $user->id, 'planned_for' => $today->toDateString().' 00:00:00']);
        $this->assertDatabaseMissing('task_plans', ['task_id' => $task->id, 'user_id' => $member->id, 'planned_for' => $today->toDateString().' 00:00:00']);
        $this->assertDatabaseHas('task_plans', ['task_id' => $task->id, 'user_id' => $member->id, 'planned_for' => $today->copy()->addDay()->toDateString().' 00:00:00']);
    }

    public function test_team_today_is_available_only_to_owner_and_admin_roles(): void
    {
        extract($this->context());
        $member = User::factory()->create(['first_name' => 'عضو', 'last_name' => 'معمولی']);
        $workspace->members()->attach($member->id, ['role' => 'user']);

        $this->actingAs($user)->get(route('today', $workspace->slug).'?view=team')
            ->assertOk()->assertSee('today-team-workspace', false)->assertSee($member->full_name);

        $this->actingAs($member)->get(route('today', $workspace->slug).'?view=team')
            ->assertOk()->assertDontSee('today-team-workspace', false);
    }

    public function test_today_view_preference_is_persisted_and_used_on_refresh(): void
    {
        extract($this->context());

        $this->actingAs($user)->get(route('today', $workspace->slug).'?view=team')
            ->assertOk()->assertSee('today-team-workspace', false);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'today_view' => 'team']);

        $this->actingAs($user)->get(route('today', $workspace->slug))
            ->assertOk()->assertSee('today-team-workspace', false);

        $this->actingAs($user)->get(route('today', $workspace->slug).'?view=mine')
            ->assertOk()->assertDontSee('today-team-workspace', false);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'today_view' => 'mine']);

        $this->actingAs($user)->get(route('today', $workspace->slug))
            ->assertOk()->assertDontSee('today-team-workspace', false);
    }

    public function test_manager_quick_create_targets_selected_member(): void
    {
        Notification::fake();
        extract($this->context());
        $member = User::factory()->create();
        $workspace->members()->attach($member->id, ['role' => 'user']);

        $response = $this->actingAs($user)->postJson(route('today.tasks.store', $workspace->slug), [
            'project_id' => $project->id,
            'title' => 'کار واگذارشده',
            'when' => 'today',
            'bucket' => 'must',
            'user_id' => $member->id,
        ])->assertCreated();

        $created = Task::findOrFail($response->json('task.dbId'));
        $this->assertSame([$member->id], $created->assignedUsers()->pluck('users.id')->all());
        $this->assertDatabaseHas('task_plans', ['task_id' => $created->id, 'user_id' => $member->id]);
        $this->assertDatabaseMissing('task_plans', ['task_id' => $created->id, 'user_id' => $user->id]);
        Notification::assertSentToTimes($member, ProjectActivityNotification::class, 1);
    }
}
