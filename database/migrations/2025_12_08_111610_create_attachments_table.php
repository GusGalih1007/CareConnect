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
        Schema::create('attachments', function (Blueprint $table) {
            $table->increments('attachment_id');
            $table->string('owner_type', 50);
            $table->unsignedInteger('owner_id');
            $table->text('path');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('owner_id')->references('donation_request_id')->on('donation_requests')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
