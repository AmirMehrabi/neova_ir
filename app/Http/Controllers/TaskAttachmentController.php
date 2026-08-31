<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Services\TaskAttachmentData;
use App\Services\TaskAttachmentManager;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class TaskAttachmentController extends Controller
{
    public function store(Request $request, string $workspace, string $project, Task $task, TaskAttachmentData $attachmentData, TaskAttachmentManager $attachmentManager)
    {
        $this->ensureTask($request, $task);
        $files = $this->filesFrom($request);
        $context = $request->input('context', 'description');
        try {
            $attachments = $attachmentManager->store($task, $files, $context, $request->user()->id);
        } catch (ValidationException $exception) {
            return response()->json(['message' => 'فایل‌های انتخاب‌شده معتبر نیستند.', 'errors' => $exception->errors()], 422);
        }

        $attachments->each->load('uploader');

        return response()->json([
            'attachments' => $attachments->map(fn ($attachment) => $attachmentData->make($attachment, $workspace, $project))->values(),
        ], 201);
    }

    public function preview(Request $request, string $workspace, string $project, Task $task, TaskAttachment $attachment, TaskAttachmentData $attachmentData)
    {
        $this->ensureAttachment($request, $task, $attachment);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);
        abort_unless(in_array($attachmentData->category($attachment->mime_type, $attachment->original_name), ['image', 'pdf', 'audio', 'video', 'text'], true), 415);

        $fallbackName = Str::ascii(basename($attachment->original_name)) ?: 'attachment';
        $disposition = (new ResponseHeaderBag)->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, basename($attachment->original_name), $fallbackName);

        return response()->file(Storage::disk('local')->path($attachment->path), [
            'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => $disposition,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self' data:; media-src 'self'; style-src 'unsafe-inline'",
        ]);
    }

    public function download(Request $request, string $workspace, string $project, Task $task, TaskAttachment $attachment)
    {
        $this->ensureAttachment($request, $task, $attachment);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name, ['X-Content-Type-Options' => 'nosniff']);
    }

    public function destroy(Request $request, string $workspace, string $project, Task $task, TaskAttachment $attachment)
    {
        $this->ensureAttachment($request, $task, $attachment);
        $attachment->delete();

        return response()->json(['success' => true]);
    }

    private function ensureTask(Request $request, Task $task): void
    {
        $task->loadMissing('column');
        abort_unless($task->column->project_id === $request->attributes->get('project')->id, 404);
    }

    private function ensureAttachment(Request $request, Task $task, TaskAttachment $attachment): void
    {
        $this->ensureTask($request, $task);
        abort_unless($attachment->task_id === $task->id, 404);
    }

    /** @return array<int, UploadedFile> */
    private function filesFrom(Request $request): array
    {
        $files = $request->file('files', []);
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }
        if ($request->hasFile('file')) {
            $files[] = $request->file('file');
        }

        return array_values(array_filter($files));
    }
}
