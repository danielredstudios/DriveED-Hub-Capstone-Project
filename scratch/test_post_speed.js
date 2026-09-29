const { chromium } = require('@playwright/test');
const { login, BASE, SCHOOL_SLUG } = require('../tests/fixtures/auth');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const ctx = await browser.newContext();
  const page = await ctx.newPage();
  await login(page, 'admin');
  await page.goto(`${BASE}/${SCHOOL_SLUG}/admin/schedules`, { waitUntil: 'domcontentloaded' });
  const csrf = await page.getAttribute('meta[name="csrf-token"]', 'content');

  const t0 = Date.now();
  const res = await ctx.request.post(`${BASE}/${SCHOOL_SLUG}/admin/schedules/create`, {
    form: {
      _token: csrf,
      date: new Date(Date.now() + 86400000).toISOString().slice(0, 10),
      start_time: '08:00',
      end_time: '17:00',
      course_id: '1',
      max_instructors: '1',
      max_students: '1',
    },
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
    },
    maxRedirects: 0,
  });

  console.log('Post took:', Date.now() - t0, 'ms');
  console.log('Status:', res.status());
  console.log('Body:', (await res.text()).slice(0, 200));

  await browser.close();
})();
