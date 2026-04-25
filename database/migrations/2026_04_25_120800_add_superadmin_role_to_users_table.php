<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin', 'admin', 'manager', 'driver', 'pic_kendaraan') DEFAULT 'driver'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'manager', 'driver', 'pic_kendaraan') DEFAULT 'driver'");
    }
};
