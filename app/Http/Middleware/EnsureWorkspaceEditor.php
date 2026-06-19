<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceEditor
{
    public function handle(Request $request, Closure $next): Response
    {
        $workspace = $request->attributes->get('workspace');

        if (! $workspace || ! $workspace->canEditBoards($request->user())) {
            abort(403);
        }

        return $next($request);
    }
}
