<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicFormController;
use App\Http\Controllers\Admin\DealerController;

Route::get('/', [PublicFormController::class, 'show'])->name('public.form');
Route::post('/', [PublicFormController::class, 'store'])->name('public.form.store');

// ADMIN AUTH
Route::get('/admin/login', [AuthController::class, 'show'])->name('admin.login.show');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.perform');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// ADMIN-ONLY
Route::middleware(['admin', 'admin.fresh'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dealers', [DealerController::class, 'index'])->name('dealers.index');
    Route::get('/dealers/create', [DealerController::class, 'create'])->name('dealers.create');
    Route::post('/dealers', [DealerController::class, 'store'])->name('dealers.store');
    Route::get('/dealers/{dealer:code}', [DealerController::class, 'show'])->name('dealers.show');
    Route::get('/dealers/{dealer:code}/edit', [DealerController::class,'edit'])->name('dealers.edit');
    Route::put('/dealers/{dealer:code}', [DealerController::class,'update'])->name('dealers.update');
});
