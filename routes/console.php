<?php

use App\Models\PeminjamanRequest;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Daily at 00:05: expire pending requests whose tanggal_peminjaman has
 * already passed, and clear their signature data.
 */
Schedule::call(function () {
    PeminjamanRequest::expirePendingPastBorrowDate();
})->dailyAt('00:05')->name('peminjaman.expire-pending-requests');
