import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './tests/e2e',
    timeout: 30_000,
    workers: 1,
    use: {
        baseURL: process.env.E2E_BASE_URL || 'http://localhost',
        executablePath: process.env.E2E_BROWSER_PATH || '/usr/bin/google-chrome',
        headless: true,
        trace: 'retain-on-failure',
    },
    projects: [
        { name: 'desktop', use: { ...devices['Desktop Chrome'], viewport: { width: 1280, height: 900 } } },
        { name: 'mobile', use: { ...devices['Desktop Chrome'], viewport: { width: 390, height: 844 }, isMobile: true } },
    ],
});
