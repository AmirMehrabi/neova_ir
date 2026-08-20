<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Workspace;
use App\Services\WorkspaceContext;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, WorkspaceContext $context)
    {
        $user = $request->user();
        $workspace = $context->resolve($user);

        return $workspace
            ? redirect()->route('today', $workspace->slug)
            : view('dashboard');
    }

    public function storeWorkspace(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $workspace = Workspace::create([
            'owner_id' => $request->user()->id,
            'name' => $request->name,
        ]);

        return redirect()->route('today', $workspace->slug);
    }

    public function storeProject(Request $request, string $workspaceSlug)
    {
        $workspace = Workspace::where('slug', $workspaceSlug)->firstOrFail();

        if (! $workspace->hasMember($request->user())
            || ! in_array($workspace->roleFor($request->user()), ['owner', 'admin'], true)) {
            abort(403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'key' => ['nullable', 'string', 'max:10', 'regex:/^[A-Z]+$/'],
        ]);

        $project = Project::create([
            'workspace_id' => $workspace->id,
            'name' => $request->name,
            'key' => strtoupper($request->input('key', '')),
        ]);

        $defaultColumns = [
            ['title' => 'پس‌زمینه', 'position' => 0, 'color' => '#94A3B8', 'workflow_role' => 'backlog'],
            ['title' => 'آماده', 'position' => 1, 'color' => '#64748B', 'workflow_role' => 'ready'],
            ['title' => 'در حال انجام', 'position' => 2, 'color' => '#0069FF', 'workflow_role' => 'active'],
            ['title' => 'انجام شده', 'position' => 3, 'color' => '#22C55E', 'workflow_role' => 'done'],
        ];

        foreach ($defaultColumns as $col) {
            $project->columns()->create($col);
        }

        return redirect()->route('board', [$workspace->slug, $project->slug]);
    }

    public function destroyWorkspace(Request $request, string $slug)
    {
        $workspace = Workspace::where('slug', $slug)->firstOrFail();

        if (! $workspace->isOwnedBy($request->user())) {
            abort(403);
        }

        $workspace->delete();

        return redirect()->route('dashboard');
    }

    public function destroyProject(Request $request, string $workspaceSlug, string $projectSlug)
    {
        $workspace = Workspace::where('slug', $workspaceSlug)->firstOrFail();

        if (! $workspace->hasMember($request->user())
            || ! in_array($workspace->roleFor($request->user()), ['owner', 'admin'], true)) {
            abort(403);
        }

        $project = Project::where('workspace_id', $workspace->id)->where('slug', $projectSlug)->firstOrFail();
        $project->delete();

        return redirect()->route('projects.index', $workspace->slug);
    }

}
