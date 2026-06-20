<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Services\KavenegarVerifyLookupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Kavenegar\KavenegarApi;
use Tests\TestCase;

class KavenegarVerifyLookupServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_invitation_uses_configured_verify_lookup_tokens(): void
    {
        config([
            'services.kavenegar.enabled' => true,
            'services.kavenegar.api_key' => 'test-key',
            'services.kavenegar.workspace_invite.template' => 'workspace-invite',
            'services.kavenegar.workspace_invite.token' => 'invitation_code',
            'services.kavenegar.workspace_invite.token2' => 'role_name',
            'services.kavenegar.workspace_invite.token3' => 'expires_at',
            'services.kavenegar.workspace_invite.token10' => 'workspace_name',
            'services.kavenegar.workspace_invite.token20' => 'inviter_name',
        ]);

        $mockApi = $this->createMock(KavenegarApi::class);
        $mockApi->expects($this->once())
            ->method('VerifyLookup')
            ->with(
                '09123456789',
                'invite-code',
                'مشاهده‌گر',
                $this->anything(),
                'workspace-invite',
                null,
                'تیم محصول',
                'علی رضایی'
            );

        $service = new KavenegarVerifyLookupService($mockApi);

        $owner = User::factory()->create(['first_name' => 'علی', 'last_name' => 'رضایی']);
        $workspace = Workspace::create(['owner_id' => $owner->id, 'name' => 'تیم محصول']);
        $invitation = WorkspaceInvitation::create([
            'workspace_id' => $workspace->id,
            'invited_by' => $owner->id,
            'phone' => '09123456789',
            'role' => 'viewer',
            'code_hash' => hash('sha256', 'invite-code'),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ])->load(['workspace', 'inviter']);

        $service->sendWorkspaceInvitation($invitation, 'invite-code');
    }

    public function test_send_otp_uses_configured_template(): void
    {
        config([
            'services.kavenegar.enabled' => true,
            'services.kavenegar.api_key' => 'test-key',
            'services.kavenegar.otp.template' => 'otp-verify',
        ]);

        $mockApi = $this->createMock(KavenegarApi::class);
        $mockApi->expects($this->once())
            ->method('VerifyLookup')
            ->with(
                '09123456789',
                '123456',
                null,
                null,
                'otp-verify'
            );

        $service = new KavenegarVerifyLookupService($mockApi);
        $service->sendOtp('09123456789', '123456');
    }

    public function test_send_otp_does_nothing_when_disabled(): void
    {
        config(['services.kavenegar.enabled' => false]);

        $mockApi = $this->createMock(KavenegarApi::class);
        $mockApi->expects($this->never())->method('VerifyLookup');

        $service = new KavenegarVerifyLookupService($mockApi);
        $service->sendOtp('09123456789', '123456');
    }
}
