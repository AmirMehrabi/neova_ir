<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Workspace extends Model
{
    protected $fillable = ['owner_id', 'name', 'slug', 'description'];

    protected static function booted(): void
    {
        static::creating(function (Workspace $workspace) {
            if (empty($workspace->slug)) {
                $workspace->slug = Str::slug($workspace->name);
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists() || $this->isOwnedBy($user);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(WorkspaceInvitation::class);
    }

    public function roleFor(User $user): ?string
    {
        if ($this->isOwnedBy($user)) {
            return 'owner';
        }

        return $this->members()
            ->where('users.id', $user->id)
            ->value('workspace_members.role');
    }

    public function canManageMembers(User $user): bool
    {
        return in_array($this->roleFor($user), ['owner', 'admin'], true);
    }

    public function canEditBoards(User $user): bool
    {
        return in_array($this->roleFor($user), ['owner', 'admin', 'user'], true);
    }

    public function canManageRole(User $actor, string $targetRole): bool
    {
        $actorRole = $this->roleFor($actor);

        if ($actorRole === 'owner') {
            return in_array($targetRole, ['admin', 'user', 'viewer'], true);
        }

        return $actorRole === 'admin' && in_array($targetRole, ['user', 'viewer'], true);
    }
}
