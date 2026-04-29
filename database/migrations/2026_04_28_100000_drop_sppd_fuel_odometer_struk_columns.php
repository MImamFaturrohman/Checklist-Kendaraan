<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $toDrop = [];
        if (Schema::hasColumn('sppd_fuels', 'odometer_path')) {
            $toDrop[] = 'odometer_path';
        }
        if (Schema::hasColumn('sppd_fuels', 'struk_path')) {
            $toDrop[] = 'struk_path';
        }
        if ($toDrop !== []) {
            Schema::table('sppd_fuels', function (Blueprint $table) use ($toDrop) {
                $table->dropColumn($toDrop);
            });
        }
    }

    public function down(): void
    {
        Schema::table('sppd_fuels', function (Blueprint $table) {
            $table->string('odometer_path')->nullable()->after('total');
            $table->string('struk_path')->nullable()->after('odometer_path');
        });
    }
};
