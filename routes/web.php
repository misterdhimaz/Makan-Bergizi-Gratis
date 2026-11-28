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
use App\Http\Controllers\AhliGizi\DashboardController as AhliGiziDashboardController;
use App\Http\Controllers\AhliGizi\FoodSafetyController as AhliGiziFoodSafetyController;
use App\Http\Controllers\AhliGizi\InventoryPortionController as AhliGiziInventoryController;
use App\Http\Controllers\AhliGizi\MenuPlanningController as AhliGiziMenuController;
use App\Http\Controllers\AhliGizi\ReadOnlyController as AhliGiziReadOnlyController;
use App\Http\Controllers\Akuntan\DashboardController as AkuntanDashboardController;
use App\Http\Controllers\Akuntan\LaporanController as AkuntanLaporanController;
use App\Http\Controllers\Akuntan\ParameterPaguController as AkuntanParameterPaguController;
use App\Http\Controllers\Akuntan\ValidasiAnggaranController as AkuntanValidasiAnggaranController;
use App\Http\Controllers\Aslap\DashboardController as AslapDashboardController;
use App\Http\Controllers\Aslap\DistribusiController as AslapDistribusiController;
use App\Http\Controllers\Aslap\KeluhanLaporanController as AslapKeluhanLaporanController;
use App\Http\Controllers\Aslap\PengadaanController as AslapPengadaanController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Distribusi\DashboardController as DistribusiDashboardController;
use App\Http\Controllers\Distribusi\PengirimanController as DistribusiPengirimanController;
use App\Http\Controllers\Distribusi\RiwayatController as DistribusiRiwayatController;
use App\Http\Controllers\GuruPic\ComplaintController as GuruPicComplaintController;
use App\Http\Controllers\GuruPic\ConfirmationController as GuruPicConfirmationController;
use App\Http\Controllers\GuruPic\DashboardController as GuruPicDashboardController;
use App\Http\Controllers\KepalaDapur\DashboardController as KepalaDapurDashboardController;
use App\Http\Controllers\KepalaDapur\LaporanController as KepalaDapurLaporanController;
use App\Http\Controllers\KepalaDapur\PersetujuanController as KepalaDapurPersetujuanController;
use App\Http\Controllers\KepalaDapur\TindakLanjutController as KepalaDapurTindakLanjutController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard sesuai peran jika sudah masuk, atau ke halaman login
Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return match (Auth::user()->role) {
        UserRole::ADMIN => redirect()->route('admin.dashboard'),
        UserRole::AHLI_GIZI => redirect()->route('ahli-gizi.dashboard'),
        UserRole::AKUNTAN => redirect()->route('akuntan.dashboard'),
        UserRole::KEPALA_DAPUR => redirect()->route('kepala-dapur.dashboard'),
        UserRole::ASISTEN_LAPANGAN => redirect()->route('aslap.dashboard'),
        UserRole::TIM_DISTRIBUSI => redirect()->route('distribusi.dashboard'),
        UserRole::GURU_PIC => redirect()->route('guru-pic.dashboard'),
        default => redirect()->route('dashboard'),
    };
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

// Dashboard umum: Administrator Sistem & Ahli Gizi ke dashboard masing-masing; peran lain ke placeholder
Route::middleware(['auth', 'akun-aktif', 'wajib-ganti-sandi', 'batas-sesi'])->group(function () {
    Route::get('/dashboard', function () {
        if (Auth::user()->role === UserRole::ADMIN) {
            return redirect()->route('admin.dashboard');
        }

        if (Auth::user()->role === UserRole::AHLI_GIZI) {
            return redirect()->route('ahli-gizi.dashboard');
        }

        if (Auth::user()->role === UserRole::AKUNTAN) {
            return redirect()->route('akuntan.dashboard');
        }

        if (Auth::user()->role === UserRole::KEPALA_DAPUR) {
            return redirect()->route('kepala-dapur.dashboard');
        }

        if (Auth::user()->role === UserRole::ASISTEN_LAPANGAN) {
            return redirect()->route('aslap.dashboard');
        }

        if (Auth::user()->role === UserRole::TIM_DISTRIBUSI) {
            return redirect()->route('distribusi.dashboard');
        }

        if (Auth::user()->role === UserRole::GURU_PIC) {
            return redirect()->route('guru-pic.dashboard');
        }

        return view('dashboard.placeholder');
    })->name('dashboard');
});

// Grup Rute Tim Distribusi
Route::prefix('distribusi')
    ->name('distribusi.')
    ->middleware(['auth', 'akun-aktif', 'role:Tim Distribusi', 'wajib-ganti-sandi', 'batas-sesi'])
    ->group(function () {
        Route::get('/dashboard', DistribusiDashboardController::class)->name('dashboard');
        Route::get('/pengiriman', [DistribusiPengirimanController::class, 'index'])->name('pengiriman.index');
        Route::post('/pengiriman/{route}', [DistribusiPengirimanController::class, 'update'])->name('pengiriman.update');
        Route::get('/riwayat', DistribusiRiwayatController::class)->name('riwayat.index');
    });

// Unduh dokumen bukti pengiriman (terotorisasi lintas peran terkait)
Route::middleware(['auth', 'akun-aktif', 'wajib-ganti-sandi', 'batas-sesi'])
    ->get('/distribusi/dokumen/{document}', [DistribusiPengirimanController::class, 'downloadDocument'])
    ->name('distribusi.dokumen.download');

// Grup Rute Asisten Lapangan (Aslap)
Route::prefix('aslap')
    ->name('aslap.')
    ->middleware(['auth', 'akun-aktif', 'role:Asisten Lapangan', 'wajib-ganti-sandi', 'batas-sesi'])
    ->group(function () {
        Route::get('/dashboard', AslapDashboardController::class)->name('dashboard');

        Route::get('/pengadaan', [AslapPengadaanController::class, 'index'])->name('pengadaan.index');
        Route::post('/pengadaan/{transaction}', [AslapPengadaanController::class, 'update'])->name('pengadaan.update');

        Route::get('/distribusi', [AslapDistribusiController::class, 'index'])->name('distribusi.index');
        Route::post('/distribusi', [AslapDistribusiController::class, 'store'])->name('distribusi.store');
        Route::patch('/distribusi/{route}', [AslapDistribusiController::class, 'update'])->name('distribusi.update');

        Route::get('/keluhan-laporan', [AslapKeluhanLaporanController::class, 'index'])->name('keluhan.index');
        Route::post('/keluhan-laporan/{ticket}', [AslapKeluhanLaporanController::class, 'handleComplaint'])->name('keluhan.handle');
        Route::get('/keluhan-laporan/ekspor/pdf', [AslapKeluhanLaporanController::class, 'exportPdf'])->name('laporan.export.pdf');
        Route::get('/keluhan-laporan/ekspor/excel', [AslapKeluhanLaporanController::class, 'exportExcel'])->name('laporan.export.excel');
        Route::get('/keluhan-laporan/ekspor/csv', [AslapKeluhanLaporanController::class, 'exportCsv'])->name('laporan.export.csv');
    });

// Grup Rute Kepala Dapur
Route::prefix('kepala-dapur')
    ->name('kepala-dapur.')
    ->middleware(['auth', 'akun-aktif', 'role:Kepala Dapur', 'wajib-ganti-sandi', 'batas-sesi'])
    ->group(function () {
        Route::get('/dashboard', KepalaDapurDashboardController::class)->name('dashboard');

        Route::get('/persetujuan', [KepalaDapurPersetujuanController::class, 'index'])->name('persetujuan.index');
        Route::post('/persetujuan/menu/{dailyMenu}', [KepalaDapurPersetujuanController::class, 'decideMenu'])->name('persetujuan.menu.decide');
        Route::post('/persetujuan/koreksi/{revision}/setujui', [KepalaDapurPersetujuanController::class, 'approveRevision'])->name('persetujuan.koreksi.approve');
        Route::post('/persetujuan/koreksi/{revision}/tolak', [KepalaDapurPersetujuanController::class, 'rejectRevision'])->name('persetujuan.koreksi.reject');

        Route::get('/tindak-lanjut', [KepalaDapurTindakLanjutController::class, 'index'])->name('tindak-lanjut.index');
        Route::post('/tindak-lanjut/temuan/{finding}', [KepalaDapurTindakLanjutController::class, 'resolveFinding'])->name('tindak-lanjut.temuan.resolve');
        Route::post('/tindak-lanjut/keluhan/{ticket}', [KepalaDapurTindakLanjutController::class, 'handleComplaint'])->name('tindak-lanjut.keluhan.handle');

        Route::get('/laporan', [KepalaDapurLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/ekspor/pdf', [KepalaDapurLaporanController::class, 'exportPdf'])->name('laporan.export.pdf');
        Route::get('/laporan/ekspor/excel', [KepalaDapurLaporanController::class, 'exportExcel'])->name('laporan.export.excel');
        Route::get('/laporan/ekspor/csv', [KepalaDapurLaporanController::class, 'exportCsv'])->name('laporan.export.csv');
    });

// Grup Rute Akuntan
Route::prefix('akuntan')
    ->name('akuntan.')
    ->middleware(['auth', 'akun-aktif', 'role:Akuntan', 'wajib-ganti-sandi', 'batas-sesi'])
    ->group(function () {
        Route::get('/dashboard', [AkuntanDashboardController::class, 'index'])->name('dashboard');
        Route::get('/validasi', [AkuntanValidasiAnggaranController::class, 'index'])->name('validasi.index');
        Route::post('/validasi/{menu}', [AkuntanValidasiAnggaranController::class, 'store'])->name('validasi.store');
        Route::get('/parameter-pagu', [AkuntanParameterPaguController::class, 'index'])->name('parameter-pagu.index');
        Route::put('/parameter-pagu/{kategori}', [AkuntanParameterPaguController::class, 'update'])->name('parameter-pagu.update');
        Route::get('/laporan', [AkuntanLaporanController::class, 'index'])->name('laporan.index');
    });

// Grup Rute Guru PIC / Sekolah & Posyandu
Route::prefix('guru-pic')
    ->name('guru-pic.')
    ->middleware(['auth', 'akun-aktif', 'role:Guru PIC / Sekolah & Posyandu', 'wajib-ganti-sandi', 'batas-sesi'])
    ->group(function () {
        Route::get('/dashboard', [GuruPicDashboardController::class, 'index'])->name('dashboard');
        Route::get('/konfirmasi', [GuruPicConfirmationController::class, 'index'])->name('confirmation.index');
        Route::post('/konfirmasi', [GuruPicConfirmationController::class, 'store'])->name('confirmation.store');
        Route::get('/keluhan', [GuruPicComplaintController::class, 'index'])->name('complaints.index');
        Route::post('/keluhan', [GuruPicComplaintController::class, 'store'])->name('complaints.store');
    });

// Grup Rute Ahli Gizi
Route::prefix('ahli-gizi')
    ->name('ahli-gizi.')
    ->middleware(['auth', 'akun-aktif', 'role:Ahli Gizi', 'wajib-ganti-sandi', 'batas-sesi'])
    ->group(function () {
        Route::get('/dashboard', [AhliGiziDashboardController::class, 'index'])->name('dashboard');
        Route::post('/menu/{dailyMenu}/ajukan-revisi', [AhliGiziDashboardController::class, 'requestRevision'])->name('menu.request-revision');

        Route::get('/perencanaan-menu', [AhliGiziMenuController::class, 'index'])->name('menu.index');
        Route::post('/perencanaan-menu', [AhliGiziMenuController::class, 'store'])->name('menu.store');
        Route::put('/perencanaan-menu/{dailyMenu}', [AhliGiziMenuController::class, 'update'])->name('menu.update');

        Route::get('/persediaan-pemorsian', [AhliGiziInventoryController::class, 'index'])->name('inventory.index');
        Route::post('/persediaan-pemorsian/kebutuhan', [AhliGiziInventoryController::class, 'storeNeeds'])->name('inventory.needs.store');
        Route::post('/persediaan-pemorsian/penerimaan/{transaction}/verifikasi', [AhliGiziInventoryController::class, 'verifyReceipt'])->name('inventory.verify');
        Route::post('/persediaan-pemorsian/realisasi', [AhliGiziInventoryController::class, 'storeRealization'])->name('inventory.realization.store');
        Route::post('/persediaan-pemorsian/ompreng', [AhliGiziInventoryController::class, 'storeOmpreng'])->name('inventory.ompreng.store');
        Route::post('/persediaan-pemorsian/kendala', [AhliGiziInventoryController::class, 'storeObstacle'])->name('inventory.obstacles.store');

        Route::get('/keamanan-pangan', [AhliGiziFoodSafetyController::class, 'index'])->name('safety.index');
        Route::post('/keamanan-pangan', [AhliGiziFoodSafetyController::class, 'store'])->name('safety.store');

        // Grup Hanya Lihat (Read-Only)
        Route::get('/distribusi', [AhliGiziReadOnlyController::class, 'distribusi'])->name('readonly.distribusi');
        Route::get('/keluhan', [AhliGiziReadOnlyController::class, 'keluhan'])->name('readonly.keluhan');
        Route::get('/laporan', [AhliGiziReadOnlyController::class, 'laporan'])->name('readonly.laporan');
        Route::get('/data-master', [AhliGiziReadOnlyController::class, 'dataMaster'])->name('readonly.data-master');
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
