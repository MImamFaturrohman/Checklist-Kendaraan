<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kendaraans', function (Blueprint $table) {
            $table->date('tanggal_stnk')->nullable()->after('set_km');
            $table->date('tanggal_pajak_stnk')->nullable()->after('tanggal_stnk');
            $table->date('tanggal_kir')->nullable()->after('tanggal_pajak_stnk');
            $table->string('status_kendaraan', 32)->default('Aktif')->after('tanggal_kir');
        });
    }

    public function down(): void
    {
        Schema::table('kendaraans', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_stnk',
                'tanggal_pajak_stnk',
                'tanggal_kir',
                'status_kendaraan',
            ]);
        });
    }
};
