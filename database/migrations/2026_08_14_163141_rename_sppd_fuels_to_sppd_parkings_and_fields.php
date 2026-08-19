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
        // 1. Rename table sppd_fuels to sppd_parkings
        Schema::rename('sppd_fuels', 'sppd_parkings');

        // 2. Rename columns inside sppd_parkings
        Schema::table('sppd_parkings', function (Blueprint $table) {
            $table->renameColumn('liter', 'lokasi');
            $table->renameColumn('harga_per_liter', 'biaya_parkir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sppd_parkings', function (Blueprint $table) {
            $table->renameColumn('biaya_parkir', 'harga_per_liter');
            $table->renameColumn('lokasi', 'liter');
        });

        Schema::rename('sppd_parkings', 'sppd_fuels');
    }
};
