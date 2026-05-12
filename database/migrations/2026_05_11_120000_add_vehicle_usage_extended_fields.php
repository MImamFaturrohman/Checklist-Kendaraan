<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_usage_logs', function (Blueprint $table) {
            $table->string('level_bbm_awal', 64)->nullable()->after('keperluan');
            $table->string('level_bbm_akhir', 64)->nullable()->after('level_bbm_awal');
            $table->unsignedInteger('km_awal')->nullable()->after('level_bbm_akhir');
            $table->unsignedInteger('km_akhir')->nullable()->after('km_awal');
            $table->text('kondisi_sebelum_penggunaan')->nullable()->after('km_akhir');
            $table->text('kondisi_setelah_penggunaan')->nullable()->after('kondisi_sebelum_penggunaan');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_usage_logs', function (Blueprint $table) {
            $table->dropColumn([
                'level_bbm_awal',
                'level_bbm_akhir',
                'km_awal',
                'km_akhir',
                'kondisi_sebelum_penggunaan',
                'kondisi_setelah_penggunaan',
            ]);
        });
    }
};
