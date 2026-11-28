import { defineConfig } from '@playwright/test';
import crypto from 'node:crypto';

const e2eDb = process.env.E2E_DB_DATABASE || 'mbg_e2e';
process.env.E2E_DB_DATABASE = e2eDb;
process.env.E2E_ADMIN_PASSWORD = process.env.E2E_ADMIN_PASSWORD || `${crypto.randomBytes(10).toString('hex')}Aa1!`;
process.env.E2E_TEMP_PASSWORD = process.env.E2E_TEMP_PASSWORD || `${crypto.randomBytes(10).toString('hex')}Bb2!`;

export default defineConfig({
    testDir: './tests/e2e',
    workers: 1,
    timeout: 60000,
    retries: 0,
    use: {
        baseURL: 'http://127.0.0.1:8011',
        viewport: { width: 1280, height: 800 },
        headless: true,
    },
    webServer: {
        command: 'node tests/e2e/start-server.mjs',
        url: 'http://127.0.0.1:8011/login',
        reuseExistingServer: false,
        timeout: 60000,
        env: {
            ...process.env,
            DB_DATABASE: e2eDb,
            E2E_ADMIN_PASSWORD: process.env.E2E_ADMIN_PASSWORD,
            E2E_TEMP_PASSWORD: process.env.E2E_TEMP_PASSWORD,
        },
    },
    reporter: [['list']],
});
