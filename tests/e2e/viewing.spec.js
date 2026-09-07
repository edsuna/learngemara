import { test, expect } from '@playwright/test';
import { useLanguage, fixtures, logIn } from './support/helpers.js';

/**
 * docs/spec/viewing.md
 *
 * Read-only is not a separate template -- it is the same page with
 * allowUpdates false. Most of what that suppresses is hidden by Alpine at
 * runtime, so it is only observable in a browser.
 */

test.describe('Viewing a case', () => {
    test.beforeEach(async ({ page }) => {
        await useLanguage(page, 'English');
    });

    test('VIEW-02 a non-owner gets no editing controls', async ({ page }) => {
        const { completeCaseId } = fixtures();
        await page.goto(`/gemara_cases/${completeCaseId}`);
        await expect(page.locator('#gc-act')).toBeVisible();

        await expect(page.locator('select[x-model="theCase.masechet"]')).toBeHidden();
        await expect(page.locator('select[x-model="theCase.daf"]')).toBeHidden();
        await expect(page.locator('#gc-act input[type="text"]')).toBeHidden();
        // Two different mechanisms, deliberately asserted differently: the
        // Not Relevant checkboxes are present but Alpine-hidden, whereas the
        // save buttons are inside an @auth block and so are absent from the
        // markup entirely for a guest.
        await expect(page.locator('.gc-diagram input[type="checkbox"]:visible')).toHaveCount(0);
        await expect(page.locator('button[x-text="localizedTexts.saveCase"]')).toHaveCount(0);
    });

    test('VIEW-03 stored values are rendered as text', async ({ page }) => {
        const { completeCaseId } = fixtures();
        await page.goto(`/gemara_cases/${completeCaseId}`);

        await expect(page.locator('#gc-act')).toContainText('E2E-FIXTURE act');
        for (const condition of ['who', 'where', 'toWhat', 'withWhat']) {
            await expect(page.locator(`#gc-${condition}`)).toContainText(`E2E-FIXTURE ${condition}`);
        }
    });

    test('VIEW-07 hideicons suppresses icons but keeps labels', async ({ page }) => {
        const { completeCaseId } = fixtures();
        await page.goto(`/gemara_cases/${completeCaseId}?hideicons=1`);
        await expect(page.locator('#gc-who')).toBeVisible();

        await expect(page.locator('#gc-who img')).toBeHidden();
        await expect(page.locator('#gc-who')).toContainText('Who');
    });

    test('VIEW-08 a saved case draws its arrows after hydration', async ({ page }) => {
        const { completeCaseId } = fixtures();
        await page.goto(`/gemara_cases/${completeCaseId}`);
        await expect(page.locator('#gc-act')).toBeVisible();

        // initCaseData() hydrates the eight conditions from snake_case columns
        // and schedules initArrows on a timer.
        const { expectArrowsConnected } = await import('./support/helpers.js');
        await expect(page.locator('.arrow')).toHaveCount(8);
        await expectArrowsConnected(page);
    });
});
