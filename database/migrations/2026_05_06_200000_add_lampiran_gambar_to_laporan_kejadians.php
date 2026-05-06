<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_kejadians', function (Blueprint $table) {
            $table->json('lampiran_gambar')->nullable()->after('penjelasan_gambar');
        });

        foreach (DB::table('laporan_kejadians')->whereNotNull('foto_path')->get() as $row) {
            $payload = json_encode([[
                'path' => $row->foto_path,
                'penjelasan' => (string) $row->penjelasan_gambar,
            ]], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            DB::table('laporan_kejadians')->where('id', $row->id)->update(['lampiran_gambar' => $payload]);
        }
    }

    public function down(): void
    {
        Schema::table('laporan_kejadians', function (Blueprint $table) {
            $table->dropColumn('lampiran_gambar');
        });
    }
};
