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
            // Tambah kolom baru
            $table->string('pimpinan_nama', 200)->nullable()->after('manager_email');
            $table->string('pimpinan_email', 255)->nullable()->after('pimpinan_nama');
            $table->string('jabatan', 150)->nullable()->after('pimpinan_email');
        });

        // Migrasi data: manager_nama → pimpinan, jabatan = "Manajer"
        DB::statement("
            UPDATE bidangs
            SET pimpinan_nama  = manager_nama,
                pimpinan_email = manager_email,
                jabatan        = 'Manajer'
            WHERE manager_nama IS NOT NULL
        ");

        // Fallback: baris yang tidak punya manajer tapi punya team leader
        DB::statement("
            UPDATE bidangs
            SET pimpinan_nama  = team_leader_nama,
                pimpinan_email = team_leader_email,
                jabatan        = 'Team Leader'
            WHERE manager_nama IS NULL
              AND team_leader_nama IS NOT NULL
        ");

        Schema::table('bidangs', function (Blueprint $table) {
            // Hapus kolom lama
            $table->dropColumn([
                'manager_nama',
                'manager_email',
                'team_leader_nama',
                'team_leader_email',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('bidangs', function (Blueprint $table) {
            $table->string('manager_nama', 200)->nullable()->after('nama');
            $table->string('manager_email', 255)->nullable()->after('manager_nama');
            $table->string('team_leader_nama', 200)->nullable()->after('manager_email');
            $table->string('team_leader_email', 255)->nullable()->after('team_leader_nama');
        });

        // Restore dari pimpinan → manager (best-effort)
        DB::statement("
            UPDATE bidangs
            SET manager_nama  = pimpinan_nama,
                manager_email = pimpinan_email
            WHERE jabatan = 'Manajer'
        ");
        DB::statement("
            UPDATE bidangs
            SET team_leader_nama  = pimpinan_nama,
                team_leader_email = pimpinan_email
            WHERE jabatan = 'Team Leader'
        ");

        Schema::table('bidangs', function (Blueprint $table) {
            $table->dropColumn(['pimpinan_nama', 'pimpinan_email', 'jabatan']);
        });
    }
};
