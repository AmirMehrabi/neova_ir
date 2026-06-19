<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE workspace_members MODIFY role ENUM('owner', 'admin', 'member', 'user', 'viewer') NOT NULL DEFAULT 'user'");
        }

        DB::table('workspace_members')
            ->where('role', 'member')
            ->update(['role' => 'user']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE workspace_members MODIFY role ENUM('owner', 'admin', 'user', 'viewer') NOT NULL DEFAULT 'user'");
        }

        Schema::create('workspace_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('phone', 11);
            $table->string('role', 20)->default('user');
            $table->string('code_hash', 64)->unique();
            $table->string('status', 20)->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'phone', 'status']);
        });

        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('workspace_invitations');

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE workspace_members MODIFY role ENUM('owner', 'admin', 'member', 'user', 'viewer') NOT NULL DEFAULT 'member'");
        }

        DB::table('workspace_members')
            ->where('role', 'user')
            ->update(['role' => 'member']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE workspace_members MODIFY role ENUM('owner', 'admin', 'member') NOT NULL DEFAULT 'member'");
        }
    }
};
