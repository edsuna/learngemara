import { test, expect } from '@playwright/test';
import { useLanguage, fillForm } from './support/helpers.js';

/**
 * docs/spec/input-conditions.md
 *
 * The state half of these lives in resources/js/__tests__/gemara_case.test.js.
 * What can only be checked in a browser is the visibility choreography: which
 * control is showing for which state, and the fact that checking Not Relevant
 * hides the very checkbox that would uncheck it.
 */

const state = (page, condition) => page.evaluate((c) => {
    const data = Alpine.$data(document.getElementById('gc-' + c));
    const ic = data.theCase.inputConditions[c];
    return { value: ic.value, notRelevant: ic.notRelevant, draft: data.tmpCase[c] };
}, condition);

test.describe('Input conditions', () => {
    test.beforeEach(async ({ page }) => {
        await useLanguage(page, 'English');
        await fillForm(page, 'act');
    });

    test('COND-01 a condition starts unanswered', async ({ page }) => {
        expect(await state(page, 'who')).toMatchObject({ value: '', notRelevant: false });
        await expect(page.locator('#gc-who input[type="text"]')).toBeVisible();
    });

    test('COND-02 typing alone does not commit the value', async ({ page }) => {
        await page.locator('#gc-who input[type="text"]').fill('An adult');

        expect((await state(page, 'who')).value).toBe('');
        await expect(page.locator('#gc-who input[type="text"]')).toBeVisible();
    });

    test('COND-03 committing replaces the input with the value', async ({ page }) => {
        const input = page.locator('#gc-who input[type="text"]');
        await input.fill('An adult');
        await input.dispatchEvent('change');

        expect((await state(page, 'who')).value).toBe('An adult');
        await expect(input).toBeHidden();
        await expect(page.locator('#gc-who')).toContainText('An adult');
    });

    test('COND-04 marking Not Relevant stores a single space', async ({ page }) => {
        await page.locator('#gc-where input[type="checkbox"]').first().check();

        // A space, not an empty string: it is what satisfies `required` on the
        // way in, before the server middleware rewrites it to NULL.
        expect(await state(page, 'where')).toMatchObject({ value: ' ', notRelevant: true });
        await expect(page.locator('#gc-where')).toContainText('Not Relevant');
    });

    test('COND-05 marking Not Relevant hides its own checkbox', async ({ page }) => {
        const checkbox = page.locator('#gc-where input[type="checkbox"]').first();
        await checkbox.check();

        // value is now non-empty, and the checkbox lives inside the
        // x-show="!value" block. Recovery is COND-06.
        await expect(checkbox).toBeHidden();
    });

    test('COND-06 double-clicking Not Relevant clears it', async ({ page }) => {
        await page.locator('#gc-where input[type="checkbox"]').first().check();
        await expect(page.locator('#gc-where')).toContainText('Not Relevant');

        await page.locator('#gc-where span').filter({ hasText: 'Not Relevant' }).first().dblclick();

        expect(await state(page, 'where')).toMatchObject({ value: '', notRelevant: false });
        await expect(page.locator('#gc-where input[type="text"]')).toBeVisible();
        await expect(page.locator('#gc-where input[type="checkbox"]').first()).toBeVisible();
    });

    test('COND-07 double-clicking a value clears it', async ({ page }) => {
        const input = page.locator('#gc-who input[type="text"]');
        await input.fill('An adult');
        await input.dispatchEvent('change');
        await expect(input).toBeHidden();

        await page.locator('#gc-who span').filter({ hasText: 'An adult' }).first().dblclick();

        expect((await state(page, 'who')).value).toBe('');
        await expect(input).toBeVisible();
    });

    test('COND-08 a Not Relevant oval is dimmed but keeps its arrow', async ({ page }) => {
        await page.locator('#gc-who input[type="checkbox"]').first().check();

        await expect(page.locator('.gc-who')).toHaveClass(/opacity-50/);
        await expect(page.locator('#gc-who')).toBeVisible();
        await expect(page.locator('.arrow')).toHaveCount(8);
    });

    test('COND-09 resetting a condition keeps the typed draft', async ({ page }) => {
        const input = page.locator('#gc-who input[type="text"]');
        await input.fill('An adult');
        await input.dispatchEvent('change');

        await page.locator('#gc-who span').filter({ hasText: 'An adult' }).first().dblclick();

        // The stored value is cleared but tmpCase is not, so the reappearing
        // input is pre-filled with the text that was just discarded.
        expect(await state(page, 'who')).toMatchObject({ value: '', draft: 'An adult' });
        await expect(input).toHaveValue('An adult');
    });
});
