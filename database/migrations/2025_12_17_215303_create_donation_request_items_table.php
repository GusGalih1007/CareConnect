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
        Schema::create('donation_request_items', function (Blueprint $table) {
            $table->uuid('donation_request_item_id')->primary();
            $table->uuid('donation_request_id');
            $table->uuid('category_id');
            $table->string('item_name', 100)->nullable(); // Nama spesifik item
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->enum('preferred_condition', ['new', 'good_used', 'needs_repair'])->default('good_used');
            $table->enum('priority', ['low', 'normal', 'urgent'])->default('normal'); // Urutan prioritas
            $table->enum('status', ['pending', 'partially_fulfilled', 'fulfilled', 'rejected'])->default('pending');
            $table->integer('fulfilled_quantity')->default(0); // Jumlah sudah terpenuhi
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('donation_request_id')->references('donation_request_id')->on('donation_requests')->onDelete('cascade');
            $table->foreign('category_id')->references('category_id')->on('categories')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_request_items');
    }
};
