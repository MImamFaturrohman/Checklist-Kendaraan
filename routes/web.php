<?php

use App\Http\Controllers\Admin\BbmOperationalPortalController;
use App\Http\Controllers\Admin\BidangController;
use App\Http\Controllers\Admin\PernyataanController;
use App\Http\Controllers\Admin\SppdAdminController;
use App\Http\Controllers\Admin\VehicleUsageLogArchiveController;
use App\Http\Controllers\BbmReportController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\LaporanKejadianController;
use App\Http\Controllers\ManagerSppdController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SppdController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\VehicleUsageLogController;
use App\Models\Checklist;
use App\Models\Kendaraan;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Landing page (public)
Route::get('/', [PeminjamanController::class, 'landingPage'])->name('landing');
Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
Route::post('/laporan-kejadian', [LaporanKejadianController::class, 'store'])->name('laporan-kejadian.store');
Route::get('/laporan-kejadian/approval/{token}', [LaporanKejadianController::class, 'showApproval'])->name('laporan-kejadian.approval.show');
Route::post('/laporan-kejadian/approval/{token}/sign', [LaporanKejadianController::class, 'submitApproval'])->name('laporan-kejadian.approval.sign');

// Public kendaraan list API for landing page form
Route::get('/api/kendaraan/public-list', [KendaraanController::class, 'apiList'])->name('api.kendaraan.public-list');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])
        ->name('notifications.read');

    Route::post('/api/presence/heartbeat', [PresenceController::class, 'heartbeat'])
        ->name('api.presence.heartbeat');

    Route::post('/api/presence/offline', [PresenceController::class, 'offline'])
        ->name('api.presence.offline');

    Route::get('/checklists/create', function () {
        $kendaraans = Kendaraan::orderBy('nomor_kendaraan')->get();
        $drivers = User::where('role', 'driver')->orderBy('name')->get();
        $user = auth()->user();

        return view('checklists.create', compact('kendaraans', 'drivers', 'user'));
    })->name('checklists.create');

    Route::post('/checklists', [ChecklistController::class, 'store'])->name('checklists.store');

    // Rekap SPPD (driver / PIC)
    Route::get('/sppd', [SppdController::class, 'index'])->name('sppd.index');
    Route::get('/sppd/create', [SppdController::class, 'create'])->name('sppd.create');
    Route::post('/sppd', [SppdController::class, 'store'])->name('sppd.store');
    Route::get('/sppd/{sppd}/edit', [SppdController::class, 'edit'])->name('sppd.edit');
    Route::put('/sppd/{sppd}', [SppdController::class, 'update'])->name('sppd.update');
    Route::delete('/sppd/{sppd}', [SppdController::class, 'destroy'])->name('sppd.destroy');
    Route::get('/sppd/{sppd}/json', [SppdController::class, 'showJson'])->name('sppd.json');
    Route::get('/sppd/{sppd}/pdf', [SppdController::class, 'downloadPdf'])->name('sppd.pdf');
    Route::post('/sppd/{sppd}/selesai', [SppdController::class, 'markCompleted'])->name('sppd.complete');

    // Laporan pengisian BBM (driver only — enforced di controller)
    Route::get('/laporan-bbm/buat', [BbmReportController::class, 'create'])->name('bbm-reports.create');
    Route::post('/laporan-bbm', [BbmReportController::class, 'store'])->name('bbm-reports.store');

    // Log penggunaan kendaraan (driver only — enforced di controller)
    Route::get('/log-penggunaan-kendaraan/buat', [VehicleUsageLogController::class, 'create'])->name('vehicle-usage-logs.create');
    Route::post('/log-penggunaan-kendaraan', [VehicleUsageLogController::class, 'store'])->name('vehicle-usage-logs.store');

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

    // Portal BBM Operasional (superadmin: penuh; manager: kartu & grafik — dicek di controller)
    Route::get('/admin/portal-bbm-operasional/{bbmReport}/json', [BbmOperationalPortalController::class, 'showJson'])->name('admin.portal-bbm-operasional.json');
    Route::get('/admin/portal-bbm-operasional/charts-series', [BbmOperationalPortalController::class, 'chartSeries'])->name('admin.portal-bbm-operasional.charts');
    Route::get('/admin/portal-bbm-operasional/activity-log', [BbmOperationalPortalController::class, 'activityLog'])->name('admin.portal-bbm-operasional.activity-log');
    Route::get('/admin/portal-bbm-operasional', [BbmOperationalPortalController::class, 'index'])->name('admin.portal-bbm-operasional');

    // AJAX API endpoints for real-time portal search
    Route::get('/api/admin/portal/database-sheet', [ChecklistController::class, 'apiPortalDatabaseSheet'])->name('api.admin.portal.database-sheet');
    Route::get('/api/admin/portal/checklist/{checklist}', [ChecklistController::class, 'apiPortalChecklistDetail'])->name('api.admin.portal.checklist-detail');
    Route::get('/api/admin/portal/log-foto', [ChecklistController::class, 'apiPortalLogFoto'])->name('api.admin.portal.log-foto');
    Route::get('/api/admin/portal/arsip-pdf', [ChecklistController::class, 'apiPortalArsipPdf'])->name('api.admin.portal.arsip-pdf');
    Route::get('/api/admin/portal/charts', [ChecklistController::class, 'apiPortalCharts'])->name('api.admin.portal.charts');

    // Approval routes (superadmin only — enforced in controller)
    Route::get('/api/admin/portal/pending-approval', [ChecklistController::class, 'apiPortalPendingApproval'])->name('api.admin.portal.pending-approval');
    Route::post('/api/admin/checklists/{checklist}/approve', [ChecklistController::class, 'approveChecklist'])->name('api.admin.checklists.approve');

    // Combined Portal Manajemen Administrasi (Master Armada + Manajemen User)
    Route::get('/admin/portal-manajemen-administrasi', [UserManagementController::class, 'portal'])->name('admin.portal-manajemen');
    Route::post('/admin/portal-manajemen-administrasi/kendaraan', [KendaraanController::class, 'store'])->name('admin.portal-manajemen.kendaraan.store');
    Route::put('/admin/portal-manajemen-administrasi/kendaraan/{kendaraan}', [KendaraanController::class, 'update'])->name('admin.portal-manajemen.kendaraan.update');
    Route::delete('/admin/portal-manajemen-administrasi/kendaraan/{kendaraan}', [KendaraanController::class, 'destroy'])->name('admin.portal-manajemen.kendaraan.destroy');
    Route::post('/admin/users', [UserManagementController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [UserManagementController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::post('/admin/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('admin.users.reset-password');
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

    // Admin: laporan kejadian (dari landing)
    Route::get('/admin/laporan-kejadian', [LaporanKejadianController::class, 'adminIndex'])->name('admin.laporan-kejadian.index');
    Route::get('/admin/laporan-kejadian/{laporanKejadian}/pdf', [LaporanKejadianController::class, 'downloadPdf'])->name('admin.laporan-kejadian.pdf');

    // Admin: arsip log penggunaan kendaraan (superadmin)
    Route::get('/admin/log-penggunaan-kendaraan', [VehicleUsageLogArchiveController::class, 'index'])->name('admin.vehicle-usage-logs.index');
    Route::get('/api/admin/vehicle-usage-logs', [VehicleUsageLogArchiveController::class, 'apiIndex'])->name('api.admin.vehicle-usage-logs');

    // Admin: Rekap SPPD
    Route::get('/admin/rekap-sppd', [SppdAdminController::class, 'index'])->name('admin.sppd.index');
    Route::get('/admin/rekap-sppd/{sppd}/pdf', [SppdAdminController::class, 'downloadPdf'])->name('admin.sppd.pdf');
    Route::get('/admin/rekap-sppd/{sppd}', [SppdAdminController::class, 'show'])->name('admin.sppd.show');
    Route::post('/admin/rekap-sppd/{sppd}/verify-approve', [SppdAdminController::class, 'verifyApprove'])->name('admin.sppd.verify-approve');
    Route::post('/admin/rekap-sppd/{sppd}/verify-reject', [SppdAdminController::class, 'verifyReject'])->name('admin.sppd.verify-reject');

    // Manager: approval page
    Route::get('/manager/peminjaman', [PeminjamanController::class, 'managerIndex'])->name('manager.peminjaman');
    Route::post('/manager/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve'])->name('manager.peminjaman.approve');
    Route::post('/manager/peminjaman/{peminjaman}/reject', [PeminjamanController::class, 'reject'])->name('manager.peminjaman.reject');

    // Manager: Rekap SPPD
    Route::get('/manager/rekap-sppd', [ManagerSppdController::class, 'index'])->name('manager.sppd.index');
    Route::get('/manager/rekap-sppd/{sppd}/pdf', [ManagerSppdController::class, 'downloadPdf'])->name('manager.sppd.pdf');
    Route::get('/manager/rekap-sppd/{sppd}', [ManagerSppdController::class, 'show'])->name('manager.sppd.show');
    Route::post('/manager/rekap-sppd/{sppd}/approve', [ManagerSppdController::class, 'approve'])->name('manager.sppd.approve');
    Route::post('/manager/rekap-sppd/{sppd}/reject', [ManagerSppdController::class, 'reject'])->name('manager.sppd.reject');
    Route::post('/manager/rekap-sppd/{sppd}/regenerate-pdf', [ManagerSppdController::class, 'regeneratePdf'])->name('manager.sppd.regenerate-pdf');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/api-update', [ProfileController::class, 'apiUpdate'])->name('profile.api.update');
});

require __DIR__.'/auth.php';
