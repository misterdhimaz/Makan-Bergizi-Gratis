import { test, expect } from '@playwright/test';

function getTempPassword() {
    const value = process.env.E2E_TEMP_PASSWORD;
    if (!value) {
        throw new Error('E2E_TEMP_PASSWORD wajib disetel untuk menjalankan E2E test.');
    }
    return value;
}

async function loginAsAkuntan(page) {
    await page.goto('/login');
    await page.fill('input[name="username"]', 'rizky.pratama');
    await page.fill('input[name="password"]', getTempPassword());
    await page.click('button[type="submit"]');
    await page.waitForURL('**/akuntan/dashboard');
}

test.describe('REKAGIZI MBG — Modul Akuntan (Desktop & Mobile)', () => {
    test('Alur lengkap Akuntan di Desktop (1280x800)', async ({ page }) => {
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
        await loginAsAkuntan(page);

        // 1. Dashboard Akuntan
        await expect(page.locator('body')).toContainText('Dashboard Akuntan');
        await expect(page.locator('body')).toContainText('SPPG Sungai Selayur 1');
        await expect(page.locator('body')).toContainText('1.414');
        await expect(page.locator('body')).toContainText('Perlu diperiksa sekarang');

        // 2. Validasi Anggaran Menu
        await page.goto('/akuntan/validasi');
        await expect(page.locator('body')).toContainText('Menunggu (1)');
        await expect(page.locator('body')).toContainText('Sudah diperiksa (4)');
        await expect(page.locator('body')).toContainText('Semua menu · Hanya lihat (6)');

        const visibleEstimateInput = page.locator('input[type="number"]:visible').first();
        await expect(visibleEstimateInput).toHaveValue('');
        await visibleEstimateInput.fill('14200');
        await expect(page.locator('body')).toContainText('Di bawah PAGU');

        await page.locator('button[type="submit"]:visible').first().click();
        await page.waitForLoadState('networkidle');
        await expect(page.locator('body')).toContainText('berhasil dicatat');

        // 3. Parameter PAGU
        await page.goto('/akuntan/parameter-pagu');
        await expect(page.locator('body')).toContainText('Parameter PAGU');
        await page.fill('#nominal_per_porsi', '15500');
        await page.fill('#pagu_catatan', 'Penyesuaian E2E');
        await page.click('button:has-text("Simpan Parameter PAGU")');
        await page.waitForLoadState('networkidle');
        await expect(page.locator('body')).toContainText('berhasil diperbarui');
        await expect(page.locator('body')).toContainText('15.500');

        // 4. Laporan Pemeriksaan PAGU (Hanya lihat)
        await page.goto('/akuntan/laporan?periode=semua');
        await expect(page.locator('body')).toContainText('Laporan Pemeriksaan PAGU');
        await expect(page.locator('body')).toContainText('Mode Hanya Lihat');

        expect(errors).toEqual([]);
    });

    test('Navigasi Mobile (390x844) dan Bottom Nav Akuntan', async ({ page }) => {
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
        await loginAsAkuntan(page);

        await expect(page.locator('body')).toContainText('Selamat pagi');
        await page.click('button[aria-label="Buka menu"]');
        await expect(page.locator('#sidebar')).toBeVisible();
        await page.click('button[aria-label="Tutup menu"]');

        await page.locator('nav[aria-label="Navigasi bawah Akuntan"] a:has-text("Validasi")').click();
        await page.waitForURL('**/akuntan/validasi');
        await expect(page.locator('body')).toContainText('Validasi Anggaran Menu');

        await page.locator('nav[aria-label="Navigasi bawah Akuntan"] a:has-text("PAGU")').click();
        await page.waitForURL('**/akuntan/parameter-pagu');
        await expect(page.locator('body')).toContainText('Parameter PAGU');

        await page.locator('nav[aria-label="Navigasi bawah Akuntan"] a:has-text("Laporan")').click();
        await page.waitForURL('**/akuntan/laporan');
        await expect(page.locator('body')).toContainText('Laporan Pemeriksaan PAGU');

        expect(errors).toEqual([]);
    });
});
