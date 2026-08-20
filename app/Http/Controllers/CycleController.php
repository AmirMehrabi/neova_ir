<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CycleController extends Controller
{
    public function configure(Request $request, string $workspace, string $project)
    {
        $workspaceModel = $request->attributes->get('workspace');
        abort_unless($workspaceModel->canManageMembers($request->user()), 403);
        $validated = $request->validate(['cycle_length_weeks' => ['nullable', 'integer', Rule::in([1, 2])]]);
        $projectModel = $request->attributes->get('project');
        $projectModel->update(['cycle_length_weeks' => $validated['cycle_length_weeks'] ?? null]);
        return response()->json(['cycleLengthWeeks' => $projectModel->cycle_length_weeks]);
    }

    public function start(Request $request, string $workspace, string $project)
    {
        $workspaceModel = $request->attributes->get('workspace');
        abort_unless($workspaceModel->canManageMembers($request->user()), 403);
        $projectModel = $request->attributes->get('project');
        abort_unless(in_array((int) $projectModel->cycle_length_weeks, [1, 2], true), 422, 'ابتدا طول چرخه را انتخاب کنید.');
        abort_if($projectModel->cycles()->where('status', 'active')->exists(), 422, 'این پروژه یک چرخه فعال دارد.');
        $validated = $request->validate(['task_ids' => ['nullable', 'array'], 'task_ids.*' => ['integer', 'distinct']]);
        $taskIds = collect($validated['task_ids'] ?? [])->map(fn ($id) => (int) $id)->values();
        $validIds = Task::whereIn('id', $taskIds)->whereHas('column', fn ($query) => $query->where('project_id', $projectModel->id))->pluck('id');
        abort_unless($validIds->count() === $taskIds->count(), 422, 'یک یا چند وظیفه به این پروژه تعلق ندارد.');

        $cycle = DB::transaction(function () use ($projectModel, $workspaceModel, $taskIds) {
            $number = (int) $projectModel->cycles()->lockForUpdate()->max('number') + 1;
            $start = now($workspaceModel->timezone)->startOfDay();
            $cycle = $projectModel->cycles()->create([
                'number' => $number, 'starts_on' => $start->toDateString(),
                'ends_on' => $start->addWeeks((int) $projectModel->cycle_length_weeks)->subDay()->toDateString(),
                'status' => 'active',
            ]);
            $cycle->tasks()->attach($taskIds->all(), ['outcome' => 'active']);
            return $cycle;
        });
        return response()->json(['cycle' => $this->data($cycle->load('tasks.column'))], 201);
    }

    public function finish(Request $request, string $workspace, string $project, Cycle $cycle)
    {
        $workspaceModel = $request->attributes->get('workspace');
        $projectModel = $request->attributes->get('project');
        abort_unless($workspaceModel->canManageMembers($request->user()), 403);
        abort_unless($cycle->project_id === $projectModel->id && $cycle->status === 'active', 404);
        $validated = $request->validate([
            'carry_task_ids' => ['nullable', 'array'], 'carry_task_ids.*' => ['integer', 'distinct'],
            'removed_task_ids' => ['nullable', 'array'], 'removed_task_ids.*' => ['integer', 'distinct'],
            'start_next' => ['nullable', 'boolean'],
        ]);
        $carry = collect($validated['carry_task_ids'] ?? [])->map(fn ($id) => (int) $id);
        $removed = collect($validated['removed_task_ids'] ?? [])->map(fn ($id) => (int) $id);
        abort_if($carry->intersect($removed)->isNotEmpty(), 422, 'یک وظیفه نمی‌تواند هم منتقل و هم حذف شود.');

        $result = DB::transaction(function () use ($cycle, $projectModel, $workspaceModel, $carry, $removed, $request) {
            $cycle->load('tasks.column');
            $open = $cycle->tasks->reject(fn (Task $task) => $task->column->workflow_role === 'done')->pluck('id');
            abort_unless($carry->merge($removed)->unique()->sort()->values()->all() === $open->sort()->values()->all(), 422, 'برای همه وظیفه‌های باز، انتقال یا حذف را مشخص کنید.');
            foreach ($cycle->tasks as $task) {
                $outcome = $task->column->workflow_role === 'done' ? 'completed' : ($carry->contains($task->id) ? 'carried' : 'removed');
                $cycle->tasks()->updateExistingPivot($task->id, ['outcome' => $outcome]);
            }
            $cycle->update(['status' => 'completed', 'completed_at' => now()]);
            $next = null;
            if ($request->boolean('start_next')) {
                $start = $cycle->ends_on->addDay();
                $next = $projectModel->cycles()->create([
                    'number' => $cycle->number + 1, 'starts_on' => $start->toDateString(),
                    'ends_on' => $start->copy()->addWeeks((int) $projectModel->cycle_length_weeks)->subDay()->toDateString(), 'status' => 'active',
                ]);
                $next->tasks()->attach($carry->all(), ['outcome' => 'active']);
            }
            return ['cycle' => $cycle->fresh('tasks'), 'next' => $next?->load('tasks')];
        });
        return response()->json(['cycle' => $this->data($result['cycle']), 'nextCycle' => $result['next'] ? $this->data($result['next']) : null]);
    }

    private function data(Cycle $cycle): array
    {
        return ['id' => $cycle->id, 'number' => $cycle->number, 'startsOn' => $cycle->starts_on->toDateString(), 'endsOn' => $cycle->ends_on->toDateString(), 'status' => $cycle->status, 'tasks' => $cycle->tasks->map(fn ($task) => ['id' => $task->id, 'title' => $task->title, 'outcome' => $task->pivot->outcome])->values()];
    }
}
