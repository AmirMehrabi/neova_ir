<?php

namespace App\Http\Controllers;

use App\Models\Project;
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

        $members = $workspace->members()->get();
        $members->push($workspace->owner);
        $members = $members->unique('id');

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

        $columnsData = $columns->map(fn($c) => [
            'id' => (string) $c->id,
            'title' => $c->title,
            'dotColor' => $colColors[$c->title] ?? 'bg-[#94A3B8]',
            'badgeClass' => $colBadge[$c->title] ?? 'bg-[#F1F5F9] text-[#64748B]',
            'tasks' => $c->tasks->map(fn($t) => [
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

        $membersData = $members->map(fn($m) => $m->full_name)->values()->toArray();

        return view('board', compact('project', 'workspace', 'columns', 'members', 'columnsData', 'membersData'));
    }

    public function storeTask(Request $request)
    {
        $request->validate([
            'column_id' => 'required|exists:project_columns,id',
            'title' => 'required|string|max:500',
        ]);

        $column = ProjectColumn::findOrFail($request->column_id);
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
            'title' => $project->key . '-' . str_pad($maxNum + 1, 3, '0', STR_PAD_LEFT) . ' ' . $request->title,
            'description' => $request->input('description', ''),
            'priority' => $request->input('priority', 'متوسط'),
            'due_date' => $request->input('due_date'),
            'assignees' => $request->input('assignees', []),
            'tags' => $request->input('tags', []),
            'position' => $maxPosition + 1,
        ]);

        return response()->json($task);
    }

    public function updateTask(Request $request, Task $task)
    {
        $task->update($request->only([
            'title', 'description', 'priority', 'due_date',
            'assignees', 'tags', 'checklist', 'comments', 'column_id', 'position',
        ]));

        return response()->json($task);
    }

    public function destroyTask(Task $task)
    {
        $task->delete();
        return response()->json(['success' => true]);
    }

    public function moveTask(Request $request, Task $task)
    {
        $request->validate([
            'column_id' => 'required|exists:project_columns,id',
            'position' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $task) {
            $oldColumnId = $task->column_id;
            $newColumnId = $request->column_id;
            $newPosition = $request->position;

            Task::where('column_id', $oldColumnId)
                ->where('position', '>', $task->position)
                ->decrement('position');

            Task::where('column_id', $newColumnId)
                ->where('position', '>=', $newPosition)
                ->increment('position');

            $task->update([
                'column_id' => $newColumnId,
                'position' => $newPosition,
            ]);
        });

        return response()->json(['success' => true]);
    }

    public function storeColumn(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:100',
        ]);

        $maxPosition = ProjectColumn::where('project_id', $request->project_id)->max('position') ?? 0;

        $column = ProjectColumn::create([
            'project_id' => $request->project_id,
            'title' => $request->title,
            'position' => $maxPosition + 1,
        ]);

        return response()->json($column);
    }

    public function destroyColumn(ProjectColumn $column)
    {
        $column->tasks()->delete();
        $column->delete();
        return response()->json(['success' => true]);
    }
}
