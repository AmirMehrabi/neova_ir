<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectColumn;
use App\Models\Task;
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
        $activeWorkspaceRole = $activeWorkspace?->roleFor($user);

        if ($activeWorkspace) {
            $projects = Project::with([
                'columns' => fn ($query) => $query->withCount('tasks'),
            ])
                ->where('workspace_id', $activeWorkspace->id)
                ->get();

            foreach ($projects as $project) {
                $totalTasks = (int) $project->columns->sum('tasks_count');
                $doneTasks = (int) $project->columns
                    ->where('title', 'انجام شده')
                    ->sum('tasks_count');

                $project->setAttribute('total_tasks', $totalTasks);
                $project->setAttribute('done_tasks', $doneTasks);
                $project->setAttribute(
                    'progress_percentage',
                    $totalTasks > 0 ? (int) round(($doneTasks / $totalTasks) * 100) : 0
                );

                $stats['total_projects']++;
                $stats['total_tasks'] += $totalTasks;
                $stats['done_tasks'] += $doneTasks;
            }
        }

        return view('dashboard', compact('workspaces', 'activeWorkspace', 'activeWorkspaceRole', 'projects', 'stats'));
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

        return redirect()->route('dashboard', ['workspace' => $workspace->slug]);
    }

    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|min:1|max:100']);
        $query = $request->q;
        $user = $request->user();

        $ownedIds = $user->ownedWorkspaces()->pluck('workspaces.id');
        $memberIds = $user->workspaces()->pluck('workspaces.id');
        $workspaceIds = $ownedIds->merge($memberIds)->unique();

        $results = [];

        $workspaces = Workspace::whereIn('id', $workspaceIds)
            ->where('name', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn ($ws) => [
                'type' => 'workspace',
                'name' => $ws->name,
                'subtitle' => $ws->projects_count ?? $ws->projects()->count().' پروژه',
                'url' => route('dashboard', ['workspace' => $ws->slug]),
            ]);
        $results = $results->merge($workspaces);

        $projects = Project::whereIn('workspace_id', $workspaceIds)
            ->where('name', 'LIKE', "%{$query}%")
            ->with('workspace')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'type' => 'project',
                'name' => $p->name,
                'subtitle' => $p->workspace->name.' · '.$p->key,
                'url' => route('board', [$p->workspace->slug, $p->slug]),
            ]);
        $results = $results->merge($projects);

        $projectIds = Project::whereIn('workspace_id', $workspaceIds)->pluck('id');
        $columnIds = ProjectColumn::whereIn('project_id', $projectIds)->pluck('id');
        $tasks = Task::whereIn('column_id', $columnIds)
            ->where('title', 'LIKE', "%{$query}%")
            ->with('column.project.workspace')
            ->limit(5)
            ->get()
            ->map(fn ($t) => [
                'type' => 'task',
                'name' => $t->title,
                'subtitle' => $t->column->project->workspace->name.' · '.$t->column->project->name,
                'url' => route('board', [$t->column->project->workspace->slug, $t->column->project->slug]),
            ]);
        $results = $results->merge($tasks);

        return response()->json($results->values());
    }
}
