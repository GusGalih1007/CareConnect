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
        Schema::create('volunteer_tasks', function (Blueprint $table) {
            $table->uuid('volunteer_task_id');
            $table->uuid('volunteer_id');
            $table->uuid('donation_id');
            $table->enum('status', ['offered', 'accepted', 'picking_up', 'in_transit', 'delivered', 'cancelled'])->default('offered');
            $table->datetime('pickup_time')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->json('proof')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('volunteer_id')->references('user_id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('donation_id')->references('donation_id')->on('donations')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volunteer_tasks');
    }
};
