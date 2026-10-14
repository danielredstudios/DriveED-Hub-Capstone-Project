// @ts-check
const { defineConfig, devices } = require('@playwright/test');

/**
 * Agentic UI Testing config for DriveED Hub
 * Codelab: https://codelabs.developers.google.com/agentic-ui-testing
 * - Tests focus on INTENT not selectors (codelab #4)
 * - Playwright CLI + BrowserMCP + Chrome DevTools MCP supported
 * - Runs against docker app http://localhost:8004
 */
module.exports = defineConfig({
  testDir: './tests/e2e',
  timeout: 30 * 1000,
  expect: { timeout: 5000 },
  fullyParallel: false, // sequential for auth state & batch scheduling
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: 1,
  reporter: [
    ['html', { open: 'never' }],
    ['list'],
    ['json', { outputFile: 'test-results/results.json' }]
  ],
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost:8004',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    actionTimeout: 10000,
    navigationTimeout: 15000,
  },
  projects: [
    {
      name: 'chromium',
      use: {
        ...devices['Desktop Chrome'],
        headless: true,
        viewport: { width: 1280, height: 720 },
      },
    },
    // Uncomment for headed debugging via Playwright CLI skill:
    // {
    //   name: 'chromium-headed',
    //   use: { ...devices['Desktop Chrome'], headless: false },
    // },
  ],
  outputDir: 'test-results/artifacts/',
  // No webServer – we connect to existing docker app at :8004
});
