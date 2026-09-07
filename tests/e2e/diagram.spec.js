import { test, expect } from '@playwright/test';
import { FIXTURE, useLanguage, fillForm, expectArrowsConnected } from './support/helpers.js';

/**
 * docs/spec/diagram.md
 *
 * The arrows are the reason this file exists in a browser and not in Vitest.
 * They are positioned in viewport coordinates by a requestAnimationFrame
 * observer inside arrows-svg -- that loop is the resize handling, the RTL
 * handling and the scroll handling all at once, and there is no resize
 * listener anywhere in the codebase because none is needed.
 *
 * A replacement that draws arrows once will satisfy DIAG-09 and fail
 * DIAG-11 through DIAG-15. That is the point of these tests: they constrain
 * how the arrows may be reimplemented, not merely that they appear.
 */

test.describe('Diagram rendering', () => {
    test.beforeEach(async ({ page }) => {
        await useLanguage(page, 'English');
    });

    test('DIAG-01 the diagram is hidden until there is gemara text', async ({ page }) => {
        await fillForm(page, 'daf');
        await expect(page.locator('.gc-diagram')).toBeHidden();
    });

    test('DIAG-02 the act oval appears once a din type is chosen', async ({ page }) => {
        await fillForm(page, 'din');
        await expect(page.locator('#gc-act')).toBeVisible();
        await expect(page.locator('#gc-who')).toBeHidden();
    });

    test('DIAG-03 condition ovals appear once the act is entered', async ({ page }) => {
        await fillForm(page, 'act');
        for (const condition of ['consequences', 'when', 'where', 'toWhat', 'withWhat', 'how', 'other', 'who']) {
            await expect(page.locator(`#gc-${condition}`)).toBeVisible();
        }
    });

    test('DIAG-04 every input condition has an oval', async ({ page }) => {
        await fillForm(page, 'act');
        // toWhat and withWhat were missing from the original condition loop.
        const conditions = ['consequences', 'when', 'where', 'toWhat', 'withWhat', 'how', 'other', 'who'];
        for (const condition of conditions) {
            await expect(page.locator(`#gc-${condition}`)).toBeAttached();
        }
        await expect(page.locator('.gc-diagram .diagram-ellipse:visible')).toHaveCount(conditions.length + 1);
    });

    test('DIAG-05 a Not Relevant condition is dimmed but still shown', async ({ page }) => {
        await fillForm(page, 'act');
        await page.locator('#gc-who input[type="checkbox"]').first().check();
        await expect(page.locator('#gc-who')).toBeVisible();
        await expect(page.locator('.gc-who')).toHaveClass(/opacity-50/);
    });

    test('DIAG-06 condition icons render by default', async ({ page }) => {
        await fillForm(page, 'act');
        await expect(page.locator('#gc-who img')).toBeVisible();
    });

    test('DIAG-07 hideicons suppresses the icons but keeps the labels', async ({ page }) => {
        await page.goto('/gemara_cases/create?hideicons=1');
        await page.waitForFunction(
            () => document.querySelectorAll('select[x-model="theCase.masechet"] option').length > 1,
            null, { timeout: 15000 },
        );
        await page.locator('select[x-model="theCase.masechet"]').selectOption('Berakhot');
        await page.locator('select[x-model="theCase.daf"]').selectOption('7a');
        const textarea = page.locator('textarea').first();
        await textarea.fill('text'); await textarea.blur();
        await page.locator('div.gc-din-type select').selectOption('מותר');
        const act = page.locator('#gc-act input[type="text"]');
        await act.fill('an act'); await act.blur();
        await expect(page.locator('#gc-who')).toBeVisible();

        await expect(page.locator('#gc-who img')).toBeHidden();
        await expect(page.locator('#gc-who')).toContainText('Who');
    });
});

test.describe('Diagram arrows', () => {
    test.beforeEach(async ({ page }) => {
        await useLanguage(page, 'English');
    });

    test('DIAG-08 no arrows before the act is entered', async ({ page }) => {
        await fillForm(page, 'din');
        await expect(page.locator('.arrow')).toHaveCount(0);
    });

    test('DIAG-09 entering the act draws all eight arrows', async ({ page }) => {
        await fillForm(page, 'act');
        await expect(page.locator('.arrow')).toHaveCount(8);
    });

    test('DIAG-10 each arrow connects its condition to the act', async ({ page }) => {
        await fillForm(page, 'act');
        await expectArrowsConnected(page);
    });

    // DIAG-11 - the scenario outline. A static SVG drawn once would pass
    // DIAG-09 and DIAG-10 and fail here at the first width change.
    for (const width of [900, 1400, 1600]) {
        test(`DIAG-11 arrows stay connected at viewport width ${width}`, async ({ page }) => {
            await fillForm(page, 'act');
            await page.setViewportSize({ width, height: 1000 });
            await expect(page.locator('.arrow')).toHaveCount(8);
            await expectArrowsConnected(page);
        });
    }

    test('DIAG-12 arrows stay connected after switching to Hebrew', async ({ page }) => {
        await fillForm(page, 'act');
        await page.evaluate(() => window.changeLanguage());
        await expect(page.locator('div.rtl').first()).toBeVisible();
        await expect(page.locator('.arrow')).toHaveCount(8);
        await expectArrowsConnected(page);
    });

    test('DIAG-13 arrows stay connected after switching back', async ({ page }) => {
        await fillForm(page, 'act');
        await page.evaluate(() => window.changeLanguage());
        await expect(page.locator('div.rtl').first()).toBeVisible();
        await page.evaluate(() => window.changeLanguage());
        await expect(page.locator('.arrow')).toHaveCount(8);
        await expectArrowsConnected(page);
    });

    test('DIAG-14 arrows stay connected after scrolling', async ({ page }) => {
        await fillForm(page, 'act');
        await page.setViewportSize({ width: 1200, height: 600 });
        await page.evaluate(() => window.scrollBy(0, 300));
        await expect(page.locator('.arrow')).toHaveCount(8);
        await expectArrowsConnected(page);
    });

    test('DIAG-15 arrows follow an oval that grows', async ({ page }) => {
        await fillForm(page, 'act');
        const input = page.locator('#gc-where input[type="text"]').first();
        await input.fill('a deliberately long value '.repeat(6));
        await input.dispatchEvent('change');
        await expect(page.locator('.arrow')).toHaveCount(8);
        await expectArrowsConnected(page);
    });

    test('DIAG-16 marking a condition Not Relevant keeps its arrow', async ({ page }) => {
        await fillForm(page, 'act');
        await page.locator('#gc-who input[type="checkbox"]').first().check();
        await expect(page.locator('.arrow')).toHaveCount(8);
        await expectArrowsConnected(page);
    });

    test('DIAG-17 clearing the act removes every arrow', async ({ page }) => {
        await fillForm(page, 'act');
        await page.locator('#gc-act span').filter({ hasText: 'an act' }).first().dblclick();
        await expect(page.locator('.arrow')).toHaveCount(0);
    });

    test('DIAG-18 re-entering the act redraws the arrows', async ({ page }) => {
        await fillForm(page, 'act');
        await page.locator('#gc-act span').filter({ hasText: 'an act' }).first().dblclick();
        await expect(page.locator('.arrow')).toHaveCount(0);

        const act = page.locator('#gc-act input[type="text"]');
        await act.fill('another act');
        await act.blur();
        await expect(page.locator('.arrow')).toHaveCount(8);
        await expectArrowsConnected(page);
    });

    test('DIAG-19 opening a saved case draws its arrows', async ({ page }) => {
        // The hydrate-then-draw path: initCaseData() maps the stored columns
        // back into Alpine state and schedules initArrows on a timer. Nothing
        // in any layer of the suite reached this line before.
        await page.goto('/gemara_cases?public=1');
        const link = page.locator(`[x-on\\:click^="editGemaraCase"]`).first();
        await expect(link).toBeAttached();

        await page.goto('/gemara_cases/create');
        const caseId = await page.evaluate(async (tag) => {
            const res = await fetch('/gemara_cases?public=1');
            const html = await res.text();
            const rows = [...html.matchAll(/editGemaraCase\((\d+)/g)].map((m) => m[1]);
            return rows.length ? rows[0] : null;
        }, FIXTURE.tag);

        await page.goto(`/gemara_cases/${caseId}`);
        await expect(page.locator('#gc-act')).toBeVisible();
        await expect(page.locator('.arrow')).toHaveCount(8);
        await expectArrowsConnected(page);
    });
});

test.describe('Diagram known defects', () => {
    // DIAG-20 - app.css styles ".gc-with-what", but the blade emits
    // "gc-withWhat" (input conditions are camelCase), so the rule matches
    // nothing; there is no ".gc-toWhat" rule at all. Both ovals silently lose
    // the vertical alignment their neighbours get.
    test.fixme('DIAG-20 every condition oval is aligned by its own rule', async ({ page }) => {
        await useLanguage(page, 'English');
        await fillForm(page, 'act');

        const alignItems = (selector) => page.evaluate(
            (s) => getComputedStyle(document.querySelector(s)).alignItems, selector,
        );

        expect(await alignItems('.gc-withWhat')).toBe(await alignItems('.gc-when'));
        expect(await alignItems('.gc-toWhat')).toBe(await alignItems('.gc-where'));
    });

    // DIAG-21 - arrowCreate() returns { node, clear }; initArrows() keeps only
    // node, so every arrow leaks its requestAnimationFrame observer.
    // resetArrows() removes the SVG but the loop keeps running.
    test.fixme('DIAG-21 arrow observers are released when arrows are removed', async ({ page }) => {
        await useLanguage(page, 'English');
        await fillForm(page, 'act');
        await page.locator('#gc-act span').filter({ hasText: 'an act' }).first().dblclick();
        await expect(page.locator('.arrow')).toHaveCount(0);

        const stillObserving = await page.evaluate(() => window.__arrowObservers ?? null);
        expect(stillObserving).toBe(0);
    });
});
