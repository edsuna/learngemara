import { expect } from '@playwright/test';

export const FIXTURE = {
    email: 'e2e-fixture@example.test',
    password: 'e2e-fixture-password',
    tag: 'E2E-FIXTURE',
};

/** Pin the display language so tests do not depend on a leftover cookie. */
export async function useLanguage(page, language) {
    await page.context().addCookies([{
        name: 'selectedLanguage',
        value: language,
        url: 'http://127.0.0.1:8000',
    }]);
}

export async function logIn(page) {
    await page.goto('/login');
    await page.fill('input[type="email"]', FIXTURE.email);
    await page.fill('input[type="password"]', FIXTURE.password);
    await page.locator('button[type="submit"]').first().click();
    await page.waitForURL('**/');
}

/**
 * Walk the create form far enough to reveal whatever the test needs.
 *
 * The form is a progressive reveal, so each stage is a precondition for the
 * next: 'daf' -> 'text' -> 'din' -> 'act' -> 'conditions'.
 */
export async function fillForm(page, upTo = 'act', { masechet = 'Berakhot', daf = '7a' } = {}) {
    await page.goto('/gemara_cases/create');
    await page.waitForFunction(
        () => document.querySelectorAll('select[x-model="theCase.masechet"] option').length > 1,
        null, { timeout: 15000 },
    );

    await page.locator('select[x-model="theCase.masechet"]').selectOption(masechet);
    await expect(page.locator('select[x-model="theCase.daf"]')).toBeVisible();
    if (upTo === 'masechet') return;

    await page.locator('select[x-model="theCase.daf"]').selectOption(daf);
    if (upTo === 'daf') return;

    const textarea = page.locator('textarea').first();
    await textarea.fill('gemara text for the test');
    await textarea.blur();
    await expect(page.locator('div.gc-din-type select')).toBeVisible();
    if (upTo === 'text') return;

    await page.locator('div.gc-din-type select').selectOption('מותר');
    await expect(page.locator('#gc-act input[type="text"]')).toBeVisible();
    if (upTo === 'din') return;

    const act = page.locator('#gc-act input[type="text"]');
    await act.fill('an act');
    await act.blur();
    await expect(page.locator('#gc-who')).toBeVisible();
    await expect(page.locator('.arrow')).toHaveCount(8);
    if (upTo === 'act') return;

    for (const condition of ['consequences', 'when', 'where', 'toWhat', 'withWhat', 'how', 'other', 'who']) {
        const input = page.locator(`#gc-${condition} input[type="text"]`).first();
        await input.fill(`value for ${condition}`);
        await input.dispatchEvent('change');
    }
}

/**
 * Assert every condition oval is still joined to the act oval by an arrow.
 *
 * Arrows are positioned in viewport coordinates by a requestAnimationFrame
 * observer, so "still connected" is a geometric question, not a DOM one. An
 * arrow counts as joining two elements when its own box reaches both.
 */
export async function expectArrowsConnected(page, tolerance = 16) {
    // The arrows reposition on the next animation frame, so connectedness is
    // eventually consistent by construction. Sampling once races the observer;
    // poll until it settles.
    await expect.poll(
        () => arrowsNotReaching(page, tolerance),
        { message: 'arrows never reconnected to their ovals', timeout: 5000 },
    ).toEqual([]);
}

async function arrowsNotReaching(page, tolerance) {
    return page.evaluate((tol) => {
        const conditions = ['consequences', 'when', 'where', 'toWhat', 'withWhat', 'how', 'other', 'who'];
        const box = (el) => {
            const r = el.getBoundingClientRect();
            return { x: r.x, y: r.y, w: r.width, h: r.height };
        };
        const act = document.getElementById('gc-act');
        if (!act) return ['NO ACT OVAL'];

        const actBox = box(act);
        const arrows = [...document.querySelectorAll('.arrow')].map(box);
        const reaches = (a, b) =>
            a.x < b.x + b.w + tol && a.x + a.w > b.x - tol &&
            a.y < b.y + b.h + tol && a.y + a.h > b.y - tol;

        return conditions.filter((condition) => {
            const el = document.getElementById('gc-' + condition);
            if (!el) return true;
            const conditionBox = box(el);
            return !arrows.some((arrow) => reaches(arrow, conditionBox) && reaches(arrow, actBox));
        });
    }, tolerance);
}
