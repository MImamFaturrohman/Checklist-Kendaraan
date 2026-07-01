<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            // Drop foreign key on approved_by first
            $table->dropForeign(['approved_by']);

            // Drop old approval columns
            $table->dropColumn(['approval_status', 'approval_note', 'approved_by']);
        });

        Schema::table('checklists', function (Blueprint $table) {
            // Add new unified status field
            // NULL = no approval needed (driver_terima exists, PDF auto-generated)
            // pending = no driver_terima, waiting admin to mark as selesai
            // complete = admin has marked selesai and PDF has been generated
            $table->string('status')->nullable()->after('pdf_path');
        });

        // Migrate existing data: any row without pdf_path that previously had approval_status=pending → set status=pending
        // (We cannot recover this precisely since approval_status column is dropped, but all existing rows without
        //  pdf_path and without driver_terima should be status=pending)
        DB::statement("UPDATE checklists SET status = 'pending' WHERE pdf_path IS NULL AND driver_terima IS NULL");
        DB::statement("UPDATE checklists SET status = 'complete' WHERE pdf_path IS NOT NULL AND driver_terima IS NULL AND status IS NULL");
    }

    public function down(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('checklists', function (Blueprint $table) {
            $table->string('approval_status')->nullable()->after('pdf_path');
            $table->text('approval_note')->nullable()->after('approval_status');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approval_note');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }
};
