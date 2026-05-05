<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bbm_reports', function (Blueprint $table) {
            $table->string('jenis_pengisian', 64)
                ->default('Operasional')
                ->after('jenis_kendaraan');
        });
    }

    public function down(): void
    {
        Schema::table('bbm_reports', function (Blueprint $table) {
            $table->dropColumn('jenis_pengisian');
        });
    }
};
