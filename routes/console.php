<?php

use App\Models\PeminjamanRequest;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Daily at midnight: clear tanda_tangan for pending requests whose
 * tanggal_peminjaman has already passed (expired).
 */
Schedule::call(function () {
    PeminjamanRequest::where('status', 'pending')
        ->whereNotNull('tanda_tangan')
        ->whereDate('tanggal_peminjaman', '<', now()->toDateString())
        ->update(['tanda_tangan' => null]);
})->dailyAt('00:05')->name('peminjaman.cleanup-expired-signatures');
