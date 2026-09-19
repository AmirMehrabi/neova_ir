<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardOrderingTest extends TestCase
{
    use RefreshDatabase;

    private function board(): array
    {
        $user = User::factory()->create();
        $workspace = Workspace::create(['owner_id' => $user->id, 'name' => 'تیم']);
        $project = Project::create(['workspace_id' => $workspace->id, 'name' => 'تخته']);
        $columns = collect(['A', 'B', 'C', 'D'])->map(fn ($title, $index) => $project->columns()->create([
            'title' => $title, 'position' => $index + 1, 'workflow_role' => 'other',
        ]));
        $this->actingAs($user);

        return [$workspace, $project, $columns];
    }

    public function test_column_can_be_saved_between_two_other_columns(): void
    {
        [$workspace, $project, $columns] = $this->board();
        $ids = [$columns[0]->id, $columns[3]->id, $columns[1]->id, $columns[2]->id];
        $this->postJson(route('board.columns.reorder', [$workspace->slug, $project->slug]), ['column_ids' => $ids])->assertOk();
        $this->assertSame($ids, $project->columns()->orderBy('position')->pluck('id')->all());
    }

    public function test_invalid_order_does_not_change_saved_positions(): void
    {
        [$workspace, $project, $columns] = $this->board();
        $this->postJson(route('board.columns.reorder', [$workspace->slug, $project->slug]), [
            'column_ids' => [$columns[0]->id, $columns[0]->id, $columns[2]->id, $columns[3]->id],
        ])->assertRedirect()->assertSessionHasErrors(['column_ids.0', 'column_ids.1']);
        $this->assertSame($columns->pluck('id')->all(), $project->columns()->orderBy('position')->pluck('id')->all());
    }

    public function test_task_moves_into_middle_of_populated_column_then_into_empty_column(): void
    {
        [$workspace, $project, $columns] = $this->board();
        $moving = Task::create(['column_id' => $columns[0]->id, 'task_number' => 1, 'title' => 'Move', 'position' => 1]);
        $first = Task::create(['column_id' => $columns[1]->id, 'task_number' => 2, 'title' => 'First', 'position' => 1]);
        $last = Task::create(['column_id' => $columns[1]->id, 'task_number' => 3, 'title' => 'Last', 'position' => 2]);
        $url = route('board.task.move', [$workspace->slug, $project->slug, $moving]);
        $this->postJson($url, ['column_id' => $columns[1]->id, 'position' => 1])->assertOk();
        $this->assertSame([$first->id, $moving->id, $last->id], $columns[1]->tasks()->orderBy('position')->pluck('id')->all());
        $this->postJson($url, ['column_id' => $columns[2]->id, 'position' => 0])->assertOk();
        $this->assertSame([$moving->id], $columns[2]->tasks()->pluck('id')->all());
        $this->assertSame([$first->id, $last->id], $columns[1]->tasks()->orderBy('position')->pluck('id')->all());
    }
}
