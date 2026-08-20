<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_opens_the_last_accessible_workspace_today(): void
    {
        $user = User::factory()->create();
        $first = Workspace::create(['owner_id' => $user->id, 'name' => 'اول']);
        $last = Workspace::create(['owner_id' => $user->id, 'name' => 'آخر']);
        $user->update(['last_workspace_id' => $last->id]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertRedirect(route('today', $last->slug));

        $this->actingAs($user)->get(route('today', $first->slug))->assertOk();
        $this->assertSame($first->id, $user->refresh()->last_workspace_id);
    }

    public function test_dashboard_falls_back_when_the_remembered_workspace_is_not_accessible(): void
    {
        $user = User::factory()->create();
        $available = Workspace::create(['owner_id' => $user->id, 'name' => 'در دسترس']);
        $foreign = Workspace::create(['owner_id' => User::factory()->create()->id, 'name' => 'خارجی']);
        $user->update(['last_workspace_id' => $foreign->id]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertRedirect(route('today', $available->slug));
    }

    public function test_user_without_a_workspace_sees_only_workspace_onboarding(): void
    {
        $this->actingAs(User::factory()->create())->get(route('dashboard'))
            ->assertOk()
            ->assertSee('فضای کاری تیم‌تان را بسازید')
            ->assertDontSee('dashboard-workspace-list', false);
    }

    public function test_shared_shell_contains_consistent_navigation_and_does_not_leak_private_projects(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $workspace = Workspace::create(['owner_id' => $owner->id, 'name' => 'تیم اصلی']);
        $workspace->members()->attach($viewer->id, ['role' => 'viewer']);
        Project::create(['workspace_id' => $workspace->id, 'name' => 'عمومی', 'visibility' => 'public']);
        Project::create(['workspace_id' => $workspace->id, 'name' => 'خیلی محرمانه', 'visibility' => 'private']);

        $this->actingAs($viewer)->get(route('today', $workspace->slug))
            ->assertOk()
            ->assertSee('workspace-sidebar', false)
            ->assertSee('workspace-mobile-nav', false)
            ->assertSee('امروز')
            ->assertSee('تخته')
            ->assertSee('پروژه‌ها')
            ->assertSee('تیم')
            ->assertSee('عمومی')
            ->assertDontSee('خیلی محرمانه');
    }

    public function test_workspace_search_is_scoped_and_task_results_open_the_drawer(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $workspace = Workspace::create(['owner_id' => $owner->id, 'name' => 'تیم']);
        $workspace->members()->attach($viewer->id, ['role' => 'viewer']);
        $public = Project::create(['workspace_id' => $workspace->id, 'name' => 'محصول', 'visibility' => 'public']);
        $publicColumn = $public->columns()->create(['title' => 'Backlog', 'position' => 0, 'workflow_role' => 'backlog']);
        $task = Task::create(['column_id' => $publicColumn->id, 'task_number' => 1, 'title' => 'رفع خطای ورود', 'position' => 1]);
        $private = Project::create(['workspace_id' => $workspace->id, 'name' => 'محرمانه', 'visibility' => 'private']);
        $privateColumn = $private->columns()->create(['title' => 'Backlog', 'position' => 0, 'workflow_role' => 'backlog']);
        Task::create(['column_id' => $privateColumn->id, 'task_number' => 1, 'title' => 'رفع خطای محرمانه', 'position' => 1]);
        $other = Workspace::create(['owner_id' => $owner->id, 'name' => 'تیم دیگر']);
        $otherProject = Project::create(['workspace_id' => $other->id, 'name' => 'بیرونی']);
        $otherColumn = $otherProject->columns()->create(['title' => 'Backlog', 'position' => 0, 'workflow_role' => 'backlog']);
        Task::create(['column_id' => $otherColumn->id, 'task_number' => 1, 'title' => 'رفع خطای بیرونی', 'position' => 1]);

        $response = $this->actingAs($viewer)->getJson(route('workspace.search', $workspace->slug).'?q=رفع خطا')
            ->assertOk();

        $this->assertCount(1, $response->json());
        $this->assertStringContainsString('?task='.$task->id, $response->json('0.url'));
    }

    public function test_board_uses_the_same_collapsible_workspace_shell(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::create(['owner_id' => $user->id, 'name' => 'تیم']);
        $project = Project::create(['workspace_id' => $workspace->id, 'name' => 'وب']);
        $project->columns()->create(['title' => 'Backlog', 'position' => 0, 'workflow_role' => 'backlog']);

        $this->actingAs($user)->get(route('board', [$workspace->slug, $project->slug]))
            ->assertOk()
            ->assertSee('workspace-shell--board', false)
            ->assertSee('app-navbar--embedded', false)
            ->assertSee('function workspaceShell', false);
    }
}
