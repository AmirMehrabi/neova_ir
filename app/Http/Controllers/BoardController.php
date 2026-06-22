<?php

namespace App\Http\Controllers;

use App\Models\ProjectColumn;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoardController extends Controller
{
    public function show(Request $request)
    {
        $project = $request->attributes->get('project');
        $workspace = $request->attributes->get('workspace');

        $columns = $project->columns()->with('tasks')->orderBy('position')->get();

        $members = $project->members()->orderBy('name')->get();
        $canEdit = $workspace->canEditBoards($request->user());

        $colColors = [
            'پس‌زمینه' => 'bg-[#94A3B8]',
            'در حال انجام' => 'bg-[#0069FF]',
            'بررسی' => 'bg-[#F59E0B]',
            'انجام شده' => 'bg-[#22C55E]',
        ];
        $colBadge = [
            'پس‌زمینه' => 'bg-[#F1F5F9] text-[#64748B]',
            'در حال انجام' => 'bg-[#E8F0FE] text-[#0069FF]',
            'بررسی' => 'bg-[#FEF3C7] text-[#D97706]',
            'انجام شده' => 'bg-[#DCFCE7] text-[#16A34A]',
        ];

        $columnsData = $columns->map(fn ($c) => [
            'id' => (string) $c->id,
            'title' => $c->title,
            'dotColor' => $colColors[$c->title] ?? 'bg-[#94A3B8]',
            'badgeClass' => $colBadge[$c->title] ?? 'bg-[#F1F5F9] text-[#64748B]',
            'tasks' => $c->tasks->map(fn ($t) => [
                'id' => $t->title,
                'dbId' => $t->id,
                'title' => $t->title,
                'description' => $t->description ?? '',
                'priority' => $t->priority,
                'assignees' => $t->assignees ?? [],
                'dueDate' => $t->due_date?->format('Y-m-d') ?? '',
                'tags' => $t->tags ?? [],
                'checklist' => $t->checklist ?? [],
                'comments' => $t->comments ?? [],
            ])->toArray(),
        ])->toArray();

        $membersData = $members->map(fn ($m) => $m->full_name)->values()->toArray();

        return view('board', compact('project', 'workspace', 'columns', 'members', 'columnsData', 'membersData', 'canEdit'));
    }

    public function storeTask(Request $request)
    {
        $request->validate([
            'column_id' => 'required|exists:project_columns,id',
            'title' => 'required|string|max:500',
            'assignees' => ['nullable', 'array'],
        ]);
        $this->validateAssignees($request);

        $column = ProjectColumn::findOrFail($request->column_id);
        $this->ensureColumnInCurrentProject($request, $column);
        $project = $column->project;
        $workspace = $project->workspace;

        $maxPosition = Task::where('column_id', $request->column_id)->max('position') ?? 0;
        $maxNum = DB::table('tasks')
            ->join('project_columns', 'tasks.column_id', '=', 'project_columns.id')
            ->where('project_columns.project_id', $project->id)
            ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(tasks.title FROM 5) AS UNSIGNED)), 0) as max_num')
            ->value('max_num') ?? 0;

        $task = Task::create([
            'column_id' => $request->column_id,
            'title' => $project->key.'-'.str_pad($maxNum + 1, 3, '0', STR_PAD_LEFT).' '.$request->title,
            'description' => $request->input('description', ''),
            'priority' => $request->input('priority', 'متوسط'),
            'due_date' => $request->input('due_date'),
            'assignees' => $request->input('assignees', []),
            'tags' => $request->input('tags', []),
            'position' => $maxPosition + 1,
        ]);

        return response()->json($task);
    }

    public function updateTask(Request $request, string $workspace, string $project, string $task)
    {
        $task = $this->findTaskInCurrentProject($request, $task);
        $this->ensureTaskInCurrentProject($request, $task);
        $this->validateAssignees($request, $task);

        $task->update($request->only([
            'title', 'description', 'priority', 'due_date',
            'assignees', 'tags', 'checklist', 'comments', 'column_id', 'position',
        ]));

        return response()->json($task);
    }

    public function destroyTask(Request $request, string $workspace, string $project, string $task)
    {
        $task = $this->findTaskInCurrentProject($request, $task);
        $this->ensureTaskInCurrentProject($request, $task);
        $task->delete();

        return response()->json(['success' => true]);
    }

    public function moveTask(Request $request, string $workspace, string $project, string $task)
    {
        $task = $this->findTaskInCurrentProject($request, $task);
        $this->ensureTaskInCurrentProject($request, $task);
        $request->validate([
            'column_id' => 'required|exists:project_columns,id',
            'position' => 'required|integer|min:0',
        ]);
        $targetColumn = ProjectColumn::findOrFail($request->column_id);
        $this->ensureColumnInCurrentProject($request, $targetColumn);

        DB::transaction(function () use ($request, $task) {
            $oldColumnId = (int) $task->column_id;
            $newColumnId = (int) $request->column_id;
            $newIndex = max(0, (int) $request->position);

            $columnIds = collect([$oldColumnId, $newColumnId])->unique()->values();
            $columns = Task::whereIn('column_id', $columnIds)
                ->orderBy('position')
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->groupBy('column_id');

            $sourceTasks = $columns->get($oldColumnId, collect())
                ->filter(fn (Task $candidate) => $candidate->id !== $task->id)
                ->values()
                ->all();

            $movingTask = $task;
            $movingTask->column_id = $newColumnId;

            if ($oldColumnId === $newColumnId) {
                $insertIndex = min($newIndex, count($sourceTasks));
                array_splice($sourceTasks, $insertIndex, 0, [$movingTask]);

                foreach ($sourceTasks as $index => $columnTask) {
                    $columnTask->update([
                        'column_id' => $oldColumnId,
                        'position' => $index + 1,
                    ]);
                }

                return;
            }

            $targetTasks = $columns->get($newColumnId, collect())->values()->all();
            $insertIndex = min($newIndex, count($targetTasks));
            array_splice($targetTasks, $insertIndex, 0, [$movingTask]);

            foreach ($sourceTasks as $index => $columnTask) {
                $columnTask->update([
                    'position' => $index + 1,
                ]);
            }

            foreach ($targetTasks as $index => $columnTask) {
                $columnTask->update([
                    'column_id' => $newColumnId,
                    'position' => $index + 1,
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function storeColumn(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:100',
        ]);

        abort_unless((int) $request->project_id === (int) $request->attributes->get('project')->id, 403);
        $maxPosition = ProjectColumn::where('project_id', $request->project_id)->max('position') ?? 0;

        $column = ProjectColumn::create([
            'project_id' => $request->project_id,
            'title' => $request->title,
            'position' => $maxPosition + 1,
        ]);

        return response()->json($column);
    }

    public function destroyColumn(Request $request, string $workspace, string $project, ProjectColumn $column)
    {
        $this->ensureColumnInCurrentProject($request, $column);
        $column->tasks()->delete();
        $column->delete();

        return response()->json(['success' => true]);
    }

    private function ensureTaskInCurrentProject(Request $request, Task $task): void
    {
        $task->loadMissing('column');
        abort_unless($task->column->project_id === $request->attributes->get('project')->id, 403);
    }

    private function findTaskInCurrentProject(Request $request, string $taskId): Task
    {
        $task = Task::query()->findOrFail($taskId);
        $this->ensureTaskInCurrentProject($request, $task);

        return $task;
    }

    private function ensureColumnInCurrentProject(Request $request, ProjectColumn $column): void
    {
        abort_unless($column->project_id === $request->attributes->get('project')->id, 403);
    }

    private function validateAssignees(Request $request, ?Task $task = null): void
    {
        if (! $request->has('assignees')) {
            return;
        }

        $request->validate(['assignees' => ['array']]);
        $allowed = $request->attributes->get('project')
            ->members()
            ->get()
            ->map(fn ($member) => $member->full_name)
            ->all();
        $allowed = array_unique(array_merge($allowed, $task?->assignees ?? []));
        $invalid = array_diff($request->input('assignees', []), $allowed);

        abort_if($invalid !== [], 422, 'یک یا چند مسئول انتخاب‌شده عضو تیم پروژه نیستند.');
    }
}
