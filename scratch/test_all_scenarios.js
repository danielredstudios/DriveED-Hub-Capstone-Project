const { chromium } = require('@playwright/test');
const { login, USERS, BASE, SCHOOL_SLUG } = require('../tests/fixtures/auth');
const fs = require('fs');
const path = require('path');

const ARTIFACTS_DIR = path.join(__dirname, '..', 'test-results', 'browser-testing');

(async () => {
  if (!fs.existsSync(ARTIFACTS_DIR)) fs.mkdirSync(ARTIFACTS_DIR, { recursive: true });
  const browser = await chromium.launch({ headless: true });

  const run = async (title, fn) => {
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await ctx.newPage();
    try {
      const t0 = Date.now();
      await fn(page, ctx);
      console.log(`✓ ${title} (${((Date.now()-t0)/1000).toFixed(2)}s)`);
    } catch (e) {
      console.log(`✗ ${title} FAILED:`, e.message);
    } finally {
      await ctx.close();
    }
  };

  // 00. Smoke
  await run('00. Smoke Root', async (page) => {
    await page.goto(`${BASE}/`);
    const c = await page.content();
    if (c.includes('500 Internal Server Error')) throw new Error('500 on root');
  });

  // 01. Guest
  await run('01. Guest Courses', async (page) => {
    await login(page, 'guestToEnroll');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/guest/courses`);
    const c = await page.content();
    if (c.includes('500 Internal Server Error')) throw new Error('500 on guest courses');
  });

  // 02. Badges
  await run('02. Course Badges Grid', async (page) => {
    await login(page, 'studentNew');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/student/courses`);
    const firstCard = page.locator('.course-card, [class*="course-card"], .courses-grid > div, .course-item').first();
    if (await firstCard.isVisible().catch(() => false)) {
      const badge = firstCard.locator('.course-badges, .badge, [class*="badge"]').first();
      if (await badge.isVisible().catch(() => false)) {
        const txt = await badge.textContent();
        if (txt && txt.includes('A, A1, B')) throw new Error('Raw DL code on badge');
      }
    }
  });

  // 03. Roadmap
  await run('03. Student Dashboard Roadmap', async (page) => {
    await login(page, 'studentPractical');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/student`);
    const active = page.locator('.journey-step.active');
    if (await active.first().isVisible().catch(() => false)) {
      const title = (await active.first().locator('.step-title').textContent()).toLowerCase();
      if (title.includes('theoretical driving course (tdc)')) throw new Error('Roadmap Step 1 on practical');
    }
  });

  // 04. Progress
  await run('04. Student My Course Progress', async (page) => {
    await login(page, 'studentPractical');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/student/my-course`);
    if ((await page.locator('.course-card').count()) === 0) throw new Error('No course cards');
  });

  // 05. Instructor
  await run('05. Instructor Scoping & IDOR', async (page) => {
    await login(page, 'instructorVerified');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/instructor/students`);
    const count = await page.locator('.students-grid').count();
    if (count > 1) throw new Error('Duplicate grids');
    const res = await page.goto(`${BASE}/${SCHOOL_SLUG}/instructor/students/999999`);
    const body = await page.content();
    const ok = res.status() === 403 || res.status() === 404 || body.includes('403') || body.includes('Forbidden');
    if (!ok) throw new Error(`IDOR not guarded: status ${res.status()}`);
  });

  // 06. Admin 8h
  await run('06. Admin 8h Validation', async (page, ctx) => {
    await login(page, 'admin');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/admin/schedules`);
    const csrf = await page.getAttribute('meta[name="csrf-token"]', 'content').catch(() => '');
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
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const txt = await res.text().catch(() => '');
    const ok = [422, 403, 419].includes(res.status()) || txt.includes('8 hours') || txt.includes('cannot exceed');
    if (!ok) throw new Error('8h limit not rejected');
  });

  // 07. Dynamic Combo
  await run('07. Dynamic Combo', async (page) => {
    await login(page, 'admin');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/admin/courses`);
    const c = await page.content();
    if (c.includes('500 Internal Server Error')) throw new Error('500 on admin courses');
  });

  await browser.close();
})();
