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
        Schema::create('donations', function (Blueprint $table) {
            $table->uuid('donation_id')->primary();
            // $table->uuid('donation_code')->unique();
            $table->uuid('user_id');
            $table->uuid('request_id')->nullable();
            $table->uuid('category_id')->nullable();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->integer('quantity')->nullable()->default(1);
            $table->enum('condition', ['new', 'good_use', 'needs_repair'])->default('good_use');
            $table->enum('status', ['available', 'reserved', 'picked_up','delivered','cancelled'])->default('available');
            $table->uuid('location_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('request_id')->references('donation_request_id')->on('donation_requests')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('category_id')->references('category_id')->on('categories')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('location_id')->references('location_id')->on('locations')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
