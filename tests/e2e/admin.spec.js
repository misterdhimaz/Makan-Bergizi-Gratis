import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

const screenshotDir = path.resolve('docs/qa-admin');
fs.mkdirSync(screenshotDir, { recursive: true });

const getAdminPassword = () => process.env.E2E_ADMIN_PASSWORD;
const getTempPassword = () => process.env.E2E_TEMP_PASSWORD;
const getE2eDb = () => process.env.E2E_DB_DATABASE || 'mbg_e2e';

async function loginAsAdmin(page) {
    await page.goto('/login');
    await page.fill('input[name="username"]', 'arief.mahendra');
    await page.fill('input[name="password"]', getAdminPassword());
    await page.click('button[type="submit"]');
    await page.waitForURL('**/admin/dashboard');
}

function expireSessionsInE2eDb() {
    const scriptPath = path.resolve('storage/framework/e2e_expire_session.php');
    fs.writeFileSync(
        scriptPath,
        `<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();

$rows = Illuminate\\Support\\Facades\\DB::table('sessions')->get();
foreach ($rows as $row) {
    $raw = base64_decode($row->payload);
    $isJson = false;
    $data = json_decode($raw, true);
    if (is_array($data)) {
        $isJson = true;
    } else {
        $data = @unserialize($raw);
    }
    if (is_array($data) && isset($data['last_activity_timestamp'])) {
        $data['last_activity_timestamp'] = time() - 3600;
        $encoded = $isJson ? json_encode($data) : serialize($data);
        Illuminate\\Support\\Facades\\DB::table('sessions')
            ->where('id', $row->id)
            ->update(['payload' => base64_encode($encoded)]);
    }
}
`,
    );
    try {
        execFileSync('php', [scriptPath], {
            env: { ...process.env, DB_DATABASE: getE2eDb() },
            stdio: 'inherit',
        });
    } finally {
        fs.unlinkSync(scriptPath);
    }
}

test.describe('REKAGIZI Admin E2E (Isolated mbg_e2e)', () => {
    let consoleErrors = [];
    let pageErrors = [];

    test.beforeEach(async ({ page }) => {
        consoleErrors = [];
        pageErrors = [];
        page.on('console', (msg) => {
            if (msg.type() === 'error') {
                consoleErrors.push(`[${page.url()}] ${msg.text()}`);
            }
        });
        page.on('pageerror', (err) => {
            pageErrors.push(`[${page.url()}] ${err.message}`);
        });
    });

    test.afterEach(async () => {
        expect(consoleErrors, `Console errors: ${consoleErrors.join('\n')}`).toEqual([]);
        expect(pageErrors, `Page errors: ${pageErrors.join('\n')}`).toEqual([]);
    });

    test('1. Autentikasi: gagal masuk, akun nonaktif, lupa sandi, wajib ganti sandi, sesi berakhir', async ({ page }) => {
        // Gagal masuk
        await page.goto('/login');
        await page.fill('input[name="username"]', 'arief.mahendra');
        await page.fill('input[name="password"]', 'SandiSalah123');
        await page.click('button[type="submit"]');
        await expect(page.locator('body')).toContainText('Username atau kata sandi salah');

        // Akun nonaktif
        await page.fill('input[name="username"]', 'rina.safitri');
        await page.fill('input[name="password"]', getTempPassword());
        await page.click('button[type="submit"]');
        await expect(page.locator('body')).toContainText('Akun Anda dinonaktifkan');

        // Lupa kata sandi
        await page.getByRole('button', { name: 'Lupa kata sandi?' }).click();
        const forgotPanel = page.locator('[role="dialog"]', { hasText: 'Reset Kata Sandi' });
        await expect(forgotPanel).toBeVisible();
        await forgotPanel.locator('input[name="identifier"]').fill('arief.mahendra');
        await forgotPanel.locator('select[name="reason"]').selectOption('Lupa kata sandi');
        await forgotPanel.getByRole('button', { name: /Kirim/i }).click();
        await page.waitForLoadState('networkidle');
        await expect(page.locator('body')).toContainText('Permintaan');

        // Ganti kata sandi wajib login pertama (nadia.putri)
        await page.goto('/login');
        await page.fill('input[name="username"]', 'nadia.putri');
        await page.fill('input[name="password"]', getTempPassword());
        await page.click('button[type="submit"]');
        await page.waitForURL('**/ganti-sandi');
        await page.fill('input[name="current_password"]', getTempPassword());
        await page.fill('input[name="password"]', 'NadiaBaru2026!');
        await page.fill('input[name="password_confirmation"]', 'NadiaBaru2026!');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard');
        await expect(page.locator('body')).toContainText('belum tersedia');

        // Logout nadia.putri lalu uji sesi berakhir sebagai admin
        await page.locator('form[action$="/logout"] button[type="submit"]').click();
        await page.waitForURL('**/login');

        await loginAsAdmin(page);
        expireSessionsInE2eDb();
        await page.goto('/admin/dashboard');
        await page.waitForURL('**/login');
        await expect(page.locator('body')).toContainText('Sesi berakhir');
    });

    test('2. Dashboard KPI & Keamanan Sesi -> Login membaca system_settings', async ({ page }) => {
        await loginAsAdmin(page);

        // Dashboard angka wajib
        await expect(page.locator('header')).toContainText('Selamat');
        await expect(page.locator('header')).toContainText('Arief');
        await expect(page.locator('main')).toContainText('1.170');
        await expect(page.locator('main')).toContainText('318');
        await expect(page.locator('main')).toContainText('850');
        await expect(page.locator('main')).toContainText('-2');
        await expect(page.locator('main')).toContainText('15');
        await expect(page.locator('main')).toContainText('2 dinonaktifkan');
        await expect(page.locator('main')).toContainText('5 / 7');

        // Ubah Keamanan Sesi -> verifikasi teks di halaman login
        await page.goto('/admin/pengaturan/keamanan-sesi');
        await page.fill('#session_idle_timeout_minutes', '45');
        await page.fill('#max_failed_login_attempts', '7');
        await page.getByRole('button', { name: /Simpan/i }).click();
        await expect(page.locator('[role="status"]')).toBeVisible();

        await page.locator('form[action$="/logout"] button[type="submit"]').first().click();
        await page.waitForURL('**/login');
        await expect(page.locator('body')).toContainText('Batas sesi otomatis 45 menit');
        await expect(page.locator('body')).toContainText('setelah 7 kali gagal masuk');

        // Kembalikan ke default 30 / 5
        await loginAsAdmin(page);
        await page.goto('/admin/pengaturan/keamanan-sesi');
        await page.fill('#session_idle_timeout_minutes', '30');
        await page.fill('#max_failed_login_attempts', '5');
        await page.getByRole('button', { name: /Simpan/i }).click();
        await expect(page.locator('[role="status"]')).toBeVisible();
    });

    test('3. Desktop: /admin/pengguna, /admin/log-aktivitas, /admin/data-master/*, /admin/pengaturan/backup', async ({ page }) => {
        await loginAsAdmin(page);

        // /admin/pengguna
        await page.goto('/admin/pengguna');
        await page.getByRole('button', { name: 'Tambah Pengguna' }).click();
        const userPanel = page.locator('[role="dialog"]', { hasText: 'Tambah / Ubah Pengguna' });
        await expect(userPanel).toBeVisible();
        await userPanel.getByRole('button', { name: 'Batal' }).click();
        await expect(userPanel).toBeHidden();

        const firstUserRow = page.locator('table tbody tr').first();
        await firstUserRow.getByRole('button', { name: 'Ubah' }).click();
        await expect(userPanel).toBeVisible();
        await expect(userPanel.locator('#user-name')).not.toHaveValue('');
        await page.screenshot({ path: path.join(screenshotDir, '01-pengguna-ubah.png') });
        await userPanel.getByRole('button', { name: 'Batal' }).click();

        await firstUserRow.getByRole('button', { name: 'Reset sandi' }).click();
        const resetDialog = page.locator('[role="dialog"]', { hasText: 'Reset kata sandi' });
        await expect(resetDialog).toBeVisible();
        await page.screenshot({ path: path.join(screenshotDir, '02-pengguna-reset-sandi.png') });
        await resetDialog.getByRole('button', { name: 'Batal' }).click();

        await page.locator('table tbody tr button:has-text("Nonaktifkan")').first().click();
        const deactivateDialog = page.locator('[role="dialog"]', { hasText: 'Nonaktifkan akun' });
        await expect(deactivateDialog).toBeVisible();
        await deactivateDialog.getByRole('button', { name: 'Batal' }).click();

        await page.goto('/admin/pengguna?q=bagus.setiawan');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle' }),
            page.locator('table tbody tr button:has-text("Aktifkan")').first().click(),
        ]);
        await expect(page.locator('[role="status"]')).toBeVisible();
        await page.goto('/admin/pengguna?q=bagus.setiawan');
        await page.locator('table tbody tr button:has-text("Nonaktifkan")').first().click();
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle' }),
            page.locator('[role="dialog"]', { hasText: 'Nonaktifkan akun' }).getByRole('button', { name: 'Nonaktifkan' }).click(),
        ]);

        // /admin/log-aktivitas
        await page.goto('/admin/log-aktivitas?aksi=revisi_data');
        await page.locator('table tbody tr').first().getByRole('button', { name: 'Detail' }).click();
        const logDrawer = page.locator('[role="dialog"]', { hasText: 'Detail Entri Log Aktivitas' });
        await expect(logDrawer).toBeVisible();
        await expect(logDrawer).toContainText('9500');
        await expect(logDrawer).toContainText('10000');
        await page.screenshot({ path: path.join(screenshotDir, '03-log-detail.png') });
        await logDrawer.getByRole('button', { name: 'Tutup panel' }).click();

        // /admin/data-master/* (7 sub-halaman)
        const subpages = [
            { url: '/admin/data-master/lokasi', addBtn: 'Tambah Lokasi', title: 'Formulir Lokasi Penerima' },
            { url: '/admin/data-master/kategori', addBtn: 'Tambah Kategori', title: 'Formulir Kategori Penerima' },
            { url: '/admin/data-master/standar-porsi', addBtn: 'Tambah Standar Porsi', title: 'Formulir Standar Porsi' },
            { url: '/admin/data-master/bahan', addBtn: 'Tambah Bahan', title: 'Formulir Daftar Bahan' },
            { url: '/admin/data-master/pemasok', addBtn: 'Tambah Pemasok', title: 'Formulir Pemasok' },
            { url: '/admin/data-master/pagu', addBtn: 'Tambah Parameter PAGU', title: 'Formulir Parameter PAGU' },
            { url: '/admin/data-master/checklist-sop', addBtn: 'Tambah Checklist SOP', title: 'Formulir Checklist SOP BGN' },
        ];
        for (const sp of subpages) {
            await page.goto(sp.url);
            await page.getByRole('button', { name: sp.addBtn }).click();
            const panel = page.locator('[role="dialog"]', { hasText: sp.title });
            await expect(panel).toBeVisible();
            await panel.getByRole('button', { name: 'Tutup panel' }).click();
            await page.locator('table tbody tr').first().getByRole('button', { name: 'Ubah' }).click();
            await expect(panel).toBeVisible();
            await panel.getByRole('button', { name: 'Tutup panel' }).click();
        }

        // /admin/pengaturan/backup (kartu Retensi Arsip tersembunyi)
        await page.goto('/admin/pengaturan/backup');
        await expect(page.locator('[role="alert"]')).toHaveCount(0);
        await expect(page.locator('main')).not.toContainText('Retensi Arsip');
        await page.screenshot({ path: path.join(screenshotDir, '04-backup.png') });

        await page.getByRole('button', { name: 'Buat Backup Manual' }).click();
        await page.waitForLoadState('networkidle');
        await expect(page.locator('[role="status"]')).toContainText('berhasil');

        const downloadPromise = page.waitForEvent('download');
        await page.locator('table tbody tr').first().getByRole('link', { name: 'Unduh' }).click();
        const download = await downloadPromise;
        expect(download.suggestedFilename()).toContain('.sql');

        await page.locator('table tbody tr').first().getByRole('button', { name: 'Pulihkan' }).click();
        const restoreDialog = page.locator('[role="dialog"]', { hasText: 'Konfirmasi Pemulihan Basis Data' });
        await expect(restoreDialog).toBeVisible();
        await restoreDialog.locator('#restore-password').fill(getAdminPassword());
        await restoreDialog.getByRole('button', { name: 'Ya, Pulihkan Sekarang' }).click();
        await page.waitForLoadState('networkidle');
        await expect(page.locator('[role="status"]')).toContainText('berhasil dipulihkan');
    });

    test('4. Mobile (390 x 844): menu geser, kartu mobile, dan bottom sheet di semua modul', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await loginAsAdmin(page);

        // Menu geser mobile
        await page.getByRole('button', { name: 'Buka menu samping' }).click();
        await page.getByRole('button', { name: 'Data Master' }).click();
        await expect(page.getByRole('link', { name: 'Lokasi Penerima' })).toBeVisible();
        await expect(page.getByRole('link', { name: 'Checklist SOP BGN' })).toBeVisible();
        await page.getByRole('button', { name: 'Pengaturan Sistem' }).click();
        await expect(page.getByRole('link', { name: 'Keamanan Sesi' })).toBeVisible();
        await expect(page.getByRole('link', { name: 'Backup & Restore' })).toBeVisible();

        // /admin/pengguna mobile cards + bottom sheet
        await page.goto('/admin/pengguna');
        const mobileUserCard = page.locator('article').filter({ hasText: 'arief.mahendra' }).first();
        await mobileUserCard.getByRole('button', { name: 'Ubah' }).click();
        await expect(page.locator('[role="dialog"]', { hasText: 'Tambah / Ubah Pengguna' })).toBeVisible();
        await page.locator('[role="dialog"]').getByRole('button', { name: 'Batal' }).click();

        await mobileUserCard.getByRole('button', { name: 'Reset sandi' }).click();
        await expect(page.locator('[role="dialog"]', { hasText: 'Reset kata sandi' })).toBeVisible();
        await page.locator('[role="dialog"]').getByRole('button', { name: 'Batal' }).click();

        // /admin/log-aktivitas mobile cards + drawer
        await page.goto('/admin/log-aktivitas?aksi=revisi_data');
        await page.getByRole('button', { name: 'Lihat Detail Log' }).first().click();
        await expect(page.locator('[role="dialog"]', { hasText: 'Detail Entri Log Aktivitas' })).toBeVisible();
        await page.locator('[role="dialog"]').getByRole('button', { name: 'Tutup panel' }).click();

        // /admin/data-master/lokasi mobile cards + bottom sheet
        await page.goto('/admin/data-master/lokasi');
        await page.locator('article button:has-text("Ubah")').first().click();
        await expect(page.locator('[role="dialog"]', { hasText: 'Formulir Lokasi Penerima' })).toBeVisible();
        await page.locator('[role="dialog"]').getByRole('button', { name: 'Tutup panel' }).click();

        // /admin/pengaturan/backup mobile cards
        await page.goto('/admin/pengaturan/backup');
        await page.locator('article button:has-text("Pulihkan")').first().click();
        await expect(page.locator('[role="dialog"]', { hasText: 'Konfirmasi Pemulihan Basis Data' })).toBeVisible();
        await page.locator('[role="dialog"]').getByRole('button', { name: 'Batal' }).first().click();
    });

    test('5. Screenshot 13 halaman pada 1280x800 dan 390x844 ke docs/qa-admin/', async ({ page }) => {
        test.setTimeout(180000);
        const routes = [
            { name: '01-login', url: '/login', auth: false },
            { name: '02-dashboard', url: '/admin/dashboard', auth: true },
            { name: '03-pengguna', url: '/admin/pengguna', auth: true },
            { name: '04-log-aktivitas', url: '/admin/log-aktivitas', auth: true },
            { name: '05-data-master', url: '/admin/data-master', auth: true },
            { name: '06-lokasi', url: '/admin/data-master/lokasi', auth: true },
            { name: '07-kategori', url: '/admin/data-master/kategori', auth: true },
            { name: '08-standar-porsi', url: '/admin/data-master/standar-porsi', auth: true },
            { name: '09-bahan', url: '/admin/data-master/bahan', auth: true },
            { name: '10-pemasok', url: '/admin/data-master/pemasok', auth: true },
            { name: '11-pagu', url: '/admin/data-master/pagu', auth: true },
            { name: '12-checklist-sop', url: '/admin/data-master/checklist-sop', auth: true },
            { name: '13-pengaturan-keamanan', url: '/admin/pengaturan/keamanan-sesi', auth: true },
            { name: '13b-pengaturan-backup', url: '/admin/pengaturan/backup', auth: true },
        ];

        // 390x844 login dulu sebelum auth
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto('/login');
        await page.screenshot({ path: path.join(screenshotDir, '390-01-login.png') });

        // 1280x800
        await page.setViewportSize({ width: 1280, height: 800 });
        await page.goto('/login');
        await page.screenshot({ path: path.join(screenshotDir, '1280-01-login.png') });
        await loginAsAdmin(page);
        for (const r of routes.filter((x) => x.auth)) {
            await page.goto(r.url);
            await page.screenshot({ path: path.join(screenshotDir, `1280-${r.name}.png`) });
        }

        // 390x844 halaman admin
        await page.setViewportSize({ width: 390, height: 844 });
        for (const r of routes.filter((x) => x.auth)) {
            await page.goto(r.url);
            await page.screenshot({ path: path.join(screenshotDir, `390-${r.name}.png`) });
        }
    });
});
