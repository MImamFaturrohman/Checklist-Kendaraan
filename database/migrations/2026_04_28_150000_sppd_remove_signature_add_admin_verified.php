<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sppds', function (Blueprint $table) {
            if (Schema::hasColumn('sppds', 'signature_path')) {
                $table->dropColumn('signature_path');
            }
        });

        Schema::table('sppds', function (Blueprint $table) {
            if (! Schema::hasColumn('sppds', 'admin_verified_by')) {
                $table->foreignId('admin_verified_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('sppds', 'admin_verified_at')) {
                $table->timestamp('admin_verified_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sppds', function (Blueprint $table) {
            if (Schema::hasColumn('sppds', 'admin_verified_by')) {
                $table->dropForeign(['admin_verified_by']);
            }
        });

        Schema::table('sppds', function (Blueprint $table) {
            if (Schema::hasColumn('sppds', 'admin_verified_at')) {
                $table->dropColumn('admin_verified_at');
            }
            if (Schema::hasColumn('sppds', 'admin_verified_by')) {
                $table->dropColumn('admin_verified_by');
            }
            if (! Schema::hasColumn('sppds', 'signature_path')) {
                $table->string('signature_path')->nullable();
            }
        });
    }
};
