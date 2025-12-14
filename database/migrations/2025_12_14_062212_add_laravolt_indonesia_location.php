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
        Schema::table('locations', function (Blueprint $table) {
            $table->char('province_code', 2)->nullable()->after('user_id');
            $table->char('city_code', 4)->nullable();
            $table->char('district_code', 7)->nullable();
            $table->char('village_code', 10)->nullable();
            $table->string('address')->nullable()->change();
            $table->decimal('latitude', 10, 7)->nullable()->change();
            $table->decimal('longitude', 10, 7)->nullable()->change();

            $table->foreign('province_code')->references('code')->on('indonesia_provinces')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('city_code')->references('code')->on('indonesia_cities')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('district_code')->references('code')->on('indonesia_districts')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('village_code')->references('code')->on('indonesia_villages')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['province_code', 'city_code', 'district_code', 'village_code']);
            $table->string('address')->nullable(false)->change();
            $table->decimal('latitude', 10, 7)->nullable(false)->change();
            $table->decimal('longitude', 10, 7)->nullable(false)->change();
        });
    }
};
