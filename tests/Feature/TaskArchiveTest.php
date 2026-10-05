<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Services\TodayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskArchiveTest extends TestCase
{
    use RefreshDatabase;

    private function board(): array
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create(['owner_id' => $owner->id, 'name' => 'Archive team']);
        $project = Project::create(['workspace_id' => $workspace->id, 'name' => 'Archive project', 'key' => 'ARC']);
        $column = $project->columns()->create(['title' => 'Done', 'position' => 1, 'workflow_role' => 'done']);
        $task = $column->tasks()->create([
            'title' => 'Keep my history', 'task_number' => 1, 'position' => 1,
            'description' => 'Notes', 'checklist' => [['text' => 'Item', 'done' => true]],
            'comments' => [['id' => 'comment-1', 'text' => 'Discussion']], 'completed_at' => now()->subDay(),
        ]);
        $this->actingAs($owner);
        $url = route('board.task.archive', [$workspace->slug, $project->slug, $task]);

        return compact('owner', 'workspace', 'project', 'column', 'task', 'url');
    }

    public function test_archive_preserves_content_and_files_and_restore_appends_without_changing_completion(): void
    {
        Storage::fake('local');
        extract($this->board());
        $upload = $this->postJson(route('task.attachments.store', [$workspace->slug, $project->slug, $task]), [
            'file' => UploadedFile::fake()->create('notes.txt', 1, 'text/plain'),
        ])->assertCreated();
        $before = $task->fresh()->only(['completed_at', 'description', 'checklist', 'comments']);
        $attachment = $task->attachments()->firstOrFail();
        $sibling = $column->tasks()->create(['title' => 'Stay visible', 'task_number' => 2, 'position' => 2]);
        $response = $this->patchJson($url, ['archived' => true])->assertOk();
        $this->assertSame([$sibling->id], array_column($response->json('board.columns.0.tasks'), 'dbId'));
        $response->assertJsonPath('board.archivedTasks.0.dbId', $task->id);
        $this->assertNotNull($task->fresh()->archived_at);
        $this->assertEquals($before, $task->fresh()->only(array_keys($before)));
        Storage::disk('local')->assertExists($attachment->path);
        $this->get(route('task.attachments.download', [$workspace->slug, $project->slug, $task, $attachment]))->assertOk();
        $this->putJson(route('board.task.update', [$workspace->slug, $project->slug, $task]), ['title' => 'Lost edit'])->assertUnprocessable();
        $this->postJson(route('board.task.comments.store', [$workspace->slug, $project->slug, $task]), ['text' => 'New comment'])->assertUnprocessable();
        $this->postJson(route('task.attachments.store', [$workspace->slug, $project->slug, $task]), ['file' => UploadedFile::fake()->create('new.txt')])->assertUnprocessable();
        $restore = $this->patchJson($url, ['archived' => false])->assertOk();
        $this->assertSame([$sibling->id, $task->id], array_column($restore->json('board.columns.0.tasks'), 'dbId'));
        $restore->assertJsonCount(0, 'board.archivedTasks');
        $this->assertEquals($before, $task->fresh()->only(array_keys($before)));
        $this->assertNull($task->fresh()->archived_at);
        $this->assertDatabaseHas('project_activities', ['task_id' => $task->id, 'kind' => 'task_archived']);
        $this->assertDatabaseHas('project_activities', ['task_id' => $task->id, 'kind' => 'task_restored']);
    }

    public function test_archive_is_idempotent_and_board_and_archive_are_mutually_exclusive(): void
    {
        extract($this->board());
        $this->patchJson($url, ['archived' => true])->assertOk();
        $timestamp = $task->fresh()->archived_at;
        $this->travel(1)->minute();
        $this->patchJson($url, ['archived' => true])->assertOk();
        $this->assertEquals($timestamp, $task->fresh()->archived_at);
        $this->getJson(route('board.realtime.snapshot', [$workspace->slug, $project->slug]))
            ->assertOk()->assertJsonCount(0, 'columns.0.tasks')->assertJsonCount(1, 'archivedTasks');
        $this->get(route('board', [$workspace->slug, $project->slug]))->assertOk()->assertSee('بایگانی وظیفه‌ها');
        $this->patchJson($url, ['archived' => false])->assertOk();
        $position = $task->fresh()->position;
        $this->patchJson($url, ['archived' => false])->assertOk();
        $this->assertSame($position, $task->fresh()->position);
    }

    public function test_viewers_can_read_archive_but_cannot_archive_or_restore_and_foreign_tasks_are_rejected(): void
    {
        extract($this->board());
        $viewer = User::factory()->create();
        $workspace->members()->attach($viewer, ['role' => 'viewer']);
        $this->patchJson($url, ['archived' => true])->assertOk();
        $this->actingAs($viewer)->getJson(route('board.realtime.snapshot', [$workspace->slug, $project->slug]))
            ->assertOk()->assertJsonCount(1, 'archivedTasks');
        $this->patchJson($url, ['archived' => false])->assertForbidden();
        $this->patchJson($url, ['archived' => true])->assertForbidden();
        $this->actingAs($owner);
        $other = Project::create(['workspace_id' => $workspace->id, 'name' => 'Other']);
        $foreign = $other->columns()->create(['title' => 'Other'])->tasks()->create(['title' => 'Foreign']);
        $this->patchJson(route('board.task.archive', [$workspace->slug, $project->slug, $foreign]), ['archived' => true])->assertForbidden();
        $this->assertNull($foreign->fresh()->archived_at);
        $project->update(['is_active' => false]);
        $this->patchJson($url, ['archived' => false])->assertForbidden();
    }

    public function test_bulk_archive_validates_every_task_before_archiving_and_preserves_column(): void
    {
        extract($this->board());
        $second = $column->tasks()->create(['title' => 'Second', 'task_number' => 2, 'position' => 2]);
        $bulkUrl = route('board.tasks.bulk', [$workspace->slug, $project->slug]);
        $this->patchJson($bulkUrl, ['task_ids' => [$task->id, 9999], 'action' => 'archive'])->assertUnprocessable();
        $this->assertNull($task->fresh()->archived_at);
        $this->patchJson($bulkUrl, ['task_ids' => [$task->id, $second->id], 'action' => 'archive'])
            ->assertOk()->assertJsonCount(0, 'board.columns.0.tasks')->assertJsonCount(2, 'board.archivedTasks');
        $this->assertDatabaseHas('project_columns', ['id' => $column->id]);
        $this->assertSame(0, Task::active()->count());
        // Archived tasks still protect their source column from deletion.
        $this->deleteJson(route('board.column.destroy', [$workspace->slug, $project->slug, $column]))->assertUnprocessable();
    }

    public function test_archived_tasks_leave_today_lists_without_losing_their_plans(): void
    {
        extract($this->board());
        $column->update(['workflow_role' => 'active']);
        $task->assignedUsers()->attach($owner->id);
        $date = app(TodayService::class)->date($workspace)->toDateString();
        $task->plans()->create(['user_id' => $owner->id, 'planned_for' => $date, 'bucket' => 'must', 'position' => 1]);
        $this->get(route('today', $workspace->slug))->assertOk()->assertViewHas('mustTasks', fn ($items) => $items->contains('dbId', $task->id));
        $this->patchJson($url, ['archived' => true])->assertOk();
        $this->get(route('today', $workspace->slug))->assertOk()
            ->assertViewHas('mustTasks', fn ($items) => ! $items->contains('dbId', $task->id))
            ->assertViewHas('availableTasks', fn ($items) => ! $items->contains('dbId', $task->id));
        $this->assertDatabaseHas('task_plans', ['task_id' => $task->id]);
        $this->patchJson($url, ['archived' => false])->assertOk();
        $this->get(route('today', $workspace->slug))->assertOk()->assertViewHas('mustTasks', fn ($items) => $items->contains('dbId', $task->id));
    }

    public function test_archived_open_tasks_are_not_carried_into_the_next_cycle(): void
    {
        extract($this->board());
        $column->update(['workflow_role' => 'active']);
        $project->update(['cycle_length_weeks' => 1]);
        $start = $this->postJson(route('cycles.start', [$workspace->slug, $project->slug]), [
            'starts_on' => today()->toDateString(), 'task_ids' => [$task->id],
        ])->assertCreated();
        $this->patchJson($url, ['archived' => true])->assertOk()->assertJsonCount(0, 'board.activeCycle.openTaskIds');
        $finish = $this->postJson(route('cycles.finish', [$workspace->slug, $project->slug, $start->json('cycle.id')]), [
            'carry_task_ids' => [], 'removed_task_ids' => [], 'start_next' => true,
        ])->assertOk()->assertJsonCount(0, 'nextCycle.tasks');
        $this->assertDatabaseHas('cycle_task', ['task_id' => $task->id, 'cycle_id' => $start->json('cycle.id'), 'outcome' => 'removed']);
        $this->assertNotNull($task->fresh()->archived_at);
    }
}
