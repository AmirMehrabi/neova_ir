<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceInvitation extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_REVOKED = 'revoked';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'workspace_id',
        'invited_by',
        'responded_by',
        'phone',
        'role',
        'code_hash',
        'status',
        'expires_at',
        'sent_at',
        'responded_at',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'sent_at' => 'datetime',
            'responded_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function respondent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->expires_at->isFuture();
    }

    public function markExpiredIfNeeded(): bool
    {
        if ($this->status === self::STATUS_PENDING && $this->expires_at->isPast()) {
            $this->update(['status' => self::STATUS_EXPIRED]);

            return true;
        }

        return false;
    }

    public static function findByCode(string $code): ?self
    {
        return static::query()
            ->with(['workspace', 'inviter'])
            ->where('code_hash', hash('sha256', $code))
            ->first();
    }
}
