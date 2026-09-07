import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: './tests/e2e',
    globalSetup: './tests/e2e/support/global-setup.js',
    globalTeardown: './tests/e2e/support/global-teardown.js',
    timeout: 30000,
    retries: 0,
    use: {
        baseURL: 'http://127.0.0.1:8000',
        headless: true,
        screenshot: 'only-on-failure',
    },
    projects: [
        {
            name: 'chromium',
            use: { browserName: 'chromium' },
        },
    ],
    webServer: {
        command: 'php artisan serve',
        url: 'http://127.0.0.1:8000',
        reuseExistingServer: true,
        timeout: 10000,
    },
});
