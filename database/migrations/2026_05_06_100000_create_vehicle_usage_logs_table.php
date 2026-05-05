<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kendaraan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nomor_kendaraan');
            $table->string('jenis_kendaraan');
            $table->time('jam_awal');
            $table->time('jam_akhir');
            $table->text('keperluan');
            $table->timestamps();

            $table->index(['nomor_kendaraan', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_usage_logs');
    }
};
