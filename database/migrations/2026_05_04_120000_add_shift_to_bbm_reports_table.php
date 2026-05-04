<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bbm_reports', function (Blueprint $table) {
            $table->string('shift', 20)->nullable()->after('waktu');
        });

        DB::table('bbm_reports')->whereNull('shift')->update(['shift' => 'luar']);
    }

    public function down(): void
    {
        Schema::table('bbm_reports', function (Blueprint $table) {
            $table->dropColumn('shift');
        });
    }
};
