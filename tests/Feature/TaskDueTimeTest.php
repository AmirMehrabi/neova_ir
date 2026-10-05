<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskDueTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_timed_deadline_can_be_created_displayed_and_cleared(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::create(['owner_id' => $user->id, 'name' => 'تیم']);
        $project = Project::create(['workspace_id' => $workspace->id, 'name' => 'تخته']);
        $column = $project->columns()->create(['title' => 'کارها', 'position' => 1, 'workflow_role' => 'other']);
        $this->actingAs($user);

        $createUrl = route('board.task.store', [$workspace->slug, $project->slug]);
        $response = $this->postJson($createUrl, [
            'column_id' => $column->id,
            'title' => 'تحویل طرح',
            'due_date' => '2026-10-03',
            'due_time' => '14:30',
        ])->assertOk();

        $taskId = $response->json('id');
        $task = Task::findOrFail($taskId);
        $this->assertSame('2026-10-03', $task->due_date->format('Y-m-d'));
        $this->assertSame('14:30', substr($task->due_time, 0, 5));
        $this->get(route('board.realtime.snapshot', [$workspace->slug, $project->slug]))
            ->assertOk()->assertJsonPath('columns.0.tasks.0.dueTime', '14:30');

        $this->putJson(route('board.task.update', [$workspace->slug, $project->slug, $taskId]), [
            'due_date' => '2026-10-03',
            'due_time' => '',
        ])->assertOk();
        $this->assertDatabaseHas('tasks', ['id' => $taskId, 'due_time' => null]);

        $this->putJson(route('board.task.update', [$workspace->slug, $project->slug, $taskId]), [
            'due_date' => null,
            'due_time' => null,
        ])->assertOk();
        $this->assertDatabaseHas('tasks', ['id' => $taskId, 'due_date' => null, 'due_time' => null]);
    }

    public function test_time_requires_a_date(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::create(['owner_id' => $user->id, 'name' => 'تیم']);
        $project = Project::create(['workspace_id' => $workspace->id, 'name' => 'تخته']);
        $column = $project->columns()->create(['title' => 'کارها', 'position' => 1, 'workflow_role' => 'other']);
        $this->actingAs($user);

        $this->postJson(route('board.task.store', [$workspace->slug, $project->slug]), [
            'column_id' => $column->id,
            'title' => 'تحویل طرح',
            'due_time' => '14:30',
        ])->assertUnprocessable()->assertJsonValidationErrors('due_date');
    }
}
