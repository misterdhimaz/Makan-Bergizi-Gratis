import { test, expect } from '@playwright/test';

function getTempPassword() {
    const value = process.env.E2E_TEMP_PASSWORD;
    if (!value) {
        throw new Error('E2E_TEMP_PASSWORD wajib disetel untuk menjalankan E2E test.');
    }
    return value;
}

async function loginAsAhliGizi(page) {
    const newPassword = 'NadiaBaru2026!';
    await page.goto('/login');
    await page.fill('input[name="username"]', 'nadia.putri');
    await page.fill('input[name="password"]', newPassword);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    if (page.url().includes('/login')) {
        const tempPassword = getTempPassword();
        await page.fill('input[name="username"]', 'nadia.putri');
        await page.fill('input[name="password"]', tempPassword);
        await page.click('button[type="submit"]');
        await page.waitForURL('**/ganti-sandi');
        await page.fill('input[name="current_password"]', tempPassword);
        await page.fill('input[name="password"]', newPassword);
        await page.fill('input[name="password_confirmation"]', newPassword);
        await page.click('button[type="submit"]');
    }

    await page.waitForURL('**/ahli-gizi/dashboard');
}

test.describe('REKAGIZI MBG — Modul Ahli Gizi (Desktop & Mobile)', () => {
    test('Alur lengkap Ahli Gizi di Desktop (1280x800)', async ({ page }) => {
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
        await loginAsAhliGizi(page);

        // 1. Dashboard Ahli Gizi
        await expect(page.locator('header')).toContainText('Nadia');
        await expect(page.locator('body')).toContainText('Beras -1 kg · Tahu -2 potong');
        await expect(page.locator('body')).toContainText('tumis buncis tumpah pada 4 ompreng saat pengantaran pagi');
        await expect(page.locator('body')).not.toContainText('HACCP');
        await expect(page.locator('body')).not.toContainText(/\bkuah\b/i);

        // Ajukan revisi menu dari Dashboard
        await page.getByRole('button', { name: 'Ajukan revisi menu' }).click();
        const revisiPanel = page.locator('[role="dialog"]', { hasText: 'Ajukan revisi menu' });
        await expect(revisiPanel).toBeVisible();
        await revisiPanel.locator('textarea[name="reason"]').fill('Buncis segar terbatas, ditambah wortel agar porsi terpenuhi.');
        await revisiPanel.getByRole('button', { name: /Kirim Pengajuan/i }).click();
        await page.waitForLoadState('networkidle');
        await expect(page.locator('body')).toContainText('Pengajuan revisi menu berhasil');

        // 2. Perencanaan Menu
        await page.goto('/ahli-gizi/perencanaan-menu');
        await expect(page.locator('body')).toContainText('Perencanaan Menu');
        await expect(page.locator('body')).toContainText('Disetujui');
        await expect(page.locator('body')).toContainText('Menunggu pemeriksaan');
        await expect(page.locator('body')).toContainText('Kepala Dapur:');

        // Ubah Draf Menu
        const editDraftBtn = page.locator('table').getByRole('button', { name: 'Ubah', exact: true }).first();
        await editDraftBtn.click();
        const draftDialog = page.locator('[role="dialog"]', { hasText: 'Ubah Draf Menu' });
        await expect(draftDialog).toBeVisible();
        await draftDialog.locator('input[name="target_portions"]').fill('1400');
        await draftDialog.getByRole('button', { name: /Ajukan ke Akuntan/i }).click();
        await page.waitForLoadState('networkidle');
        await expect(page.locator('body')).toContainText('berhasil');

        // 3. Persediaan & Pemorsian
        await page.goto('/ahli-gizi/persediaan-pemorsian?tab=penerimaan');
        await expect(page.locator('body')).toContainText('Asisten Lapangan');
        await page.getByRole('button', { name: 'Verifikasi' }).first().click();
        const verifDialog = page.locator('[role="dialog"]', { hasText: 'Verifikasi penerimaan bahan' });
        await expect(verifDialog).toBeVisible();
        await verifDialog.locator('select[name="verification_status"]').selectOption('sesuai');
        await verifDialog.locator('textarea[name="verification_notes"]').fill('Kualitas bahan sesuai spesifikasi.');
        await verifDialog.getByRole('button', { name: 'Simpan Verifikasi' }).click();
        await page.waitForLoadState('networkidle');
        await expect(page.locator('body')).toContainText('Verifikasi penerimaan bahan berhasil');

        await page.goto('/ahli-gizi/persediaan-pemorsian?tab=realisasi');
        await expect(page.locator('body')).toContainText('Jumlah ompreng terisi');
        await page.getByRole('button', { name: 'Catat ompreng terisi' }).click();
        const omprengDialog = page.locator('[role="dialog"]', { hasText: 'Catat ompreng terisi' });
        await expect(omprengDialog).toBeVisible();
        await omprengDialog.locator('input[name="actual_good_portions"]').fill('1414');
        await omprengDialog.getByRole('button', { name: /Simpan/i }).click();
        await page.waitForLoadState('networkidle');
        await expect(page.locator('body')).toContainText('Jumlah ompreng terisi berhasil dicatat');

        await page.goto('/ahli-gizi/persediaan-pemorsian?tab=kendala');
        await page.getByRole('button', { name: 'Tambah catatan kendala' }).click();
        const kendalaDialog = page.locator('[role="dialog"]', { hasText: 'Tambah catatan kendala' });
        await expect(kendalaDialog).toBeVisible();
        await kendalaDialog.locator('textarea[name="description"]').fill('Truk pengantar bahan terlambat 15 menit karena hujan.');
        await kendalaDialog.getByRole('button', { name: /Simpan/i }).click();
        await page.waitForLoadState('networkidle');
        await expect(page.locator('body')).toContainText('Truk pengantar bahan terlambat 15 menit');

        // 4. Keamanan Pangan & APD
        await page.goto('/ahli-gizi/keamanan-pangan');
        await expect(page.locator('body')).toContainText('Keamanan Pangan');
        await expect(page.locator('body')).toContainText('13:00 WIB');
        await expect(page.locator('body')).toContainText('Hendra Wijaya');
        await expect(page.locator('body')).not.toContainText('HACCP');

        await page.locator('input[name="compliant_volunteers"]').fill('35');
        await page.getByRole('button', { name: 'Simpan Pemeriksaan' }).click();
        await page.waitForLoadState('networkidle');
        await expect(page.locator('body')).toContainText('Pemeriksaan Keamanan Pangan & APD berhasil disimpan');

        // 5. Grup Hanya lihat
        for (const path of ['/ahli-gizi/distribusi', '/ahli-gizi/keluhan', '/ahli-gizi/laporan', '/ahli-gizi/data-master']) {
            await page.goto(path);
            await expect(page.locator('body')).toContainText('Hanya lihat');
            await expect(page.locator('body')).not.toContainText('HACCP');
            await expect(page.locator('body')).not.toContainText(/\bkuah\b/i);
        }

        expect(errors, `Browser errors found:\n${errors.join('\n')}`).toEqual([]);
    });

    test('Responsif Mobile (390x844): drawer, bottom sheet, tanpa overflow horizontal', async ({ page }) => {
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
        await loginAsAhliGizi(page);

        for (const path of [
            '/ahli-gizi/dashboard',
            '/ahli-gizi/perencanaan-menu',
            '/ahli-gizi/persediaan-pemorsian',
            '/ahli-gizi/keamanan-pangan',
            '/ahli-gizi/distribusi',
        ]) {
            await page.goto(path);
            const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
            expect(overflow, `Horizontal overflow on ${path}`).toBeLessThanOrEqual(2);
        }

        // Buka drawer mobile
        await page.goto('/ahli-gizi/dashboard');
        await page.getByRole('button', { name: /Buka menu/i }).click();
        await expect(page.getByRole('link', { name: 'Perencanaan Menu' }).first()).toBeVisible();

        expect(errors, `Browser errors found:\n${errors.join('\n')}`).toEqual([]);
    });
});
