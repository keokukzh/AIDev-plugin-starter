import { test, expect } from "@playwright/test";

test("REST: /wp-json/aidev/v1/message returns JSON", async ({ request }) => {
  const res = await request.get("/wp-json/aidev/v1/message");
  expect(res.ok()).toBeTruthy();
  const json = await res.json();
  expect(json).toHaveProperty("message");
});

test("REST fallback (?rest_route=...) returns JSON", async ({ request }) => {
  const res = await request.get("/?rest_route=/aidev/v1/message");
  expect(res.ok()).toBeTruthy();
});
