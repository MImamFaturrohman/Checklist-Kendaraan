<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            // Ubah driver_terima menjadi nullable
            $table->string('driver_terima')->nullable()->change();

            // Approval
            // NULL = no approval needed (driver_terima exists)
            // pending = waiting superadmin approval (no driver_terima)
            // approved = approved by superadmin
            // rejected = rejected by superadmin
            $table->string('approval_status')->nullable()->after('pdf_path');
            $table->text('approval_note')->nullable()->after('approval_status');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approval_note');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            // Hapus foreign key terlebih dahulu
            $table->dropForeign(['approved_by']);

            // Hapus kolom approval
            $table->dropColumn([
                'approval_status',
                'approval_note',
                'approved_by'
            ]);

            // Kembalikan driver_terima menjadi NOT NULL
            $table->string('driver_terima')->nullable(false)->change();
        });
    }
};