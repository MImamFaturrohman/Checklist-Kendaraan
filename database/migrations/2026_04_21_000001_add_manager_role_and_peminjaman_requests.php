<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'driver', 'manager') DEFAULT 'driver'");

        Schema::create('peminjaman_requests', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('nip');
            $table->string('divisi');
            $table->string('nomor_kendaraan');
            $table->string('jenis_kendaraan');
            $table->text('alasan');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan_manager')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman_requests');
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'driver') DEFAULT 'driver'");
    }
};
