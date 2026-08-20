<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedTinyInteger('cycle_length_weeks')->nullable()->after('board_style');
        });
        Schema::create('cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('status', 20)->default('active');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'number']);
            $table->index(['project_id', 'status']);
        });
        Schema::create('cycle_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('outcome', 20)->default('active');
            $table->timestamps();
            $table->unique(['cycle_id', 'task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cycle_task');
        Schema::dropIfExists('cycles');
        Schema::table('projects', fn (Blueprint $table) => $table->dropColumn('cycle_length_weeks'));
    }
};
