<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bbm_reports', function (Blueprint $table) {
            $table->dropForeign(['kendaraan_id']);
        });

        // MySQL: kolom harus nullable agar ON DELETE SET NULL berlaku
        DB::statement('ALTER TABLE bbm_reports MODIFY kendaraan_id BIGINT UNSIGNED NULL');

        Schema::table('bbm_reports', function (Blueprint $table) {
            $table->foreign('kendaraan_id')
                ->references('id')
                ->on('kendaraans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('bbm_reports')->whereNull('kendaraan_id')->exists()) {
            throw new RuntimeException('Tidak bisa rollback: ada laporan BBM dengan kendaraan_id NULL. Hapus atau tautkan ulang baris tersebut.');
        }

        Schema::table('bbm_reports', function (Blueprint $table) {
            $table->dropForeign(['kendaraan_id']);
        });

        DB::statement('ALTER TABLE bbm_reports MODIFY kendaraan_id BIGINT UNSIGNED NOT NULL');

        Schema::table('bbm_reports', function (Blueprint $table) {
            $table->foreign('kendaraan_id')
                ->references('id')
                ->on('kendaraans')
                ->restrictOnDelete();
        });
    }
};
