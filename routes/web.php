<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealerController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\IcsController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('admin.index'));

Route::get('/login', [AuthController::class, 'show'])->name('login.show');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('admin')->group(function () {
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.index');
    Route::get('/admin/export', [ExportController::class, 'csv'])->name('export.csv');
    Route::get('/admin/ics', [IcsController::class, 'feed'])->name('ics.feed');
    Route::get('/admin/dealers/{dealer}/edit', [DealerController::class, 'edit'])->name('dealers.edit');
    Route::patch('/admin/dealers/{dealer}', [DealerController::class, 'update'])->name('dealers.update');
});

Route::get('/form', [SubmissionController::class, 'create'])->name('submissions.create');
Route::post('/form', [SubmissionController::class, 'store'])->name('submissions.store');
