<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_attachments', function (Blueprint $table) {
            $table->string('context', 20)->default('description')->after('task_id');
            $table->uuid('comment_id')->nullable()->after('context');
            $table->index(['task_id', 'context']);
            $table->index(['task_id', 'comment_id']);
        });
    }

    public function down(): void
    {
        Schema::table('task_attachments', function (Blueprint $table) {
            $table->dropIndex(['task_id', 'context']);
            $table->dropIndex(['task_id', 'comment_id']);
            $table->dropColumn(['context', 'comment_id']);
        });
    }
};
