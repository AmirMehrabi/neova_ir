<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Task extends Model
{
    protected static function booted(): void
    {
        static::deleting(function (Task $task) {
            $paths = $task->attachments()->pluck('path');
            if ($paths->isNotEmpty()) Storage::disk('local')->delete($paths->all());
        });
    }

    protected $fillable = [
        'column_id', 'task_number', 'title', 'description', 'priority',
        'due_date', 'is_blocked', 'blocked_reason', 'completed_at',
        'assignees', 'tags', 'checklist', 'comments', 'position',
    ];

    protected function casts(): array
    {
        return [
            'assignees' => 'array',
            'tags' => 'array',
            'checklist' => 'array',
            'comments' => 'array',
            'due_date' => 'date',
            'is_blocked' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function getDisplayIdAttribute(): string
    {
        $key = $this->column?->project?->key ?? '';
        $num = str_pad($this->task_number ?? 0, 3, '0', STR_PAD_LEFT);

        return $key !== '' ? $key.'-'.$num : '-'.$num;
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(ProjectColumn::class, 'column_id');
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps();
    }

    public function plans(): HasMany
    {
        return $this->hasMany(TaskPlan::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function cycles(): BelongsToMany
    {
        return $this->belongsToMany(Cycle::class, 'cycle_task')->withPivot('outcome')->withTimestamps();
    }

    public function project()
    {
        return $this->column->project;
    }
}
