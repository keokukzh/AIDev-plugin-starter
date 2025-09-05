import { defineConfig } from "@playwright/test";

export default defineConfig({
  testDir: "tests/e2e",
  reporter: [
    ["list"],
    ["html",  { outputFolder: "playwright-report", open: "never" }],
    ["junit", { outputFile: "test-results/junit.xml" }],
  ],
  use: { baseURL: process.env.E2E_BASE_URL || "http://localhost:8888" },
  workers: 1,
  projects: [{ name: "chromium", use: { browserName: "chromium" } }],
});
