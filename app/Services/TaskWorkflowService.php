<?php

namespace App\Services;

use App\Models\ProjectColumn;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskWorkflowService
{
    public function __construct(
        private ProjectActivityNotifier $notifier,
        private ProjectActivityLogger $activityLogger,
    ) {}

    public function move(Task $task, ProjectColumn $target, int $position, User $actor): Task
    {
        $task->loadMissing('column.project');
        abort_unless($target->project_id === $task->column->project_id, 422, 'ستون مقصد به این پروژه تعلق ندارد.');

        $source = $task->column;
        $wasDone = $source->workflow_role === 'done';
        $willBeDone = $target->workflow_role === 'done';

        DB::transaction(function () use ($task, $target, $position, $wasDone, $willBeDone) {
            $oldColumnId = (int) $task->column_id;
            $newColumnId = (int) $target->id;
            $columns = Task::whereIn('column_id', [$oldColumnId, $newColumnId])
                ->orderBy('position')->orderBy('id')->lockForUpdate()->get()->groupBy('column_id');

            $sourceTasks = $columns->get($oldColumnId, collect())->reject(fn (Task $item) => $item->id === $task->id)->values()->all();
            $targetTasks = $oldColumnId === $newColumnId
                ? $sourceTasks
                : $columns->get($newColumnId, collect())->values()->all();
            $insertAt = min(max(0, $position), count($targetTasks));
            array_splice($targetTasks, $insertAt, 0, [$task]);

            if ($oldColumnId !== $newColumnId) {
                foreach ($sourceTasks as $index => $item) $item->update(['position' => $index + 1]);
            }

            foreach ($targetTasks as $index => $item) {
                $changes = ['column_id' => $newColumnId, 'position' => $index + 1];
                if ($item->id === $task->id) {
                    if (! $wasDone && $willBeDone) $changes += ['completed_at' => now(), 'is_blocked' => false, 'blocked_reason' => null];
                    if ($wasDone && ! $willBeDone) $changes['completed_at'] = null;
                }
                $item->update($changes);
            }
        });

        $task = $task->fresh(['column.project', 'assignedUsers']);
        $this->notifier->taskMoved($task, $actor, $target->title);
        $this->activityLogger->taskMoved($task, $source->title, $target->title, $actor);
        if (! $wasDone && $willBeDone) $this->activityLogger->taskCompleted($task, $actor);
        if ($wasDone && ! $willBeDone) $this->activityLogger->taskReopened($task, $actor);

        return $task;
    }

    public function complete(Task $task, User $actor): Task
    {
        $task->loadMissing('column.project.columns');
        if ($task->column->workflow_role === 'done') {
            if (! $task->completed_at) $task->update(['completed_at' => now(), 'is_blocked' => false, 'blocked_reason' => null]);
            return $task->fresh(['column.project', 'assignedUsers']);
        }
        $target = $task->column->project->columns->firstWhere('workflow_role', 'done');
        abort_unless($target, 422, 'برای این پروژه ستون انجام‌شده تعریف نشده است.');

        return $this->move($task, $target, $target->tasks()->count(), $actor);
    }

    public function reopen(Task $task, User $actor): Task
    {
        $task->loadMissing('column.project.columns');
        $columns = $task->column->project->columns;
        $target = $columns->firstWhere('workflow_role', 'ready')
            ?? $columns->firstWhere('workflow_role', 'backlog')
            ?? $columns->first(fn (ProjectColumn $column) => $column->workflow_role !== 'done');
        abort_unless($target, 422, 'ستون مناسبی برای بازکردن دوباره وظیفه وجود ندارد.');

        return $this->move($task, $target, $target->tasks()->count(), $actor);
    }

    public function block(Task $task, string $reason, User $actor): Task
    {
        $task->loadMissing('column.project');
        abort_if($task->column->workflow_role === 'done', 422, 'وظیفه انجام‌شده را نمی‌توان مسدود کرد.');
        $task->update(['is_blocked' => true, 'blocked_reason' => $reason]);
        $this->activityLogger->taskBlocked($task, $actor, $reason);
        return $task->fresh(['column.project', 'assignedUsers']);
    }

    public function unblock(Task $task, User $actor): Task
    {
        $task->update(['is_blocked' => false, 'blocked_reason' => null]);
        $this->activityLogger->taskUnblocked($task, $actor);
        return $task->fresh(['column.project', 'assignedUsers']);
    }
}
