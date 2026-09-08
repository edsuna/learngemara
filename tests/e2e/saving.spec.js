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

/**
 * SAVE-16 - the browser half. A rejected save has to say which field failed;
 * previously the only surface was alert(result.message), a summary string, and
 * a network failure reported nothing at all.
 */
test.describe('Saving failures', () => {
    test.beforeEach(async ({ page }) => {
        await useLanguage(page, 'English');
        await logIn(page);
    });

    test('SAVE-16 a rejected save names the fields that failed', async ({ page }) => {
        await page.route('**/gemara_cases', (route) => {
            if (route.request().method() !== 'POST') return route.fallback();
            return route.fulfill({
                status: 422,
                contentType: 'application/json',
                body: JSON.stringify({
                    message: 'The given data was invalid.',
                    errors: { act: ['The act field is required.'], who: ['The who field is required.'] },
                }),
            });
        });

        await fillForm(page, 'conditions');
        await page.locator('button[x-text="localizedTexts.saveCase"]').click();

        const modal = page.locator('.gc-save-errors');
        await expect(modal).toBeVisible();
        await expect(modal).toContainText('The act field is required.');
        await expect(modal).toContainText('The who field is required.');
    });

    test('SAVE-16 a network failure is reported rather than swallowed', async ({ page }) => {
        await page.route('**/gemara_cases', (route) => {
            if (route.request().method() !== 'POST') return route.fallback();
            return route.abort('failed');
        });

        await fillForm(page, 'conditions');
        await page.locator('button[x-text="localizedTexts.saveCase"]').click();

        await expect(page.locator('.gc-save-errors')).toContainText('could not be saved');
    });
});
