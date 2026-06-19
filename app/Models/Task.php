<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'column_id', 'title', 'description', 'priority',
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

    public function column(): BelongsTo
    {
        return $this->belongsTo(ProjectColumn::class, 'column_id');
    }

    public function project()
    {
        return $this->column->project;
    }
}
