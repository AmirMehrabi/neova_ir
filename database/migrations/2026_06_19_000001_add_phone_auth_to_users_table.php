<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users_new', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('national_code', 10)->nullable()->unique();
            $table->string('name');
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        DB::statement('INSERT INTO users_new (id, name, password, remember_token, created_at, updated_at) SELECT id, name, password, remember_token, created_at, updated_at FROM users');

        Schema::drop('users');
        Schema::rename('users_new', 'users');

        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->string('code', 6);
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamps();

            $table->index(['phone', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');

        Schema::create('users_new', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        DB::statement('INSERT INTO users_new (id, name, password, remember_token, created_at, updated_at) SELECT id, name, password, remember_token, created_at, updated_at FROM users');

        Schema::drop('users');
        Schema::rename('users_new', 'users');
    }
};
