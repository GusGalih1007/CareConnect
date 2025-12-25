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
        Schema::create('request_item_validations', function (Blueprint $table) {
            $table->uuid('request_item_validation_id')->primary();
            $table->uuid('request_validation_id');
            $table->uuid('donation_request_item_id');
            $table->uuid('category_id');
            $table->uuid('admin_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'need_revision'])->default('pending');
            $table->text('note')->nullable();
            $table->json('evidence_files')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('request_validation_id')->references('request_validation_id')->on('donation_request_validations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('donation_request_item_id')->references('donation_request_item_id')->on('donation_request_items')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('admin_id')->references('user_id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('category_id')->references('category_id')->on('categories')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_item_validations');
    }
};
