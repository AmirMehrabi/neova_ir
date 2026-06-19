<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->integer('position')->default(0);
            $table->string('color', 20)->nullable();
            $table->timestamps();

            $table->index(['project_id', 'position']);
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('column_id')->constrained('project_columns')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['بالا', 'متوسط', 'پایین'])->default('متوسط');
            $table->date('due_date')->nullable();
            $table->json('assignees')->nullable();
            $table->json('tags')->nullable();
            $table->json('checklist')->nullable();
            $table->json('comments')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['column_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('project_columns');
    }
};
