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
        Schema::create('donation_item_matches', function (Blueprint $table) {
            $table->uuid('donation_item_match_id')->primary();
            $table->uuid('donation_item_id');
            $table->uuid('donation_request_item_id');
            $table->integer('matched_quantity'); // Jumlah yang dimatch
            $table->integer('score')->default(0); // Skor kecocokan
            $table->enum('status', ['pending', 'accepted', 'rejected', 'fulfilled'])->default('pending');
            $table->timestamps();

            $table->foreign('donation_item_id')->references('donation_item_id')->on('donation_items');
            $table->foreign('donation_request_item_id')->references('donation_request_item_id')->on('donation_request_items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_item_matches');
    }
};
