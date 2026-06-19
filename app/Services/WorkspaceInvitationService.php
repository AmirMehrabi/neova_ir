<?php

namespace App\Services;

use App\Jobs\SendWorkspaceInvitationSms;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Notifications\WorkspaceInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WorkspaceInvitationService
{
    public function create(Workspace $workspace, User $inviter, string $phone, string $role): WorkspaceInvitation
    {
        $phone = $this->normalizePhone($phone);

        if ($workspace->owner->phone === $phone || $workspace->members()->where('phone', $phone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'این کاربر در حال حاضر عضو فضای کاری است.',
            ]);
        }

        $existing = WorkspaceInvitation::query()
            ->where('workspace_id', $workspace->id)
            ->where('phone', $phone)
            ->where('status', WorkspaceInvitation::STATUS_PENDING)
            ->first();

        if ($existing?->isPending()) {
            throw ValidationException::withMessages([
                'phone' => 'برای این شماره یک دعوت فعال وجود دارد.',
            ]);
        }

        if ($existing) {
            $existing->update(['status' => WorkspaceInvitation::STATUS_EXPIRED]);
        }

        [$invitation, $code] = DB::transaction(function () use ($workspace, $inviter, $phone, $role) {
            $code = Str::lower(Str::random(20));
            $invitation = WorkspaceInvitation::create([
                'workspace_id' => $workspace->id,
                'invited_by' => $inviter->id,
                'phone' => $phone,
                'role' => $role,
                'code_hash' => hash('sha256', $code),
                'status' => WorkspaceInvitation::STATUS_PENDING,
                'expires_at' => now()->addDays(7),
            ]);

            return [$invitation, $code];
        });

        $invitation->load(['workspace', 'inviter']);
        $this->notifyRegisteredUser($invitation);
        SendWorkspaceInvitationSms::dispatch($invitation->id, $code);

        return $invitation;
    }

    public function resend(WorkspaceInvitation $invitation): WorkspaceInvitation
    {
        $code = Str::lower(Str::random(20));
        $invitation->update([
            'code_hash' => hash('sha256', $code),
            'status' => WorkspaceInvitation::STATUS_PENDING,
            'expires_at' => now()->addDays(7),
            'sent_at' => null,
            'responded_at' => null,
            'responded_by' => null,
        ]);

        SendWorkspaceInvitationSms::dispatch($invitation->id, $code);

        return $invitation->refresh();
    }

    public function accept(WorkspaceInvitation $invitation, User $user): void
    {
        if ($invitation->status === WorkspaceInvitation::STATUS_ACCEPTED
            && $invitation->responded_by === $user->id) {
            return;
        }

        $this->ensureRespondableBy($invitation, $user);

        DB::transaction(function () use ($invitation, $user) {
            $invitation->workspace->members()->syncWithoutDetaching([
                $user->id => ['role' => $invitation->role],
            ]);

            $invitation->update([
                'status' => WorkspaceInvitation::STATUS_ACCEPTED,
                'responded_by' => $user->id,
                'responded_at' => now(),
            ]);
        });

        $this->markNotificationRead($invitation, $user);
    }

    public function decline(WorkspaceInvitation $invitation, User $user): void
    {
        if ($invitation->status === WorkspaceInvitation::STATUS_DECLINED
            && $invitation->responded_by === $user->id) {
            return;
        }

        $this->ensureRespondableBy($invitation, $user);

        $invitation->update([
            'status' => WorkspaceInvitation::STATUS_DECLINED,
            'responded_by' => $user->id,
            'responded_at' => now(),
        ]);

        $this->markNotificationRead($invitation, $user);
    }

    public function syncPendingNotifications(User $user): void
    {
        WorkspaceInvitation::query()
            ->with(['workspace', 'inviter'])
            ->where('phone', $user->phone)
            ->where('status', WorkspaceInvitation::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->whereNull('notified_at')
            ->each(fn (WorkspaceInvitation $invitation) => $this->notify($invitation, $user));
    }

    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '98')) {
            $digits = '0'.substr($digits, 2);
        } elseif (! str_starts_with($digits, '0')) {
            $digits = '0'.$digits;
        }

        if (! preg_match('/^09\d{9}$/', $digits)) {
            throw ValidationException::withMessages(['phone' => 'شماره تلفن نادرست است.']);
        }

        return $digits;
    }

    private function notifyRegisteredUser(WorkspaceInvitation $invitation): void
    {
        $user = User::where('phone', $invitation->phone)->first();

        if ($user) {
            $this->notify($invitation, $user);
        }
    }

    private function notify(WorkspaceInvitation $invitation, User $user): void
    {
        $user->notify(new WorkspaceInvitationNotification($invitation));
        $invitation->update(['notified_at' => now()]);
    }

    private function ensureRespondableBy(WorkspaceInvitation $invitation, User $user): void
    {
        $invitation->markExpiredIfNeeded();

        if ($invitation->phone !== $user->phone) {
            abort(403);
        }

        if (! $invitation->isPending()) {
            throw ValidationException::withMessages([
                'invitation' => 'این دعوت‌نامه دیگر قابل استفاده نیست.',
            ]);
        }
    }

    private function markNotificationRead(WorkspaceInvitation $invitation, User $user): void
    {
        $user->unreadNotifications()
            ->where('type', WorkspaceInvitationNotification::class)
            ->get()
            ->filter(fn ($notification) => (int) ($notification->data['invitation_id'] ?? 0) === $invitation->id)
            ->each->markAsRead();
    }
}
