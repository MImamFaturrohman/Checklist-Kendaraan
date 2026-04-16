<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create kendaraans table
        Schema::create('kendaraans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kendaraan')->unique();
            $table->string('jenis_kendaraan');
            $table->timestamps();
        });

        // 2. Update users role enum: manager -> driver
        // Drop old column and recreate
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'driver'])->default('driver')->after('password');
        });

        // 3. Drop old checklist child tables and recreate with new columns
        Schema::dropIfExists('checklist_perlengkapans');
        Schema::dropIfExists('checklist_mesins');
        Schema::dropIfExists('checklist_interiors');
        Schema::dropIfExists('checklist_exteriors');
        Schema::dropIfExists('checklists');

        // 4. Recreate checklists
        Schema::create('checklists', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('shift');
            $table->string('driver_serah');
            $table->string('driver_terima');
            $table->string('nomor_kendaraan');
            $table->string('jenis_kendaraan');
            $table->time('jam_serah_terima');
            $table->string('level_bbm');
            $table->string('bbm_terakhir')->nullable();
            $table->unsignedBigInteger('km_awal');
            $table->unsignedBigInteger('km_akhir')->nullable();
            $table->string('foto_bbm_dashboard')->nullable();
            $table->text('catatan_khusus')->nullable();
            $table->text('tanda_tangan_serah')->nullable();
            $table->text('tanda_tangan_terima')->nullable();
            $table->string('pdf_path')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // 5. Recreate checklist_exteriors with keterangan + 4 foto columns
        Schema::create('checklist_exteriors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('checklists')->cascadeOnDelete();
            $table->string('body_kendaraan')->nullable();
            $table->string('body_kendaraan_keterangan')->nullable();
            $table->string('kaca')->nullable();
            $table->string('kaca_keterangan')->nullable();
            $table->string('spion')->nullable();
            $table->string('spion_keterangan')->nullable();
            $table->string('lampu_utama')->nullable();
            $table->string('lampu_utama_keterangan')->nullable();
            $table->string('lampu_sein')->nullable();
            $table->string('lampu_sein_keterangan')->nullable();
            $table->string('ban')->nullable();
            $table->string('ban_keterangan')->nullable();
            $table->string('velg')->nullable();
            $table->string('velg_keterangan')->nullable();
            $table->string('wiper')->nullable();
            $table->string('wiper_keterangan')->nullable();
            $table->text('catatan')->nullable();
            $table->string('foto_depan')->nullable();
            $table->string('foto_kanan')->nullable();
            $table->string('foto_kiri')->nullable();
            $table->string('foto_belakang')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 6. Recreate checklist_interiors with keterangan + 3 foto columns
        Schema::create('checklist_interiors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('checklists')->cascadeOnDelete();
            $table->string('jok')->nullable();
            $table->string('jok_keterangan')->nullable();
            $table->string('dashboard')->nullable();
            $table->string('dashboard_keterangan')->nullable();
            $table->string('ac')->nullable();
            $table->string('ac_keterangan')->nullable();
            $table->string('sabuk_pengaman')->nullable();
            $table->string('sabuk_pengaman_keterangan')->nullable();
            $table->string('audio')->nullable();
            $table->string('audio_keterangan')->nullable();
            $table->string('kebersihan')->nullable();
            $table->string('kebersihan_keterangan')->nullable();
            $table->text('catatan')->nullable();
            $table->string('foto_1')->nullable();
            $table->string('foto_2')->nullable();
            $table->string('foto_3')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 7. Recreate checklist_mesins with keterangan + 3 foto columns
        Schema::create('checklist_mesins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('checklists')->cascadeOnDelete();
            $table->string('mesin')->nullable();
            $table->string('mesin_keterangan')->nullable();
            $table->string('oli')->nullable();
            $table->string('oli_keterangan')->nullable();
            $table->string('radiator')->nullable();
            $table->string('radiator_keterangan')->nullable();
            $table->string('rem')->nullable();
            $table->string('rem_keterangan')->nullable();
            $table->string('kopling')->nullable();
            $table->string('kopling_keterangan')->nullable();
            $table->string('transmisi')->nullable();
            $table->string('transmisi_keterangan')->nullable();
            $table->string('indikator')->nullable();
            $table->string('indikator_keterangan')->nullable();
            $table->text('catatan')->nullable();
            $table->string('foto_1')->nullable();
            $table->string('foto_2')->nullable();
            $table->string('foto_3')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 8. Recreate checklist_perlengkapans
        Schema::create('checklist_perlengkapans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('checklists')->cascadeOnDelete();
            $table->string('stnk')->nullable();
            $table->string('kir')->nullable();
            $table->string('dongkrak')->nullable();
            $table->string('toolkit')->nullable();
            $table->string('segitiga')->nullable();
            $table->string('apar')->nullable();
            $table->string('ban_cadangan')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_perlengkapans');
        Schema::dropIfExists('checklist_mesins');
        Schema::dropIfExists('checklist_interiors');
        Schema::dropIfExists('checklist_exteriors');
        Schema::dropIfExists('checklists');
        Schema::dropIfExists('kendaraans');

        // Revert role
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'manager'])->default('manager')->after('password');
        });
    }
};
