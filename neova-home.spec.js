const { test, expect } = require('@playwright/test');

test('homepage renders and interactions work', async ({ page }) => {
    const consoleMessages = [];

    page.on('console', message => {
        if (['error', 'warning'].includes(message.type())) {
            consoleMessages.push(`${message.type()}: ${message.text()}`);
        }
    });

    await page.goto('http://127.0.0.1:8090/');

    await expect(page).toHaveTitle('نئووا | مدیریت روشن کار تیمی');
    await expect(page.getByRole('heading', { level: 1 })).toContainText('کار تیمی');
    await expect(page.locator('.neova-board')).toBeVisible();
    await expect(page.locator('.neova-mini-board .is-done article')).not.toHaveClass(/is-arrived/);

    await page.locator('.neova-mini-board').hover();
    await expect(page.locator('.neova-mini-board .is-done article')).toHaveClass(/is-arrived/);

    await page.getByRole('link', { name: 'شروع رایگان', exact: true }).first().click();
    await expect(page).toHaveURL(/\/auth$/);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('ورود');

    expect(consoleMessages).toEqual([]);
});

test('mobile layout has no page-level horizontal overflow', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('http://127.0.0.1:8090/');

    const widths = await page.evaluate(() => ({
        viewport: document.documentElement.clientWidth,
        page: document.documentElement.scrollWidth,
        boardViewport: document.querySelector('.neova-board-wrap').clientWidth,
        boardContent: document.querySelector('.neova-board-wrap').scrollWidth,
    }));

    expect(widths.page).toBe(widths.viewport);
    expect(widths.boardContent).toBeGreaterThan(widths.boardViewport);
});
