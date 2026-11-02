import { test, expect } from '@playwright/test';
import { FIXTURE } from './support/helpers.js';

/**
 * docs/spec/course-landing.md
 *
 * The course address is selected by the request's host, so these tests use full
 * URLs rather than the configured baseURL. Browsers resolve *.localhost to the
 * local machine. Copy is not quoted: lang/en/course.php can change without
 * breaking anything here. PHPUnit covers the content; this file covers what only
 * a browser can show, namely layout and real navigation.
 */
const COURSE = 'http://course.localhost:8000';
const enrollLink = (page) => page.locator('main a[href$="/enroll"]').first();

test.describe('Course landing page (spec)', () => {
    test('COURSE-01 the course address serves the landing page, and the tool keeps its own', async ({ page }) => {
        await page.goto(COURSE + '/');
        await expect(page.locator('#about-the-method')).toBeAttached();

        await page.goto('/');
        await expect(page.locator('h1')).toHaveText('How to Learn Gemara');
        await expect(page.locator('#about-the-method')).toHaveCount(0);
    });

    test('COURSE-02 the page says what the course does for me', async ({ page }) => {
        await page.goto(COURSE + '/');
        await expect(page.locator('img[src*="logo"]')).toBeVisible();
        await expect(page.locator('h1')).toHaveCount(1);
        await expect(page.locator('h1')).toBeVisible();
    });

    test('COURSE-03 Enroll leads to the enroll step', async ({ page }) => {
        await page.goto(COURSE + '/');
        await expect(enrollLink(page)).toBeVisible();
        await expect(enrollLink(page)).toHaveAttribute('href', COURSE + '/enroll');
    });

    test('COURSE-04 the tool link leaves the course address', async ({ page }) => {
        await page.goto(COURSE + '/');
        const href = await page.locator('main a[href$="/gemara_cases/create"]').first().getAttribute('href');
        expect(new URL(href).hostname).not.toBe('course.localhost');
    });

    test('COURSE-05 an analyzed case is reachable without an account', async ({ page }) => {
        await page.goto(COURSE + '/');
        const href = await page.locator('main a[href*="/gemara_cases"]:not([href$="/create"])').first().getAttribute('href');
        expect(new URL(href).hostname).not.toBe('course.localhost');

        const response = await page.goto(href);
        expect(response.status()).toBe(200);
        await expect(page).not.toHaveURL(/\/login/);
    });

    test('COURSE-07 and COURSE-08 the footer link reaches the method section', async ({ page }) => {
        await page.goto(COURSE + '/');
        await page.locator('footer a[href="#about-the-method"]').click();
        await expect(page.locator('#about-the-method')).toBeInViewport();
    });

    test('COURSE-09 and COURSE-12 signing in from the course returns to the course, then signing out', async ({ page }) => {
        await page.goto(COURSE + '/');
        await page.locator('nav a[href$="/sign-in"]').click();
        await expect(page).toHaveURL(COURSE + '/login');

        await page.fill('input[type="email"]', FIXTURE.email);
        await page.fill('input[type="password"]', FIXTURE.password);
        await page.locator('button[type="submit"]').first().click();

        await expect(page).toHaveURL(COURSE + '/');
        await expect(page.locator('nav')).toContainText('E2E Fixture User');

        await page.locator('nav form[action$="/logout"] button').click();
        await expect(page).toHaveURL(COURSE + '/');
        await expect(page.locator('nav a[href$="/sign-in"]')).toBeVisible();
    });

    test('ENROLL-01 enrolling as a guest asks for an account', async ({ page }) => {
        // The registration itself is in PHPUnit, whose database rolls back;
        // registering here would leave an account in the development database.
        await page.goto(COURSE + '/');
        await enrollLink(page).click();
        await expect(page).toHaveURL(COURSE + '/register');
        await expect(page.locator('#password_confirmation')).toBeVisible();
    });

    test('ENROLL-02 enrolling while signed in does not ask to sign in', async ({ page }) => {
        await page.goto(COURSE + '/sign-in');
        await page.fill('input[type="email"]', FIXTURE.email);
        await page.fill('input[type="password"]', FIXTURE.password);
        await page.locator('button[type="submit"]').first().click();
        await expect(page).toHaveURL(COURSE + '/');

        await enrollLink(page).click();
        await expect(page).toHaveURL(COURSE + '/');
        await expect(page.locator('[role="status"]')).toBeVisible();
    });
});

test.describe('Course landing page on a phone (spec)', () => {
    test.use({ viewport: { width: 390, height: 844 } });

    test('COURSE-10 the page fits a phone', async ({ page }) => {
        await page.goto(COURSE + '/');
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
        expect(overflow).toBeLessThanOrEqual(0);
        await expect(enrollLink(page)).toBeInViewport();
    });
});
