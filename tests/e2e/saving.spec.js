import { test, expect } from '@playwright/test';
import { useLanguage, fillForm, logIn, FIXTURE, fixtures } from './support/helpers.js';

/** docs/spec/saving.md - the client-side gates. Persistence is covered in PHPUnit. */

test.describe('Saving', () => {
    test.beforeEach(async ({ page }) => {
        await useLanguage(page, 'English');
    });

    test('SAVE-01 a guest is told to log in instead of being offered save', async ({ page }) => {
        await fillForm(page, 'act');
        await expect(page.locator('button[x-text="localizedTexts.saveCase"]')).toHaveCount(0);
        await expect(page.getByText('Please login to be able to save.')).toBeVisible();
    });

    test('SAVE-03 save is disabled until the case is complete', async ({ page }) => {
        await logIn(page);
        await fillForm(page, 'act');

        await expect(page.locator('button[x-text="localizedTexts.saveCase"]')).toBeDisabled();
        await expect(page.locator('div.bg-red-600').first()).toBeVisible();
    });

    test('SAVE-04 the badge explains what is missing', async ({ page }) => {
        await logIn(page);
        await fillForm(page, 'act');

        await page.locator('div.bg-red-600').first().click();
        await expect(page.locator('body')).toContainText('You must fill in the Consequences or mark it N/R');
    });

    test('SAVE-05 completing every condition enables save', async ({ page }) => {
        await logIn(page);
        await fillForm(page, 'conditions');

        await expect(page.locator('button[x-text="localizedTexts.saveCase"]')).toBeEnabled();
    });

    test('SAVE-06 saving stores the case and returns to the list', async ({ page }) => {
        await logIn(page);
        await fillForm(page, 'conditions');

        // Tagged so global teardown removes it.
        const title = page.locator('input[x-model="theCase.title"]');
        if (await title.count()) {
            await title.fill(`${FIXTURE.tag} saved by SAVE-06`);
        }

        await page.locator('button[x-text="localizedTexts.saveCase"]').click();
        await page.waitForURL('**/gemara_cases/**');
        expect(page.url()).toContain('/gemara_cases');
    });

    test('SAVE-09 Save as New is offered only when editing', async ({ page }) => {
        await logIn(page);

        await fillForm(page, 'conditions');
        await expect(page.locator('button[x-text="localizedTexts.saveCaseAs"]')).toBeHidden();

        const { completeCaseId } = fixtures();
        await page.goto(`/gemara_cases/${completeCaseId}/edit`);
        await expect(page.locator('#gc-act')).toBeVisible();
        await expect(page.locator('button[x-text="localizedTexts.saveCaseAs"]')).toBeVisible();
    });
});
