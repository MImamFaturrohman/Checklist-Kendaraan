<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bbm_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kendaraan_id')->constrained()->restrictOnDelete();
            $table->string('nomor_kendaraan');
            $table->string('jenis_kendaraan');
            $table->date('tanggal');
            $table->time('waktu');
            $table->decimal('odometer_sebelum', 12, 2);
            $table->decimal('odometer_sesudah', 12, 2);
            $table->decimal('liter', 10, 3);
            $table->decimal('harga_per_liter', 15, 2);
            $table->decimal('total_harga', 15, 2);
            $table->string('odometer_photo_path');
            $table->string('struk_photo_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bbm_reports');
    }
};
