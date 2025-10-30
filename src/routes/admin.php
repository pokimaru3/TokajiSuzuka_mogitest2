<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\StampCorrectionController;
use App\Http\Controllers\Admin\StaffController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware(['guest'])
    ->name('admin.login');

Route::post('/login', [AdminAuthController::class, 'login'])
    ->middleware(['guest']);

Route::middleware(['auth'])->group(function () {
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('admin.attendance.list');
    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])->name('admin.attendance.detail');
    Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('admin.attendance.update');
    Route::get('/stamp_correction_request/list', [StampCorrectionController::class, 'index'])->name('admin.correction.list');
    Route::get('/stamp_correction_request/approve/{id}', [StampCorrectionController::class, 'show'])->name('admin.correction.show');
    Route::post('/stamp_correction_request/approve/{id}', [StampCorrectionController::class, 'approve'])->name('admin.correction.approve');
    Route::get('/staff/list', [StaffController::class, 'index'])->name('admin.staff.list');
    Route::get('/attendance/staff/{id}', [StaffController::class, 'showAttendance'])->name('admin.staff.attendance');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});
