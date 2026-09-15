// @ts-check
const { defineConfig, devices } = require("@playwright/test");

/**
 * Browser tests for the CDN's JavaScript components.
 *
 * These load the real third-party libraries through a fixture page rather than mocking
 * them, because the behaviour worth protecting here is timing between our code and
 * EditorJS — which a mock would define away.
 */
module.exports = defineConfig({
  testDir: __dirname,
  testMatch: /.*\.spec\.js/,
  fullyParallel: true,
  reporter: [["list"]],
  timeout: 30_000,
  expect: { timeout: 10_000 },
  use: { ...devices["Desktop Chrome"] },
  projects: [{ name: "chromium", use: { ...devices["Desktop Chrome"] } }],
});
