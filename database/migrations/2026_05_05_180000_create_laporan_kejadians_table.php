<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_kejadians', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 255);
            $table->string('nip', 50);
            $table->string('jabatan', 150);
            $table->foreignId('bidang_id')->constrained('bidangs')->cascadeOnDelete();
            $table->dateTime('waktu_kejadian');
            $table->string('kategori', 20);
            $table->string('lokasi_kejadian', 500);
            $table->string('nomor_kendaraan', 20);
            $table->string('jenis_kendaraan', 100);
            $table->text('peristiwa');
            $table->text('sebelum_kejadian');
            $table->text('uraian_kejadian');
            $table->text('akibat');
            $table->string('foto_path')->nullable();
            $table->longText('ttd_manager');
            $table->longText('ttd_pelapor');
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_kejadians');
    }
};
