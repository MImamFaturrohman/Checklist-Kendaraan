<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bidangs', function (Blueprint $table) {
            $table->string('manager_nama', 200)->nullable()->after('nama');
            $table->string('manager_email', 255)->nullable()->after('manager_nama');
        });

        Schema::table('laporan_kejadians', function (Blueprint $table) {
            $table->string('manager_approval_token', 64)->nullable()->unique()->after('pdf_path');
            // make ttd_manager nullable (it was NOT NULL in the original migration)
            $table->longText('ttd_manager')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bidangs', function (Blueprint $table) {
            $table->dropColumn(['manager_nama', 'manager_email']);
        });

        Schema::table('laporan_kejadians', function (Blueprint $table) {
            $table->dropColumn('manager_approval_token');
            $table->longText('ttd_manager')->nullable(false)->change();
        });
    }
};
