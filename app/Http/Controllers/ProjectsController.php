<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProjectsController extends Controller
{
    public function index(Request $request, string $workspace)
    {
        $workspaceModel = $request->attributes->get('workspace');
        $projects = $workspaceModel->projects()->with(['columns' => fn ($query) => $query->withCount('tasks')])->orderBy('name')->get()
            ->filter(fn ($project) => $project->canUserView($request->user(), $workspaceModel))->values();

        foreach ($projects as $project) {
            $project->setAttribute('active_tasks', $project->columns->where('workflow_role', 'active')->sum('tasks_count'));
            $project->setAttribute('open_tasks', $project->columns->where('workflow_role', '!=', 'done')->sum('tasks_count'));
        }

        return view('projects', [
            'workspace' => $workspaceModel,
            'projects' => $projects,
            'canManage' => $workspaceModel->canManageMembers($request->user()),
        ]);
    }
}
