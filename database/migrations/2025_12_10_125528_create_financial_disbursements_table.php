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
        Schema::create('financial_disbursements', function (Blueprint $table) {
            $table->uuid('financial_disbursement_id')->primary();
            $table->uuid('financial_request_id');
            $table->uuid('admin_id');
            $table->decimal('amount', 15, 2);
            $table->json('proof')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('financial_request_id')->references('financial_request_id')->on('financial_requests')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('admin_id')->references('user_id')->on('users')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_disbursements');
    }
};
