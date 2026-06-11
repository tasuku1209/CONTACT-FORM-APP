<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

// (一般側)ルート　bladeはurl直打ちですが、一応ルート名設定
Route::get('/', [ContactController::class, 'index'])
    ->name('contacts.index');

Route::post('/contacts/confirm', [ContactController::class, 'confirm'])
    ->name('contacts.confirm');

Route::post('/contacts', [ContactController::class, 'store'])
    ->name('contacts.store');

Route::get('/thanks', [ContactController::class, 'thanks'])
    ->name('contacts.thanks');

// (管理側)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])
        ->name('admin.index');
});
