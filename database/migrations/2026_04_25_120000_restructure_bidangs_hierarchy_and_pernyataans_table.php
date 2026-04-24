<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bidangs', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
        });

        $distinctGrups = DB::table('bidangs')->distinct()->pluck('grup')->filter();

        $order = 0;
        foreach ($distinctGrups as $grup) {
            $parentId = DB::table('bidangs')->insertGetId([
                'parent_id' => null,
                'grup' => $grup,
                'nama' => $grup,
                'sort_order' => $order++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('bidangs')
                ->where('grup', $grup)
                ->where('id', '!=', $parentId)
                ->update(['parent_id' => $parentId]);
        }

        Schema::table('bidangs', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('bidangs')->restrictOnDelete();
            $table->dropColumn('grup');
        });

        Schema::create('pernyataans', function (Blueprint $table) {
            $table->id();
            $table->text('isi_pernyataan');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('peminjaman_pernyataan_points')) {
            $points = DB::table('peminjaman_pernyataan_points')->orderBy('urutan')->orderBy('id')->get();
            foreach ($points as $p) {
                DB::table('pernyataans')->insert([
                    'isi_pernyataan' => $p->teks,
                    'urutan' => (int) $p->urutan,
                    'created_at' => $p->created_at ?? now(),
                    'updated_at' => $p->updated_at ?? now(),
                ]);
            }
        }

        Schema::dropIfExists('peminjaman_pernyataan_meta');
        Schema::dropIfExists('peminjaman_pernyataan_points');
    }

    public function down(): void
    {
        // Rollback tidak didukung: struktur parent_id + pernyataans mengganti skema lama.
    }
};
