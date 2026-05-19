<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite does not enforce ENUM columns; the Laravel column type is just a string
        // constraint. Only MySQL / MariaDB need the ENUM definition altered.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE peminjaman_requests MODIFY COLUMN status ENUM('pending','approved','rejected','expired') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Revert expired rows to rejected before narrowing the ENUM
            DB::statement("UPDATE peminjaman_requests SET status = 'rejected' WHERE status = 'expired'");
            DB::statement("ALTER TABLE peminjaman_requests MODIFY COLUMN status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
        }
    }
};
