<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BoardRealtimeUxTest extends TestCase
{
    use RefreshDatabase;

    private function board(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = Workspace::create(['owner_id' => $owner->id, 'name' => 'تیم']);
        $workspace->members()->attach($member, ['role' => 'user']);
        $project = Project::create(['workspace_id' => $workspace->id, 'name' => 'پروژه', 'key' => 'UX']);
        $ready = $project->columns()->create(['title' => 'Ready', 'position' => 1, 'workflow_role' => 'ready']);
        $done = $project->columns()->create(['title' => 'Done', 'position' => 2, 'workflow_role' => 'done']);
        $task = $ready->tasks()->create(['title' => 'Task', 'task_number' => 1, 'position' => 1]);
        $this->actingAs($owner);

        return compact('owner', 'member', 'workspace', 'project', 'ready', 'done', 'task');
    }

    public function test_move_response_refreshes_versions_completion_and_sibling_order_for_next_edit(): void
    {
        extract($this->board());
        $sibling = $done->tasks()->create(['title' => 'Sibling', 'task_number' => 2, 'position' => 1]);
        $this->freezeTime();
        $response = $this->postJson(route('board.task.move', [$workspace->slug, $project->slug, $task]), ['column_id' => $done->id, 'position' => 0])->assertOk();
        $tasks = collect($response->json('board.columns.1.tasks'));
        $moved = $tasks->firstWhere('dbId', $task->id);
        $this->assertNotNull($moved['completedAt']);
        $this->assertSame([$task->id, $sibling->id], $tasks->pluck('dbId')->all());
        $this->assertSame((int) $task->fresh()->edit_version, $moved['version']);
        $this->assertSame([$task->id], $response->json('board.originTaskIds'));
        $url = route('board.task.update', [$workspace->slug, $project->slug, $task]);
        $edited = $this->putJson($url, ['title' => 'First edit', 'expected_version' => $moved['version']])->assertOk();
        $version = collect($edited->json('board.columns.1.tasks'))->firstWhere('dbId', $task->id)['version'];
        $this->putJson($url, ['title' => 'Second edit', 'expected_version' => $version])->assertOk();
        $this->assertSame('Second edit', $task->fresh()->title);
    }

    public function test_last_task_save_wins_even_with_an_older_version_in_the_same_second(): void
    {
        extract($this->board());
        $this->freezeTime();
        $version = $task->fresh()->edit_version;
        $url = route('board.task.update', [$workspace->slug, $project->slug, $task]);
        $this->putJson($url, ['title' => 'Remote edit', 'expected_version' => $version])->assertOk();
        $this->actingAs($member);
        $this->putJson($url, ['title' => 'Last edit', 'expected_version' => $version])->assertOk();
        $this->assertSame('Last edit', $task->fresh()->title);
        $this->assertGreaterThan($version, $task->fresh()->edit_version);
    }

    public function test_last_task_save_wins_with_an_older_timestamp(): void
    {
        extract($this->board());
        $url = route('board.task.update', [$workspace->slug, $project->slug, $task]);
        $timestamp = $task->updated_at->toISOString();
        $this->travel(2)->seconds();
        $this->putJson($url, ['title' => 'First edit'])->assertOk();
        $this->putJson($url, ['title' => 'Last edit', 'expected_updated_at' => $timestamp])->assertOk();
        $this->assertSame('Last edit', $task->fresh()->title);
    }

    public function test_invalid_edit_returns_json_errors_and_can_be_corrected_without_refreshing(): void
    {
        extract($this->board());
        $url = route('board.task.update', [$workspace->slug, $project->slug, $task]);
        $version = $task->fresh()->edit_version;
        $this->putJson($url, ['title' => '', 'expected_version' => $version])
            ->assertUnprocessable()->assertJsonValidationErrors('title');
        $this->assertSame($version, $task->fresh()->edit_version);
        $this->putJson($url, ['title' => 'Corrected', 'expected_version' => $version])->assertOk();
    }

    public function test_reordering_siblings_and_appending_comments_do_not_invalidate_editor_fields(): void
    {
        extract($this->board());
        $sibling = $ready->tasks()->create(['title' => 'Sibling', 'task_number' => 2, 'position' => 2]);
        $version = $task->fresh()->edit_version;
        $this->postJson(route('board.task.move', [$workspace->slug, $project->slug, $sibling]), ['column_id' => $ready->id, 'position' => 0])->assertOk();
        $this->postJson(route('board.task.comments.store', [$workspace->slug, $project->slug, $task]), ['text' => 'Discussion'])->assertOk();
        $this->assertSame($version, $task->fresh()->edit_version);
        $this->putJson(route('board.task.update', [$workspace->slug, $project->slug, $task]), ['title' => 'Draft edit', 'expected_version' => $version])->assertOk();
        $this->assertSame('Discussion', $task->fresh()->comments[0]['text']);
    }

    public function test_comment_and_bulk_responses_include_canonical_versions(): void
    {
        extract($this->board());
        $comment = $this->postJson(route('board.task.comments.store', [$workspace->slug, $project->slug, $task]), ['text' => 'Hello'])->assertOk();
        $this->assertSame('Hello', $comment->json('board.columns.0.tasks.0.comments.0.text'));
        $version = $comment->json('board.columns.0.tasks.0.version');
        $bulk = $this->patchJson(route('board.tasks.bulk', [$workspace->slug, $project->slug]), ['task_ids' => [$task->id], 'action' => 'priority', 'value' => 'بالا'])->assertOk();
        $this->assertGreaterThan($version, $bulk->json('board.columns.0.tasks.0.version'));
        $this->assertSame('بالا', $bulk->json('board.columns.0.tasks.0.priority'));
    }

    public function test_column_colors_are_saved_and_returned_in_board_snapshots(): void
    {
        extract($this->board());
        $url = route('board.column.update', [$workspace->slug, $project->slug, $ready]);
        $this->patchJson($url, ['title' => $ready->title, 'color' => '#9581A5'])
            ->assertOk()->assertJsonPath('board.columns.0.dotHex', '#9581A5');
        $this->assertSame('#9581A5', $ready->fresh()->color);
        $this->getJson(route('board.realtime.snapshot', [$workspace->slug, $project->slug]))
            ->assertOk()->assertJsonPath('columns.0.dotHex', '#9581A5');
        $this->patchJson($url, ['title' => $ready->title, 'color' => 'red;display:none'])
            ->assertUnprocessable()->assertJsonValidationErrors('color');
        $this->assertSame('#9581A5', $ready->fresh()->color);
        $this->postJson(route('board.column.store', [$workspace->slug, $project->slug]), [
            'project_id' => $project->id, 'title' => 'Custom', 'color' => '#123ABC',
        ])->assertOk()->assertJsonPath('board.columns.2.dotHex', '#123ABC');
    }

    public function test_project_settings_conflict_preserves_newer_name_and_returns_current_identity(): void
    {
        extract($this->board());
        $version = $project->fresh()->edit_version;
        $url = route('board.project.update', [$workspace->slug, $project->slug]);
        $this->patchJson($url, ['name' => 'New name', 'expected_version' => $version])->assertOk()->assertJsonPath('board.project.name', 'New name');
        $this->patchJson($url, ['name' => 'Old draft', 'expected_version' => $version])->assertStatus(409);
        $this->assertSame('New name', $project->fresh()->name);
    }

    public function test_archive_is_reversible_preserves_files_and_blocks_task_writes(): void
    {
        Storage::fake('local');
        extract($this->board());
        $upload = $this->postJson(route('task.attachments.store', [$workspace->slug, $project->slug, $task]), ['file' => UploadedFile::fake()->create('notes.txt', 1, 'text/plain')])->assertCreated();
        $attachment = $task->attachments()->findOrFail($upload->json('attachments.0.id'));
        $url = route('board.project.archive', [$workspace->slug, $project->slug]);
        $this->patchJson($url, ['is_active' => false])->assertOk()->assertJsonPath('board.canEdit', false);
        $this->putJson(route('board.task.update', [$workspace->slug, $project->slug, $task]), ['title' => 'Blocked'])->assertForbidden();
        $this->postJson(route('today.tasks.store', $workspace->slug), ['project_id' => $project->id, 'title' => 'Blocked', 'when' => 'today'])->assertNotFound();
        $this->get(route('projects.index', [$workspace->slug, 'archived' => true]))->assertOk()->assertSee('پروژه');
        Storage::disk('local')->assertExists($attachment->path);
        $this->patchJson($url, ['is_active' => true])->assertOk()->assertJsonPath('board.canEdit', true);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Task']);
    }

    public function test_member_cannot_archive_or_manage_settings(): void
    {
        extract($this->board());
        $this->actingAs($member)->patchJson(route('board.project.archive', [$workspace->slug, $project->slug]), ['is_active' => false])->assertForbidden();
        $this->get(route('projects.index', $workspace->slug))->assertOk()->assertDontSee('مدیریت پروژه');
    }

    public function test_project_list_and_board_expose_direct_settings_and_accessible_controls(): void
    {
        extract($this->board());
        $this->get(route('projects.index', $workspace->slug))->assertOk()->assertSee('settings=general')->assertSee('settings=delete')->assertSee('بایگانی پروژه');
        $this->get(route('board', [$workspace->slug, $project->slug, 'settings' => 'general']))->assertOk()->assertSee('تنظیمات پروژه')->assertSee('aria-labelledby="project-settings-title"', false)->assertSee('جستجو در این تخته');
    }
}
