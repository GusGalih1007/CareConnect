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
        Schema::table('attachments', function (Blueprint $table) {
            $table->foreign('owner_id', 'attachments_donation_request_item')->references('donation_request_item_id')->on('donation_request_items')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('owner_id', 'attachment_financial_request')->references('financial_request_id')->on('financial_request')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            //
        });
    }
};
