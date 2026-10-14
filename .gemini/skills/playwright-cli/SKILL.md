# Playwright CLI Skill – Agentic UI Testing (codelab #7)
# Progressive disclosure: loaded only when agent needs Playwright (codelab #7)

This skill teaches Gemini CLI / agentic agents to use Playwright CLI for DriveED Hub.

## Core
- Install globally: `npm install -g @playwright/cli@latest` then `npm install @playwright/test`
- Install browsers: `npx playwright install --with-deps chromium chrome`
- Install skill: `npx degit microsoft/playwright-cli/skills/playwright-cli ~/.gemini/skills/playwright-cli`
- Teach headed mode: add to SKILL.md Core section:
  ```
  # Run in headed mode so we can see the browser
  playwright-cli open https://playwright.dev --headed
  playwright-cli open http://localhost:8004 --headed
  ```

## Commands for DriveED Hub (baseURL http://localhost:8004, school slug drived-hub)
```bash
# Spawn headed for debugging
playwright-cli open http://localhost:8004 --headed

# Run all agentic tests
npx playwright test --headed
npx playwright test --ui
npx playwright show-report

# Single suite (intent-based)
npx playwright test tests/e2e/01-enrollment-500.spec.js --headed
npx playwright test tests/e2e/03-dashboard-roadmap.spec.js --headed
```

## Intent → Implementation mapping (codelab #4: intent over selectors)
- NEVER use brittle `div > div > button`. Use `getByRole`, `getByLabel`, `getByText` with regex /i.
- Screenshots as artifacts: `test-results/artifacts/<name>.png` (codelab #9 Antigravity artifacts)
- Use `assertNo500` helper after every enrollment/scheduling action.
- Auth: use `tests/fixtures/auth.js` – `login(page,'admin')` handles CSRF, waitForLoadState.

## When to use BrowserMCP vs Playwright vs Chrome DevTools MCP
| Tool | When | How |
|------|------|-----|
| BrowserMCP | Local Chrome with extension, need eyes/hands fast | `gemini extensions install https://github.com/derailed-dash/browsermcp-ext` then `/mcp` |
| Playwright | CI/headless, screenshots, videos, parallel | `playwright-cli open` + `npx playwright test` |
| Chrome DevTools MCP | Cloud Shell headless without extension | `gemini extensions install https://github.com/ChromeDevTools/chrome-devtools-mcp` + `--headless --executable-path=/usr/bin/google-chrome` |

## DriveED Hub specific flows (teach agent)
1. **Enrollment 500**: Must test both `student_license` and `credential_file` field names; double-click submit to test race guard.
2. **Scoping**: Admin `/drived-hub/admin/user-management` shows all; Instructor `/drived-hub/instructor/students` shows single `.students-grid`.
3. **Dashboard**: Check `.info-card` course_type != hardcoded Theoretical; roadmap `.journey-step.active` should be Step 3 for Practical.
4. **Badges**: Grid `.course-badges` must NOT contain DL codes; detail `/student/courses/<id>` must contain `Restrictions & Eligibility`.
5. **Scheduling**: Batch via `batch_dates[]`; max 8h via 08:00-17:00 => 422; overlap via same instructor same date/time.
6. **Cancellation**: Student `removeFromQueue` should toast `slot freed` and notify instructor.
7. **Combo**: Admin `/admin/courses` combo_type bundle checkboxes `combo_course_ids[]`.
