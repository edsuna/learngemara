import { test, expect } from '@playwright/test';
import { useLanguage, fillForm } from './support/helpers.js';

/**
 * docs/spec/case-authoring.md
 *
 * The form is a progressive reveal, and each answered field collapses into
 * text: the select carries x-show="allowUpdates && !theCase.masechet", so it
 * disappears the moment a value exists. Changing an answer means
 * double-clicking its text, not using the dropdown again.
 */

const AMUD_TEXT = 'מֵאֵימָתַי קוֹרִין אֶת שְׁמַע בָּעֲרָבִין';

/** Stub Sefaria so the suite does not depend on a third party being up. */
async function stubSefaria(page, { fail = false } = {}) {
    // A regex, not a glob: '**/sefaria.org' would require a slash before the
    // host, but the URL is https://www.sefaria.org/...
    await page.route(/sefaria\.org\/api\/texts\//, (route) => {
        if (fail) return route.abort('failed');
        return route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({ he: [AMUD_TEXT] }),
        });
    });
}

/** Dismiss whichever modal is open by clicking outside its inner panel. */
async function closeModal(page) {
    const overlay = page.locator('div.fixed.top-0.left-0').filter({ visible: true }).first();
    await overlay.click({ position: { x: 5, y: 5 } });
    await expect(overlay).toBeHidden();
}

test.describe('Progressive reveal', () => {
    test.beforeEach(async ({ page }) => {
        await useLanguage(page, 'English');
    });

    test('CASE-01 only the masechet picker is offered initially', async ({ page }) => {
        await page.goto('/gemara_cases/create');
        await expect(page.locator('select[x-model="theCase.masechet"]')).toBeVisible();
        await expect(page.locator('select[x-model="theCase.daf"]')).toBeHidden();
    });

    test('CASE-02 choosing a masechet reveals the daf picker and hides its own', async ({ page }) => {
        await fillForm(page, 'masechet');
        await expect(page.locator('select[x-model="theCase.daf"]')).toBeVisible();
        await expect(page.locator('select[x-model="theCase.masechet"]')).toBeHidden();
        await expect(page.locator('body')).toContainText('Berakhot');
    });

    test('CASE-03 choosing a daf hides the daf picker', async ({ page }) => {
        await fillForm(page, 'daf');
        await expect(page.locator('select[x-model="theCase.daf"]')).toBeHidden();
        await expect(page.locator('body')).toContainText('7a');
    });

    test('CASE-04 double-clicking the masechet resets the whole downstream chain', async ({ page }) => {
        await fillForm(page, 'text');
        await expect(page.locator('.gc-diagram')).toBeVisible();

        await page.locator('span').filter({ hasText: /^Berakhot$/ }).first().dblclick();

        await expect(page.locator('select[x-model="theCase.masechet"]')).toBeVisible();
        await expect(page.locator('select[x-model="theCase.daf"]')).toBeHidden();
        // resetCase -> resetDaf -> resetDafText: the gemara text goes too, so
        // the diagram disappears with it.
        await expect(page.locator('.gc-diagram')).toBeHidden();
    });

    test('CASE-05 the diagram stays hidden until gemara text is committed', async ({ page }) => {
        await fillForm(page, 'daf');
        await expect(page.locator('.gc-diagram')).toBeHidden();

        const textarea = page.locator('textarea').first();
        await textarea.fill('some gemara text');
        await textarea.blur();

        await expect(page.locator('.gc-diagram')).toBeVisible();
    });

    test('CASE-06 the din type offers exactly the six adjudications', async ({ page }) => {
        await fillForm(page, 'text');
        // a placeholder plus מותר אסור חייב פטור כשר פסול
        await expect(page.locator('div.gc-din-type select option')).toHaveCount(7);
    });

    test('CASE-07 the act input appears once a din type is chosen', async ({ page }) => {
        await fillForm(page, 'text');
        await expect(page.locator('#gc-act input[type="text"]')).toBeHidden();
        await page.locator('div.gc-din-type select').selectOption('מותר');
        await expect(page.locator('#gc-act input[type="text"]')).toBeVisible();
    });

    test('CASE-08 entering the act reveals the condition ovals', async ({ page }) => {
        await fillForm(page, 'din');
        await expect(page.locator('#gc-who')).toBeHidden();

        const act = page.locator('#gc-act input[type="text"]');
        await act.fill('an act');
        await act.blur();

        await expect(page.locator('#gc-who')).toBeVisible();
    });
});

test.describe('Amud text lookup', () => {
    test.beforeEach(async ({ page }) => {
        await useLanguage(page, 'English');
    });

    test('CASE-09 Show Amud Text fetches the daf from Sefaria', async ({ page }) => {
        await stubSefaria(page);
        await fillForm(page, 'daf', { masechet: 'Berakhot', daf: '2a' });

        const [request] = await Promise.all([
            page.waitForRequest(/sefaria\.org\/api\/texts\//),
            page.locator('button:has(img)').first().click(),
        ]);

        expect(request.url()).toContain('Berakhot.2a');
        await expect(page.locator('body')).toContainText(AMUD_TEXT);
    });

    test('CASE-10 the amud text does not populate the gemara text', async ({ page }) => {
        await stubSefaria(page);
        await fillForm(page, 'daf', { masechet: 'Berakhot', daf: '2a' });

        await page.locator('button:has(img)').first().click();
        await expect(page.locator('body')).toContainText(AMUD_TEXT);

        // The modal is reference material; the user transcribes the statement.
        await expect(page.locator('textarea').first()).toHaveValue('');
    });

    test('CASE-11 changing the daf discards a loaded amud text', async ({ page }) => {
        await stubSefaria(page);
        await fillForm(page, 'daf', { masechet: 'Berakhot', daf: '2a' });
        await page.locator('button:has(img)').first().click();
        await expect(page.locator('body')).toContainText(AMUD_TEXT);

        // Dismissing the modal is itself a click-away that clears amudText.
        // Click the overlay through a locator rather than at raw coordinates:
        // Playwright then waits for it to be actionable, and the assertion
        // that follows fails loudly if it did not close, instead of leaving
        // the next step to time out against an overlay that swallows clicks.
        await closeModal(page);

        await page.locator('span').filter({ hasText: /^2a$/ }).first().dblclick();
        const daf = page.locator('select[x-model="theCase.daf"]');
        await expect(daf).toBeVisible();
        await daf.selectOption('2b');

        // The guarantee that matters: nothing is held over from the previous
        // daf, so the next lookup fetches the daf now selected.
        const [request] = await Promise.all([
            page.waitForRequest(/sefaria\.org\/api\/texts\//),
            page.locator('button:has(img)').first().click(),
        ]);
        expect(request.url()).toContain('Berakhot.2b');

        // waitForRequest resolves when the request is sent, not when its
        // response has been applied -- assert the visible outcome, which
        // retries until the fetch settles.
        await expect(page.locator('body')).toContainText(AMUD_TEXT);
    });

    // CASE-12 - known defect. getAmudText() has no .catch(), so a failed
    // request leaves amudText unassigned and the modal is gated on it being
    // truthy: the button appears to do nothing at all.
    test.fixme('CASE-12 a failed Sefaria lookup tells the user', async ({ page }) => {
        await stubSefaria(page, { fail: true });
        await fillForm(page, 'daf', { masechet: 'Berakhot', daf: '2a' });
        await page.locator('button:has(img)').first().click();
        await expect(page.locator('body')).toContainText(/could not|unavailable|failed/i);
    });
});
