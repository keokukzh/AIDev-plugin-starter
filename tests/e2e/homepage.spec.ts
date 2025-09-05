import { test, expect } from '@playwright/test';

const expectedTitle =
  process.env.E2E_EXPECTED_TITLE_REGEX
    ? new RegExp(process.env.E2E_EXPECTED_TITLE_REGEX)
    : /WordPress|AIDev|AIDev-plugin-starter/i;

test('homepage loads', async ({ page }) => {
  await page.goto('/'); // nutzt baseURL aus config
  await expect(page).toHaveTitle(expectedTitle);
  await expect(page.locator('body')).toBeVisible();
});
