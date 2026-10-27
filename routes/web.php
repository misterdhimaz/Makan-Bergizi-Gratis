<?php

use App\Http\Controllers\ApdInspectionController;
use App\Http\Controllers\AppearanceSettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplaintTicketController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\MenuPlanningController;
use App\Http\Controllers\ProductionInventoryController;
use App\Http\Controllers\QualityControlController;
use App\Http\Controllers\RbacManagementController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard jika sudah terautentikasi, atau ke halaman login
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Rute Tamu (Guest) dengan Proteksi Rate Limiter
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login.post');
});

// Grup Rute Terproteksi Autentikasi & Sesi Aktif
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Global Search Endpoint (Mencari lintas Menu, Bahan Baku, Mitra Sekolah, Surat Jalan, & Tiket Keluhan)
    Route::get('/global-search', [DashboardController::class, 'globalSearch'])->name('global.search');

    // 1. Dashboard Analytics Multi-Role & Pusat Komando (Dengan Filter Lengkap 1.414 Porsi)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Modul Perencanaan & Validasi Multi-Tahap (Ahli Gizi -> Akuntan PAGU -> Kepala Dapur Approval)
    Route::middleware('role:admin,ahli_gizi,kepala_dapur,akuntan')->group(function () {
        Route::get('/menu-planning', [MenuPlanningController::class, 'index'])->name('menu-planning.index');
        Route::post('/menu-planning', [MenuPlanningController::class, 'store'])
            ->middleware('role:admin,ahli_gizi')
            ->name('menu-planning.store');
        Route::post('/menu-planning/{dailyMenu}/verify-pagu', [MenuPlanningController::class, 'verifyPagu'])
            ->middleware('role:admin,akuntan')
            ->name('menu-planning.verify-pagu');
        Route::post('/menu-planning/{dailyMenu}/approve', [MenuPlanningController::class, 'approve'])
            ->middleware('role:admin,kepala_dapur')
            ->name('menu-planning.approve');
        Route::delete('/menu-planning/{dailyMenu}', [MenuPlanningController::class, 'destroy'])
            ->middleware('role:admin,ahli_gizi')
            ->name('menu-planning.destroy');
    });

    // 3. Monitoring Produksi Dapur, Pemorsian Ompreng & Bahan Baku (35 Relawan Dapur)
    Route::middleware('role:admin,kepala_dapur,asisten_lapangan,akuntan,ahli_gizi')->group(function () {
        Route::get('/production-inventory', [ProductionInventoryController::class, 'index'])->name('production-inventory.index');
        Route::post('/production-inventory/ingredient', [ProductionInventoryController::class, 'storeIngredient'])
            ->middleware('role:admin,ahli_gizi,asisten_lapangan')
            ->name('production-inventory.store-ingredient');
        Route::put('/production-inventory/ingredient/{ingredient}', [ProductionInventoryController::class, 'updateIngredient'])
            ->middleware('role:admin,ahli_gizi,asisten_lapangan')
            ->name('production-inventory.update-ingredient');
        Route::delete('/production-inventory/ingredient/{ingredient}', [ProductionInventoryController::class, 'destroyIngredient'])
            ->middleware('role:admin')
            ->name('production-inventory.destroy-ingredient');
        Route::post('/production-inventory/transaction', [ProductionInventoryController::class, 'storeTransaction'])
            ->middleware('role:admin,asisten_lapangan,akuntan')
            ->name('production-inventory.transaction');
        Route::post('/production-inventory/run', [ProductionInventoryController::class, 'storeProductionRun'])
            ->middleware('role:admin,kepala_dapur,ahli_gizi')
            ->name('production-inventory.store-run');
        Route::post('/production-inventory/{productionRun}/update', [ProductionInventoryController::class, 'updateProduction'])
            ->middleware('role:admin,kepala_dapur,asisten_lapangan')
            ->name('production-inventory.update');
    });

    // 4. Quality Control: Keamanan Pangan HACCP & Suhu Pangan (Ahli Gizi, Kepala Dapur, Admin)
    Route::middleware('role:admin,ahli_gizi,kepala_dapur,asisten_lapangan')->group(function () {
        Route::get('/quality-control', [QualityControlController::class, 'index'])->name('quality-control.index');
        Route::post('/quality-control', [QualityControlController::class, 'store'])->name('quality-control.store');
        Route::post('/quality-control/{qcInspection}/resolve', [QualityControlController::class, 'resolveAction'])
            ->middleware('role:admin,kepala_dapur')
            ->name('quality-control.resolve');
        Route::delete('/quality-control/{qcInspection}', [QualityControlController::class, 'destroy'])
            ->middleware('role:admin')
            ->name('quality-control.destroy');
    });

    // 5. Pemeriksaan Kepatuhan APD Dapur (Form Ceklis Harian Ahli Gizi - 35 Relawan)
    Route::middleware('role:admin,ahli_gizi,kepala_dapur,asisten_lapangan')->group(function () {
        Route::get('/apd-inspection', [ApdInspectionController::class, 'index'])->name('apd-inspection.index');
        Route::post('/apd-inspection', [ApdInspectionController::class, 'store'])->name('apd-inspection.store');
        Route::put('/apd-inspection/{apdInspection}/verify', [ApdInspectionController::class, 'verify'])
            ->middleware('role:admin,kepala_dapur')
            ->name('apd-inspection.verify');
    });

    // 6. Modul Distribusi, Konfirmasi Organoleptik & Penanganan Keluhan (Fitur 7)
    Route::middleware('role:admin,tim_distribusi,guru_pic,asisten_lapangan,kepala_dapur')->group(function () {
        Route::get('/distribution', [DistributionController::class, 'index'])->name('distribution.index');
        Route::post('/distribution', [DistributionController::class, 'store'])
            ->middleware('role:admin,tim_distribusi,asisten_lapangan')
            ->name('distribution.store');
        Route::post('/distribution/{delivery}/dispatch', [DistributionController::class, 'updateDispatch'])
            ->middleware('role:admin,tim_distribusi,asisten_lapangan')
            ->name('distribution.dispatch');
        Route::post('/distribution/{delivery}/confirm', [DistributionController::class, 'storeConfirmation'])
            ->middleware('role:admin,guru_pic,asisten_lapangan')
            ->name('distribution.confirm');
        Route::delete('/distribution/{delivery}', [DistributionController::class, 'destroy'])
            ->middleware('role:admin')
            ->name('distribution.destroy');

        // Tiket Keluhan Digital (Fitur 7)
        Route::post('/complaints', [ComplaintTicketController::class, 'store'])->name('complaints.store');
        Route::post('/complaints/{complaintTicket}/resolve', [ComplaintTicketController::class, 'resolve'])
            ->middleware('role:admin,asisten_lapangan,kepala_dapur')
            ->name('complaints.resolve');
    });

    // 6. Manajemen Pusat: 7 Role RBAC, Master 7 Mitra (1.414 Porsi), Konfigurasi SOP & Audit Trail
    Route::middleware('role:admin,akuntan')->group(function () {
        Route::get('/users-rbac', [RbacManagementController::class, 'index'])->name('users-rbac.index');

        Route::middleware('role:admin')->group(function () {
            Route::post('/users-rbac/user', [RbacManagementController::class, 'storeUser'])->name('users-rbac.store-user');
            Route::put('/users-rbac/user/{user}', [RbacManagementController::class, 'updateUser'])->name('users-rbac.update-user');
            Route::post('/users-rbac/user/{user}/toggle', [RbacManagementController::class, 'toggleUserStatus'])->name('users-rbac.toggle-user');
            Route::delete('/users-rbac/user/{user}', [RbacManagementController::class, 'destroyUser'])->name('users-rbac.destroy-user');

            Route::post('/users-rbac/institution', [RbacManagementController::class, 'storeInstitution'])->name('users-rbac.store-institution');
            Route::put('/users-rbac/institution/{institution}', [RbacManagementController::class, 'updateInstitution'])->name('users-rbac.update-institution');
            Route::post('/users-rbac/institution/{institution}/toggle', [RbacManagementController::class, 'toggleInstitution'])->name('users-rbac.toggle-institution');
            Route::delete('/users-rbac/institution/{institution}', [RbacManagementController::class, 'destroyInstitution'])->name('users-rbac.destroy-institution');
        });

        // Konfigurasi SOP & Parameter Pagu Anggaran
        Route::post('/sop-config/{sop}', [RbacManagementController::class, 'updateSop'])->name('sop-config.update');
    });

    // 7. Pengaturan Tampilan & Tema Sistem (Admin Pusat)
    Route::middleware('role:admin')->group(function () {
        Route::get('/settings/appearance', [AppearanceSettingController::class, 'edit'])->name('settings.appearance');
        Route::post('/settings/appearance', [AppearanceSettingController::class, 'update'])->name('settings.appearance.update');
        Route::post('/settings/theme', [AppearanceSettingController::class, 'updateTheme'])->name('settings.theme.update');
        Route::post('/settings/theme/quick', [AppearanceSettingController::class, 'quickTheme'])->name('settings.theme.quick');
        Route::delete('/settings/appearance/{type}', [AppearanceSettingController::class, 'destroyFile'])
            ->whereIn('type', ['background', 'logo'])
            ->name('settings.appearance.destroy');
    });

    // 8. Pelaporan Digital & Ekspor Berkas (Fitur 8 - PDF, Excel & CSV)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/print-daily', [ReportController::class, 'printDaily'])->name('reports.print-daily');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
    Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
});
