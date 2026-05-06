<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE laporan_kejadians CHANGE akibat penjelasan_gambar TEXT NOT NULL');
        } else {
            Schema::getConnection()->statement('ALTER TABLE laporan_kejadians RENAME COLUMN akibat TO penjelasan_gambar');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE laporan_kejadians CHANGE penjelasan_gambar akibat TEXT NOT NULL');
        } else {
            Schema::getConnection()->statement('ALTER TABLE laporan_kejadians RENAME COLUMN penjelasan_gambar TO akibat');
        }
    }
};
