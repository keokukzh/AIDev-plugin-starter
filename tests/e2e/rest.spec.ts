import { test, expect } from "@playwright/test";

const baseUrl = process.env.E2E_BASE_URL ?? "http://localhost:8888";

async function restGet(request, path) {
  let res = await request.get(`${baseUrl}/wp-json${path}`);
  if (!res.ok()) {
    res = await request.get(`${baseUrl}/index.php?rest_route=${path}`);
  }
  return res;
}

test("REST: /aidev/v1/message returns JSON", async ({ request }) => {
  const res = await restGet(request, "/aidev/v1/message");
  expect(res.ok()).toBeTruthy();
  const json = await res.json();
  expect(json).toHaveProperty("message");
});
