<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sppds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama_driver');
            $table->string('keperluan_dinas');
            $table->string('no_kendaraan', 50);
            $table->string('jenis_kendaraan', 120);
            $table->date('tanggal_dinas');
            $table->text('tujuan');
            $table->decimal('total_tol', 14, 2)->default(0);
            $table->decimal('total_bbm', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->string('status', 32)->default('pending');
            $table->text('revision_note')->nullable();
            $table->timestamp('revision_at')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('admin_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('admin_verified_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('sppd_tolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sppd_id')->constrained('sppds')->cascadeOnDelete();
            $table->string('leg', 16)->default('berangkat');
            $table->string('dari_tol');
            $table->string('ke_tol');
            $table->decimal('harga', 12, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('sppd_fuels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sppd_id')->constrained('sppds')->cascadeOnDelete();
            $table->decimal('liter', 10, 2)->default(0);
            $table->decimal('harga_per_liter', 12, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sppd_fuels');
        Schema::dropIfExists('sppd_tolls');
        Schema::dropIfExists('sppds');
    }
};
