import { test, expect } from '@playwright/test';

test.describe('Case create form', () => {
    test('loads the create form page', async ({ page }) => {
        await page.goto('/gemara_cases/create');
        await expect(page.locator('h1')).toBeVisible();
    });

    test('displays masechet dropdown', async ({ page }) => {
        await page.goto('/gemara_cases/create');
        const masechetSelect = page.locator('select[x-model="theCase.masechet"]');
        await expect(masechetSelect).toBeVisible();
    });

    test('masechet dropdown populates with tractates from API', async ({ page }) => {
        await page.goto('/gemara_cases/create');
        const masechetSelect = page.locator('select[x-model="theCase.masechet"]');
        await page.waitForTimeout(1000);
        const options = masechetSelect.locator('option');
        const count = await options.count();
        expect(count).toBeGreaterThan(1);
    });

    test('selecting masechet shows daf dropdown', async ({ page }) => {
        await page.goto('/gemara_cases/create');
        await page.waitForTimeout(1000);

        const masechetSelect = page.locator('select[x-model="theCase.masechet"]');
        const options = masechetSelect.locator('option');
        const count = await options.count();
        if (count > 1) {
            const value = await options.nth(1).getAttribute('value');
            await masechetSelect.selectOption(value);
            await page.waitForTimeout(500);

            const dafSelect = page.locator('select[x-model="theCase.daf"]');
            await expect(dafSelect).toBeVisible();
            const dafOptions = dafSelect.locator('option');
            const dafCount = await dafOptions.count();
            expect(dafCount).toBeGreaterThan(1);
        }
    });

    test('language toggle switches UI language', async ({ page, context }) => {
        await context.addCookies([{ name: 'selectedLanguage', value: 'English', url: 'http://127.0.0.1:8000' }]);
        await page.goto('/gemara_cases/create');
        const toggle = page.locator('.cursor-pointer.rounded-lg');
        await expect(toggle).toHaveText('To Hebrew');

        await page.evaluate(() => window.changeLanguage());
        await expect(toggle).toHaveText('לאנגלית');
    });

    test('din type dropdown contains all 6 options', async ({ page }) => {
        await page.goto('/gemara_cases/create');
        await page.waitForTimeout(1000);

        const dinSelect = page.locator('div.gc-din-type select');
        const dinOptions = dinSelect.locator('option');
        const dinCount = await dinOptions.count();
        expect(dinCount).toBe(7); // placeholder + 6 din types
    });

    test('form flow: masechet -> daf -> text reveals diagram', async ({ page }) => {
        await page.goto('/gemara_cases/create');
        await page.waitForTimeout(1000);

        // Select masechet
        const masechetSelect = page.locator('select[x-model="theCase.masechet"]');
        const options = masechetSelect.locator('option');
        if (await options.count() <= 1) return;
        await masechetSelect.selectOption(await options.nth(1).getAttribute('value'));
        await page.waitForTimeout(500);

        // Select daf
        const dafSelect = page.locator('select[x-model="theCase.daf"]');
        const dafOptions = dafSelect.locator('option');
        if (await dafOptions.count() <= 1) return;
        await dafSelect.selectOption(await dafOptions.nth(1).getAttribute('value'));
        await page.waitForTimeout(300);

        // Enter gemara text via the textarea (model is tmpSelectedText, blur copies to theCase.gemaraText)
        const textarea = page.locator('textarea').first();
        await textarea.fill('Test gemara text');
        await textarea.blur();
        await page.waitForTimeout(300);

        // Din type section should now be visible
        const dinSelect = page.locator('div.gc-din-type select');
        await expect(dinSelect).toBeVisible();

        // Select din type
        await dinSelect.selectOption('מותר');
        await page.waitForTimeout(300);

        // Act input should now be visible
        const actInput = page.locator('#gc-act input[type="text"]');
        await expect(actInput).toBeVisible();
    });

    test('shows login message after completing act and din type', async ({ page }) => {
        await page.goto('/gemara_cases/create');
        await page.waitForTimeout(1000);

        // Select masechet
        const masechetSelect = page.locator('select[x-model="theCase.masechet"]');
        const options = masechetSelect.locator('option');
        if (await options.count() <= 1) return;
        await masechetSelect.selectOption(await options.nth(1).getAttribute('value'));
        await page.waitForTimeout(500);

        // Select daf
        const dafSelect = page.locator('select[x-model="theCase.daf"]');
        const dafOptions = dafSelect.locator('option');
        if (await dafOptions.count() <= 1) return;
        await dafSelect.selectOption(await dafOptions.nth(1).getAttribute('value'));
        await page.waitForTimeout(300);

        // Enter gemara text
        const textarea = page.locator('textarea').first();
        await textarea.fill('Test gemara text');
        await textarea.blur();
        await page.waitForTimeout(300);

        // Select din type first (act section requires dinType)
        const dinSelect = page.locator('div.gc-din-type select');
        await dinSelect.selectOption('מותר');
        await page.waitForTimeout(300);

        // Fill act
        const actInput = page.locator('#gc-act input[type="text"]');
        await actInput.fill('Test act');
        await actInput.blur();
        await page.waitForTimeout(500);

        // Login message should be visible
        const loginMessage = page.getByText('Please login to be able to save.');
        await expect(loginMessage).toBeVisible({ timeout: 5000 });
    });

    test('input condition fields are present in DOM', async ({ page }) => {
        await page.goto('/gemara_cases/create');
        await page.waitForTimeout(500);

        const conditions = ['who', 'how', 'where', 'when', 'consequences', 'other'];
        for (const condition of conditions) {
            const el = page.locator(`#gc-${condition}`);
            await expect(el).toBeAttached();
        }
    });

    test('act element is present', async ({ page }) => {
        await page.goto('/gemara_cases/create');
        await page.waitForTimeout(500);
        await expect(page.locator('#gc-act')).toBeAttached();
    });
});
