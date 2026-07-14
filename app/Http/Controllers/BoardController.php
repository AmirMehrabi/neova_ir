<?php

namespace App\Http\Controllers;

use App\Models\ProjectColumn;
use App\Models\Task;
use App\Models\User;
use App\Services\ProjectActivityNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BoardController extends Controller
{
    public function show(Request $request)
    {
        $project = $request->attributes->get('project');
        $workspace = $request->attributes->get('workspace');

        $columns = $project->columns()->with('tasks')->orderBy('position')->get();

        $members = $project->members()->orderBy('name')->get();
        $workspacePeople = collect([$workspace->owner])
            ->merge($workspace->members()->orderBy('name')->get())
            ->unique('id')
            ->values();
        $canEdit = $workspace->canEditBoards($request->user());
        $canManageProject = $workspace->canManageMembers($request->user());

        $colColors = [
            'پس‌زمینه' => 'bg-[#94A3B8]',
            'در حال انجام' => 'bg-[#0069FF]',
            'بررسی' => 'bg-[#F59E0B]',
            'انجام شده' => 'bg-[#22C55E]',
        ];
        $colHexColors = [
            'پس‌زمینه' => '#94A3B8',
            'در حال انجام' => '#0069FF',
            'بررسی' => '#F59E0B',
            'انجام شده' => '#22C55E',
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
            'dotHex' => $c->color ?: ($colHexColors[$c->title] ?? '#94A3B8'),
            'badgeClass' => $colBadge[$c->title] ?? 'bg-[#F1F5F9] text-[#64748B]',
            'tasks' => $c->tasks->map(fn ($t) => [
                'id' => $t->display_id,
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

        $membersData = $members->map(fn ($member) => [
            'id' => $member->id,
            'name' => $member->full_name,
            'phone' => $member->phone,
        ])->values()->toArray();
        $workspacePeopleData = $workspacePeople->map(fn ($member) => [
            'id' => $member->id,
            'name' => $member->full_name,
            'phone' => $member->phone,
            'role' => $workspace->roleFor($member),
        ])->values()->toArray();

        return view('board', compact(
            'project',
            'workspace',
            'columns',
            'members',
            'columnsData',
            'membersData',
            'workspacePeopleData',
            'canEdit',
            'canManageProject',
        ));
    }

    public function storeTask(
        Request $request,
        string $workspace,
        string $project,
        ProjectActivityNotifier $notifier,
    ) {
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
            ->pluck('tasks.task_number')
            ->max() ?? 0;

        $taskNumber = $maxNum + 1;

        $task = Task::create([
            'column_id' => $request->column_id,
            'task_number' => $taskNumber,
            'title' => $request->title,
            'description' => $request->input('description', ''),
            'priority' => $request->input('priority', 'متوسط'),
            'due_date' => $request->input('due_date'),
            'assignees' => $request->input('assignees', []),
            'tags' => $request->input('tags', []),
            'position' => $maxPosition + 1,
        ]);
        $notifier->taskCreated($task, $request->user());

        return response()->json($task);
    }

    public function updateTask(
        Request $request,
        string $workspace,
        string $project,
        string $task,
        ProjectActivityNotifier $notifier,
    ) {
        $task = $this->findTaskInCurrentProject($request, $task);
        $this->ensureTaskInCurrentProject($request, $task);
        $this->validateAssignees($request, $task);
        $before = $task->only([
            'title', 'description', 'priority', 'due_date', 'assignees',
            'tags', 'checklist', 'comments', 'column_id',
        ]);

        $task->update($request->only([
            'title', 'description', 'priority', 'due_date',
            'assignees', 'tags', 'checklist', 'comments', 'column_id', 'position',
        ]));
        $notifier->taskUpdated($task->refresh(), $before, $request->user());

        return response()->json($task);
    }

    public function destroyTask(Request $request, string $workspace, string $project, string $task)
    {
        $task = $this->findTaskInCurrentProject($request, $task);
        $this->ensureTaskInCurrentProject($request, $task);
        $task->delete();

        return response()->json(['success' => true]);
    }

    public function moveTask(
        Request $request,
        string $workspace,
        string $project,
        string $task,
        ProjectActivityNotifier $notifier,
    ) {
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
        $notifier->taskMoved($task->refresh(), $request->user(), $targetColumn->title);

        return response()->json(['success' => true]);
    }

    public function addComment(
        Request $request,
        string $workspace,
        string $project,
        string $task,
        ProjectActivityNotifier $notifier,
    ) {
        $task = $this->findTaskInCurrentProject($request, $task);
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:5000'],
            'mention_ids' => ['nullable', 'array'],
            'mention_ids.*' => ['integer'],
        ]);
        $before = $task->only([
            'title', 'description', 'priority', 'due_date', 'assignees',
            'tags', 'checklist', 'comments', 'column_id',
        ]);
        $comment = [
            'id' => (string) str()->uuid(),
            'author' => $request->user()->full_name,
            'author_id' => $request->user()->id,
            'text' => $validated['text'],
            'mention_ids' => array_values(array_unique($validated['mention_ids'] ?? [])),
            'time' => 'همین الان',
            'created_at' => now()->toIso8601String(),
        ];
        $comments = $task->comments ?? [];
        $comments[] = $comment;
        $task->update(['comments' => $comments]);
        $notifier->taskUpdated($task->refresh(), $before, $request->user());

        return response()->json(['comment' => $comment]);
    }

    public function updateProject(Request $request, string $workspace, string $project)
    {
        $workspaceModel = $request->attributes->get('workspace');
        $projectModel = $request->attributes->get('project');
        abort_unless($workspaceModel->canManageMembers($request->user()), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'key' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('projects', 'key')
                    ->where('workspace_id', $workspaceModel->id)
                    ->ignore($projectModel->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $projectModel->update([
            'name' => $validated['name'],
            'key' => mb_strtoupper($validated['key'] ?? ''),
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'تنظیمات پروژه ذخیره شد.',
            'project' => $projectModel->only(['id', 'name', 'key', 'description']),
        ]);
    }

    public function addProjectMember(Request $request, string $workspace, string $project)
    {
        $workspaceModel = $request->attributes->get('workspace');
        $projectModel = $request->attributes->get('project');
        abort_unless($workspaceModel->canManageMembers($request->user()), 403);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ]);
        $user = User::findOrFail($validated['user_id']);
        abort_unless($workspaceModel->hasMember($user), 422, 'این کاربر عضو فضای کاری نیست.');

        $projectModel->members()->syncWithoutDetaching([
            $user->id => ['added_by' => $request->user()->id],
        ]);

        return response()->json([
            'message' => 'عضو به پروژه اضافه شد.',
            'member' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'phone' => $user->phone,
            ],
        ]);
    }

    public function removeProjectMember(Request $request, string $workspace, string $project, User $user)
    {
        $workspaceModel = $request->attributes->get('workspace');
        $projectModel = $request->attributes->get('project');
        abort_unless($workspaceModel->canManageMembers($request->user()), 403);
        abort_unless($projectModel->members()->where('users.id', $user->id)->exists(), 404);

        DB::transaction(function () use ($projectModel, $user) {
            $projectModel->members()->detach($user->id);
            $projectModel->columns()->with('tasks')->get()->each(function (ProjectColumn $column) use ($user) {
                $column->tasks->each(function (Task $task) use ($user) {
                    $assignees = collect($task->assignees ?? [])
                        ->reject(fn (string $name) => $name === $user->full_name)
                        ->values()
                        ->all();
                    if ($assignees !== ($task->assignees ?? [])) {
                        $task->update(['assignees' => $assignees]);
                    }
                });
            });
        });

        return response()->json(['message' => 'عضو از پروژه حذف شد.']);
    }

    public function storeColumn(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:100',
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        abort_unless((int) $request->project_id === (int) $request->attributes->get('project')->id, 403);
        $maxPosition = ProjectColumn::where('project_id', $request->project_id)->max('position') ?? 0;

        $column = ProjectColumn::create([
            'project_id' => $request->project_id,
            'title' => $request->title,
            'color' => $request->input('color'),
            'position' => $maxPosition + 1,
        ]);

        return response()->json($column);
    }

    public function updateColumn(Request $request, string $workspace, string $project, ProjectColumn $column)
    {
        $this->ensureColumnInCurrentProject($request, $column);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);
        $column->update([
            'title' => trim($validated['title']),
            'color' => $validated['color'] ?? $column->color,
        ]);

        return response()->json($column->fresh());
    }

    public function destroyColumn(Request $request, string $workspace, string $project, ProjectColumn $column)
    {
        $this->ensureColumnInCurrentProject($request, $column);
        abort_if($column->project->columns()->count() <= 1, 422, 'پروژه باید حداقل یک ستون داشته باشد.');
        $column->tasks()->delete();
        $column->delete();

        $column->project->columns()->orderBy('position')->orderBy('id')->get()->each(function (ProjectColumn $remaining, int $index) {
            if ((int) $remaining->position !== $index + 1) {
                $remaining->update(['position' => $index + 1]);
            }
        });

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

    public function activity(Request $request, string $workspace, string $project)
    {
        $projectModel = $request->attributes->get('project');

        $notifications = \App\Models\Notification::query()
            ->where('type', 'App\\Notifications\\ProjectActivityNotification')
            ->whereRaw("JSON_EXTRACT(data, '$.project_id') = ?", [$projectModel->id])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(fn ($n) => [
                'kind' => $n->data['kind'] ?? '',
                'message' => $n->data['message'] ?? '',
                'time' => $n->created_at->diffForHumans(),
            ]);

        return response()->json($notifications);
    }
}
