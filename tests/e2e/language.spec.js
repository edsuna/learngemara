import { test, expect } from '@playwright/test';
import { useLanguage, fillForm } from './support/helpers.js';

/**
 * docs/spec/language.md
 *
 * Cookie handling and the text lookup itself are unit-tested; what needs a
 * browser is the propagation -- one window CustomEvent, every Alpine root
 * listening independently -- and the fact that the diagram is deliberately
 * left LTR inside an RTL page so its grid order stays fixed.
 */

test.describe('Language and direction', () => {
    test('LANG-04 the choice survives a reload', async ({ page }) => {
        await useLanguage(page, 'English');
        await page.goto('/');
        await page.evaluate(() => window.changeLanguage());
        await expect(page.locator('div.rtl').first()).toBeVisible();

        await page.reload();

        await expect(page.locator('div.rtl').first()).toBeVisible();
        const cookie = (await page.context().cookies()).find((c) => c.name === 'selectedLanguage');
        expect(cookie.value).toBe('Hebrew');
    });

    test('LANG-06 the diagram stays left-to-right in Hebrew', async ({ page }) => {
        await useLanguage(page, 'English');
        await fillForm(page, 'act');

        const columnOrder = async () => page.evaluate(() => {
            const conditions = ['consequences', 'when', 'where', 'toWhat', 'withWhat', 'how'];
            return conditions
                .map((c) => ({ c, x: document.getElementById('gc-' + c).getBoundingClientRect().x }))
                .sort((a, b) => a.x - b.x)
                .map((o) => o.c);
        });

        const before = await columnOrder();

        await page.evaluate(() => window.changeLanguage());
        await expect(page.locator('div.rtl').first()).toBeVisible();

        // The page is RTL, but .gc-diagram carries a hard .ltr so the ovals do
        // not mirror -- if they did, the arrow anchor map would no longer
        // describe what is on screen.
        await expect(page.locator('.gc-diagram')).toHaveClass(/ltr/);
        expect(await columnOrder()).toEqual(before);
    });

    test('LANG-07 toggling does not lose entered data', async ({ page }) => {
        await useLanguage(page, 'English');
        await fillForm(page, 'act');

        await page.evaluate(() => window.changeLanguage());
        await expect(page.locator('div.rtl').first()).toBeVisible();

        const theCase = await page.evaluate(
            () => Alpine.$data(document.querySelector('[x-data="gemaraCase()"]')).theCase,
        );
        expect(theCase.masechet).toBe('Berakhot');
        expect(theCase.daf).toBe('7a');
        expect(theCase.dinType).toBe('מותר');
        expect(theCase.act).toBe('an act');
        await expect(page.locator('.arrow')).toHaveCount(8);
    });
});

/**
 * REF-10 - fillInDapim() builds the dapim array at masechet selection, so the
 * selection, using whatever language is active then. Nothing rebuilds it on the
 * togglelanguage event, so the daf dropdown keeps the language it was created
 * in while every other label on the page switches.
 */
test('REF-10 switching language relabels the daf list', async ({ page }) => {
    await useLanguage(page, 'English');
    await page.goto('/gemara_cases/create');
    await page.waitForFunction(
        () => document.querySelectorAll('select[x-model="theCase.masechet"] option').length > 1,
        null, { timeout: 15000 },
    );
    await page.locator('select[x-model="theCase.masechet"]').selectOption('Shabbat');

    const daf = page.locator('select[x-model="theCase.daf"]');
    await expect(daf.locator('option').nth(1)).toHaveText('2a');

    await page.evaluate(() => window.changeLanguage());
    await expect(page.locator('div.rtl').first()).toBeVisible();

    await expect(daf.locator('option').nth(1)).toHaveText('ב׳.');
});
