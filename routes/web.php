<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/admin/contacts/{contact}', [AdminController::class, 'show'])
        ->name('admin.show');
    Route::delete('/admin/contacts/{contact}', [AdminController::class, 'destroy'])
        ->name('admin.destroy');
    Route::resource('admin/tags', TagController::class)
        ->only([
            'store',
            'edit',
            'update',
            'destroy',
        ]);
});
