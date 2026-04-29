<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sppd_tolls', 'leg')) {
            Schema::table('sppd_tolls', function (Blueprint $table) {
                $table->string('leg', 16)->default('berangkat');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sppd_tolls', 'leg')) {
            Schema::table('sppd_tolls', function (Blueprint $table) {
                $table->dropColumn('leg');
            });
        }
    }
};
