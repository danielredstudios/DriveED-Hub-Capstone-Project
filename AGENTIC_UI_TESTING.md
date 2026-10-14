# Agentic UI Testing – DriveED Hub

> Codelab: https://codelabs.developers.google.com/agentic-ui-testing
> Stack: **Gemini CLI + BrowserMCP + Playwright CLI + Chrome DevTools MCP**

This implements the full codelab flow for the DriveED Hub Capstone (Laravel 12 + Docker at `http://localhost:8004`).

## 1. What you get

| Codelab # | Concept | Implementation in this repo |
|-----------|---------|-----------------------------|
| 3 | Demo app | `docker compose up -d` → `http://localhost:8004` (school `drived-hub`) |
| 5 | MCP | `BrowserMCP` + `Chrome DevTools MCP` via `~/.gemini/extensions` |
| 6 | BrowserMCP hands/eyes | `gemini extensions install https://github.com/derailed-dash/browsermcp-ext` + Chrome extension `Connect` |
| 7 | Skill + Playwright | `.gemini/skills/playwright-cli/SKILL.md` + `playwright.config.js` + `tests/e2e/*.spec.js` |
| 9 | Antigravity artifacts | `test-results/artifacts/*.png` + `playwright-report/` |

Tests focus on **intent, not selectors** (codelab #4) – `getByRole`, `getByLabel`, `getByText` with `/i`.

## 2. Quick start

```bash
# 1. Start demo app (codelab #3)
docker compose up -d --build
docker exec driveed_hub_app php artisan migrate --force   # ensures 2026_05_01 combo column

# 2. Install Playwright (codelab #7)
npm install
npx playwright install --with-deps chromium chrome

# 3. Run all agentic tests (headless for CI, headed for eyes)
npx playwright test                # headless (CI)
npx playwright test --headed       # headed via Playwright CLI skill
npx playwright test --ui           # UI mode
npx playwright show-report         # HTML report

# 4. Run single intent (natural language prompt → deterministic test)
npx playwright test tests/e2e/01-enrollment-500.spec.js --headed
npx playwright test tests/e2e/03-dashboard-roadmap.spec.js --headed
```

## 3. Natural language prompts → automated tests

Use Gemini CLI (with the Playwright skill) instead of hand-writing selectors:

```bash
gemini
# Inside Gemini CLI:
/mcp                               # verify browsermcp + chrome-devtools are listed
/mcp disable browsermcp            # when using Playwright only (codelab #7)
```

**Example prompts (codelab #6 copy-paste, adapted to DriveED Hub):**

```
Using BrowserMCP, connect to http://localhost:8004/drived-hub/login. If already logged in, logout. Then login as 'admin1@driveedhub.test' with password 'DriveDemo123', and verify dashboard shows DriveED Hub. Report pending enrollments count in a markdown table.

Using Playwright, connect to http://localhost:8004 in headed mode and keep browser open. Login as 'student1@driveedhub.test' / 'DriveDemo123', verify dashboard Enrollment Status is not hardcoded Theoretical, and roadmap active step is Step 3 for Practical. Take screenshot to test-results/artifacts/dashboard.png, wait 2s, read again.

Using Playwright, enroll as guest guest4@driveedhub.test in the first available course via student_license field, double-click submit to test race guard, and verify no 500. Save screenshot to test-results/artifacts/enrollment.png.
```

## 4. Test matrix – covers all 11 fixed bugs

| File | Intent (natural language) | Asserts |
|------|---------------------------|---------|
| `00-smoke.spec.js` | App is up | Welcome + login render, screenshot artifact |
| `01-enrollment-500.spec.js` | Enroll without 500 | `student_license` + `credential_file` accepted, race double-click, no 500, friendly validation |
| `02-scoping.spec.js` | Lists are school-scoped | Admin sees students, instructor single grid, IDOR 403 for 999999 |
| `03-dashboard-roadmap.spec.js` | Dashboard dynamic | Course_type != Theoretical default, roadmap Step 3 active for Practical, progress ring + lesson Done/Next |
| `04-course-badges.spec.js` | Badges hidden on grid, shown on detail | Grid no DL codes, detail has Restrictions & Eligibility |
| `05-scheduling.spec.js` | Batch + 8h + double booking | Batch UI exists, 08:00-17:00 9h → 422, overlap blocked |
| `06-cancellation-sync.spec.js` | Cancel sync | Student remove queue → slot freed toast, instructor sees no 500 |
| `07-combo.spec.js` | Dynamic combo | Admin can select existing courses via combo_course_ids[], student sees combo |

## 5. BrowserMCP vs Playwright vs DevTools

```bash
# BrowserMCP (needs local Chrome + extension Connect)
gemini extensions install https://github.com/derailed-dash/browsermcp-ext
# Then in Chrome: open http://localhost:8004/drived-hub/login → BrowserMCP extension → Connect → /mcp in Gemini CLI

# Chrome DevTools MCP (Cloud Shell headless)
gemini extensions install https://github.com/ChromeDevTools/chrome-devtools-mcp
# Edit ~/.gemini/extensions/chrome-devtools-mcp/gemini-extension.json → set executable-path /usr/bin/google-chrome --headless
```

## 6. CI

Playwright runs headless in CI; artifacts are `test-results/artifacts/*.png`, `trace.zip`, `video.webm`. Add to `docker-compose.yml`:
```yaml
services:
  e2e:
    image: mcr.microsoft.com/playwright:v1.55.0-jammy
    command: npx playwright test --reporter=html
```

## 7. Teaching the agent Playwright (skill)

The skill is at `.gemini/skills/playwright-cli/SKILL.md`. It adds headed mode:
```bash
playwright-cli open http://localhost:8004 --headed
```
Install via:
```bash
mkdir -p ~/.gemini/skills
npx degit microsoft/playwright-cli/skills/playwright-cli ~/.gemini/skills/playwright-cli
# then append headed snippet to SKILL.md Core section
```
