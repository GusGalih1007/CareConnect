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
        Schema::create('donation_financials', function (Blueprint $table) {
            $table->uuid('donation_financial_id')->primary();
            $table->uuid('donor_id');
            $table->uuid('financial_request_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->uuid('payment_gateway_id');
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('gateway_reference', 191)->nullable();
            $table->json('proof');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('donor_id')->references('user_id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('financial_request_id')->references('financial_request_id')->on('financial_requests')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('payment_gateway_id')->references('payment_gateway_id')->on('payment_gateways')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_financials');
    }
};
