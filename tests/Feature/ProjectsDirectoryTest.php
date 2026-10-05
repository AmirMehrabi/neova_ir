<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectsDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_and_tab_counts_only_include_accessible_projects(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $workspace = Workspace::create(['owner_id' => $owner->id, 'name' => 'Team']);
        $workspace->members()->attach($viewer->id, ['role' => 'viewer']);
        Project::create(['workspace_id' => $workspace->id, 'name' => 'Product', 'key' => 'PROD', 'visibility' => 'public']);
        Project::create(['workspace_id' => $workspace->id, 'name' => 'Secret', 'visibility' => 'private']);
        Project::create(['workspace_id' => $workspace->id, 'name' => 'Archive', 'visibility' => 'public', 'is_active' => false]);

        $this->actingAs($viewer)->get(route('projects.index', [$workspace->slug, 'q' => 'prod']))
            ->assertOk()->assertViewHas('projects', fn ($projects) => $projects->count() === 1 && $projects->first()->key === 'PROD')
            ->assertViewHas('projectCounts', ['active' => 1, 'archived' => 1])
            ->assertDontSee('Secret')->assertDontSee('projects-menu', false)->assertDontSee('id="project-create"', false);
        $this->get(route('projects.index', [$workspace->slug, 'archived' => 1]))
            ->assertOk()->assertViewHas('projects', fn ($projects) => $projects->count() === 1 && $projects->first()->name === 'Archive');
        $this->get(route('projects.index', [$workspace->slug, 'q' => 'unmatched']))
            ->assertOk()->assertSee('پروژه‌ای پیدا نشد');
    }

    public function test_recent_sort_follows_workspace_history_and_unknown_sort_falls_back_to_name(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create(['owner_id' => $owner->id, 'name' => 'Team']);
        $first = Project::create(['workspace_id' => $workspace->id, 'name' => 'Alpha']);
        $last = Project::create(['workspace_id' => $workspace->id, 'name' => 'Zulu']);
        $this->actingAs($owner)->withSession(["recent_projects.{$workspace->id}" => [$last->id]])
            ->get(route('projects.index', [$workspace->slug, 'sort' => 'recent']))
            ->assertOk()->assertViewHas('projects', fn ($projects) => $projects->modelKeys() === [$last->id, $first->id]);
        $this->get(route('projects.index', [$workspace->slug, 'sort' => 'invalid']))
            ->assertOk()->assertViewHas('sort', 'name')
            ->assertViewHas('projects', fn ($projects) => $projects->modelKeys() === [$first->id, $last->id]);
    }

    public function test_creation_validation_returns_to_directory_and_success_opens_new_board(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create(['owner_id' => $owner->id, 'name' => 'Team']);
        $this->actingAs($owner)->from(route('projects.index', $workspace->slug))
            ->post(route('dashboard.project.store', $workspace->slug), ['name' => 'Product', 'key' => 'bad'])
            ->assertRedirect(route('projects.index', $workspace->slug))->assertSessionHasErrors('key');
        $response = $this->post(route('dashboard.project.store', $workspace->slug), ['name' => 'Product', 'key' => 'PROD']);
        $project = $workspace->projects()->firstOrFail();
        $response->assertRedirect(route('board', [$workspace->slug, $project->slug]));
        $this->assertSame(4, $project->columns()->count());
    }
}
