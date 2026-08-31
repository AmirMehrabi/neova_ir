<?php

namespace App\Services;

use App\Models\TaskAttachment;

class TaskAttachmentData
{
    public function make(TaskAttachment $attachment, string $workspace, string $project): array
    {
        $category = $this->category($attachment->mime_type, $attachment->original_name);

        return [
            'id' => $attachment->id,
            'name' => $attachment->original_name,
            'mimeType' => $attachment->mime_type,
            'size' => $attachment->size,
            'context' => $attachment->context ?: 'description',
            'commentId' => $attachment->comment_id,
            'category' => $category,
            'previewable' => in_array($category, ['image', 'pdf', 'audio', 'video', 'text'], true),
            'previewUrl' => route('task.attachments.preview', [$workspace, $project, $attachment->task_id, $attachment->id], false),
            'downloadUrl' => route('task.attachments.download', [$workspace, $project, $attachment->task_id, $attachment->id], false),
            'uploadedBy' => $attachment->uploader?->full_name,
            'createdAt' => $attachment->created_at?->toIso8601String(),
        ];
    }

    public function category(?string $mimeType, string $name): string
    {
        $mime = strtolower((string) $mimeType);
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if (str_starts_with($mime, 'image/') && $extension !== 'svg') {
            return 'image';
        }
        if ($mime === 'application/pdf' || $extension === 'pdf') {
            return 'pdf';
        }
        if (str_starts_with($mime, 'audio/')) {
            return 'audio';
        }
        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }
        if (str_starts_with($mime, 'text/') || in_array($extension, ['txt', 'csv', 'md', 'json'], true)) {
            return 'text';
        }
        if (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'], true)) {
            return 'archive';
        }

        return 'document';
    }
}
