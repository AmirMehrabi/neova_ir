<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Services\TodayService;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request, string $workspace, TodayService $today)
    {
        $workspaceModel = $request->attributes->get('workspace');
        $date = $today->date($workspaceModel);
        $projectIds = $today->visibleProjectIds($workspaceModel, $request->user());
        $startUtc = $date->utc();
        $endUtc = $date->endOfDay()->utc();

        $tasks = Task::query()
            ->whereHas('column', fn ($query) => $query->whereIn('project_id', $projectIds))
            ->where(function ($query) use ($date, $startUtc, $endUtc) {
                $query->whereHas('plans', fn ($plans) => $plans->whereDate('planned_for', $date->toDateString()))
                    ->orWhereHas('column', fn ($column) => $column->where('workflow_role', 'active'))
                    ->orWhere('is_blocked', true)
                    ->orWhereBetween('completed_at', [$startUtc, $endUtc]);
            })
            ->with(['column.project', 'assignedUsers', 'plans' => fn ($query) => $query->whereDate('planned_for', $date->toDateString())])
            ->get();

        $members = collect([$workspaceModel->owner])->merge($workspaceModel->members()->orderBy('name')->get())->unique('id')->values();
        $team = $members->map(function (User $member) use ($tasks, $startUtc, $endUtc) {
            $memberTasks = $tasks->filter(fn (Task $task) => $task->assignedUsers->contains('id', $member->id));
            $map = fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'project' => $task->column->project->name,
                'reason' => $task->blocked_reason,
            ];
            $blocked = $memberTasks->where('is_blocked', true)->map($map)->values();
            $done = $memberTasks->filter(fn (Task $task) => $task->completed_at?->betweenIncluded($startUtc, $endUtc))->map($map)->values();
            $active = $memberTasks->reject(fn (Task $task) => $task->is_blocked || $done->contains('id', $task->id))
                ->filter(fn (Task $task) => $task->column->workflow_role === 'active' || $task->plans->where('user_id', $member->id)->isNotEmpty())
                ->unique('id')->map($map)->values();

            return [
                'id' => $member->id,
                'name' => $member->full_name,
                'initials' => $member->initials,
                'avatar' => $member->avatar,
                'active' => $active,
                'blocked' => $blocked,
                'done' => $done,
            ];
        });

        $projects = $workspaceModel->projects()->get()->filter(fn ($project) => $project->canUserView($request->user(), $workspaceModel))->values();

        return view('team', compact('workspaceModel', 'projects', 'team', 'date'))->with('workspace', $workspaceModel);
    }
}
