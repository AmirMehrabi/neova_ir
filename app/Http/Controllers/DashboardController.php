<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        $workspaces = Workspace::with('owner', 'projects')
            ->whereIn('workspaces.id', $workspaceIds)
            ->get();

        return view('dashboard', compact('workspaces'));
    }

    public function workspace(Request $request, string $slug)
    {
        $workspace = $request->attributes->get('workspace');
        $projects = $workspace->projects()->with('columns')->get();

        return view('workspace', compact('workspace', 'projects'));
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

        return redirect()->route('workspace', $workspace->slug);
    }

    public function storeProject(Request $request)
    {
        $workspace = $request->attributes->get('workspace');

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

    public function destroyWorkspace(Request $request)
    {
        $workspace = $request->attributes->get('workspace');

        if (!$workspace->isOwnedBy($request->user())) {
            abort(403);
        }

        $workspace->delete();

        return redirect()->route('dashboard');
    }

    public function destroyProject(Request $request)
    {
        $project = $request->attributes->get('project');
        $project->delete();

        return redirect()->route('workspace', $project->workspace->slug);
    }
}
