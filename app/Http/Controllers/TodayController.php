<?php

namespace App\Http\Controllers;

use App\Models\ProjectColumn;
use App\Models\Task;
use App\Models\TaskPlan;
use App\Services\TaskAssignmentService;
use App\Services\TaskData;
use App\Services\TaskWorkflowService;
use App\Services\TodayService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Project;
use App\Services\ProjectActivityLogger;
use App\Services\ProjectActivityNotifier;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class TodayController extends Controller
{
    public function index(Request $request, TodayService $today, TaskData $taskData)
    {
        $workspace = $request->attributes->get('workspace');
        $user = $request->user();
        $date = $today->date($workspace);
        $projectIds = $today->visibleProjectIds($workspace, $user);
        $canManageTeam = in_array($workspace->roleFor($user), ['owner', 'admin'], true);
        $requestedView = $request->query('view');
        if (in_array($requestedView, ['mine', 'team'], true)) {
            $selectedView = $requestedView === 'team' && ! $canManageTeam ? 'mine' : $requestedView;
            if ($user->today_view !== $selectedView) $user->update(['today_view' => $selectedView]);
        }
        $viewMode = ($requestedView ?? $user->today_view) === 'team' && $canManageTeam ? 'team' : 'mine';

        $plans = TaskPlan::query()
            ->where('user_id', $user->id)
            ->whereDate('planned_for', $date->toDateString())
            ->whereHas('task.column', fn ($query) => $query->whereIn('project_id', $projectIds))
            ->with(['task.column.project.workspace', 'task.assignedUsers'])
            ->orderBy('bucket')->orderBy('position')->get();

        $items = $plans->map(fn (TaskPlan $plan) => $taskData->make($plan->task, $user, $date));
        $blocked = $items->where('isBlocked', true)->values();
        $done = $items->filter(fn (array $item) => $item['column']['role'] === 'done')->values();
        $active = $items->reject(fn (array $item) => $item['isBlocked'] || $item['column']['role'] === 'done');

        $overdue = Task::query()
            ->whereHas('assignedUsers', fn ($query) => $query->whereKey($user->id))
            ->whereHas('column', fn ($query) => $query->whereIn('project_id', $projectIds)->where('workflow_role', '!=', 'done'))
            ->whereDate('due_date', '<', $date->toDateString())
            ->with(['column.project.workspace', 'assignedUsers'])
            ->get()->reject(fn (Task $task) => $items->contains('dbId', $task->id))
            ->map(fn (Task $task) => $taskData->make($task))->values();

        $projects = $workspace->projects()->with('columns')->get()
            ->filter(fn ($project) => $project->canUserView($user, $workspace))->values();
        $projectEligibility = $projects->mapWithKeys(fn (Project $project) => [
            $project->id => $project->eligibleAssignees()->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ]);

        $availableTasks = Task::query()
            ->whereHas('column', fn ($query) => $query->whereIn('project_id', $projectIds)->where('workflow_role', '!=', 'done'))
            ->with(['column.project.workspace', 'assignedUsers'])
            ->orderByDesc('updated_at')->limit(100)->get()
            ->reject(fn (Task $task) => $items->contains('dbId', $task->id))
            ->map(fn (Task $task) => $taskData->make($task) + ['eligibleUserIds' => $projectEligibility->get($task->column->project_id, [])])->values();

        $workspacePeople = collect([$workspace->owner])
            ->merge($workspace->members)
            ->unique('id')
            ->values();
        $teamPlans = TaskPlan::query()
            ->whereIn('user_id', $workspacePeople->pluck('id'))
            ->whereDate('planned_for', $date->toDateString())
            ->whereHas('task.column', fn ($query) => $query->whereIn('project_id', $projectIds))
            ->with(['task.column.project.workspace', 'task.assignedUsers'])
            ->orderBy('position')
            ->get()
            ->groupBy('user_id');
        $teamPulse = $workspacePeople->take(5)->map(function ($person) use ($teamPlans) {
            $plans = $teamPlans->get($person->id, collect());
            $focus = $plans->first(fn ($plan) => ! $plan->task->is_blocked && $plan->task->column->workflow_role !== 'done');
            $blocked = $plans->first(fn ($plan) => $plan->task->is_blocked);

            return [
                'name' => $person->full_name,
                'initials' => $person->initials,
                'avatar' => $person->avatar,
                'focus' => $blocked?->task->blocked_reason ?: $focus?->task->title,
                'project' => ($blocked ?: $focus)?->task->column->project->name,
                'blocked' => (bool) $blocked,
            ];
        });

        $teamDays = $canManageTeam ? $workspacePeople->map(function (User $person) use ($teamPlans, $taskData, $date) {
            $items = $teamPlans->get($person->id, collect())
                ->map(fn (TaskPlan $plan) => $taskData->make($plan->task, $person, $date));
            $blocked = $items->where('isBlocked', true)->values();
            $done = $items->filter(fn (array $item) => $item['column']['role'] === 'done')->values();
            $active = $items->reject(fn (array $item) => $item['isBlocked'] || $item['column']['role'] === 'done')->values();

            return [
                'id' => $person->id,
                'name' => $person->full_name,
                'initials' => $person->initials,
                'avatar' => $person->avatar,
                'mustTasks' => $active->filter(fn (array $item) => $item['plan']['bucket'] === 'must')->values(),
                'optionalTasks' => $active->filter(fn (array $item) => $item['plan']['bucket'] === 'optional')->values(),
                'blockedTasks' => $blocked,
                'doneTasks' => $done,
            ];
        })->values() : collect();

        return view('today', [
            'workspace' => $workspace,
            'todayDate' => $date,
            'mustTasks' => $active->filter(fn (array $item) => $item['plan']['bucket'] === 'must')->values(),
            'optionalTasks' => $active->filter(fn (array $item) => $item['plan']['bucket'] === 'optional')->values(),
            'blockedTasks' => $blocked,
            'doneTasks' => $done,
            'overdueTasks' => $overdue,
            'projects' => $projects,
            'availableTasks' => $availableTasks,
            'teamPulse' => $teamPulse,
            'canEdit' => $workspace->canEditBoards($user),
            'canManageTeam' => $canManageTeam,
            'viewMode' => $viewMode,
            'teamDays' => $teamDays,
            'workspacePeople' => $workspacePeople,
            'projectEligibility' => $projectEligibility,
        ]);
    }

    public function snapshot(Request $request, TodayService $today, TaskData $taskData)
    {
        $data = $this->index($request, $today, $taskData)->getData();

        return response()->json([
            'mustTasks' => $data['mustTasks'],
            'optionalTasks' => $data['optionalTasks'],
            'blockedTasks' => $data['blockedTasks'],
            'doneTasks' => $data['doneTasks'],
            'overdueTasks' => $data['overdueTasks'],
            'availableTasks' => $data['availableTasks'],
            'teamDays' => $data['teamDays'],
            'projects' => $data['projects']->map(fn ($project) => [
                'id' => $project->id,
                'name' => $project->name,
                'eligibleUserIds' => $data['projectEligibility']->get($project->id, []),
            ])->values(),
            'people' => $data['workspacePeople']->map(fn ($person) => [
                'id' => $person->id,
                'name' => $person->full_name,
            ])->values(),
            'generatedAt' => now()->toIso8601String(),
        ]);
    }

    public function quickCreate(
        Request $request,
        string $workspace,
        TodayService $today,
        TaskAssignmentService $assignments,
        ProjectActivityLogger $activityLogger,
        ProjectActivityNotifier $notifier,
        TaskData $taskData,
    ) {
        $workspaceModel = $request->attributes->get('workspace');
        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:500'],
            'when' => ['required', Rule::in(['unscheduled', 'today', 'tomorrow'])],
            'bucket' => ['nullable', Rule::in(['must', 'optional'])],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
        $target = $this->targetUser($request, $today, $validated['user_id'] ?? null);
        $project = Project::with('columns')->findOrFail($validated['project_id']);
        abort_unless($project->workspace_id === $workspaceModel->id && $project->canUserView($request->user(), $workspaceModel), 404);
        abort_unless($project->eligibleAssignees()->contains('id', $target->id), 422, 'این شخص نمی‌تواند مسئول وظیفه‌ای در این پروژه باشد.');
        $column = $project->columns->firstWhere('workflow_role', 'backlog') ?? $project->columns->first();
        abort_unless($column, 422, 'این پروژه ستون کاری ندارد.');

        $task = DB::transaction(function () use ($project, $column, $validated, $request, $assignments, $today, $workspaceModel, $target) {
            Project::whereKey($project->id)->lockForUpdate()->first();
            $maxNumber = Task::whereHas('column', fn ($query) => $query->where('project_id', $project->id))->max('task_number') ?? 0;
            $task = Task::create([
                'column_id' => $column->id,
                'task_number' => $maxNumber + 1,
                'title' => $validated['title'],
                'priority' => 'متوسط',
                'position' => ($column->tasks()->max('position') ?? 0) + 1,
            ]);
            $assignments->assign($task, $target);
            if ($validated['when'] !== 'unscheduled') {
                $date = $today->date($workspaceModel)->addDays($validated['when'] === 'tomorrow' ? 1 : 0);
                $today->plan($task, $target, $request->user(), $date->toDateString(), $validated['bucket'] ?? 'must');
            }
            return $task;
        });
        $activityLogger->taskCreated($task, $request->user());
        if ($validated['when'] === 'unscheduled') $notifier->taskCreated($task, $request->user());

        return response()->json(['task' => $taskData->make($task->fresh(), $target, $today->date($workspaceModel))], 201);
    }

    public function plan(Request $request, string $workspace, string $project, Task $task, TodayService $today, TaskData $taskData)
    {
        $this->ensureTask($request, $task);
        $validated = $request->validate([
            'planned_for' => ['required', 'date'],
            'bucket' => ['required', Rule::in(['must', 'optional'])],
            'position' => ['nullable', 'integer', 'min:1'],
            'assign_to_me' => ['nullable', 'boolean'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
        $target = $this->targetUser($request, $today, $validated['user_id'] ?? null);
        if ($request->boolean('assign_to_me') || ($target->isNot($request->user()) && ! $task->assignedUsers()->whereKey($target->id)->exists())) {
            app(TaskAssignmentService::class)->assign($task, $target);
        }
        $today->plan($task, $target, $request->user(), $validated['planned_for'], $validated['bucket'], $validated['position'] ?? null);
        return response()->json(['task' => $taskData->make($task->fresh(), $target, $today->date($request->attributes->get('workspace')))]);
    }

    public function unplan(Request $request, string $workspace, string $project, Task $task, TodayService $today)
    {
        $this->ensureTask($request, $task);
        $validated = $request->validate(['planned_for' => ['required', 'date'], 'user_id' => ['nullable', 'integer', 'exists:users,id']]);
        $target = $this->targetUser($request, $today, $validated['user_id'] ?? null);
        $today->unplan($task, $target, $request->user(), $validated['planned_for']);
        return response()->json(['success' => true]);
    }

    public function moveTomorrow(Request $request, string $workspace, string $project, Task $task, TodayService $today)
    {
        $this->ensureTask($request, $task);
        $workspaceModel = $request->attributes->get('workspace');
        $validated = $request->validate(['user_id' => ['nullable', 'integer', 'exists:users,id']]);
        $target = $this->targetUser($request, $today, $validated['user_id'] ?? null);
        $current = $today->date($workspaceModel);
        $tomorrow = $current->addDay();
        $today->move($task, $target, $request->user(), $current->toDateString(), $tomorrow->toDateString());

        return response()->json([
            'success' => true,
            'planned_for' => $tomorrow->toDateString(),
        ]);
    }

    public function reorder(Request $request, string $workspace, TodayService $today)
    {
        $validated = $request->validate([
            'task_ids' => ['required', 'array', 'min:1', 'max:100'],
            'task_ids.*' => ['required', 'integer', 'distinct'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
        $workspaceModel = $request->attributes->get('workspace');
        $target = $this->targetUser($request, $today, $validated['user_id'] ?? null);
        $date = $today->date($workspaceModel)->toDateString();
        $ids = collect($validated['task_ids'])->values();

        $plans = TaskPlan::query()
            ->where('user_id', $target->id)
            ->whereDate('planned_for', $date)
            ->whereIn('task_id', $ids)
            ->whereHas('task', fn ($query) => $query->where('is_blocked', false))
            ->whereHas('task.column', fn ($query) => $query->where('workflow_role', '!=', 'done'))
            ->whereHas('task.column.project', fn ($query) => $query->where('workspace_id', $workspaceModel->id))
            ->get()
            ->keyBy('task_id');

        abort_unless($plans->count() === $ids->count(), 422, 'فهرست اولویت‌ها معتبر نیست.');

        DB::transaction(function () use ($ids, $plans) {
            $ids->each(function ($taskId, $index) use ($plans) {
                $plans->get($taskId)->update(['bucket' => 'must', 'position' => $index + 1]);
            });
        });

        return response()->json(['success' => true, 'task_ids' => $ids]);
    }

    public function state(Request $request, string $workspace, string $project, Task $task, TaskWorkflowService $workflow, TaskAssignmentService $assignments, TaskData $taskData)
    {
        $this->ensureTask($request, $task);
        $validated = $request->validate([
            'action' => ['required', Rule::in(['assign_to_me', 'complete', 'reopen', 'block', 'unblock'])],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);
        if ($validated['action'] === 'block' && trim((string) ($validated['reason'] ?? '')) === '') {
            abort(422, 'دلیل انسداد را وارد کنید.');
        }
        $task = match ($validated['action']) {
            'assign_to_me' => tap($task, fn () => $assignments->assignToMe($task, $request->user()))->fresh(),
            'complete' => $workflow->complete($task, $request->user()),
            'reopen' => $workflow->reopen($task, $request->user()),
            'block' => $workflow->block($task, trim((string) ($validated['reason'] ?? '')), $request->user()),
            'unblock' => $workflow->unblock($task, $request->user()),
        };
        return response()->json(['task' => $taskData->make($task, $request->user(), app(TodayService::class)->date($request->attributes->get('workspace')))]);
    }

    private function ensureTask(Request $request, Task $task): void
    {
        $task->loadMissing('column');
        abort_unless($task->column->project_id === $request->attributes->get('project')->id, 404);
    }

    private function targetUser(Request $request, TodayService $today, mixed $targetId): User
    {
        $target = $targetId ? User::findOrFail((int) $targetId) : $request->user();
        abort_unless($today->canManage($request->user(), $target, $request->attributes->get('workspace')), 403);

        return $target;
    }
}
