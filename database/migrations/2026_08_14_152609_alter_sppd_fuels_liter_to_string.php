<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah kolom sppd_fuels.liter dari decimal(10,2) menjadi string(255)
     * agar dapat menyimpan nama lokasi parkir.
     * Kolom 'total' juga tidak lagi relevan (biaya parkir langsung dari harga_per_liter).
     */
    public function up(): void
    {
        Schema::table('sppd_fuels', function (Blueprint $table) {
            $table->string('liter', 255)->default('')->change();
        });
    }

    public function down(): void
    {
        // Set ke 0 untuk baris yang tidak bisa dikonversi balik ke decimal
        DB::statement("UPDATE sppd_fuels SET liter = '0' WHERE liter = '' OR liter REGEXP '[^0-9.]'");
        Schema::table('sppd_fuels', function (Blueprint $table) {
            $table->decimal('liter', 10, 2)->default(0)->change();
        });
    }
};
