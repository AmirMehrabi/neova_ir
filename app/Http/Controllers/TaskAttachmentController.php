<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    public function store(Request $request, string $workspace, string $project, Task $task)
    {
        $this->ensureTask($request, $task);
        $validated = $request->validate(['file' => ['required', 'file', 'max:10240', 'mimes:pdf,png,jpg,jpeg,gif,webp,txt,csv,doc,docx,xls,xlsx,zip']]);
        $file = $validated['file'];
        $path = $file->store("task-attachments/{$task->id}", 'local');
        abort_unless($path, 500, 'ذخیره پیوست انجام نشد.');
        $attachment = $task->attachments()->create([
            'uploaded_by' => $request->user()->id,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
        return response()->json(['attachment' => $this->data($attachment)], 201);
    }

    public function download(Request $request, string $workspace, string $project, Task $task, TaskAttachment $attachment)
    {
        $this->ensureTask($request, $task);
        abort_unless($attachment->task_id === $task->id, 404);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);
        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }

    public function destroy(Request $request, string $workspace, string $project, Task $task, TaskAttachment $attachment)
    {
        $this->ensureTask($request, $task);
        abort_unless($attachment->task_id === $task->id, 404);
        $attachment->delete();
        return response()->json(['success' => true]);
    }

    private function ensureTask(Request $request, Task $task): void
    {
        $task->loadMissing('column');
        abort_unless($task->column->project_id === $request->attributes->get('project')->id, 404);
    }

    private function data(TaskAttachment $attachment): array
    {
        return ['id' => $attachment->id, 'name' => $attachment->original_name, 'mimeType' => $attachment->mime_type, 'size' => $attachment->size];
    }
}
