import { test, expect } from '@playwright/test';

const email = process.env.E2E_EMAIL;
const password = process.env.E2E_PASSWORD;

test.beforeEach(async ({ page }) => {
    test.skip(!email || !password, 'Set E2E_EMAIL and E2E_PASSWORD for authenticated browser tests.');

    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.getByRole('button', { name: 'Войти' }).click();
    await expect(page).not.toHaveURL(/\/login/);
});

test('latest discussion links open the target message anchor', async ({ page }) => {
    await page.goto('/');

    const link = page.locator('section[aria-label="Последние комментарии и ответы"] a').first();
    await expect(link).toBeVisible();

    const href = await link.getAttribute('href');
    expect(href).toMatch(/#(?:c|r)-\d+$/);
    await link.click();
    await expect(page).toHaveURL(new RegExp(`${href.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}$`));

    const target = page.locator(href.slice(href.indexOf('#')));
    await expect(target).toBeVisible();
    await expect(target).toHaveAttribute('tabindex', '-1');
    await expect.poll(async () => (await target.boundingBox())?.y ?? Number.POSITIVE_INFINITY, { timeout: 5_000 }).toBeLessThan(await page.evaluate(() => window.innerHeight));
});

test('latest discussion links are keyboard accessible', async ({ page }) => {
    await page.goto('/');

    const link = page.locator('section[aria-label="Последние комментарии и ответы"] a').first();
    await expect(link).toBeVisible();
    await link.focus();
    await expect(link).toBeFocused();
    await expect(link).toHaveAttribute('aria-label', /Комментарий|Ответ/);
});
