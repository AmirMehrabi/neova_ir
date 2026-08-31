<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeamAttachmentCycleTest extends TestCase
{
    use RefreshDatabase;

    private function context(): array
    {
        $owner = User::factory()->create(['first_name' => 'امیر', 'last_name' => 'تست']);
        $member = User::factory()->create(['first_name' => 'سارا', 'last_name' => 'تست']);
        $workspace = Workspace::create(['owner_id' => $owner->id, 'name' => 'تیم']);
        $workspace->members()->attach($member->id, ['role' => 'user']);
        $project = Project::create(['workspace_id' => $workspace->id, 'name' => 'محصول', 'key' => 'PRD', 'cycle_length_weeks' => 1]);
        $backlog = $project->columns()->create(['title' => 'پس‌زمینه', 'position' => 0, 'workflow_role' => 'backlog']);
        $active = $project->columns()->create(['title' => 'در حال انجام', 'position' => 1, 'workflow_role' => 'active']);
        $done = $project->columns()->create(['title' => 'انجام شده', 'position' => 2, 'workflow_role' => 'done']);
        $task = Task::create(['column_id' => $active->id, 'task_number' => 1, 'title' => 'پیاده‌سازی امروز', 'position' => 1]);
        $task->assignedUsers()->attach($member->id);

        return compact('owner', 'member', 'workspace', 'project', 'backlog', 'active', 'done', 'task');
    }

    public function test_team_today_is_derived_from_active_and_blocked_tasks(): void
    {
        extract($this->context());

        $this->actingAs($owner)->get(route('team.index', $workspace->slug))
            ->assertOk()->assertSee('سارا تست')->assertSee('پیاده‌سازی امروز');

        $task->update(['is_blocked' => true, 'blocked_reason' => 'منتظر ارائه‌دهنده']);
        $this->actingAs($owner)->get(route('team.index', $workspace->slug))
            ->assertOk()->assertSee('منتظر ارائه‌دهنده');
    }

    public function test_private_project_task_does_not_leak_to_unassigned_member(): void
    {
        extract($this->context());
        $project->update(['visibility' => 'private']);

        $this->actingAs($member)->get(route('team.index', $workspace->slug))
            ->assertOk()->assertDontSee('پیاده‌سازی امروز');
    }

    public function test_authorized_user_can_upload_and_download_private_attachment(): void
    {
        Storage::fake('local');
        extract($this->context());

        $response = $this->actingAs($owner)->post(route('task.attachments.store', [$workspace->slug, $project->slug, $task]), [
            'file' => UploadedFile::fake()->create('brief.pdf', 20, 'application/pdf'),
        ])->assertCreated();
        $attachment = $task->attachments()->findOrFail($response->json('attachments.0.id'));
        Storage::disk('local')->assertExists($attachment->path);

        $this->actingAs($owner)->get(route('task.attachments.download', [$workspace->slug, $project->slug, $task, $attachment]))->assertOk();
    }

    public function test_multiple_description_files_include_protected_preview_and_download_metadata(): void
    {
        Storage::fake('local');
        extract($this->context());

        $response = $this->actingAs($owner)->post(route('task.attachments.store', [$workspace->slug, $project->slug, $task]), [
            'context' => 'description',
            'files' => [
                UploadedFile::fake()->create('screen.png', 4, 'image/png'),
                UploadedFile::fake()->create('notes.txt', 4, 'text/plain'),
            ],
        ])->assertCreated()->assertJsonCount(2, 'attachments')
            ->assertJsonPath('attachments.0.context', 'description')
            ->assertJsonPath('attachments.0.category', 'image')
            ->assertJsonPath('attachments.0.previewable', true);

        $attachment = TaskAttachment::findOrFail($response->json('attachments.0.id'));
        $this->actingAs($owner)->get(route('task.attachments.preview', [$workspace->slug, $project->slug, $task, $attachment]))
            ->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_comment_can_contain_files_without_text_and_returns_contextual_attachments(): void
    {
        Storage::fake('local');
        extract($this->context());

        $response = $this->actingAs($owner)->post(route('board.task.comments.store', [$workspace->slug, $project->slug, $task]), [
            'text' => '',
            'files' => [UploadedFile::fake()->create('voice.mp3', 10, 'audio/mpeg')],
        ])->assertOk()->assertJsonPath('comment.text', '')
            ->assertJsonPath('comment.attachments.0.context', 'comment')
            ->assertJsonPath('comment.attachments.0.category', 'audio');

        $commentId = $response->json('comment.id');
        $this->assertDatabaseHas('task_attachments', ['task_id' => $task->id, 'context' => 'comment', 'comment_id' => $commentId]);
        $this->assertSame($commentId, $task->fresh()->comments[0]['id']);
    }

    public function test_empty_comment_and_unsafe_or_excessive_uploads_are_rejected(): void
    {
        Storage::fake('local');
        extract($this->context());

        $this->actingAs($owner)->withHeader('Accept', 'application/json')->post(route('board.task.comments.store', [$workspace->slug, $project->slug, $task]), ['text' => ''])
            ->assertInvalid(['text', 'files']);

        $this->actingAs($owner)->withHeader('Accept', 'application/json')->post(route('task.attachments.store', [$workspace->slug, $project->slug, $task]), [
            'files' => [UploadedFile::fake()->create('payload.php', 1, 'text/x-php')],
        ])->assertInvalid(['files.0']);

        $files = collect(range(1, 11))->map(fn ($number) => UploadedFile::fake()->create("file-{$number}.txt", 1, 'text/plain'))->all();
        $this->actingAs($owner)->withHeader('Accept', 'application/json')->post(route('task.attachments.store', [$workspace->slug, $project->slug, $task]), ['files' => $files])
            ->assertInvalid(['files']);
    }

    public function test_non_previewable_file_is_download_only_and_delete_cleans_storage(): void
    {
        Storage::fake('local');
        extract($this->context());

        $response = $this->actingAs($owner)->post(route('task.attachments.store', [$workspace->slug, $project->slug, $task]), [
            'files' => [UploadedFile::fake()->create('brief.docx', 5, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')],
        ])->assertCreated()->assertJsonPath('attachments.0.previewable', false);
        $attachment = TaskAttachment::findOrFail($response->json('attachments.0.id'));
        $path = $attachment->path;

        $this->actingAs($owner)->get(route('task.attachments.preview', [$workspace->slug, $project->slug, $task, $attachment]))->assertStatus(415);
        $this->actingAs($owner)->delete(route('task.attachments.destroy', [$workspace->slug, $project->slug, $task, $attachment]))->assertOk();
        Storage::disk('local')->assertMissing($path);
    }

    public function test_viewer_can_preview_and_download_but_cannot_upload_or_delete(): void
    {
        Storage::fake('local');
        extract($this->context());
        $workspace->members()->updateExistingPivot($member->id, ['role' => 'viewer']);
        $path = UploadedFile::fake()->create('guide.pdf', 5, 'application/pdf')->store("task-attachments/{$task->id}", 'local');
        $attachment = $task->attachments()->create([
            'context' => 'description', 'uploaded_by' => $owner->id, 'original_name' => 'guide.pdf',
            'path' => $path, 'mime_type' => 'application/pdf', 'size' => 5120,
        ]);

        $this->actingAs($member)->get(route('task.attachments.preview', [$workspace->slug, $project->slug, $task, $attachment]))->assertOk();
        $this->actingAs($member)->get(route('task.attachments.download', [$workspace->slug, $project->slug, $task, $attachment]))->assertOk();
        $this->actingAs($member)->post(route('task.attachments.store', [$workspace->slug, $project->slug, $task]), [
            'files' => [UploadedFile::fake()->create('blocked.txt', 1, 'text/plain')],
        ])->assertForbidden();
        $this->actingAs($member)->delete(route('task.attachments.destroy', [$workspace->slug, $project->slug, $task, $attachment]))->assertForbidden();
    }

    public function test_board_contains_contextual_upload_and_file_library_controls(): void
    {
        extract($this->context());

        $this->actingAs($owner)->get(route('board', [$workspace->slug, $project->slug]))
            ->assertOk()->assertSee('فایل‌های توضیحات')->assertSee('فایل‌های بارگذاری‌شده')
            ->assertSee('کتابخانه فایل‌های وظیفه')->assertSee('افزودن فایل')
            ->assertSee('handleTaskEscape($event)', false);
    }

    public function test_cycle_can_finish_and_carry_open_work_into_next_cycle(): void
    {
        extract($this->context());
        $completed = Task::create(['column_id' => $done->id, 'task_number' => 2, 'title' => 'کار تمام', 'position' => 1, 'completed_at' => now()]);

        $start = $this->actingAs($owner)->postJson(route('cycles.start', [$workspace->slug, $project->slug]), [
            'task_ids' => [$task->id, $completed->id],
        ])->assertCreated();

        $cycleId = $start->json('cycle.id');
        $this->actingAs($owner)->postJson(route('cycles.finish', [$workspace->slug, $project->slug, $cycleId]), [
            'carry_task_ids' => [$task->id], 'removed_task_ids' => [], 'start_next' => true,
        ])->assertOk()->assertJsonPath('cycle.status', 'completed')->assertJsonPath('nextCycle.number', 2);

        $this->assertDatabaseHas('cycle_task', ['cycle_id' => $cycleId, 'task_id' => $task->id, 'outcome' => 'carried']);
        $this->assertDatabaseHas('cycle_task', ['cycle_id' => $cycleId, 'task_id' => $completed->id, 'outcome' => 'completed']);
    }
}
