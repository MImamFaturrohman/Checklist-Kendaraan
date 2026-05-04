<?php

use App\Models\Checklist;
use App\Models\Kendaraan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kendaraans', function (Blueprint $table) {
            $table->unsignedBigInteger('km_current')->nullable()->after('set_km');
        });

        foreach (Kendaraan::query()->pluck('nomor_kendaraan') as $nomor) {
            $kmAkhir = Checklist::query()
                ->where('nomor_kendaraan', $nomor)
                ->whereNotNull('km_akhir')
                ->orderByDesc('created_at')
                ->value('km_akhir');

            if ($kmAkhir !== null) {
                Kendaraan::query()
                    ->where('nomor_kendaraan', $nomor)
                    ->update(['km_current' => $kmAkhir]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('kendaraans', function (Blueprint $table) {
            $table->dropColumn('km_current');
        });
    }
};
