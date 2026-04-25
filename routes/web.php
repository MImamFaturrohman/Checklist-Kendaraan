<?php

use App\Http\Controllers\Admin\BidangController;
use App\Http\Controllers\Admin\PernyataanController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Models\Checklist;
use App\Models\Kendaraan;
use App\Models\User;
use Illuminate\Support\Facades\Route;

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

    Route::get('/admin/portal-pemeriksaan/export', function () {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        return app(ChecklistController::class)->exportExcel();
    })->name('admin.portal-pemeriksaan.export');

    // Combined Portal Pemeriksaan Kendaraan
    Route::get('/admin/portal-pemeriksaan', [ChecklistController::class, 'portalPemeriksaan'])->name('admin.portal-pemeriksaan');

    // AJAX API endpoints for real-time portal search
    Route::get('/api/admin/portal/database-sheet', [ChecklistController::class, 'apiPortalDatabaseSheet'])->name('api.admin.portal.database-sheet');
    Route::get('/api/admin/portal/log-foto', [ChecklistController::class, 'apiPortalLogFoto'])->name('api.admin.portal.log-foto');
    Route::get('/api/admin/portal/arsip-pdf', [ChecklistController::class, 'apiPortalArsipPdf'])->name('api.admin.portal.arsip-pdf');
    Route::get('/api/admin/portal/charts', [ChecklistController::class, 'apiPortalCharts'])->name('api.admin.portal.charts');

    // Combined Portal Manajemen Administrasi (Master Armada + Manajemen User)
    Route::get('/admin/portal-manajemen-administrasi', [UserManagementController::class, 'portal'])->name('admin.portal-manajemen');
    Route::post('/admin/portal-manajemen-administrasi/kendaraan', [KendaraanController::class, 'store'])->name('admin.portal-manajemen.kendaraan.store');
    Route::put('/admin/portal-manajemen-administrasi/kendaraan/{kendaraan}', [KendaraanController::class, 'update'])->name('admin.portal-manajemen.kendaraan.update');
    Route::delete('/admin/portal-manajemen-administrasi/kendaraan/{kendaraan}', [KendaraanController::class, 'destroy'])->name('admin.portal-manajemen.kendaraan.destroy');
    Route::post('/admin/users', [UserManagementController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [UserManagementController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::get('/api/admin/portal/kendaraan', [UserManagementController::class, 'apiKendaraan'])->name('api.admin.portal.kendaraan');
    Route::get('/api/admin/portal/users', [UserManagementController::class, 'apiUsers'])->name('api.admin.portal.users');

    // Admin: resource API (JSON) — dipakai halaman Peminjaman Kendaraan (AJAX)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('bidangs', BidangController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('pernyataans', PernyataanController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    // Admin: peminjaman kendaraan + PDF download
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
