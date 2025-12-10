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
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('message_id')->primary();
            // $table->uuid('message_code');
            $table->uuid('sender_id');
            $table->uuid('receiver_id');
            $table->text('body');
            $table->json('attachments')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sender_id')->references('user_id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('receiver_id')->references('user_id')->on('users')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
