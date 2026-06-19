<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspace
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('workspace');
        $workspace = Workspace::where('slug', $slug)->firstOrFail();

        if (!$workspace->hasMember($request->user())) {
            abort(403);
        }

        $request->attributes->set('workspace', $workspace);

        return $next($request);
    }
}
