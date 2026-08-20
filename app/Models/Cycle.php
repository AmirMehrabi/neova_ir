<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cycle extends Model
{
    protected $fillable = ['project_id', 'number', 'starts_on', 'ends_on', 'status', 'completed_at'];
    protected function casts(): array { return ['starts_on' => 'date', 'ends_on' => 'date', 'completed_at' => 'datetime']; }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function tasks(): BelongsToMany { return $this->belongsToMany(Task::class, 'cycle_task')->withPivot('outcome')->withTimestamps(); }
}
