<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProject
{
    public function handle(Request $request, Closure $next): Response
    {
        $workspace = $request->attributes->get('workspace');
        $slug = $request->route('project');

        $project = Project::where('workspace_id', $workspace->id)
            ->where('slug', $slug)
            ->firstOrFail();

        $request->attributes->set('project', $project);

        return $next($request);
    }
}
