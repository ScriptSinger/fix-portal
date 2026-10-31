import assert from 'node:assert/strict';
import { chromium } from 'playwright-core';

const required = [
    'E2E_BASE_URL', 'E2E_EMAIL', 'E2E_PASSWORD', 'E2E_DISCUSSION_URL',
    'E2E_MESSAGE_TEXT', 'E2E_UPDATED_TEXT', 'E2E_COMMENT_ID',
    'E2E_OLD_COMMENT_ID', 'E2E_FOREIGN_COMMENT_ID',
];
const missing = required.filter((name) => !process.env[name]);
if (missing.length) {
    throw new Error(`Missing E2E environment variables: ${missing.join(', ')}`);
}

const baseUrl = process.env.E2E_BASE_URL.replace(/\/$/, '');
const discussionUrl = new URL(process.env.E2E_DISCUSSION_URL, `${baseUrl}/`).toString();
const executablePath = process.env.E2E_BROWSER_PATH || '/usr/bin/google-chrome';
const viewports = [
    { name: 'desktop', width: 1440, height: 900 },
    { name: 'mobile', width: 390, height: 844 },
];

const browser = await chromium.launch({ executablePath, args: ['--no-sandbox'] });
let failures = 0;
let uploadedImageId = null;

try {
    for (const viewport of viewports) {
        const context = await browser.newContext({ viewport: { width: viewport.width, height: viewport.height } });
        const page = await context.newPage();
        page.on('dialog', (dialog) => dialog.accept());
        page.on('pageerror', (error) => console.error(`${viewport.name} pageerror: ${error.message}`));
        page.on('console', (message) => {
            if (message.type() === 'error') console.error(`${viewport.name} console: ${message.text()}`);
        });

        try {
            await page.goto(`${baseUrl}/login`, { waitUntil: 'networkidle' });
            await page.locator('input[name="email"]').fill(process.env.E2E_EMAIL);
            await page.locator('input[name="password"]').fill(process.env.E2E_PASSWORD);
            await page.getByRole('button', { name: 'Войти' }).click();
            await page.waitForLoadState('networkidle');

            await page.goto(discussionUrl, { waitUntil: 'networkidle' });
            if (viewport.name === 'desktop') {
                const csrf = await page.locator('meta[name="csrf-token"]').getAttribute('content');
                const put = (id, body) => page.evaluate(async ({ id, body, csrf }) => {
                    const response = await fetch(`/comments/${id}`, {
                        method: 'PUT',
                        headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify(body),
                    });
                    return response.status;
                }, { id, body, csrf });

                assert.equal(await put(process.env.E2E_COMMENT_ID, { text: '' }), 422, 'empty edit must be rejected');
                assert.equal(await put(process.env.E2E_OLD_COMMENT_ID, { text: 'forbidden' }), 403, 'old edit must be forbidden');
                assert.equal(await put(process.env.E2E_FOREIGN_COMMENT_ID, { text: 'forbidden' }), 403, 'foreign edit must be forbidden');
            }
            const row = page.locator('[data-discussion-row]').filter({ hasText: process.env.E2E_MESSAGE_TEXT }).first();
            const updatedRow = page.locator('[data-discussion-row]').filter({ hasText: process.env.E2E_UPDATED_TEXT }).first();
            const target = await row.count() ? row : updatedRow;
            assert.equal(await target.count(), 1, `${viewport.name}: target discussion message was not found`);

            await target.getByRole('button', { name: 'Действия с сообщением' }).click();
            await target.getByRole('button', { name: 'Редактировать' }).click();

            const editor = page.locator('[x-ref="editor"] [contenteditable="true"]').first();
            await editor.waitFor({ state: 'visible' });
            assert.equal(await editor.isVisible(), true, `${viewport.name}: editor is not visible`);

            if (await row.count()) {
                const imageInput = page.locator('[x-ref="file"]').first();
                const imageResponse = page.waitForResponse((response) =>
                    response.request().method() === 'POST' && response.url().includes('/api/users-images'),
                );
                await imageInput.setInputFiles({
                    name: 'e2e.png',
                    mimeType: 'image/png',
                    buffer: Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', 'base64'),
                });
                uploadedImageId = (await (await imageResponse).json()).id;
                await editor.fill(process.env.E2E_UPDATED_TEXT);
                const responsePromise = page.waitForResponse((response) =>
                    response.request().method() === 'PUT' && response.status() === 200,
                );
                await page.getByRole('button', { name: 'Сохранить редактирование' }).click();
                const response = await responsePromise;
                assert.match(response.headers()['content-type'] || '', /json/);
                await assertEventually(
                    () => page.locator('[data-discussion-row]').filter({ hasText: process.env.E2E_UPDATED_TEXT }).count(),
                    1,
                );
                await assertEventually(
                    async () => (await page.locator('[data-discussion-row]').filter({ hasText: process.env.E2E_UPDATED_TEXT }).first().locator('img').count()) > 0 ? 1 : 0,
                    1,
                );
            } else {
                await page.getByRole('button', { name: 'Закрыть редактирование' }).click();
                const deleteResponse = page.waitForResponse((response) =>
                    response.request().method() === 'DELETE' && response.status() === 200,
                );
                await target.getByRole('button', { name: 'Действия с сообщением' }).click();
                await target.getByRole('button', { name: 'Удалить' }).click();
                await deleteResponse;
                await assertEventually(() => page.locator('[data-discussion-row]').filter({ hasText: process.env.E2E_UPDATED_TEXT }).count(), 0);
                if (uploadedImageId) {
                    const csrf = await page.locator('meta[name="csrf-token"]').getAttribute('content');
                    await page.evaluate(async ({ id, csrf }) => fetch(`/api/users-images/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                        credentials: 'same-origin',
                    }), { id: uploadedImageId, csrf });
                }
            }

            console.log(`✓ ${viewport.name} discussion edit`);
        } catch (error) {
            failures += 1;
            console.error(`✗ ${viewport.name} discussion edit`, error.message);
        } finally {
            await context.close();
        }
    }
} finally {
    await browser.close();
}

if (failures) process.exitCode = 1;

async function assertEventually(read, expected, attempts = 20) {
    for (let i = 0; i < attempts; i += 1) {
        if (await read() === expected) return;
        await new Promise((resolve) => setTimeout(resolve, 100));
    }
    assert.equal(await read(), expected);
}
