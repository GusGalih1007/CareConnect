<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            // $table->uuid('user_code')->unique();
            $table->string('username', 100);
            $table->string('email');
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->uuid('role_id');
            $table->json('user_type')->nullable(); // if user role is not admin/applocation manager
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('is_active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('role_id')->references('role_id')->on('roles')->onDelete('restrict')->onUpdate('cascade');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
