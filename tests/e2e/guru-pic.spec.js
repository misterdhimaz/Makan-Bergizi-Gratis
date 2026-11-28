import { test, expect } from '@playwright/test';

function getTempPassword() {
    const value = process.env.E2E_TEMP_PASSWORD;
    if (!value) {
        throw new Error('E2E_TEMP_PASSWORD wajib disetel untuk menjalankan E2E test.');
    }
    return value;
}

async function loginAsGuruPic(page) {
    await page.goto('/login');
    await page.fill('input[name="username"]', 'rina.marlina');
    await page.fill('input[name="password"]', getTempPassword());
    await page.click('button[type="submit"]');
    await page.waitForURL('**/guru-pic/dashboard');
}

test.describe('REKAGIZI MBG — Modul Guru PIC Sekolah (Desktop & Mobile)', () => {
    test('Alur lengkap Guru PIC di Desktop (1280x800)', async ({ page }) => {
        const errors = [];
        page.on('console', (msg) => {
            if (msg.type() === 'error') {
                errors.push(`console.error: ${msg.text()}`);
            }
        });
        page.on('pageerror', (err) => {
            errors.push(`pageerror: ${err.message}`);
        });

        await page.setViewportSize({ width: 1280, height: 800 });
        await loginAsGuruPic(page);

        // 1. Dashboard Guru PIC
        await expect(page.locator('body')).toContainText('Pesantren Al-Falah');
        await expect(page.locator('body')).toContainText('350');
        await expect(page.locator('body')).toContainText('Telat 10 mnt');
        await expect(page.locator('body')).toContainText('Menu Hari Ini');
        await expect(page.locator('body')).toContainText('Jejak Pengiriman Hari Ini');
        await expect(page.locator('body')).not.toContainText('Koneksi stabil');

        // 2. Konfirmasi & Organoleptik — uji deteksi otomatis real-time & simpan
        await page.goto('/guru-pic/konfirmasi');
        await expect(page.locator('#telat-alert')).toContainText('Terlambat 10 menit');

        await page.fill('#porsi-input', '350');
        await page.fill('#jam-input', '08:00');
        await expect(page.locator('#auto-status-banner')).toContainText('Sesuai pengiriman & tepat waktu');

        await page.selectOption('select[name="packaging_condition"]', 'baik');
        await page.fill('input[name="notes"]', 'Diterima lengkap dan tepat waktu.');
        await page.click('#submit-konfirmasi-btn');
        await page.waitForLoadState('networkidle');
        await expect(page.locator('body')).toContainText('Konfirmasi penerimaan & uji organoleptik berhasil disimpan');

        // 3. Keluhan & Tindak Lanjut — buat tiket prioritas
        await page.goto('/guru-pic/keluhan');
        await page.getByRole('button', { name: 'Buat Tiket Keluhan' }).first().click();
        const dialog = page.locator('[role="dialog"]', { hasText: 'Buat Tiket Keluhan' });
        await expect(dialog).toBeVisible();
        await expect(dialog).toContainText('Nomor Tiket Otomatis: #KLP-');
        await dialog.locator('select[name="category"]').selectOption('kemasan_tumpah');
        await dialog.locator('select[name="priority"]').selectOption('serius');
        await dialog.locator('textarea[name="description"]').fill('Tutup 2 ompreng longgar saat tiba di pos penerima.');
        await dialog.getByRole('button', { name: 'Kirim Tiket Keluhan' }).click();
        await page.waitForLoadState('networkidle');
        await expect(page.locator('body')).toContainText('Notifikasi prioritas telah diteruskan');
        await expect(page.locator('body')).toContainText('Tutup 2 ompreng longgar saat tiba di pos penerima.');

        expect(errors, `Browser errors found:\n${errors.join('\n')}`).toEqual([]);
    });

    test('Responsif Mobile (390x844): drawer, bottom sheet, touch target >=44px, tanpa overflow', async ({ page }) => {
        const errors = [];
        page.on('console', (msg) => {
            if (msg.type() === 'error') {
                errors.push(`console.error: ${msg.text()}`);
            }
        });
        page.on('pageerror', (err) => {
            errors.push(`pageerror: ${err.message}`);
        });

        await page.setViewportSize({ width: 390, height: 844 });
        await loginAsGuruPic(page);

        for (const path of ['/guru-pic/dashboard', '/guru-pic/konfirmasi', '/guru-pic/keluhan']) {
            await page.goto(path);
            const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
            expect(overflow, `Horizontal overflow on ${path}`).toBeLessThanOrEqual(2);
        }

        // Cek tombol & input minimal 44px di halaman Konfirmasi mobile
        await page.goto('/guru-pic/konfirmasi');
        const minTouchHeight = await page.evaluate(() => {
            const els = Array.from(document.querySelectorAll('#konfirmasi-form input:not([type="hidden"]), #konfirmasi-form select, #konfirmasi-form button'));
            return Math.min(...els.map((el) => el.getBoundingClientRect().height));
        });
        expect(minTouchHeight).toBeGreaterThanOrEqual(44);

        // Buka drawer mobile
        await page.getByRole('button', { name: /Buka menu navigasi/i }).click();
        await expect(page.getByRole('link', { name: 'Keluhan & Tindak Lanjut' }).first()).toBeVisible();

        expect(errors, `Browser errors found:\n${errors.join('\n')}`).toEqual([]);
    });
});
