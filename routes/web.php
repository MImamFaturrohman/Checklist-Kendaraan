<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/checklists/create', function () {
        return view('checklists.create');
    })->name('checklists.create');

    Route::get('/admin/database-sheet', function () {
        abort_unless(auth()->user()?->role === 'admin', 403);

        return view('coming-soon', [
            'title' => 'Database Sheet',
            'message' => 'Fitur database sheet sedang disiapkan.',
        ]);
    })->name('admin.database-sheet');

    Route::get('/admin/log-foto-fisik', function () {
        abort_unless(auth()->user()?->role === 'admin', 403);

        return view('coming-soon', [
            'title' => 'Log Foto Fisik',
            'message' => 'Fitur log foto fisik sedang disiapkan.',
        ]);
    })->name('admin.log-foto-fisik');

    Route::get('/admin/arsip-pdf', function () {
        abort_unless(auth()->user()?->role === 'admin', 403);

        return view('coming-soon', [
            'title' => 'Arsip PDF',
            'message' => 'Fitur arsip PDF sedang disiapkan.',
        ]);
    })->name('admin.arsip-pdf');

    Route::get('/admin/master-armada', function () {
        abort_unless(auth()->user()?->role === 'admin', 403);

        return view('coming-soon', [
            'title' => 'Master Armada',
            'message' => 'Fitur master armada sedang disiapkan.',
        ]);
    })->name('admin.master-armada');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
