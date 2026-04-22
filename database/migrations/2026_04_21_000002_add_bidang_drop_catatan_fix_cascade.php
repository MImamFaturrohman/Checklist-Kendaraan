<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add bidang to kendaraans
        Schema::table('kendaraans', function (Blueprint $table) {
            $table->string('bidang')->nullable()->after('jenis_kendaraan');
        });

        // 2. Drop catatan from checklist sub-tables
        Schema::table('checklist_exteriors', function (Blueprint $table) {
            $table->dropColumn('catatan');
        });
        Schema::table('checklist_interiors', function (Blueprint $table) {
            $table->dropColumn('catatan');
        });
        Schema::table('checklist_mesins', function (Blueprint $table) {
            $table->dropColumn('catatan');
        });

        // 3. Make checklists.user_id nullable + change cascade to nullOnDelete
        Schema::table('checklists', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kendaraans', function (Blueprint $table) {
            $table->dropColumn('bidang');
        });

        Schema::table('checklist_exteriors', function (Blueprint $table) {
            $table->text('catatan')->nullable();
        });
        Schema::table('checklist_interiors', function (Blueprint $table) {
            $table->text('catatan')->nullable();
        });
        Schema::table('checklist_mesins', function (Blueprint $table) {
            $table->text('catatan')->nullable();
        });

        Schema::table('checklists', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
