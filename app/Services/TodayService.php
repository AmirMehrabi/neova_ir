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

    public function plan(Task $task, User $user, string $date, string $bucket = 'must', ?int $position = null): TaskPlan
    {
        $this->assignments->backfill($task);
        abort_unless($task->assignedUsers()->whereKey($user->id)->exists(), 422, 'ابتدا وظیفه را به خودتان اختصاص دهید.');

        return DB::transaction(function () use ($task, $user, $date, $bucket, $position) {
            $position ??= (int) TaskPlan::where('user_id', $user->id)
                ->whereDate('planned_for', $date)->where('bucket', $bucket)->max('position') + 1;
            $plan = TaskPlan::updateOrCreate(
                ['task_id' => $task->id, 'user_id' => $user->id, 'planned_for' => $date],
                ['bucket' => $bucket, 'position' => max(1, $position)],
            );
            $this->activityLogger->taskPlanned($task, $user, $date, $bucket);
            return $plan;
        });
    }

    public function unplan(Task $task, User $user, string $date): void
    {
        $deleted = TaskPlan::where('task_id', $task->id)->where('user_id', $user->id)
            ->whereDate('planned_for', $date)->delete();
        if ($deleted) $this->activityLogger->taskUnplanned($task, $user, $date);
    }

    public function move(Task $task, User $user, string $fromDate, string $toDate): TaskPlan
    {
        return DB::transaction(function () use ($task, $user, $fromDate, $toDate) {
            $current = TaskPlan::query()
                ->where('task_id', $task->id)
                ->where('user_id', $user->id)
                ->whereDate('planned_for', $fromDate)
                ->lockForUpdate()
                ->firstOrFail();

            $bucket = $current->bucket;
            $current->delete();
            $this->activityLogger->taskUnplanned($task, $user, $fromDate);

            return $this->plan($task, $user, $toDate, $bucket);
        });
    }
}
