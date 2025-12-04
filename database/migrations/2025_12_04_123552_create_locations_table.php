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
        Schema::create('locations', function (Blueprint $table) {
            $table->increment('location_id');
            $table->unsignedBigInteger('user_id');
            $table->string('address');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longtitude', 10, 7);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
