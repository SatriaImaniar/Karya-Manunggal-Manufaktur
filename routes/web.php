<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\JenisKerusakanController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// =========================================================================
// LANDING PAGE — publik, bisa diakses siapapun tanpa auth
// =========================================================================
Route::get('/', function () {
    return view('welcome');
})->name('home');

// =========================================================================
// AUTH ROUTES — hanya untuk tamu (belum login)
// =========================================================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.process');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// =========================================================================
// ADMIN/SPV ROUTES — dilindungi auth + role:admin
// =========================================================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [MaintenanceController::class, 'dashboard'])
        ->name('dashboard');

    // Master Mesin (CRUD)
    Route::resource('machines', MachineController::class)
        ->except(['show']);

    // Manajemen Teknisi
    Route::resource('users', UserController::class)
        ->except(['show']);

    // Data Historis & Kalkulasi TBM
    Route::get('/maintenance/history', [MaintenanceController::class, 'historyIndex'])
        ->name('maintenance.history');
    Route::post('/maintenance/history', [MaintenanceController::class, 'storeHistory'])
        ->name('maintenance.history.store');
    Route::delete('/maintenance/history/{history}', [MaintenanceController::class, 'destroyHistory'])
        ->name('maintenance.history.destroy');
    Route::get('/maintenance/calculation/{machine}', [MaintenanceController::class, 'showCalculation'])
        ->name('maintenance.calculation');

    // Jadwal Maintenance
    Route::get('/schedules', [MaintenanceController::class, 'scheduleIndex'])
        ->name('schedules.index');
    Route::patch('/schedules/{schedule}/assign', [MaintenanceController::class, 'assignSchedule'])
        ->name('schedules.assign');

    // Rekap & Export Data
    Route::get('/report', [MaintenanceController::class, 'reportIndex'])
        ->name('report.index');
    Route::get('/report/export', [MaintenanceController::class, 'exportData'])
        ->name('report.export');
    Route::get('/machines/{machine}/summary', [MaintenanceController::class, 'machineSummary'])
        ->name('machines.summary');

    // Master Data: Jenis Kerusakan
    Route::resource('jenis-kerusakan', JenisKerusakanController::class)
        ->except(['create', 'edit', 'show']);
});

// =========================================================================
// TEKNISI ROUTES — dilindungi auth + role:teknisi
// =========================================================================
Route::middleware(['auth', 'role:teknisi'])->prefix('teknisi')->name('teknisi.')->group(function () {

    // Dashboard Teknisi
    Route::get('/dashboard', [MaintenanceController::class, 'teknisiDashboard'])
        ->name('dashboard');

    // Jadwal yang Ditugaskan
    Route::get('/schedules', [MaintenanceController::class, 'teknisiSchedules'])
        ->name('schedules');
    Route::patch('/schedules/{schedule}/status', [MaintenanceController::class, 'updateScheduleStatus'])
        ->name('schedules.update-status');
});

// =========================================================================
// FALLBACK ROUTE — tangkap semua URL yang tidak terdefinisi
// =========================================================================
Route::fallback(function () {
    if (auth()->check()) {
        $role = auth()->user()->role;
        return redirect()->route($role === 'admin' ? 'admin.dashboard' : 'teknisi.dashboard');
    }
    return redirect()->route('login');
});
