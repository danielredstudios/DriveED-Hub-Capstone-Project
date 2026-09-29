const { chromium } = require('@playwright/test');
const { login, USERS, BASE, SCHOOL_SLUG } = require('../tests/fixtures/auth');
const fs = require('fs');
const path = require('path');

const LOCAL_DIR = path.join(__dirname, '..', 'test-results', 'fix-screenshots');
const ARTIFACT_DIR = 'C:\\Users\\RED\\.gemini\\antigravity\\brain\\3433858c-4c66-4ecf-9661-95b39d8bbbdb';

if (!fs.existsSync(LOCAL_DIR)) fs.mkdirSync(LOCAL_DIR, { recursive: true });
if (!fs.existsSync(ARTIFACT_DIR)) fs.mkdirSync(ARTIFACT_DIR, { recursive: true });

async function saveScreenshot(page, filename, options = {}, alias = null) {
  const localPath = path.join(LOCAL_DIR, filename);
  const artifactPath = path.join(ARTIFACT_DIR, filename);
  
  await page.screenshot({ path: localPath, ...options });
  fs.copyFileSync(localPath, artifactPath);
  console.log(`Saved screenshot: ${filename}`);

  if (alias) {
    const aliasLocal = path.join(LOCAL_DIR, alias);
    const aliasArtifact = path.join(ARTIFACT_DIR, alias);
    fs.copyFileSync(localPath, aliasLocal);
    fs.copyFileSync(localPath, aliasArtifact);
    console.log(`  (also saved alias: ${alias})`);
  }
}

async function main() {
  const browser = await chromium.launch({ headless: true });

  console.log('--- 1. ENROLLMENT 500 ERROR FIX ---');
  {
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await ctx.newPage();
    await login(page, 'guestToEnroll');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/guest/courses`, { waitUntil: 'domcontentloaded' });
    const enrollBtn = page.getByRole('button', { name: /enroll now/i }).first();
    if (await enrollBtn.isVisible().catch(() => false)) {
      await enrollBtn.click();
      await page.waitForTimeout(800);
    }
    await saveScreenshot(page, '01_CRITICAL_enrollment_500_error_undefined_studentLicenseStatus.png', {}, 'ENROLLMENT_500_ERROR_FIX.png');
    await ctx.close();
  }

  console.log('--- 2. STUDENT LIST SCOPING - ADMIN FIX ---');
  {
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await ctx.newPage();
    await login(page, 'admin');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/admin/user-management`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);
    await saveScreenshot(page, '02_CRITICAL_student_list_scoping_admin.png', {}, 'STUDENT_LIST_SCOPING_ADMIN_FIX.png');
    await ctx.close();
  }

  console.log('--- 3. STUDENT LIST SCOPING - INSTRUCTOR FIX (SINGLE GRID, NO DUPLICATE) ---');
  {
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await ctx.newPage();
    await login(page, 'instructorVerified');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/instructor/students`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);
    await saveScreenshot(page, '03_CRITICAL_student_list_scoping_instructor_no_duplicate.png', {}, 'STUDENT_LIST_SCOPING_INSTRUCTOR_FIX.png');
    await ctx.close();
  }

  console.log('--- 4. STATIC COURSE DISPLAY FIX (DYNAMIC PRACTICAL INDICATOR) ---');
  {
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await ctx.newPage();
    await login(page, 'studentPractical');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/student`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);
    await saveScreenshot(page, '04_STUDENT_DASHBOARD_dynamic_course_display_pdc.png', {}, 'STATIC_COURSE_DISPLAY_DYNAMIC_FIX.png');
    await ctx.close();
  }

  console.log('--- 5. INCORRECT ROADMAP STEPS FIX (STEP 3 ACTIVE FOR PRACTICAL) ---');
  {
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await ctx.newPage();
    await login(page, 'studentPractical');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/student`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);
    const roadmap = page.locator('.license-journey-card');
    if (await roadmap.isVisible().catch(() => false)) {
      await roadmap.scrollIntoViewIfNeeded();
      await page.waitForTimeout(500);
    }
    await saveScreenshot(page, '05_STUDENT_DASHBOARD_roadmap_practical_step3.png', {}, 'ROADMAP_PRACTICAL_STEP3_FIX.png');
    await ctx.close();
  }

  console.log('--- 6. PROGRESS TRACKING & LESSON SEQUENCE FIX ---');
  {
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await ctx.newPage();
    await login(page, 'studentPractical');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/student/my-course`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);
    const moduleHeader = page.locator('.module-header').first();
    if (await moduleHeader.isVisible().catch(() => false)) {
      await moduleHeader.click();
      await page.waitForTimeout(600);
    }
    await saveScreenshot(page, '06_STUDENT_DASHBOARD_progress_tracking_lesson_sequence.png', {}, 'PROGRESS_TRACKING_LESSON_SEQUENCE_FIX.png');
    await ctx.close();
  }

  console.log('--- 7. COURSE BADGES DE-CLUTTERED GRID VIEW FIX ---');
  {
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await ctx.newPage();
    await login(page, 'studentNew');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/student/courses`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);
    await saveScreenshot(page, '07_STUDENT_DASHBOARD_course_badges_grid_decluttered.png', {}, 'COURSE_BADGES_GRID_DECLUTTERED_FIX.png');
    await ctx.close();
  }

  console.log('--- 8. COURSE BADGES RESTRICTIONS & ELIGIBILITY ON DETAIL MODAL ---');
  {
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await ctx.newPage();
    await login(page, 'studentNew');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/student/courses`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(800);
    const viewBtn = page.getByRole('button', { name: /view course|enroll now/i }).first();
    if (await viewBtn.isVisible().catch(() => false)) {
      await viewBtn.click();
      await page.waitForTimeout(1000);
    }
    await saveScreenshot(page, '08_STUDENT_DASHBOARD_course_badges_detail_modal_eligibility.png', {}, 'COURSE_BADGES_DETAIL_ELIGIBILITY_FIX.png');
    await ctx.close();
  }

  console.log('--- 9. TDC BATCH SCHEDULING FIX ---');
  {
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await ctx.newPage();
    await login(page, 'admin');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/admin/schedules`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);
    const createBtn = page.locator('button:has-text("Create Schedule"), button:has-text("Add Schedule"), button:has-text("New Time Slot"), button.btn-primary').first();
    if (await createBtn.isVisible().catch(() => false)) {
      await createBtn.click();
      await page.waitForTimeout(800);
    }
    await saveScreenshot(page, '09_SCHEDULING_tdc_batch_scheduling.png', {}, 'TDC_BATCH_SCHEDULING_FIX.png');
    await ctx.close();
  }

  console.log('--- 10. MAX HOUR VALIDATION FIX ---');
  {
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await ctx.newPage();
    await login(page, 'admin');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/admin/schedules`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);

    const openBtn = page.locator('button:has-text("Create Schedule"), button:has-text("Add Schedule"), button:has-text("New Time Slot")').first();
    if (await openBtn.isVisible().catch(() => false)) {
      await openBtn.click();
      await page.waitForTimeout(600);
      const startInput = page.locator('input[name="start_time"]');
      const endInput = page.locator('input[name="end_time"]');
      if (await startInput.isVisible().catch(() => false)) {
        await startInput.fill('08:00');
        await endInput.fill('17:00');
        const submitBtn = page.locator('#createScheduleModal button[type="submit"], form button:has-text("Create"), form button:has-text("Save")').first();
        if (await submitBtn.isVisible().catch(() => false)) {
          await submitBtn.click();
          await page.waitForTimeout(1000);
        }
      }
    }
    await saveScreenshot(page, '10_SCHEDULING_max_hour_validation_daily_cap.png', {}, 'MAX_HOUR_VALIDATION_FIX.png');
    await ctx.close();
  }

  console.log('--- 11. DOUBLE BOOKING PREVENTION & CANCELLATION SYNCING FIX ---');
  {
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await ctx.newPage();
    await login(page, 'admin');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/admin/schedules`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);
    await saveScreenshot(page, '11_SCHEDULING_double_booking_prevention.png', {}, 'DOUBLE_BOOKING_PREVENTION_FIX.png');
    await ctx.close();
  }

  console.log('--- 12. CANCELLATION SYNCING FIX ---');
  {
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await ctx.newPage();
    await login(page, 'studentPractical');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/student/schedule`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);
    await saveScreenshot(page, '12_SCHEDULING_cancellation_syncing.png', {}, 'CANCELLATION_SYNCING_FIX.png');
    await ctx.close();
  }

  console.log('--- 13. DYNAMIC COMBO CREATION FIX ---');
  {
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await ctx.newPage();
    await login(page, 'admin');
    await page.goto(`${BASE}/${SCHOOL_SLUG}/admin/courses`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);
    const createCourseBtn = page.getByRole('button', { name: /create new course|add course/i }).first();
    if (await createCourseBtn.isVisible().catch(() => false)) {
      await createCourseBtn.click();
      await page.waitForTimeout(800);
      const typeSelect = page.locator('select[name="course_type"]');
      if (await typeSelect.isVisible().catch(() => false)) {
        await typeSelect.selectOption('combo');
        await page.waitForTimeout(600);
      }
    }
    await saveScreenshot(page, '13_ADMIN_COURSES_dynamic_combo_creation.png', {}, 'DYNAMIC_COMBO_CREATION_FIX.png');
    await ctx.close();
  }

  await browser.close();
  console.log('ALL SCREENSHOTS CAPTURED SUCCESSFULLY!');
}

main().catch(err => {
  console.error('Error capturing screenshots:', err);
  process.exit(1);
});
