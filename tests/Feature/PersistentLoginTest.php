<?php

namespace Tests\Feature;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PersistentLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_user_remains_authenticated_after_the_session_is_lost(): void
    {
        $user = User::factory()->create(['phone' => '09123456789']);
        OtpCode::create([
            'phone' => $user->phone,
            'code' => '123456',
            'expires_at' => now()->addMinutes(3),
            'used' => false,
        ]);

        $response = $this->postJson(route('auth.verify-otp'), [
            'phone' => $user->phone,
            'code' => '123456',
        ])->assertOk()->assertJsonPath('success', true);

        $recaller = $response->headers->getCookies();
        $rememberCookie = collect($recaller)->first(fn ($cookie) => $cookie->getName() === Auth::guard()->getRecallerName());

        $this->assertNotNull($rememberCookie);
        $this->assertGreaterThan(now()->addDays(300)->getTimestamp(), $rememberCookie->getExpiresTime());

        $this->flushSession();
        Auth::forgetGuards();

        $this->withUnencryptedCookie($rememberCookie->getName(), $rememberCookie->getValue())
            ->get(route('dashboard'))
            ->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    public function test_new_user_gets_a_persistent_login_and_logout_revokes_it(): void
    {
        $response = $this->withSession(['otp_verified_phone' => '09123456788'])
            ->post(route('auth.profile.store'), [
                'first_name' => 'آرمان',
                'last_name' => 'رضایی',
            ])->assertRedirect();

        $user = User::where('phone', '09123456788')->firstOrFail();
        $rememberName = Auth::guard()->getRecallerName();
        $this->assertNotNull(collect($response->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === $rememberName));

        $token = $user->getRememberToken();
        $this->post(route('auth.logout'))->assertRedirect(route('auth'));

        $this->assertGuest();
        $this->assertNotSame($token, $user->fresh()->getRememberToken());
    }
}
