<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_own_notification_as_read_with_json_response(): void
    {
        $user = User::factory()->create();
        $notification = DatabaseNotification::create([
            'id' => '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d',
            'type' => 'test',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['message' => 'اعلان آزمایشی'],
        ]);

        $this->actingAs($user)
            ->patchJson(route('notifications.read', $notification))
            ->assertOk()
            ->assertJson(['ok' => true, 'unread_count' => 0]);

        $this->assertNotNull($notification->refresh()->read_at);
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $notification = DatabaseNotification::create([
            'id' => '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6e',
            'type' => 'test',
            'notifiable_type' => User::class,
            'notifiable_id' => $owner->id,
            'data' => ['message' => 'اعلان آزمایشی'],
        ]);

        $this->actingAs($otherUser)
            ->patchJson(route('notifications.read', $notification))
            ->assertForbidden();

        $this->assertNull($notification->refresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read_with_json_response(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        foreach (range(1, 7) as $number) {
            $this->createNotification($user, "اعلان {$number}");
        }
        $otherNotification = $this->createNotification($otherUser, 'اعلان کاربر دیگر');

        $this->actingAs($user)
            ->postJson(route('notifications.read-all'))
            ->assertOk()
            ->assertJson(['ok' => true, 'unread_count' => 0]);

        $this->assertSame(0, $user->unreadNotifications()->count());
        $this->assertNull($otherNotification->refresh()->read_at);
    }

    public function test_mark_read_redirects_back_for_standard_form_requests(): void
    {
        $user = User::factory()->create();
        $notification = $this->createNotification($user, 'اعلان قدیمی');
        $returnUrl = route('notifications.index', ['filter' => 'unread']);

        $this->actingAs($user)
            ->from($returnUrl)
            ->patch(route('notifications.read', $notification))
            ->assertRedirect($returnUrl);

        $this->assertNotNull($notification->refresh()->read_at);
    }

    public function test_notifications_page_can_be_filtered_to_unread_items(): void
    {
        $user = User::factory()->create();
        Workspace::create(['owner_id' => $user->id, 'name' => 'فضای آزمایشی']);
        $unread = $this->createNotification($user, 'اعلان خوانده‌نشده');
        $this->createNotification($user, 'اعلان خوانده‌شده', now());

        $response = $this->actingAs($user)->get(route('notifications.index', ['filter' => 'unread']));

        $response->assertOk()->assertDontSee('اعلان خوانده‌نشده‌ای وجود ندارد');
        $notifications = $response->viewData('notifications');
        $this->assertCount(1, $notifications);
        $this->assertSame($unread->id, $notifications->first()->id);
    }

    private function createNotification(User $user, string $message, mixed $readAt = null): DatabaseNotification
    {
        return DatabaseNotification::create([
            'id' => (string) Str::uuid(),
            'type' => 'test',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['message' => $message],
            'read_at' => $readAt,
        ]);
    }
}
