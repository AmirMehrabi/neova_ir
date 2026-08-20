<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Project extends Model
{
    public const BOARD_STYLES = ['simple', 'creative'];

    protected $fillable = ['workspace_id', 'name', 'slug', 'description', 'custom_tags', 'key', 'is_active', 'visibility', 'board_style', 'cycle_length_weeks'];

    protected $casts = ['custom_tags' => 'array'];

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name);
            }
            if (empty($project->key)) {
                $project->key = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $project->name), 0, 3));
            }
        });
        static::deleting(function (Project $project) {
            $paths = TaskAttachment::query()->whereHas('task.column', fn ($query) => $query->where('project_id', $project->id))->pluck('path');
            if ($paths->isNotEmpty()) Storage::disk('local')->delete($paths->all());
        });
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function columns(): HasMany
    {
        return $this->hasMany(ProjectColumn::class)->orderBy('position');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('added_by')
            ->withTimestamps();
    }

    public function tasks()
    {
        return Task::whereIn('column_id', $this->columns()->pluck('id'));
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProjectActivity::class);
    }

    public function cycles(): HasMany
    {
        return $this->hasMany(Cycle::class)->orderByDesc('number');
    }

    public function activeCycle()
    {
        return $this->hasOne(Cycle::class)->where('status', 'active')->latestOfMany();
    }

    public function canUserView(User $user, Workspace $workspace): bool
    {
        if ($this->visibility === 'public') {
            return true;
        }

        if ($workspace->isOwnedBy($user)) {
            return true;
        }

        if ($workspace->roleFor($user) === 'admin') {
            return true;
        }

        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function eligibleAssignees(): Collection
    {
        $workspace = $this->workspace()->with(['owner', 'members'])->firstOrFail();
        $people = collect([$workspace->owner])->merge($workspace->members);

        if ($this->visibility === 'private') {
            $projectMemberIds = $this->members()->pluck('users.id');
            $people = $people->filter(fn (User $user) =>
                $workspace->isOwnedBy($user)
                || $workspace->roleFor($user) === 'admin'
                || $projectMemberIds->contains($user->id)
            );
        }

        return $people->unique('id')->values();
    }
}
