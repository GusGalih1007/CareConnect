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
        Schema::create('financial_usage_reports', function (Blueprint $table) {
            $table->uuid('financial_usage_report_id')->primary();
            $table->uuid('financial_request_id');
            $table->uuid('user_id');
            $table->text('description')->nullable();
            $table->json('files')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('financial_request_id')->references('financial_request_id')->on('financial_requests')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_usage_reports');
    }
};
