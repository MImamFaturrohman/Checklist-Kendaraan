<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_kejadians', function (Blueprint $table) {
            $table->longText('ttd_pelapor')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('laporan_kejadians', function (Blueprint $table) {
            $table->longText('ttd_pelapor')->nullable(false)->change();
        });
    }
};
