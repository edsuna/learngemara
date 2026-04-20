import { test, expect } from '@playwright/test';

test.describe('Cases list page', () => {
    test('loads the public cases page', async ({ page }) => {
        await page.goto('/gemara_cases?public=1');
        await expect(page.locator('h1')).toBeVisible();
    });

    test('displays masechet filter dropdown', async ({ page }) => {
        await page.goto('/gemara_cases?public=1');
        const masechetSelect = page.locator('select').first();
        await expect(masechetSelect).toBeVisible();
    });

    test('masechet filter populates with tractates', async ({ page }) => {
        await page.goto('/gemara_cases?public=1');
        const masechetSelect = page.locator('select').first();
        await page.waitForTimeout(1000);
        const options = masechetSelect.locator('option');
        const count = await options.count();
        expect(count).toBeGreaterThan(1);
    });

    test('search input is visible', async ({ page }) => {
        await page.goto('/gemara_cases?public=1');
        const searchInput = page.locator('input[type="text"]');
        await expect(searchInput).toBeVisible();
    });

    test('language toggle switches UI language', async ({ page, context }) => {
        await context.addCookies([{ name: 'selectedLanguage', value: 'English', url: 'http://127.0.0.1:8000' }]);
        await page.goto('/gemara_cases?public=1');
        const toggle = page.locator('.cursor-pointer.rounded-lg');
        await expect(toggle).toHaveText('To Hebrew');

        await page.evaluate(() => window.changeLanguage());
        await expect(toggle).toHaveText('לאנגלית');
    });

    test('selecting a masechet shows daf filter', async ({ page }) => {
        await page.goto('/gemara_cases?public=1');
        await page.waitForTimeout(1000);

        const masechetSelect = page.locator('select').first();
        const options = masechetSelect.locator('option');
        const count = await options.count();
        if (count > 1) {
            const value = await options.nth(1).getAttribute('value');
            await masechetSelect.selectOption(value);
            await page.waitForTimeout(500);

            const dafSelect = page.locator('select').nth(1);
            const dafOptions = dafSelect.locator('option');
            const dafCount = await dafOptions.count();
            expect(dafCount).toBeGreaterThan(1);
        }
    });
});
