<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
