<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->string('timezone', 64)->default('Asia/Tehran')->after('description');
        });

        Schema::table('project_columns', function (Blueprint $table) {
            $table->string('workflow_role', 20)->nullable()->after('wip_limit')->index();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('due_date')->index();
            $table->text('blocked_reason')->nullable()->after('is_blocked');
            $table->timestamp('completed_at')->nullable()->after('blocked_reason')->index();
        });

        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['task_id', 'user_id']);
            $table->index(['user_id', 'task_id']);
        });

        Schema::create('task_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('planned_for');
            $table->string('bucket', 20)->default('must');
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
            $table->unique(['task_id', 'user_id', 'planned_for']);
            $table->index(['user_id', 'planned_for', 'bucket', 'position'], 'task_plans_today_index');
        });

        $roles = [
            'پس‌زمینه' => 'backlog',
            'بک‌لاگ' => 'backlog',
            'Backlog' => 'backlog',
            'آماده' => 'ready',
            'Ready' => 'ready',
            'در حال انجام' => 'active',
            'Doing' => 'active',
            'In Progress' => 'active',
            'بررسی' => 'active',
            'انجام شده' => 'done',
            'Done' => 'done',
            'Completed' => 'done',
        ];

        foreach ($roles as $title => $role) {
            DB::table('project_columns')->where('title', $title)->update(['workflow_role' => $role]);
        }
        DB::table('project_columns')->whereNull('workflow_role')->update(['workflow_role' => 'other']);

        DB::table('tasks')->whereNotNull('assignees')->orderBy('id')->chunkById(200, function ($tasks) {
            foreach ($tasks as $task) {
                $names = json_decode($task->assignees, true);
                if (! is_array($names) || $names === []) continue;
                $project = DB::table('project_columns')->join('projects', 'projects.id', '=', 'project_columns.project_id')
                    ->where('project_columns.id', $task->column_id)
                    ->select('projects.id', 'projects.workspace_id', 'projects.visibility')->first();
                if (! $project) continue;
                $workspace = DB::table('workspaces')->where('id', $project->workspace_id)->first();
                if (! $workspace) continue;
                $eligibleIds = collect([$workspace->owner_id])->merge(
                    DB::table('workspace_members')->where('workspace_id', $workspace->id)->pluck('user_id')
                );
                if ($project->visibility === 'private') {
                    $adminIds = DB::table('workspace_members')->where('workspace_id', $workspace->id)->where('role', 'admin')->pluck('user_id');
                    $projectIds = DB::table('project_members')->where('project_id', $project->id)->pluck('user_id');
                    $eligibleIds = collect([$workspace->owner_id])->merge($adminIds)->merge($projectIds);
                }
                $users = DB::table('users')->whereIn('id', $eligibleIds->unique())->get()->groupBy(function ($user) {
                    return trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: $user->name;
                });
                foreach (array_unique($names) as $name) {
                    $matches = $users->get($name, collect());
                    if ($matches->count() !== 1) continue;
                    DB::table('task_user')->insertOrIgnore([
                        'task_id' => $task->id, 'user_id' => $matches->first()->id,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_plans');
        Schema::dropIfExists('task_user');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['is_blocked', 'blocked_reason', 'completed_at']);
        });
        Schema::table('project_columns', function (Blueprint $table) {
            $table->dropColumn('workflow_role');
        });
        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
};
