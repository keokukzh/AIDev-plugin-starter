import { test, expect, request } from '@playwright/test';

test('Agent endpoint returns JSON', async ({ request }) => {
  const res = await request.post('/?rest_route=/aidev/v1/agent', {
    data: { message: 'ping' },
  });
  expect(res.ok()).toBeTruthy();
  const json = await res.json();
  expect(json).toHaveProperty('reply');
});
