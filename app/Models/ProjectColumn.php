<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectColumn extends Model
{
    public const WORKFLOW_ROLES = ['backlog', 'ready', 'active', 'done', 'other'];

    protected $table = 'project_columns';

    protected $fillable = ['project_id', 'title', 'position', 'color', 'wip_limit', 'workflow_role'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'column_id')->orderBy('position');
    }
}
