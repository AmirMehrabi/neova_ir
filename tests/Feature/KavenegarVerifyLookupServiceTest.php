<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Services\KavenegarVerifyLookupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KavenegarVerifyLookupServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_invitation_uses_configured_verify_lookup_tokens(): void
    {
        Http::fake(['api.kavenegar.com/*' => Http::response(['return' => ['status' => 200]], 200)]);
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

        app(KavenegarVerifyLookupService::class)
            ->sendWorkspaceInvitation($invitation, 'invite-code');

        Http::assertSent(function ($request) use ($workspace, $owner) {
            return str_contains($request->url(), '/v1/test-key/verify/lookup.json')
                && $request['receptor'] === '09123456789'
                && $request['template'] === 'workspace-invite'
                && $request['token'] === 'invite-code'
                && $request['token2'] === 'مشاهده‌گر'
                && $request['token10'] === $workspace->name
                && $request['token20'] === $owner->full_name;
        });
    }
}
