<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
            $table->text('catatan_khusus')->nullable();
            $table->string('tanda_tangan')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('checklist_exteriors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('checklists')->cascadeOnDelete();
            $table->string('body_kendaraan')->nullable();
            $table->string('kaca')->nullable();
            $table->string('spion')->nullable();
            $table->string('lampu_utama')->nullable();
            $table->string('lampu_sein')->nullable();
            $table->string('ban')->nullable();
            $table->string('velg')->nullable();
            $table->string('wiper')->nullable();
            $table->text('catatan')->nullable();
            $table->string('foto')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('checklist_interiors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('checklists')->cascadeOnDelete();
            $table->string('jok')->nullable();
            $table->string('dashboard')->nullable();
            $table->string('ac')->nullable();
            $table->string('sabuk_pengaman')->nullable();
            $table->string('audio')->nullable();
            $table->string('kebersihan')->nullable();
            $table->text('catatan')->nullable();
            $table->string('foto')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('checklist_mesins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('checklists')->cascadeOnDelete();
            $table->string('mesin')->nullable();
            $table->string('oli')->nullable();
            $table->string('radiator')->nullable();
            $table->string('rem')->nullable();
            $table->string('kopling')->nullable();
            $table->string('transmisi')->nullable();
            $table->string('indikator')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_perlengkapans');
        Schema::dropIfExists('checklist_mesins');
        Schema::dropIfExists('checklist_interiors');
        Schema::dropIfExists('checklist_exteriors');
        Schema::dropIfExists('checklists');
    }
};
