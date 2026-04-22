<?php

use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\ProfileController;
use App\Models\Checklist;
use App\Models\Kendaraan;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Landing page (public)
Route::get('/', [PeminjamanController::class, 'landingPage'])->name('landing');
Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');

// Public kendaraan list API for landing page form
Route::get('/api/kendaraan/public-list', [KendaraanController::class, 'apiList'])->name('api.kendaraan.public-list');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/checklists/create', function () {
        $kendaraans = Kendaraan::orderBy('nomor_kendaraan')->get();
        $drivers = User::where('role', 'driver')->orderBy('name')->get();
        $user = auth()->user();
        return view('checklists.create', compact('kendaraans', 'drivers', 'user'));
    })->name('checklists.create');

    Route::post('/checklists', [ChecklistController::class, 'store'])->name('checklists.store');

    // API endpoints for checklist form
    Route::get('/api/kendaraan/lookup', [ChecklistController::class, 'lookupKendaraan'])->name('api.kendaraan.lookup');
    Route::get('/api/kendaraan/last-km', [ChecklistController::class, 'lastKm'])->name('api.kendaraan.last-km');
    Route::get('/api/kendaraan/list', [KendaraanController::class, 'apiList'])->name('api.kendaraan.list');

    // Admin routes
    Route::get('/admin/database-sheet', [ChecklistController::class, 'databaseSheet'])->name('admin.database-sheet');

    Route::get('/admin/database-sheet/export', function () {
        abort_unless(auth()->user()?->role === 'admin', 403);
        return app(ChecklistController::class)->exportExcel();
    })->name('admin.database-sheet.export');

    Route::get('/admin/log-foto-fisik', [ChecklistController::class, 'logFotoFisik'])->name('admin.log-foto-fisik');

    Route::get('/admin/arsip-pdf', [ChecklistController::class, 'arsipPdf'])->name('admin.arsip-pdf');

    // Master Armada CRUD
    Route::get('/admin/master-armada', [KendaraanController::class, 'index'])->name('admin.master-armada');
    Route::post('/admin/master-armada', [KendaraanController::class, 'store'])->name('admin.master-armada.store');
    Route::put('/admin/master-armada/{kendaraan}', [KendaraanController::class, 'update'])->name('admin.master-armada.update');
    Route::delete('/admin/master-armada/{kendaraan}', [KendaraanController::class, 'destroy'])->name('admin.master-armada.destroy');

    // Admin: request peminjaman list (read-only)
    Route::get('/admin/peminjaman', [PeminjamanController::class, 'adminIndex'])->name('admin.peminjaman');

    // Manager: approval page
    Route::get('/manager/peminjaman', [PeminjamanController::class, 'managerIndex'])->name('manager.peminjaman');
    Route::post('/manager/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve'])->name('manager.peminjaman.approve');
    Route::post('/manager/peminjaman/{peminjaman}/reject', [PeminjamanController::class, 'reject'])->name('manager.peminjaman.reject');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
