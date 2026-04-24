<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman_requests', function (Blueprint $table) {
            $table->renameColumn('divisi', 'jabatan');
            $table->date('tanggal_peminjaman')->nullable()->after('jenis_kendaraan');
            $table->longText('tanda_tangan')->nullable()->after('alasan');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman_requests', function (Blueprint $table) {
            $table->renameColumn('jabatan', 'divisi');
            $table->dropColumn(['tanggal_peminjaman', 'tanda_tangan']);
        });
    }
};
