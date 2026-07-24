<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
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
}
