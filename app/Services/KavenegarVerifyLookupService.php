<?php

namespace App\Services;

use App\Models\WorkspaceInvitation;
use Kavenegar\KavenegarApi;
use RuntimeException;

class KavenegarVerifyLookupService
{
    private ?KavenegarApi $api;

    public function __construct(?KavenegarApi $api = null)
    {
        $this->api = $api;
    }

    private function api(): KavenegarApi
    {
        if ($this->api === null) {
            $apiKey = config('services.kavenegar.api_key');

            if (! $apiKey) {
                throw new RuntimeException('Kavenegar API key is not configured.');
            }

            $this->api = new KavenegarApi($apiKey);
        }

        return $this->api;
    }

    public function sendOtp(string $phone, string $code): void
    {
        if (! config('services.kavenegar.enabled')) {
            return;
        }

        $template = config('services.kavenegar.otp.template');

        if (! $template) {
            throw new RuntimeException('Kavenegar OTP template is not configured.');
        }

        $this->api()->VerifyLookup($phone, $code, null, null, $template);
    }

    public function sendWorkspaceInvitation(WorkspaceInvitation $invitation, string $code): void
    {
        if (! config('services.kavenegar.enabled')) {
            return;
        }

        $template = config('services.kavenegar.workspace_invite.template');

        if (! $template) {
            throw new RuntimeException('Kavenegar workspace invitation configuration is incomplete.');
        }

        $availableValues = [
            'invitation_code' => $code,
            'workspace_name' => $invitation->workspace->name,
            'inviter_name' => $invitation->inviter->full_name,
            'role_name' => $this->roleName($invitation->role),
            'expires_at' => $invitation->expires_at->format('Y/m/d'),
        ];

        $token = $this->resolveToken('token', $availableValues);
        $token2 = $this->resolveToken('token2', $availableValues);
        $token3 = $this->resolveToken('token3', $availableValues);
        $token10 = $this->resolveToken('token10', $availableValues);
        $token20 = $this->resolveToken('token20', $availableValues);

        $this->api()->VerifyLookup($invitation->phone, $token, $token2, $token3, $template, null, $token10, $token20);
    }

    private function resolveToken(string $tokenKey, array $availableValues): ?string
    {
        $mappedValue = config("services.kavenegar.workspace_invite.{$tokenKey}");

        return $mappedValue && isset($availableValues[$mappedValue])
            ? $availableValues[$mappedValue]
            : null;
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
