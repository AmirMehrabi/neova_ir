<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'column_id', 'task_number', 'title', 'description', 'priority',
        'due_date', 'assignees', 'tags', 'checklist', 'comments', 'position',
    ];

    protected function casts(): array
    {
        return [
            'assignees' => 'array',
            'tags' => 'array',
            'checklist' => 'array',
            'comments' => 'array',
            'due_date' => 'date',
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

    public function project()
    {
        return $this->column->project;
    }
}
