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
        Schema::create('page_role_actions', function (Blueprint $table) {
            $table->uuid('page_role_actions_id');
            $table->uuid('page_code');
            $table->uuid('role_id');
            $table->string('page_name', 150);
            $table->json('action');
            $table->timestamps();

            $table->foreign('page_code')->references('page_code')->on('pages')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('role_id')->references('role_id')->on('roles')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_role_actions');
    }
};
