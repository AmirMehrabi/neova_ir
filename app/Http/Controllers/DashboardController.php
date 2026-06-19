<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->ownedWorkspaces()->count() === 0 && $user->workspaces()->count() === 0) {
            Workspace::create([
                'owner_id' => $user->id,
                'name' => 'فضای کاری من',
            ]);
        }

        $ownedIds = $user->ownedWorkspaces()->pluck('workspaces.id');
        $memberIds = $user->workspaces()->pluck('workspaces.id');
        $workspaceIds = $ownedIds->merge($memberIds)->unique();

        $workspaces = Workspace::with(['projects' => function ($q) {
            $q->withCount('columns');
        }])
            ->withCount('projects')
            ->whereIn('workspaces.id', $workspaceIds)
            ->get();

        $activeSlug = $request->query('workspace');
        $activeWorkspace = $workspaces->firstWhere('slug', $activeSlug) ?? $workspaces->first();

        $projects = collect();
        $stats = ['total_projects' => 0, 'total_tasks' => 0, 'done_tasks' => 0];

        if ($activeWorkspace) {
            $projects = Project::with('columns')
                ->where('workspace_id', $activeWorkspace->id)
                ->get();

            foreach ($projects as $project) {
                $stats['total_projects']++;
                foreach ($project->columns as $column) {
                    $taskCount = $column->tasks()->count();
                    $stats['total_tasks'] += $taskCount;
                    if ($column->title === 'انجام شده') {
                        $stats['done_tasks'] += $taskCount;
                    }
                }
            }
        }

        return view('dashboard', compact('workspaces', 'activeWorkspace', 'projects', 'stats'));
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

        return redirect()->route('dashboard', ['workspace' => $workspace->slug]);
    }

    public function storeProject(Request $request, string $workspaceSlug)
    {
        $workspace = Workspace::where('slug', $workspaceSlug)->firstOrFail();

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
            ['title' => 'پس‌زمینه', 'position' => 0, 'color' => '#94A3B8'],
            ['title' => 'در حال انجام', 'position' => 1, 'color' => '#0069FF'],
            ['title' => 'بررسی', 'position' => 2, 'color' => '#F59E0B'],
            ['title' => 'انجام شده', 'position' => 3, 'color' => '#22C55E'],
        ];

        foreach ($defaultColumns as $col) {
            $project->columns()->create($col);
        }

        return redirect()->route('board', [$workspace->slug, $project->slug]);
    }

    public function destroyWorkspace(Request $request, string $slug)
    {
        $workspace = Workspace::where('slug', $slug)->firstOrFail();

        if (!$workspace->isOwnedBy($request->user())) {
            abort(403);
        }

        $workspace->delete();

        return redirect()->route('dashboard');
    }

    public function destroyProject(Request $request, string $workspaceSlug, string $projectSlug)
    {
        $workspace = Workspace::where('slug', $workspaceSlug)->firstOrFail();
        $project = Project::where('workspace_id', $workspace->id)->where('slug', $projectSlug)->firstOrFail();
        $project->delete();

        return redirect()->route('dashboard', ['workspace' => $workspace->slug]);
    }
}
