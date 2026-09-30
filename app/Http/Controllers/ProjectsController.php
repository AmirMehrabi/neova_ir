<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProjectsController extends Controller
{
    public function index(Request $request, string $workspace)
    {
        $workspaceModel = $request->attributes->get('workspace');
        $archived = $request->boolean('archived');
        $projects = $workspaceModel->projects()->where('is_active', ! $archived)->with(['columns' => fn ($query) => $query->withCount('tasks')])->orderBy('name')->get()
            ->filter(fn ($project) => $project->canUserView($request->user(), $workspaceModel))->values();

        foreach ($projects as $project) {
            $project->setAttribute('active_tasks', $project->columns->where('workflow_role', 'active')->sum('tasks_count'));
            $project->setAttribute('open_tasks', $project->columns->where('workflow_role', '!=', 'done')->sum('tasks_count'));
        }

        return view('projects', [
            'workspace' => $workspaceModel,
            'projects' => $projects,
            'canManage' => $workspaceModel->canManageMembers($request->user()),
            'archived' => $archived,
        ]);
    }

    public function archive(Request $request)
    {
        $workspace = $request->attributes->get('workspace');
        $project = $request->attributes->get('project');
        abort_unless($workspace->canManageMembers($request->user()), 403);
        $validated = $request->validate(['is_active' => ['required', 'boolean']]);
        $project->update(['is_active' => $validated['is_active']]);
        $message = $project->is_active ? 'پروژه بازگردانده شد.' : 'پروژه بایگانی شد؛ وظیفه‌ها و فایل‌ها حفظ شدند.';

        return $request->expectsJson()
            ? response()->json(['message' => $message])
            : redirect()->route('projects.index', [$workspace->slug, 'archived' => ! $project->is_active])->with('status', $message);
    }
}
