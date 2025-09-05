import { test, expect } from "@playwright/test";

// Erlaube override über ENV, sonst akzeptiere beides:
const expectedTitle =
  process.env.E2E_SITE_TITLE
    ? new RegExp(process.env.E2E_SITE_TITLE, "i")
    : /AIDev-plugin-starter|WordPress/i;

test("homepage loads", async ({ page }) => {
  await page.goto("/");
  await expect(page).toHaveTitle(expectedTitle);
  // kleine Zusatzsicherheit: Body sichtbar
  await expect(page.locator("body")).toBeVisible();
});