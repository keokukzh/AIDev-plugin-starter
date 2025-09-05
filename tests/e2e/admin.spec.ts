import { test, expect } from "@playwright/test";

const USER = process.env.WP_USER ?? "admin";
const PASS = process.env.WP_PASS ?? "password";

test("admin can open plugin settings page", async ({ page }) => {
  await page.goto("/wp-login.php");
  await page.fill("#user_login", USER);
  await page.fill("#user_pass", PASS);
  await Promise.all([
    page.waitForNavigation(),
    page.click("#wp-submit"),
  ]);
  await expect(page).toHaveURL(/\/wp-admin\//);

  await page.goto("/wp-admin/options-general.php?page=aidev-plugin-starter");
  await expect(page.locator("h1")).toHaveText(/AIDev Starter Settings/i);
  await expect(page.locator("#aidev_ps_message")).toBeVisible();
  await expect(page.locator("#aidev_ps_remote_url")).toBeVisible();
});