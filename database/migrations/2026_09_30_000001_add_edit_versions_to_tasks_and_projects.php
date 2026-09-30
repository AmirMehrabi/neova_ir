<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', fn (Blueprint $table) => $table->unsignedBigInteger('edit_version')->default(1));
        Schema::table('projects', fn (Blueprint $table) => $table->unsignedBigInteger('edit_version')->default(1));
    }

    public function down(): void
    {
        Schema::table('tasks', fn (Blueprint $table) => $table->dropColumn('edit_version'));
        Schema::table('projects', fn (Blueprint $table) => $table->dropColumn('edit_version'));
    }
};
