<?php

use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\ProfileController;
use App\Models\Checklist;
use App\Models\ChecklistExterior;
use App\Models\ChecklistInterior;
use App\Models\ChecklistMesin;
use App\Models\Kendaraan;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/checklists/create', function () {
        $kendaraans = Kendaraan::orderBy('nomor_kendaraan')->get();
        $drivers = User::where('role', 'driver')->orderBy('name')->get();
        return view('checklists.create', compact('kendaraans', 'drivers'));
    })->name('checklists.create');

    Route::post('/checklists', [ChecklistController::class, 'store'])->name('checklists.store');

    // API endpoints
    Route::get('/api/kendaraan/lookup', [ChecklistController::class, 'lookupKendaraan'])->name('api.kendaraan.lookup');
    Route::get('/api/kendaraan/last-km', [ChecklistController::class, 'lastKm'])->name('api.kendaraan.last-km');
    Route::get('/api/kendaraan/list', [KendaraanController::class, 'apiList'])->name('api.kendaraan.list');

    // Admin routes
    Route::get('/admin/database-sheet', function () {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $checklists = Checklist::with(['exterior', 'interior', 'mesin', 'perlengkapan'])->orderByDesc('created_at')->get();
        $totalChecklists = $checklists->count();
        $totalVehicles = Kendaraan::count();
        $totalDrivers = User::where('role', 'driver')->count();
        $thisMonth = Checklist::whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->count();
        return view('admin.database-sheet', compact('checklists', 'totalChecklists', 'totalVehicles', 'totalDrivers', 'thisMonth'));
    })->name('admin.database-sheet');

    Route::get('/admin/database-sheet/export', function () {
        abort_unless(auth()->user()?->role === 'admin', 403);
        return app(ChecklistController::class)->exportExcel();
    })->name('admin.database-sheet.export');

    Route::get('/admin/log-foto-fisik', function () {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $checklists = Checklist::with(['exterior', 'interior', 'mesin'])->orderByDesc('created_at')->get();
        return view('admin.log-foto-fisik', compact('checklists'));
    })->name('admin.log-foto-fisik');

    Route::get('/admin/arsip-pdf', function () {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $checklists = Checklist::whereNotNull('pdf_path')->orderByDesc('created_at')->get();
        return view('admin.arsip-pdf', compact('checklists'));
    })->name('admin.arsip-pdf');

    // Master Armada CRUD
    Route::get('/admin/master-armada', [KendaraanController::class, 'index'])->name('admin.master-armada');
    Route::post('/admin/master-armada', [KendaraanController::class, 'store'])->name('admin.master-armada.store');
    Route::put('/admin/master-armada/{kendaraan}', [KendaraanController::class, 'update'])->name('admin.master-armada.update');
    Route::delete('/admin/master-armada/{kendaraan}', [KendaraanController::class, 'destroy'])->name('admin.master-armada.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
