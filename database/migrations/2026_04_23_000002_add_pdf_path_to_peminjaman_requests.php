<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman_requests', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('tanda_tangan');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman_requests', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
        });
    }
};
