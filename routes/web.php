<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailSPFController;

Route::get('/', function () {
    return redirect('/mailspf');
});

Route::get('/mailspf', [MailSPFController::class, 'index'])
    ->name('mailspf.index');

Route::post('/mailspf/check', [MailSPFController::class, 'check'])
    ->name('mailspf.check');