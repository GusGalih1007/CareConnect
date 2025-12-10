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
        Schema::create('donation_matches', function (Blueprint $table) {
            $table->uuid('donation_match_id');
            $table->uuid('donation_id');
            $table->uuid('request_id');
            $table->integer('score')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('donation_id')->references('donation_id')->on('donations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('request_id')->references('donation_request_id')->on('donation_requests')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_matches');
    }
};
