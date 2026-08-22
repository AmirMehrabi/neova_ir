<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskPlan;
use App\Models\User;
use App\Models\Workspace;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TodayService
{
    public function __construct(
        private TaskAssignmentService $assignments,
        private ProjectActivityLogger $activityLogger,
        private ProjectActivityNotifier $notifier,
    ) {}

    public function date(Workspace $workspace): CarbonImmutable
    {
        return CarbonImmutable::now($workspace->timezone ?: 'Asia/Tehran')->startOfDay();
    }

    public function visibleProjectIds(Workspace $workspace, User $user): Collection
    {
        return $workspace->projects()->get()
            ->filter(fn (Project $project) => $project->canUserView($user, $workspace))
            ->pluck('id')->values();
    }

    public function canManage(User $actor, User $target, Workspace $workspace): bool
    {
        if (! $workspace->hasMember($target)) return false;

        return $actor->is($target) || in_array($workspace->roleFor($actor), ['owner', 'admin'], true);
    }

    public function plan(Task $task, User $target, User $actor, string $date, string $bucket = 'must', ?int $position = null): TaskPlan
    {
        $this->assignments->backfill($task);
        abort_unless($this->assignments->eligible($task)->contains('id', $target->id), 422, 'این شخص نمی‌تواند مسئول این وظیفه باشد.');
        abort_unless($task->assignedUsers()->whereKey($target->id)->exists(), 422, 'ابتدا وظیفه را به این شخص اختصاص دهید.');

        $plan = DB::transaction(function () use ($task, $target, $actor, $date, $bucket, $position) {
            $position ??= (int) TaskPlan::where('user_id', $target->id)
                ->whereDate('planned_for', $date)->where('bucket', $bucket)->max('position') + 1;
            $plan = TaskPlan::updateOrCreate(
                ['task_id' => $task->id, 'user_id' => $target->id, 'planned_for' => $date],
                ['bucket' => $bucket, 'position' => max(1, $position)],
            );
            $this->activityLogger->taskPlanned($task, $actor, $target, $date, $bucket);
            return $plan;
        });
        $this->notifier->todayPlanChanged($task, $actor, $target, 'planned', $date);
        return $plan;
    }

    public function unplan(Task $task, User $target, User $actor, string $date): void
    {
        $deleted = TaskPlan::where('task_id', $task->id)->where('user_id', $target->id)
            ->whereDate('planned_for', $date)->delete();
        if ($deleted) {
            $this->activityLogger->taskUnplanned($task, $actor, $target, $date);
            $this->notifier->todayPlanChanged($task, $actor, $target, 'unplanned', $date);
        }
    }

    public function move(Task $task, User $target, User $actor, string $fromDate, string $toDate): TaskPlan
    {
        $plan = DB::transaction(function () use ($task, $target, $actor, $fromDate, $toDate) {
            $current = TaskPlan::query()
                ->where('task_id', $task->id)
                ->where('user_id', $target->id)
                ->whereDate('planned_for', $fromDate)
                ->lockForUpdate()
                ->firstOrFail();

            $bucket = $current->bucket;
            $current->delete();
            $position = (int) TaskPlan::where('user_id', $target->id)->whereDate('planned_for', $toDate)->where('bucket', $bucket)->max('position') + 1;
            $plan = TaskPlan::create(['task_id' => $task->id, 'user_id' => $target->id, 'planned_for' => $toDate, 'bucket' => $bucket, 'position' => $position]);
            $this->activityLogger->taskPlanMoved($task, $actor, $target, $fromDate, $toDate);
            return $plan;
        });
        $this->notifier->todayPlanChanged($task, $actor, $target, 'moved', $toDate);
        return $plan;
    }
}
