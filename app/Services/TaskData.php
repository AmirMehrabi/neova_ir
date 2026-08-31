<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Carbon\CarbonInterface;

class TaskData
{
    public function __construct(private readonly TaskAttachmentData $attachmentData) {}

    public function make(Task $task, ?User $viewer = null, ?CarbonInterface $plannedFor = null): array
    {
        $task->loadMissing(['column.project.workspace', 'assignedUsers', 'attachments.uploader']);
        $plan = $plannedFor && $viewer
            ? $task->plans()->where('user_id', $viewer->id)->whereDate('planned_for', $plannedFor->toDateString())->first()
            : null;

        $workspace = $task->column->project->workspace;
        $project = $task->column->project;
        $attachments = $task->attachments->map(fn ($attachment) => $this->attachmentData->make($attachment, $workspace->slug, $project->slug))->values();
        $comments = collect($task->comments ?? [])->map(function ($comment) use ($attachments) {
            $comment['attachments'] = $attachments->where('context', 'comment')->where('commentId', $comment['id'] ?? null)->values()->all();

            return $comment;
        })->values()->all();

        return [
            'id' => $task->display_id,
            'dbId' => $task->id,
            'title' => $task->title,
            'description' => $task->description ?? '',
            'priority' => $task->priority,
            'dueDate' => $task->due_date?->format('Y-m-d') ?? '',
            'isBlocked' => $task->is_blocked,
            'blockedReason' => $task->blocked_reason,
            'completedAt' => $task->completed_at?->toIso8601String(),
            'assignees' => $task->assignedUsers->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->full_name,
                'initials' => $user->initials,
                'avatar' => $user->avatar,
            ])->values()->all(),
            'legacyAssignees' => $task->assignees ?? [],
            'tags' => $task->tags ?? [],
            'checklist' => $task->checklist ?? [],
            'comments' => $comments,
            'attachments' => $attachments->all(),
            'column' => [
                'id' => $task->column->id,
                'title' => $task->column->title,
                'role' => $task->column->workflow_role,
            ],
            'project' => [
                'id' => $task->column->project->id,
                'name' => $task->column->project->name,
                'slug' => $task->column->project->slug,
                'key' => $task->column->project->key,
            ],
            'plan' => $plan ? [
                'date' => $plan->planned_for->format('Y-m-d'),
                'bucket' => $plan->bucket,
                'position' => $plan->position,
            ] : null,
        ];
    }
}
