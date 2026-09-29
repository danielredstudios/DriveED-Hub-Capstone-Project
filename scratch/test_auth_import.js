const { login, USERS, BASE, SCHOOL_SLUG } = require('../tests/fixtures/auth');
const { chromium } = require('@playwright/test');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const ctx = await browser.newContext();
  const page = await ctx.newPage();

  console.log('Logging in as studentPractical using auth fixture...');
  await login(page, 'studentPractical');
  console.log('URL after login:', page.url());

  console.log('Logging in as instructorVerified...');
  const ctx2 = await browser.newContext();
  const page2 = await ctx2.newPage();
  await login(page2, 'instructorVerified');
  console.log('URL after instructor login:', page2.url());
  const res = await page2.goto(`${BASE}/${SCHOOL_SLUG}/instructor/students/999999`, { waitUntil: 'domcontentloaded' });
  console.log('IDOR URL:', page2.url(), 'status:', res.status());
  await ctx2.close();
  await browser.close();
})();
