<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TaskAttachmentManager
{
    private const EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'heic', 'pdf',
        'txt', 'csv', 'md', 'json', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'odt', 'ods', 'odp', 'mp3', 'wav', 'm4a', 'ogg', 'mp4', 'webm', 'mov',
        'zip', 'rar', '7z', 'tar', 'gz',
    ];

    private const BLOCKED_MIMES = [
        'text/html', 'text/x-php', 'application/x-httpd-php', 'application/x-dosexec',
        'application/x-executable', 'application/x-sharedlib', 'application/x-shellscript',
    ];

    /** @param array<int, UploadedFile> $files */
    public function validate(array $files, string $context): void
    {
        $validator = Validator::make(['files' => $files, 'context' => $context], [
            'context' => ['required', Rule::in(['description', 'comment'])],
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['required', 'file', 'max:25600'],
        ]);

        $validator->after(function ($validator) use ($files) {
            if (collect($files)->sum(fn (UploadedFile $file) => $file->getSize()) > 100 * 1024 * 1024) {
                $validator->errors()->add('files', 'حجم مجموع فایل‌ها نباید بیشتر از ۱۰۰ مگابایت باشد.');
            }
            foreach ($files as $index => $file) {
                $extension = strtolower($file->getClientOriginalExtension());
                $mime = strtolower((string) $file->getMimeType());
                if (! in_array($extension, self::EXTENSIONS, true) || in_array($mime, self::BLOCKED_MIMES, true)) {
                    $validator->errors()->add("files.{$index}", 'نوع این فایل مجاز نیست.');
                }
            }
        });
        $validator->validate();
    }

    /** @param array<int, UploadedFile> $files */
    public function store(Task $task, array $files, string $context, int $userId, ?string $commentId = null): Collection
    {
        $this->validate($files, $context);
        $storedPaths = [];

        try {
            return DB::transaction(function () use ($task, $files, $context, $userId, $commentId, &$storedPaths) {
                return collect($files)->map(function (UploadedFile $file) use ($task, $context, $userId, $commentId, &$storedPaths) {
                    $path = $file->store("task-attachments/{$task->id}", 'local');
                    abort_unless($path, 500, 'ذخیره پیوست انجام نشد.');
                    $storedPaths[] = $path;

                    return $task->attachments()->create([
                        'context' => $context,
                        'comment_id' => $commentId,
                        'uploaded_by' => $userId,
                        'original_name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                });
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);
            throw $exception;
        }
    }
}
