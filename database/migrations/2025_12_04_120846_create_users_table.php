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
            $table->increment('user_id');
            $table->char('user_code', 36)->unique();
            $table->string('username', 100);
            $table->string('email');
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->enum('user_type', ['donator', 'receiver']);
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('is_active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('role_id')->references('role_id')->on('roles')->onDelete('set null')->onUpdate('cascade');
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
