const { chromium } = require('@playwright/test');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  await page.goto('http://localhost:8004/drived-hub/login');
  await page.getByLabel(/email/i).first().fill('student1@driveedhub.test');
  await page.getByLabel(/password/i).first().fill('DriveDemo123');
  await page.getByRole('button', { name: /log in|sign in|login/i }).first().click();
  await page.waitForURL(url => !url.href.endsWith('/login'), { timeout: 10000 }).catch(() => {});
  console.log('URL after login:', page.url());
  await page.goto('http://localhost:8004/drived-hub/student/my-course', { waitUntil: 'domcontentloaded' });
  console.log('URL after my-course:', page.url());
  console.log('Course cards:', await page.locator('.course-card').count());
  console.log('Errors:', await page.locator('.error, .alert, .invalid-feedback, [role="alert"]').allTextContents());
  console.log('Body:', (await page.textContent('body')).replace(/\s+/g, ' ').slice(0, 500));
  await browser.close();
})();
