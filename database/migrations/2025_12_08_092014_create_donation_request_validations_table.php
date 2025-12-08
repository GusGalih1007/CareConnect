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
        Schema::create('donation_request_validations', function (Blueprint $table) {
            $table->increments('request_validation_id');
            $table->unsignedInteger('donation_request_id');
            $table->unsignedInteger('admin_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'need_revision'])->default('pending');
            $table->text('note')->nullable();
            $table->json('evidence_files')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('donation_request_id')->references('donation_request_id')->on('donation_requests')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('admin_id')->references('user_id')->on('users')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_request_validations');
    }
};
