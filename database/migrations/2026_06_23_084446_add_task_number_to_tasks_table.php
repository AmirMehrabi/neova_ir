<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedInteger('task_number')->nullable()->after('column_id');
        });

        $projects = DB::table('projects')->get();
        foreach ($projects as $project) {
            $key = $project->key;
            $pattern = $key !== ''
                ? '/^' . preg_quote($key, '/') . '-(\d+)/u'
                : '/^-(\d+)/u';

            $columnIds = DB::table('project_columns')
                ->where('project_id', $project->id)
                ->pluck('id');

            $tasks = DB::table('tasks')
                ->whereIn('column_id', $columnIds)
                ->orderBy('id')
                ->get();

            foreach ($tasks as $task) {
                if (preg_match($pattern, $task->title, $matches)) {
                    $number = (int) $matches[1];
                    $cleanTitle = ltrim(substr($task->title, strlen($matches[0])));

                    DB::table('tasks')->where('id', $task->id)->update([
                        'task_number' => $number,
                        'title' => $cleanTitle,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('task_number');
        });
    }
};
