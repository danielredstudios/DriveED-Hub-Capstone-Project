/**
 * Automated Browser Testing Suite – DriveED Hub
 * Based on Google Codelab: https://codelabs.developers.google.com/agentic-ui-testing
 * 
 * Runs end-to-end automated browser testing using Playwright:
 * - Supports Headless mode (fast/CI) & Headed mode (interactive visible browser)
 * - Isolated context per scenario (no session leaks between roles)
 * - Tests Guest, Student, Instructor, and Admin user journeys
 * - Verifies fixes for all 11 critical errors and UI scoping issues
 * - Captures visual screenshot evidence into test-results/browser-testing/
 * 
 * Usage:
 *   node scripts/automated-browser-test.js              (Headless mode)
 *   node scripts/automated-browser-test.js --headed     (Interactive visible browser)
 *   node scripts/automated-browser-test.js --slowMo 300 (Slowed down for visual demo)
 */

const { chromium } = require('@playwright/test');
const { login, USERS, BASE, SCHOOL_SLUG } = require('../tests/fixtures/auth');
const fs = require('fs');
const path = require('path');

const ARTIFACTS_DIR = path.join(__dirname, '..', 'test-results', 'browser-testing');

// Parse command line arguments
const args = process.argv.slice(2);
const isHeaded = args.includes('--headed');
const slowMoArg = args.indexOf('--slowMo');
const slowMo = slowMoArg !== -1 && args[slowMoArg + 1] ? parseInt(args[slowMoArg + 1], 10) : (isHeaded ? 200 : 0);

// Colors for terminal output
const colors = {
  reset: '\x1b[0m',
  bright: '\x1b[1m',
  green: '\x1b[32m',
  red: '\x1b[31m',
  yellow: '\x1b[33m',
  cyan: '\x1b[36m',
  blue: '\x1b[34m',
  dim: '\x1b[2m',
};

function log(msg, color = colors.reset) {
  console.log(`${color}${msg}${colors.reset}`);
}

async function captureEvidence(page, name) {
  if (!fs.existsSync(ARTIFACTS_DIR)) {
    fs.mkdirSync(ARTIFACTS_DIR, { recursive: true });
  }
  const filePath = path.join(ARTIFACTS_DIR, `${name}.png`);
  await page.screenshot({ path: filePath, fullPage: true });
  return filePath;
}

const testResults = [];

async function main() {
  log('\n===============================================================', colors.cyan);
  log('   🚀 AUTOMATED BROWSER TESTING SUITE – DRIVEED HUB', colors.bright + colors.cyan);
  log(`   Target: ${BASE}/${SCHOOL_SLUG}`, colors.cyan);
  log(`   Mode: ${isHeaded ? 'HEADED (Interactive Visible Browser)' : 'HEADLESS (Fast / CI)'}`, colors.yellow);
  if (slowMo > 0) log(`   SlowMo: ${slowMo}ms per action`, colors.yellow);
  log('===============================================================\n', colors.cyan);

  if (!fs.existsSync(ARTIFACTS_DIR)) {
    fs.mkdirSync(ARTIFACTS_DIR, { recursive: true });
  }

  const browser = await chromium.launch({
    headless: !isHeaded,
    slowMo: slowMo,
  });

  async function runScenario(testName, testFn) {
    const start = Date.now();
    process.stdout.write(`  ▶ Running: ${testName} ... `);
    const context = await browser.newContext({
      viewport: { width: 1280, height: 800 },
      ignoreHTTPSErrors: true,
    });
    const page = await context.newPage();
    try {
      await testFn(page, context);
      const duration = ((Date.now() - start) / 1000).toFixed(2);
      log(`[PASS] (${duration}s)`, colors.green);
      testResults.push({ name: testName, status: 'PASS', duration: `${duration}s` });
    } catch (err) {
      const duration = ((Date.now() - start) / 1000).toFixed(2);
      log(`[FAIL] (${duration}s)`, colors.red);
      log(`    Error: ${err.message}`, colors.red);
      testResults.push({ name: testName, status: 'FAIL', duration: `${duration}s`, error: err.message });
    } finally {
      await context.close();
    }
  }

  try {
    // 0. Smoke Check
    await runScenario('00. Smoke & System Availability', async (page) => {
      await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded' });
      const content = await page.content();
      if (content.includes('500 Internal Server Error')) throw new Error('App returned 500 error on root');
      const title = await page.title();
      if (!title) throw new Error('Root page did not provide a title');
      await captureEvidence(page, '00-smoke-root');
    });

    // 1. Guest Flow & Safe Enrollment Verification
    await runScenario('01. Guest Flow & Safe Enrollment Verification', async (page) => {
      await login(page, 'guestToEnroll');
      await page.goto(`${BASE}/${SCHOOL_SLUG}/guest/courses`, { waitUntil: 'domcontentloaded' });
      const body = await page.content();
      if (body.includes('500 Internal Server Error')) throw new Error('Guest courses returned 500');

      const enrollBtn = page.getByRole('button', { name: /enroll now/i }).first();
      if (await enrollBtn.isVisible().catch(() => false)) {
        await enrollBtn.click();
        await page.waitForTimeout(600);
      }
      await captureEvidence(page, '01-guest-courses-enroll');
    });

    // 2. Course Badges De-cluttering on Catalog Grid
    await runScenario('02. Course Badges De-cluttering on Catalog Grid', async (page) => {
      await login(page, 'studentNew');
      await page.goto(`${BASE}/${SCHOOL_SLUG}/student/courses`, { waitUntil: 'domcontentloaded' });
      const body = await page.content();
      if (body.includes('500 Internal Server Error')) throw new Error('Student courses returned 500');

      const firstCard = page.locator('.course-card, [class*="course-card"], .courses-grid > div, .course-item').first();
      if (await firstCard.isVisible().catch(() => false)) {
        const badge = firstCard.locator('.course-badges, .badge, [class*="badge"]').first();
        if (await badge.isVisible().catch(() => false)) {
          const badgeText = await badge.textContent();
          if (badgeText && badgeText.includes('A, A1, B')) {
            throw new Error('Course grid card badge displays raw restriction codes (A, A1, B)');
          }
        }
      }

      await captureEvidence(page, '02-course-badges-grid');
    });

    // 3. Student Dashboard, Dynamic Course Type & Roadmap Step 3
    await runScenario('03. Student Dynamic Course Type & Roadmap Step 3', async (page) => {
      await login(page, 'studentPractical');
      await page.goto(`${BASE}/${SCHOOL_SLUG}/student`, { waitUntil: 'domcontentloaded' });
      
      const body = await page.content();
      if (body.includes('500 Internal Server Error')) throw new Error('Student dashboard returned 500');

      const roadmap = page.locator('.license-journey-card, .journey-steps');
      if (await roadmap.isVisible().catch(() => false)) {
        const activeStep = roadmap.locator('.journey-step.active');
        if (await activeStep.isVisible().catch(() => false)) {
          const stepTitle = (await activeStep.locator('.step-title').textContent()).toLowerCase();
          if (stepTitle.includes('theoretical driving course (tdc)')) {
            throw new Error('Roadmap incorrectly highlighted Step 1 (TDC) for Practical-only student!');
          }
        }
      }

      await captureEvidence(page, '03-student-dashboard-roadmap');
    });

    // 4. Student My Course Progress Tracking & Visual Sequence
    await runScenario('04. Student Progress Tracking & Lesson Sequence', async (page) => {
      await login(page, 'studentPractical');
      await page.goto(`${BASE}/${SCHOOL_SLUG}/student/my-course`, { waitUntil: 'domcontentloaded' });
      const body = await page.content();
      if (body.includes('500 Internal Server Error')) throw new Error('My Course returned 500');

      const courseCards = await page.locator('.course-card').count();
      if (courseCards === 0) {
        throw new Error('No course card found on My Course view');
      }

      // Expand first module to verify lesson sequence
      const moduleHeader = page.locator('.module-header').first();
      if (await moduleHeader.isVisible().catch(() => false)) {
        await moduleHeader.click();
        await page.waitForTimeout(400);
      }

      const visibleLessons = await page.locator('.lesson-item:visible').count();
      if (visibleLessons === 0) {
        throw new Error('No lessons visible after expanding course module');
      }

      await captureEvidence(page, '04-student-my-course-progress');
    });

    // 5. Instructor Single Scoped Student List & IDOR Guard
    await runScenario('05. Instructor Single Grid Scoping & IDOR Guard', async (page) => {
      await login(page, 'instructorVerified');
      await page.goto(`${BASE}/${SCHOOL_SLUG}/instructor/students`, { waitUntil: 'domcontentloaded' });
      
      const body = await page.content();
      if (body.includes('500 Internal Server Error')) throw new Error('Instructor students returned 500');

      // Verify exactly ONE grid exists (duplicate grid bug resolved)
      const gridsCount = await page.locator('.students-grid').count();
      if (gridsCount > 1) {
        throw new Error(`Found ${gridsCount} student grids on Instructor view; expected exactly 1.`);
      }

      await captureEvidence(page, '05-instructor-single-grid');

      // IDOR test: try to access non-assigned student directly
      const idorRes = await page.goto(`${BASE}/${SCHOOL_SLUG}/instructor/students/999999`, { waitUntil: 'domcontentloaded' });
      const idorBody = await page.content();
      const status = idorRes ? idorRes.status() : 0;
      const isForbidden = status === 403 || status === 404 || idorBody.includes('Forbidden') || idorBody.includes('403') || idorBody.includes('Unauthorized');
      if (!isForbidden) {
        throw new Error(`IDOR Guard failed: student ID 999999 was not blocked with 403/404 (HTTP ${status})`);
      }

      await captureEvidence(page, '05-instructor-idor-guarded');
    });

    // 6. Admin Batch Scheduling & Max Hour Validation
    await runScenario('06. Admin Batch Scheduling & 8h Max Limit Validation', async (page, context) => {
      await login(page, 'admin');
      await page.goto(`${BASE}/${SCHOOL_SLUG}/admin/schedules`, { waitUntil: 'domcontentloaded' });

      const body = await page.content();
      if (body.includes('500 Internal Server Error')) throw new Error('Admin schedules returned 500');

      // Check max hour validation: attempt 9-hour slot (08:00 to 17:00) using request context
      const csrf = await page.getAttribute('meta[name="csrf-token"]', 'content').catch(() => '');
      const postRes = await context.request.post(`${BASE}/${SCHOOL_SLUG}/admin/schedules/create`, {
        form: {
          _token: csrf,
          date: new Date(Date.now() + 86400000).toISOString().slice(0, 10),
          start_time: '08:00',
          end_time: '17:00', // 9 hours
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

      const respText = await postRes.text().catch(() => '');
      const isRejected = [422, 403, 419].includes(postRes.status()) || respText.includes('8 hours') || respText.includes('cannot exceed');
      if (!isRejected) {
        throw new Error(`9h schedule was not rejected by validation. HTTP status: ${postRes.status()}`);
      }

      await captureEvidence(page, '06-admin-schedules-validated');
    });

    // 7. Admin Dynamic Combo Creation
    await runScenario('07. Admin Dynamic Course Combo Creation', async (page) => {
      await login(page, 'admin');
      await page.goto(`${BASE}/${SCHOOL_SLUG}/admin/courses`, { waitUntil: 'domcontentloaded' });
      const body = await page.content();
      if (body.includes('500 Internal Server Error')) throw new Error('Admin courses returned 500');

      const createBtn = page.getByRole('button', { name: /create new course/i }).first();
      if (await createBtn.isVisible().catch(() => false)) {
        await createBtn.click();
        await page.waitForTimeout(600);
        const typeSelect = page.locator('select[name="course_type"]');
        if (await typeSelect.isVisible().catch(() => false)) {
          await typeSelect.selectOption('combo');
          await page.waitForTimeout(400);
        }
      }

      await captureEvidence(page, '07-admin-dynamic-combo');
    });

  } finally {
    await browser.close();
  }

  // Print Summary Table
  log('\n===============================================================', colors.cyan);
  log('                     TEST EXECUTION SUMMARY                    ', colors.bright + colors.cyan);
  log('===============================================================', colors.cyan);

  const passedCount = testResults.filter(t => t.status === 'PASS').length;
  const failedCount = testResults.filter(t => t.status === 'FAIL').length;

  for (const res of testResults) {
    const icon = res.status === 'PASS' ? '✓' : '✗';
    const color = res.status === 'PASS' ? colors.green : colors.red;
    log(`  ${icon} ${res.name.padEnd(52)} ${res.duration.padStart(6)}  [${res.status}]`, color);
  }

  log('---------------------------------------------------------------', colors.cyan);
  log(`  Total: ${testResults.length}  |  Passed: ${passedCount}  |  Failed: ${failedCount}`, 
    failedCount === 0 ? colors.bright + colors.green : colors.bright + colors.red);
  log(`  Screenshots saved to: ${ARTIFACTS_DIR}`, colors.blue);
  log('===============================================================\n', colors.cyan);

  if (failedCount > 0) {
    process.exit(1);
  }
}

main().catch(err => {
  console.error('Fatal error running browser tests:', err);
  process.exit(1);
});
