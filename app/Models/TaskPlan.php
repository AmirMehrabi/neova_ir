<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskPlan extends Model
{
    protected $fillable = ['task_id', 'user_id', 'planned_for', 'bucket', 'position'];

    protected function casts(): array
    {
        return ['planned_for' => 'date'];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
