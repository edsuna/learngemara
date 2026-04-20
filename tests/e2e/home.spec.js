import { test, expect } from '@playwright/test';

test.describe('Home page', () => {
    test('loads and displays the page title', async ({ page }) => {
        await page.goto('/');
        await expect(page.locator('h1')).toBeVisible();
        await expect(page.locator('h1')).toHaveText('How to Learn Gemara');
    });

    test('has a link to create a new case', async ({ page }) => {
        await page.goto('/');
        const createLink = page.locator('a', { hasText: 'Analytic skillset tool' });
        await expect(createLink).toBeVisible();
    });

    test('has a link to public cases', async ({ page }) => {
        await page.goto('/');
        const publicLink = page.locator('a', { hasText: 'Public Gemara Cases' });
        await expect(publicLink).toBeVisible();
    });

    test('has login and register links when not authenticated', async ({ page }) => {
        await page.goto('/');
        await expect(page.locator('a.text-indigo-600', { hasText: 'Log in' })).toBeVisible();
        await expect(page.locator('a.text-indigo-600', { hasText: 'Register' })).toBeVisible();
    });

    test('language toggle switches UI language', async ({ page, context }) => {
        await context.addCookies([{ name: 'selectedLanguage', value: 'English', url: 'http://127.0.0.1:8000' }]);
        await page.goto('/');
        const toggle = page.locator('.cursor-pointer.rounded-lg');
        await expect(toggle).toHaveText('To Hebrew');

        await page.evaluate(() => window.changeLanguage());
        await expect(toggle).toHaveText('לאנגלית');
    });

    test('logo is visible and links to home', async ({ page }) => {
        await page.goto('/');
        const logo = page.locator('img[src*="logo"]');
        await expect(logo).toBeVisible();
    });
});
