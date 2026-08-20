<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\WorkspaceContext;
use Illuminate\Http\Request;

class WorkspaceSearchController extends Controller
{
    public function __invoke(Request $request, string $workspace, WorkspaceContext $context)
    {
        $validated = $request->validate(['q' => ['required', 'string', 'min:1', 'max:100']]);
        $workspaceModel = $request->attributes->get('workspace');
        $projects = $context->visibleProjects($workspaceModel, $request->user());
        $query = $validated['q'];

        $projectResults = $projects->filter(fn ($project) => str_contains(mb_strtolower($project->name), mb_strtolower($query)))
            ->take(5)->map(fn ($project) => [
                'type' => 'project',
                'name' => $project->name,
                'subtitle' => $project->key,
                'url' => route('board', [$workspaceModel->slug, $project->slug], false),
            ]);

        $tasks = Task::query()
            ->whereHas('column', fn ($builder) => $builder->whereIn('project_id', $projects->pluck('id')))
            ->where('title', 'like', "%{$query}%")
            ->with('column.project')
            ->latest('updated_at')->limit(10)->get()
            ->map(fn (Task $task) => [
                'type' => 'task',
                'name' => $task->title,
                'subtitle' => $task->column->project->name.' · '.$task->display_id,
                'url' => route('board', [$workspaceModel->slug, $task->column->project->slug], false).'?task='.$task->id,
            ]);

        return response()->json($projectResults->concat($tasks)->values());
    }
}
