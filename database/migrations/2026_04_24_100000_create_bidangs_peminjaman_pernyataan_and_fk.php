<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bidangs', function (Blueprint $table) {
            $table->id();
            $table->string('grup', 120);
            $table->string('nama', 200);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('peminjaman_pernyataan_meta', function (Blueprint $table) {
            $table->id();
            $table->text('pengantar_text');
            $table->timestamps();
        });

        Schema::create('peminjaman_pernyataan_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->text('teks');
            $table->timestamps();
        });

        Schema::table('peminjaman_requests', function (Blueprint $table) {
            $table->foreignId('bidang_id')->nullable()->after('jabatan')->constrained('bidangs')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bidang_id');
        });

        Schema::dropIfExists('peminjaman_pernyataan_points');
        Schema::dropIfExists('peminjaman_pernyataan_meta');
        Schema::dropIfExists('bidangs');
    }
};
