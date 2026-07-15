<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectColumn;
use App\Models\Task;
use App\Models\User;

class ProjectActivityLogger
{
    public function log(
        Project $project,
        User $actor,
        string $kind,
        string $message,
        ?Task $task = null,
        ?ProjectColumn $column = null,
        ?array $metadata = null,
        ?string $subject = null,
    ): ProjectActivity {
        return ProjectActivity::create([
            'project_id' => $project->id,
            'actor_id' => $actor->id,
            'task_id' => $task?->id,
            'column_id' => $column?->id,
            'kind' => $kind,
            'subject' => $subject ?? $task?->title ?? $column?->title,
            'message' => $message,
            'metadata' => $metadata,
        ]);
    }

    public function taskCreated(Task $task, User $actor): void
    {
        $task->loadMissing('column.project');
        $project = $task->column->project;
        $this->log($project, $actor, 'task_created', "{$actor->full_name} وظیفه «{$task->title}» را ایجاد کرد.", $task, $task->column);
        $this->recordTaskChanges($task, $actor, $this->emptyTaskState(), $this->taskState($task), skip: ['title']);
    }

    public function taskUpdated(Task $task, array $before, User $actor): void
    {
        $task->loadMissing('column.project');
        $this->recordTaskChanges($task, $actor, $before, $this->taskState($task));
    }

    public function taskDeleted(Task $task, User $actor): void
    {
        $task->loadMissing('column.project');
        $this->log($task->column->project, $actor, 'task_deleted', "{$actor->full_name} وظیفه «{$task->title}» را حذف کرد.", null, $task->column, [
            'task_id' => $task->id,
            'title' => $task->title,
        ], $task->title);
    }

    public function taskMoved(Task $task, string $fromColumn, string $toColumn, User $actor): void
    {
        $task->loadMissing('column.project');
        $this->log($task->column->project, $actor, 'task_moved', "{$actor->full_name} وظیفه «{$task->title}» را از «{$fromColumn}» به «{$toColumn}» منتقل کرد.", $task, $task->column, [
            'from' => $fromColumn,
            'to' => $toColumn,
        ]);
    }

    public function commentAdded(Task $task, array $comment, User $actor): void
    {
        $task->loadMissing('column.project');
        $this->log($task->column->project, $actor, 'task_comment_added', "{$actor->full_name} در وظیفه «{$task->title}» گفتگو اضافه کرد.", $task, $task->column, [
            'comment' => $comment['text'] ?? '',
        ]);
    }

    public function projectChanged(Project $project, array $changes, User $actor): void
    {
        foreach ($changes as $field => [$before, $after]) {
            $labels = ['name' => 'نام پروژه', 'key' => 'کلید پروژه', 'description' => 'توضیحات پروژه', 'board_style' => 'سبک تخته', 'custom_tags' => 'برچسب‌های پروژه'];
            $this->log($project, $actor, 'project_'.$field.'_changed', "{$actor->full_name} {$labels[$field]} را تغییر داد.", metadata: ['field' => $field, 'before' => $before, 'after' => $after]);
        }
    }

    public function columnChanged(ProjectColumn $column, User $actor, string $kind, string $message, ?array $metadata = null): void
    {
        $column->loadMissing('project');
        $this->log($column->project, $actor, $kind, $message, column: $column, metadata: $metadata);
    }

    public function memberChanged(Project $project, User $member, User $actor, bool $added): void
    {
        $verb = $added ? 'اضافه کرد' : 'حذف کرد';
        $kind = $added ? 'project_member_added' : 'project_member_removed';
        $this->log($project, $actor, $kind, "{$actor->full_name} عضو «{$member->full_name}» را {$verb}.", metadata: ['member_id' => $member->id, 'member_name' => $member->full_name]);
    }

    private function recordTaskChanges(Task $task, User $actor, array $before, array $after, array $skip = []): void
    {
        $project = $task->column->project;
        $title = $task->title;
        $compare = [
            'description' => ['توضیحات', 'task_description_changed'],
            'priority' => ['اولویت', 'task_priority_changed'],
            'due_date' => ['سررسید', 'task_due_date_changed'],
        ];

        foreach ($compare as $field => [$label, $kind]) {
            if (in_array($field, $skip, true) || ($before[$field] ?? null) == ($after[$field] ?? null)) continue;
            $old = $before[$field] ?? null;
            $new = $after[$field] ?? null;
            $action = $field === 'description' ? ($old ? 'تغییر داد' : 'اضافه کرد') : ($old ? 'تغییر داد' : 'تعیین کرد');
            $actualKind = $field === 'description' && ! $new ? 'task_description_removed' : $kind;
            $this->log($project, $actor, $actualKind, "{$actor->full_name} {$label} وظیفه «{$title}» را {$action}.", $task, $task->column, ['field' => $field, 'before' => $old, 'after' => $new]);
        }

        foreach (['assignees' => 'مسئول', 'tags' => 'برچسب'] as $field => $label) {
            $old = collect($before[$field] ?? []);
            $new = collect($after[$field] ?? []);
            foreach ($new->diff($old) as $value) $this->log($project, $actor, "task_{$field}_added", "{$actor->full_name} {$label} «{$value}» را به وظیفه «{$title}» اضافه کرد.", $task, $task->column, ['value' => $value]);
            foreach ($old->diff($new) as $value) $this->log($project, $actor, "task_{$field}_removed", "{$actor->full_name} {$label} «{$value}» را از وظیفه «{$title}» حذف کرد.", $task, $task->column, ['value' => $value]);
        }

        if (! in_array('checklist', $skip, true)) {
            $oldChecklist = collect($before['checklist'] ?? [])->keyBy('text');
            $newChecklist = collect($after['checklist'] ?? [])->keyBy('text');
            foreach ($newChecklist->diffKeys($oldChecklist) as $item) {
                $this->log($project, $actor, 'task_checklist_item_added', "{$actor->full_name} آیتم «{$item['text']}» را به چک‌لیست «{$title}» اضافه کرد.", $task, $task->column, ['item' => $item]);
            }
            foreach ($oldChecklist->diffKeys($newChecklist) as $item) {
                $this->log($project, $actor, 'task_checklist_item_removed', "{$actor->full_name} آیتم «{$item['text']}» را از چک‌لیست «{$title}» حذف کرد.", $task, $task->column, ['item' => $item]);
            }
            foreach ($newChecklist->intersectByKeys($oldChecklist) as $text => $item) {
                $oldItem = $oldChecklist[$text];
                if (($oldItem['done'] ?? false) == ($item['done'] ?? false)) continue;
                $kind = ($item['done'] ?? false) ? 'task_checklist_item_completed' : 'task_checklist_item_reopened';
                $verb = ($item['done'] ?? false) ? 'تکمیل کرد' : 'دوباره باز کرد';
                $this->log($project, $actor, $kind, "{$actor->full_name} آیتم «{$text}» در چک‌لیست «{$title}» را {$verb}.", $task, $task->column, ['item' => $item]);
            }
        }

        if (! in_array('comments', $skip, true) && count($after['comments'] ?? []) > count($before['comments'] ?? [])) {
            $this->log($project, $actor, 'task_comment_added', "{$actor->full_name} در وظیفه «{$title}» گفتگو اضافه کرد.", $task, $task->column);
        }
    }

    private function taskState(Task $task): array
    {
        return $task->only(['title', 'description', 'priority', 'due_date', 'assignees', 'tags', 'checklist', 'comments', 'column_id']);
    }

    private function emptyTaskState(): array
    {
        return ['title' => null, 'description' => null, 'priority' => null, 'due_date' => null, 'assignees' => [], 'tags' => [], 'checklist' => [], 'comments' => [], 'column_id' => null];
    }
}
