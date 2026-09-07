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

/** docs/spec/case-list.md - the parts that need a browser. */
test.describe('Case list (spec)', () => {
    test('LIST-09 the active filter is in the URL and is reproducible', async ({ page }) => {
        await page.context().addCookies([{ name: 'selectedLanguage', value: 'English', url: 'http://127.0.0.1:8000' }]);
        await page.goto('/gemara_cases?public=1');
        await page.waitForFunction(
            () => document.querySelectorAll('select')[0].options.length > 1,
            null, { timeout: 15000 },
        );

        // Take the baseline from the URL form, which is stable on load;
        // counting straight after the dropdown selection races Livewire.
        await page.goto('/gemara_cases?public=1&masechet=Berakhot');
        const expectedRows = await page.locator('.case-row').count();
        expect(expectedRows).toBeGreaterThan(0);

        await page.goto('/gemara_cases?public=1');
        await page.waitForFunction(
            () => document.querySelectorAll('select')[0].options.length > 1,
            null, { timeout: 15000 },
        );
        await page.locator('select').first().selectOption('Berakhot');

        // Both the address bar and the result set must converge on the filter.
        await expect(page).toHaveURL(/masechet=Berakhot/);
        await expect(page.locator('.case-row')).toHaveCount(expectedRows);
    });

    test('LIST-15 deleting takes two clicks', async ({ page }) => {
        const { logIn, FIXTURE } = await import('./support/helpers.js');
        await logIn(page);
        await page.goto('/gemara_cases?public=0');

        const { deletableCaseId } = (await import('./support/helpers.js')).fixtures();

        // Target the disposable fixture specifically -- deleting the shared one
        // would pull it out from under every test that reads it.
        const row = page.locator('.case-row').filter({ hasText: 'deletable case' }).first();
        await expect(row).toBeVisible();

        await page.locator(`[wire\\:click="confirmRemove(${deletableCaseId})"]`).click();
        // Still there: the first click only asks.
        await expect(row).toBeVisible();

        const confirm = page.locator(`[wire\\:click="removeGemaraCase(${deletableCaseId})"]`);
        await expect(confirm).toBeVisible();
        await confirm.click();

        await expect(page.locator('.case-row').filter({ hasText: 'deletable case' })).toHaveCount(0);
    });

    test('LIST-17 delete controls are not shown to non-owners', async ({ page }) => {
        await page.goto('/gemara_cases?public=1');
        await expect(page.locator('.case-row').first()).toBeVisible();

        // A guest sees rows but no delete affordance anywhere.
        await expect(page.locator('[wire\\:click^="confirmRemove"]')).toHaveCount(0);
    });
});
