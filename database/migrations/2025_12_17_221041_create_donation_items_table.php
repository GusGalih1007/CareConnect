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
        Schema::create('donation_items', function (Blueprint $table) {
            $table->uuid('donation_item_id')->primary();
            $table->uuid('donation_id');
            $table->uuid('category_id');
            $table->string('item_name')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->enum('condition', ['new', 'good_used', 'needs_repair'])->default('good_used');
            $table->enum('status', ['available', 'reserved', 'picked_up', 'delivered', 'cancelled'])->default('available');
            $table->integer('reserved_quantity')->default(0); // Untuk partial fulfillment
            $table->json('images')->nullable(); // Multiple images per item
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('donation_id')->references('donation_id')->on('donations')->onDelete('cascade');
            $table->foreign('category_id')->references('category_id')->on('categories')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_items');
    }
};
