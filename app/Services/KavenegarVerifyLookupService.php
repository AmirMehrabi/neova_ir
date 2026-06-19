<?php

namespace App\Services;

use App\Models\WorkspaceInvitation;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class KavenegarVerifyLookupService
{
    public function sendWorkspaceInvitation(WorkspaceInvitation $invitation, string $code): void
    {
        if (! config('services.kavenegar.enabled')) {
            return;
        }

        $apiKey = config('services.kavenegar.api_key');
        $template = config('services.kavenegar.workspace_invite.template');

        if (! $apiKey || ! $template) {
            throw new RuntimeException('Kavenegar workspace invitation configuration is incomplete.');
        }

        $availableValues = [
            'invitation_code' => $code,
            'workspace_name' => $invitation->workspace->name,
            'inviter_name' => $invitation->inviter->full_name,
            'role_name' => $this->roleName($invitation->role),
            'expires_at' => $invitation->expires_at->format('Y/m/d'),
        ];

        $payload = [
            'receptor' => $invitation->phone,
            'template' => $template,
        ];

        foreach (['token', 'token2', 'token3', 'token10', 'token20'] as $token) {
            $mappedValue = config("services.kavenegar.workspace_invite.{$token}");

            if ($mappedValue && isset($availableValues[$mappedValue])) {
                $payload[$token] = $availableValues[$mappedValue];
            }
        }

        Http::timeout(15)
            ->retry(3, 500)
            ->get("https://api.kavenegar.com/v1/{$apiKey}/verify/lookup.json", $payload)
            ->throw();
    }

    private function roleName(string $role): string
    {
        return match ($role) {
            'admin' => 'مدیر',
            'viewer' => 'مشاهده‌گر',
            default => 'کاربر',
        };
    }
}
