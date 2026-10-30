import puppeteer from 'puppeteer-core';
import fs from 'node:fs/promises';
import {fileURLToPath} from 'node:url';
import {execFileSync} from 'node:child_process';
import assert from 'node:assert/strict';

const output = new URL('../docs/screenshots/bug-report-2026-10-30/', import.meta.url);
await fs.mkdir(output, {recursive: true});
const docker = 'C:/Users/RED/AppData/Local/Programs/DockerDesktop/resources/bin/docker.exe';
const fixture = mode => execFileSync(docker, ['exec', 'driveed_hub_app', 'php', 'tests/bug-report-browser-fixture.php', mode], {encoding: 'utf8'});
const results = [];
const record = message => {results.push({pass: true, message}); console.log('PASS: ' + message);};
let browser;
try {
    const prepared = fixture('prepare');
    assert(prepared.includes('Prepared isolated browser fixtures.'), prepared);
    const data = JSON.parse(await fs.readFile(new URL('../tmp/bug-report-browser-fixture.json', import.meta.url), 'utf8'));
    browser = await puppeteer.launch({executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true, args: ['--no-sandbox'], defaultViewport: {width: 1440, height: 1000}});
    const screenshot = (page, name) => page.screenshot({path: fileURLToPath(new URL(name + '.png', output))});
    const visit = async (page, role, route) => {
        const response = await page.goto('http://localhost:8004/drived-hub/' + role + '/' + route, {waitUntil: 'networkidle2'});
        assert.equal(response.status(), 200);
        assert(!page.url().endsWith('/login'));
        record(role + '/' + route + ' loads successfully');
    };
    const login = async role => {
        const context = await browser.createBrowserContext();
        const page = await context.newPage();
        page.on('pageerror', error => results.push({pass: false, role, error: error.message}));
        await page.goto('http://localhost:8004/drived-hub/login', {waitUntil: 'networkidle2'});
        await page.type('[name="email"]', role + '1@driveedhub.test');
        await page.type('[name="password"]', 'DriveDemo123');
        await Promise.all([page.waitForNavigation({waitUntil: 'networkidle2'}), page.click('button[type="submit"]')]);
        assert(page.url().includes('/' + role));
        assert.equal(await page.$eval('.topbar-logo', el => el.innerText.trim()), 'DriveED Hub');
        assert.equal(await page.$eval('.topbar-logo', el => el.querySelectorAll('img').length), 0);
        assert.equal(await page.$eval('.topbar', el => getComputedStyle(el).backgroundColor), 'rgb(23, 75, 121)');
        record(role + ' header uses configured school name/color and no removed logo');
        return page;
    };
    const admin = await login('admin');
    await visit(admin, 'admin', 'user-management');
    await admin.click('details.table-card > summary');
    assert((await admin.$eval('details.table-card', el => el.innerText)).includes('qa-email-tracking@example.invalid'));
    record('User Management lists pending student email with resend/remove actions');
    await screenshot(admin, 'admin-user-management');
    await admin.evaluate(() => openCreateStudentModal());
    assert((await admin.$eval('#createStudentModal h3', el => el.innerText)).includes('Add New Student'));
    await screenshot(admin, 'admin-add-student');
    await visit(admin, 'admin', 'verify-session-completion');
    await admin.click('.schedule-log-export details > summary');
    assert.equal(await admin.$$eval('summary', els => els.filter(el => el.textContent === 'Export Report').length), 1);
    record('One Export Report menu offers all records and filtered records');
    await screenshot(admin, 'admin-verify-session-completion');
    const downloads = await admin.evaluate(async () => {
        const base = document.getElementById('scheduleLogsExportForm').action;
        const checks = [];
        for (const suffix of ['', '?start=2099-01-01&end=2099-01-02']) {
            for (const format of ['pdf', 'xlsx']) {
                const response = await fetch(base.replace(/\/pdf$/, '/' + format) + suffix);
                const bytes = new Uint8Array(await response.arrayBuffer());
                checks.push({format, status: response.status, signature: String.fromCharCode(...bytes.slice(0, format === 'pdf' ? 4 : 2))});
            }
        }
        return checks;
    });
    for (const check of downloads) {assert.equal(check.status, 200); assert.equal(check.signature, check.format === 'pdf' ? '%PDF' : 'PK');}
    record('Authenticated all/filtered downloads produce valid PDF and Excel files');
    await visit(admin, 'admin', 'theoretical');
    await admin.evaluate(() => switchTab('passed'));
    const review = await admin.$('#passedTableBody a.btn-view');
    if (review) {
        const href = await review.evaluate(el => el.getAttribute('href'));
        assert(!href.includes('user-management'));
        if (href !== '#') {
            await Promise.all([admin.waitForNavigation({waitUntil: 'networkidle2'}), review.click()]);
            assert((await admin.$eval('body', el => el.innerText)).includes('Training'));
            record('Passed-student View opens the training review');
        } else {
            const message = new Promise(resolve => admin.once('dialog', async dialog => {resolve(dialog.message()); await dialog.accept();}));
            await review.click();
            assert.equal(await message, 'This student has no data/information.');
            record('Student without enrollment shows the requested no-data message');
        }
    }
    await screenshot(admin, 'admin-student-training');
    const instructor = await login('instructor');
    await visit(instructor, 'instructor', 'my-schedule?view=calendar');
    await instructor.click('[data-calendar-date].is-today');
    assert(await instructor.$eval('#calendarDayDialog', el => el.open));
    assert(await instructor.$eval('#calendarDayDialog', el => Math.abs(el.getBoundingClientRect().top + el.getBoundingClientRect().height / 2 - window.innerHeight / 2) < 5));
    assert((await instructor.$eval('#calendarDayContent', el => el.innerText)).includes('Log Session'));
    record('Instructor calendar day opens schedules and session-log actions');
    await screenshot(instructor, 'instructor-calendar-day');
    await instructor.click(`#calendarDayContent button[onclick*="(${data.booking_id})"]`);
    await instructor.waitForFunction(() => !document.querySelector('#scheduleSessionLogForm [type="submit"]').disabled);
    await instructor.select('#scheduleLogAttendance', 'late');
    await instructor.type('#scheduleLogGrade', '93');
    await instructor.type('#scheduleLogNotes', 'QA session log: attendance, grade, and notes saved together.');
    await screenshot(instructor, 'instructor-session-log');
    await Promise.all([instructor.waitForNavigation({waitUntil: 'networkidle2'}), instructor.click('#scheduleSessionLogForm [type="submit"]')]);
    record('Instructor saves attendance, grade, and notes through the schedule form');
    await visit(instructor, 'instructor', 'my-schedule');
    const pastCount = await instructor.$$eval('#my-slots-view [data-is-past="true"].is-hidden', els => els.length);
    await instructor.click('#show-past-my');
    assert.equal(await instructor.$$eval('#my-slots-view [data-is-past="true"].is-hidden', els => els.length), 0);
    assert(pastCount > 0);
    record('Show Past Schedules reveals historical instructor sessions');
    await screenshot(instructor, 'instructor-past-schedules');
    await visit(instructor, 'instructor', 'sessions/create');
    assert(await instructor.$('#session_grade'));
    await screenshot(instructor, 'instructor-manual-session');
    await visit(admin, 'admin', 'verify-session-completion');
    const details = await admin.evaluate(async id => {
        const response = await fetch('/drived-hub/admin/verify-session-completion/' + id, {headers: {'Accept': 'application/json'}});
        return response.json();
    }, data.booking_id);
    assert.equal(Number(details.booking.session_grade), 93);
    assert.equal(details.booking.attendance_status, 'late');
    assert.equal(details.booking.status, 'done');
    const loggedCard = await admin.$(`[data-booking-id="${data.booking_id}"]`);
    assert(loggedCard);
    assert((await loggedCard.evaluate(el => el.innerText)).includes('QA session log'));
    await loggedCard.scrollIntoView();
    record('Admin receives the same booking ID, grade, and attendance');
    await screenshot(admin, 'admin-logged-session-visible');
    const student = await login('student');
    await visit(student, 'student', 'schedule?view=calendar');
    await student.click('[data-calendar-date].is-today');
    assert(await student.$eval('#calendarDayDialog', el => el.open));
    assert((await student.$eval('#calendarDayContent', el => el.innerText)).includes('QA session log'));
    record('Student calendar day shows the logged schedule and instructor notes');
    await screenshot(student, 'student-calendar-day');
    await student.setViewport({width: 390, height: 844});
    assert(await student.$eval('#calendarDayDialog', el => el.getBoundingClientRect().width <= window.innerWidth));
    assert(await student.$eval('.calendar-grid', el => el.scrollWidth <= el.clientWidth));
    await screenshot(student, 'student-calendar-mobile');
    record('Calendar day dialog fits a 390px mobile viewport');
    await student.keyboard.press('Escape');
    assert.equal(await student.$eval('#calendarDayDialog', el => el.open), false);
    record('Calendar day dialog closes with Escape');
    fixture('hidden');
    await instructor.reload({waitUntil: 'networkidle2'});
    assert.equal(await instructor.$eval('.topbar-logo', el => el.innerText.trim()), '');
    assert.equal(await instructor.$eval('.topbar-logo', el => el.querySelectorAll('img').length), 0);
    await screenshot(instructor, 'branding-name-and-logo-hidden');
    record('Disabled school name and removed logo stay hidden after reload');
    assert(results.every(result => result.pass), 'Browser console errors were recorded');
} catch (error) {
    results.push({pass: false, error: error.stack});
    process.exitCode = 1;
    console.error(error);
} finally {
    if (browser) await browser.close();
    console.log(fixture('cleanup').trim());
    await fs.writeFile(new URL('results.json', output), JSON.stringify(results, null, 2));
    console.log(`${results.filter(result => result.pass).length} browser checks passed.`);
}
