<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('bbm_reports')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('bbm_reports')->where('id', $row->id)->update([
                    'odometer_sebelum' => (int) round((float) $row->odometer_sebelum),
                    'odometer_sesudah' => (int) round((float) $row->odometer_sesudah),
                ]);
            }
        });

        Schema::table('bbm_reports', function (Blueprint $table) {
            $table->unsignedInteger('odometer_sebelum')->change();
            $table->unsignedInteger('odometer_sesudah')->change();
        });
    }

    public function down(): void
    {
        Schema::table('bbm_reports', function (Blueprint $table) {
            $table->decimal('odometer_sebelum', 12, 2)->change();
            $table->decimal('odometer_sesudah', 12, 2)->change();
        });
    }
};
