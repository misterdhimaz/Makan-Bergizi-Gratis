<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DataMasterOverviewController;
use App\Http\Controllers\Admin\IngredientMasterController;
use App\Http\Controllers\Admin\LocationMasterController;
use App\Http\Controllers\Admin\PaguParameterController;
use App\Http\Controllers\Admin\PortionStandardController;
use App\Http\Controllers\Admin\RecipientCategoryController;
use App\Http\Controllers\Admin\SecuritySettingsController;
use App\Http\Controllers\Admin\SopChecklistController;
use App\Http\Controllers\Admin\SupplierMasterController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard sesuai peran jika sudah masuk, atau ke halaman login
Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return Auth::user()->role === UserRole::ADMIN
        ? redirect()->route('admin.dashboard')
        : redirect()->route('dashboard');
});

// Rute Tamu (Guest) — tanpa registrasi mandiri & tanpa verifikasi email
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login.show');
    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:20,1')
        ->name('login.post');
    Route::post('/lupa-sandi', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('password.forgot');
});

// Alias route 'login' untuk redirect middleware auth bawaan Laravel
Route::get('/masuk', fn () => redirect()->route('login.show'))->name('login');

// Grup Rute Terproteksi Autentikasi & Keamanan Sesi
Route::middleware(['auth', 'akun-aktif', 'batas-sesi'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/ganti-sandi', [ForcePasswordChangeController::class, 'show'])->name('password.change');
    Route::post('/ganti-sandi', [ForcePasswordChangeController::class, 'store'])->name('password.change.store');
});

// Dashboard umum: Administrator Sistem selalu ke admin.dashboard; peran lain ke placeholder
Route::middleware(['auth', 'akun-aktif', 'wajib-ganti-sandi', 'batas-sesi'])->group(function () {
    Route::get('/dashboard', function () {
        if (Auth::user()->role === UserRole::ADMIN) {
            return redirect()->route('admin.dashboard');
        }

        return view('dashboard.placeholder');
    })->name('dashboard');
});

// Grup Rute Admin — Seluruh modul Administrator Sistem (Tahap 1–5)
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'akun-aktif', 'role:Administrator Sistem', 'wajib-ganti-sandi', 'batas-sesi'])
    ->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

        Route::get('/pengguna', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/pengguna', [AdminUserController::class, 'store'])->name('users.store');
        Route::put('/pengguna/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::patch('/pengguna/{user}/nonaktifkan', [AdminUserController::class, 'deactivate'])->name('users.deactivate');
        Route::patch('/pengguna/{user}/aktifkan', [AdminUserController::class, 'activate'])->name('users.activate');
        Route::post('/pengguna/{user}/reset-sandi', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');

        // Log Aktivitas (append-only: hanya index & show untuk drawer, tanpa store/update/destroy)
        Route::get('/log-aktivitas', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/log-aktivitas/{log}', [AuditLogController::class, 'show'])->name('audit-logs.show');

        // Data Master (ringkasan + 7 sub-modul; index, store, update, nonaktifkan, aktifkan; TANPA destroy)
        Route::get('/data-master', DataMasterOverviewController::class)->name('data-master.index');

        Route::prefix('data-master')->name('data-master.')->group(function () {
            Route::get('/lokasi', [LocationMasterController::class, 'index'])->name('lokasi.index');
            Route::post('/lokasi', [LocationMasterController::class, 'store'])->name('lokasi.store');
            Route::put('/lokasi/{location}', [LocationMasterController::class, 'update'])->name('lokasi.update');
            Route::patch('/lokasi/{location}/nonaktifkan', [LocationMasterController::class, 'deactivate'])->name('lokasi.deactivate');
            Route::patch('/lokasi/{location}/aktifkan', [LocationMasterController::class, 'activate'])->name('lokasi.activate');

            Route::get('/kategori', [RecipientCategoryController::class, 'index'])->name('kategori.index');
            Route::post('/kategori', [RecipientCategoryController::class, 'store'])->name('kategori.store');
            Route::put('/kategori/{category}', [RecipientCategoryController::class, 'update'])->name('kategori.update');
            Route::patch('/kategori/{category}/nonaktifkan', [RecipientCategoryController::class, 'deactivate'])->name('kategori.deactivate');
            Route::patch('/kategori/{category}/aktifkan', [RecipientCategoryController::class, 'activate'])->name('kategori.activate');

            Route::get('/standar-porsi', [PortionStandardController::class, 'index'])->name('standar-porsi.index');
            Route::post('/standar-porsi', [PortionStandardController::class, 'store'])->name('standar-porsi.store');
            Route::put('/standar-porsi/{standard}', [PortionStandardController::class, 'update'])->name('standar-porsi.update');
            Route::patch('/standar-porsi/{standard}/nonaktifkan', [PortionStandardController::class, 'deactivate'])->name('standar-porsi.deactivate');
            Route::patch('/standar-porsi/{standard}/aktifkan', [PortionStandardController::class, 'activate'])->name('standar-porsi.activate');

            Route::get('/bahan', [IngredientMasterController::class, 'index'])->name('bahan.index');
            Route::post('/bahan', [IngredientMasterController::class, 'store'])->name('bahan.store');
            Route::put('/bahan/{ingredient}', [IngredientMasterController::class, 'update'])->name('bahan.update');
            Route::patch('/bahan/{ingredient}/nonaktifkan', [IngredientMasterController::class, 'deactivate'])->name('bahan.deactivate');
            Route::patch('/bahan/{ingredient}/aktifkan', [IngredientMasterController::class, 'activate'])->name('bahan.activate');

            Route::get('/pemasok', [SupplierMasterController::class, 'index'])->name('pemasok.index');
            Route::post('/pemasok', [SupplierMasterController::class, 'store'])->name('pemasok.store');
            Route::put('/pemasok/{supplier}', [SupplierMasterController::class, 'update'])->name('pemasok.update');
            Route::patch('/pemasok/{supplier}/nonaktifkan', [SupplierMasterController::class, 'deactivate'])->name('pemasok.deactivate');
            Route::patch('/pemasok/{supplier}/aktifkan', [SupplierMasterController::class, 'activate'])->name('pemasok.activate');

            Route::get('/pagu', [PaguParameterController::class, 'index'])->name('pagu.index');
            Route::post('/pagu', [PaguParameterController::class, 'store'])->name('pagu.store');
            Route::put('/pagu/{pagu}', [PaguParameterController::class, 'update'])->name('pagu.update');
            Route::patch('/pagu/{pagu}/nonaktifkan', [PaguParameterController::class, 'deactivate'])->name('pagu.deactivate');
            Route::patch('/pagu/{pagu}/aktifkan', [PaguParameterController::class, 'activate'])->name('pagu.activate');

            Route::get('/checklist-sop', [SopChecklistController::class, 'index'])->name('checklist-sop.index');
            Route::post('/checklist-sop', [SopChecklistController::class, 'store'])->name('checklist-sop.store');
            Route::put('/checklist-sop/{checklist}', [SopChecklistController::class, 'update'])->name('checklist-sop.update');
            Route::patch('/checklist-sop/{checklist}/nonaktifkan', [SopChecklistController::class, 'deactivate'])->name('checklist-sop.deactivate');
            Route::patch('/checklist-sop/{checklist}/aktifkan', [SopChecklistController::class, 'activate'])->name('checklist-sop.activate');
        });

        // Pengaturan Sistem: Keamanan Sesi & Backup/Restore
        Route::get('/pengaturan/keamanan-sesi', [SecuritySettingsController::class, 'edit'])->name('settings.keamanan-sesi');
        Route::put('/pengaturan/keamanan-sesi', [SecuritySettingsController::class, 'update'])->name('settings.keamanan-sesi.update');

        Route::get('/pengaturan/backup', [BackupController::class, 'index'])->name('settings.backup.index');
        Route::post('/pengaturan/backup', [BackupController::class, 'store'])->name('settings.backup.store');
        Route::get('/pengaturan/backup/{backup}/unduh', [BackupController::class, 'download'])->name('settings.backup.download');
        Route::post('/pengaturan/backup/{backup}/pulihkan', [BackupController::class, 'restore'])->name('settings.backup.restore');
    });

// Penjaga rute tulis operasional (memastikan Administrator Sistem selalu mendapat 403)
Route::middleware(['auth', 'akun-aktif', 'wajib-ganti-sandi', 'batas-sesi', 'anti-operasional-admin'])->group(function () {
    Route::post('/menu-planning', fn () => response()->view('dashboard.placeholder', [], 501))->name('menu-planning.store');
    Route::post('/distribution', fn () => response()->view('dashboard.placeholder', [], 501))->name('distribution.store');
});
