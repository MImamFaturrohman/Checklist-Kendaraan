<?php

use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
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

    // Combined Portal Pemeriksaan Kendaraan
    Route::get('/admin/portal-pemeriksaan', [ChecklistController::class, 'portalPemeriksaan'])->name('admin.portal-pemeriksaan');

    // AJAX API endpoints for real-time portal search
    Route::get('/api/admin/portal/database-sheet', [ChecklistController::class, 'apiPortalDatabaseSheet'])->name('api.admin.portal.database-sheet');
    Route::get('/api/admin/portal/log-foto', [ChecklistController::class, 'apiPortalLogFoto'])->name('api.admin.portal.log-foto');
    Route::get('/api/admin/portal/arsip-pdf', [ChecklistController::class, 'apiPortalArsipPdf'])->name('api.admin.portal.arsip-pdf');
    Route::get('/api/admin/portal/charts', [ChecklistController::class, 'apiPortalCharts'])->name('api.admin.portal.charts');

    // Master Armada CRUD
    Route::get('/admin/master-armada', [KendaraanController::class, 'index'])->name('admin.master-armada');
    Route::post('/admin/master-armada', [KendaraanController::class, 'store'])->name('admin.master-armada.store');
    Route::put('/admin/master-armada/{kendaraan}', [KendaraanController::class, 'update'])->name('admin.master-armada.update');
    Route::delete('/admin/master-armada/{kendaraan}', [KendaraanController::class, 'destroy'])->name('admin.master-armada.destroy');

    // Admin: Driver CRUD
    Route::get('/admin/drivers', [DriverController::class, 'index'])->name('admin.drivers');
    Route::post('/admin/drivers', [DriverController::class, 'store'])->name('admin.drivers.store');
    Route::put('/admin/drivers/{user}', [DriverController::class, 'update'])->name('admin.drivers.update');
    Route::delete('/admin/drivers/{user}', [DriverController::class, 'destroy'])->name('admin.drivers.destroy');

    // Combined Portal Manajemen Administrasi (Master Armada + Manajemen User)
    Route::get('/admin/portal-manajemen-administrasi', [UserManagementController::class, 'portal'])->name('admin.portal-manajemen');
    Route::post('/admin/users', [UserManagementController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [UserManagementController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::get('/api/admin/portal/kendaraan', [UserManagementController::class, 'apiKendaraan'])->name('api.admin.portal.kendaraan');
    Route::get('/api/admin/portal/users', [UserManagementController::class, 'apiUsers'])->name('api.admin.portal.users');

    // Admin: request peminjaman list (read-only) + PDF download
    Route::get('/admin/peminjaman', [PeminjamanController::class, 'adminIndex'])->name('admin.peminjaman');
    Route::get('/admin/peminjaman/{peminjaman}/pdf', [PeminjamanController::class, 'downloadPdf'])->name('admin.peminjaman.pdf');

    // Manager: approval page
    Route::get('/manager/peminjaman', [PeminjamanController::class, 'managerIndex'])->name('manager.peminjaman');
    Route::post('/manager/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve'])->name('manager.peminjaman.approve');
    Route::post('/manager/peminjaman/{peminjaman}/reject', [PeminjamanController::class, 'reject'])->name('manager.peminjaman.reject');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/api-update', [ProfileController::class, 'apiUpdate'])->name('profile.api.update');
});

require __DIR__.'/auth.php';
