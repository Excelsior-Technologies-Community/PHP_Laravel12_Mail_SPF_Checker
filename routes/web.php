<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailSPFController;

Route::get('/', fn() => redirect('/mailspf'));

// Web UI
Route::get('/mailspf',              [MailSPFController::class, 'index'])->name('mailspf.index');
Route::post('/mailspf/check',       [MailSPFController::class, 'check'])->name('mailspf.check');
Route::post('/mailspf/bulk',        [MailSPFController::class, 'bulkCheck'])->name('mailspf.bulk');
Route::post('/mailspf/dns-lookup',  [MailSPFController::class, 'dnsLookup'])->name('mailspf.dns');

// JSON API Hub
Route::prefix('api/v1')->group(function () {
    Route::post('/check',      [MailSPFController::class, 'apiCheck']);
    Route::post('/bulk-check', [MailSPFController::class, 'apiBulk']);
    Route::post('/dns-lookup', [MailSPFController::class, 'apiDns']);
});