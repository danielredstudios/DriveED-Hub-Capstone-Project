const { chromium } = require('@playwright/test');
const { USERS, loginUrl } = require('../tests/fixtures/auth');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  await page.goto(loginUrl());
  await page.getByLabel(/email/i).first().fill('instructor1@driveedhub.test');
  await page.getByLabel(/password/i).first().fill('DriveDemo123');
  await page.getByRole('button', { name: /log in|login/i }).first().click();
  await page.waitForTimeout(2000);
  console.log('URL:', page.url());
  const res = await page.goto('http://localhost:8004/drived-hub/instructor/students/999999');
  console.log('IDOR URL:', page.url(), 'status:', res.status());
  console.log('IDOR content snippet:', (await page.textContent('body')).replace(/\s+/g, ' ').slice(0, 300));
  await browser.close();
})();
