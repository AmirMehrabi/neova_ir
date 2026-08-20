<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WorkspaceHomeController extends Controller
{
    public function board(Request $request, string $workspace)
    {
        $workspaceModel = $request->attributes->get('workspace');
        $projects = $workspaceModel->projects()->where('is_active', true)->orderBy('name')->get()
            ->filter(fn ($project) => $project->canUserView($request->user(), $workspaceModel));
        $lastId = $request->session()->get("last_project.{$workspaceModel->id}");
        $project = $projects->firstWhere('id', $lastId) ?? $projects->first();

        return $project
            ? redirect()->route('board', [$workspaceModel->slug, $project->slug])
            : redirect()->route('projects.index', $workspaceModel->slug);
    }
}
