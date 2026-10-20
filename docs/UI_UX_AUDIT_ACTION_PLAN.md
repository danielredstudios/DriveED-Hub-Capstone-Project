# UI/UX Audit Action Plan

> Created: March 1, 2026  
> Source inputs: `docs/PANELIST_FEEDBACK_COMPARISON.md`, `docs/PANELIST_FEEDBACK_PRIORITIES.md`, and current static UI audit

---

## Purpose

This checklist translates audit findings into actionable work items with:
- Priority (`P0` = urgent, `P1` = high, `P2` = medium)
- Scope (target files/pages)
- Acceptance criteria (how we verify completion)

---

## Status Legend

- [ ] Not started
- [~] In progress
- [x] Completed
- [!] Blocked

---

## Phase 1 — Critical Content and Clarity Fixes (P0)

### 1) Fix text encoding/misrendered symbols (mojibake)
**Priority:** P0  
**Why:** User-visible trust and readability issue

**Target files (confirmed):**
- `resources/views/school/guest/courses.blade.php`
- `resources/views/school/guest/dashboard.blade.php`

**Known bad text examples:**
- `âœ“`, `âœ•`, `âš `, `â„¹`, `â­`, `âš ï¸`, `âœ“ Copied!`

**Tasks:**
- [x] Replace corrupted symbols with valid UTF-8 characters or inline SVG icons.
- [x] Ensure files are saved as UTF-8 consistently.
- [x] Rebuild views and verify rendering.

**Acceptance criteria:**
- No mojibake strings remain in guest-facing pages.
- Flash messages and badges display proper symbols/icons.
- `php artisan view:clear` and `php artisan view:cache` succeed.

---

### 2) Enforce user-facing terminology consistency (Schedule vs Booking)
**Priority:** P0  
**Why:** Reduces cognitive load and aligns with approved panel direction

**Primary target files (confirmed):**
- `resources/views/school/admin/bookings.blade.php`
- `resources/views/school/student/schedule.blade.php`

**Tasks:**
- [x] Replace user-facing “booking/bookings” where appropriate with “schedule/schedules”.
- [x] Keep internal model/route naming untouched unless explicitly required.
- [x] Standardize microcopy for queue actions (request/confirm/cancel).

**Acceptance criteria:**
- Public labels are consistent within each page and workflow.
- No mixed “Book Lesson / Request Booking / Confirm Booking” language in the same interaction path.
- Existing route/controller behavior remains unchanged.

---

## Phase 2 — Navigation and UX Consistency (P1)

### 3) Clarify session lifecycle navigation labels
**Priority:** P1

**Target file:**
- `resources/views/layouts/app.blade.php`

**Current potentially confusing cluster:**
- `Schedules`
- `Student Sessions`
- `Session Completions`
- `Phase Progressions`

**Tasks:**
- [x] Add concise helper labels/tooltips/subtitles in nav or page headers.
- [x] Ensure each page clearly states its lifecycle stage and actions.

**Acceptance criteria:**
- A first-time admin can distinguish each page purpose from label + subtitle alone.
- No duplicate-looking menu labels without clarifying context.

---

### 4) Improve action copy in student scheduling flow
**Priority:** P1

**Target file:**
- `resources/views/school/student/schedule.blade.php`

**Tasks:**
- [x] Define a single copy pattern for each state:
  - Available slot
  - Queued request
  - Confirmed schedule
  - Cancelled schedule
- [x] Update confirmation modal titles/messages to match pattern.
- [x] Update fallback toast/notification text for contextual clarity.

**Acceptance criteria:**
- Every button label maps to one clear backend outcome.
- Confirmation prompts are unambiguous and stateful.
- Success/error messages tell users what changed and what happens next.

---

## Phase 3 — Accessibility and Readability Improvements (P1)

### 5) Add accessibility labels to icon-only controls
**Priority:** P1

**Sample targets:**
- `resources/views/school/guest/courses.blade.php`
- `resources/views/school/register.blade.php`
- `resources/views/school/student/schedule.blade.php`
- Additional modals/alerts with `&times;` close buttons

**Tasks:**
- [x] Add `aria-label` for all icon-only close/action buttons.
- [x] Ensure keyboard focus indicators remain visible.

**Progress note (March 1, 2026):**
- Completed for sample targets:
  - `resources/views/school/student/schedule.blade.php`
  - `resources/views/school/guest/courses.blade.php`
  - `resources/views/school/register.blade.php`
- Optional next pass: broader app-wide sweep for any remaining icon-only controls outside sampled pages.

**Acceptance criteria:**
- All icon-only buttons have accessible names.
- Keyboard-only users can operate modal close controls reliably.

---

### 6) Increase small text/button readability in high-traffic screens
**Priority:** P1

**Primary target:**
- `resources/views/school/student/schedule.blade.php`

**Tasks:**
- [x] Raise minimum interactive text size in key CTA regions.
- [x] Increase compact button heights where needed for touch comfort.

**Acceptance criteria:**
- CTA buttons and queue actions are readable on mobile without zoom.
- Touch targets are consistently usable.

---

## Phase 4 — Design System Hardening / Maintainability (P2)

### 7) Reduce inline-style sprawl on top-risk pages
**Priority:** P2  
**Why:** Prevents future visual inconsistency and regressions

**High-inline-style pages identified:**
- `resources/views/school/admin/settings.blade.php`
- `resources/views/school/student/schedule.blade.php`
- `resources/views/school/admin/enrollment-requests/index.blade.php`
- `resources/views/school/guest/dashboard.blade.php`
- `resources/views/school/admin/courses.blade.php`

**Tasks:**
- [x] Extract repeated inline styles into reusable classes/partials.
- [x] Standardize shared components (alerts, badges, section cards, modal headers, action buttons).
- [x] Keep role-specific visual differences intentional and documented.

**Progress note (March 1, 2026):**
- Started in `resources/views/school/student/schedule.blade.php`:
  - Extracted repeated queue action button inline styles into reusable classes (`queue-action-*`).
  - Replaced repeated queue metadata inline text styles with shared class (`queue-meta`).
  - Standardized mobile queue trigger sizing through shared class (`mobile-queue-btn`).
- Continued in `resources/views/school/guest/dashboard.blade.php`:
  - Refactored the dev test-credentials modal from inline style attributes to reusable `tc-*` classes.
  - Corrected modal emoji mojibake in that block (`🎉`, `💡`).
  - Extracted the enrollment-upgrade guidance callout (container/icon/title/text/logout button) into reusable classes.
- Continued in `resources/views/school/admin/enrollment-requests/index.blade.php`:
  - Extracted branch banner, branch filter, action toolbar, export menu, and shared form display styles into reusable classes.
  - Refactored reject/cancel/license-reject modals into shared `action-modal-*` classes.
  - Reduced inline style usage from dense page-level clusters to mainly residual icon-size attributes.
- Continued in `resources/views/school/admin/settings.blade.php` (focused pass):
  - Extracted login preview notice and login background controls (image preview + opacity row + helper text) into reusable classes.
  - Replaced related inline select/checkbox/layout styles in that section with shared utility classes.
  - Extracted tabs container and preview clusters (buttons, modal, calendar preview grid/header, status badges) into reusable classes.
  - Removed inline style attributes from those preview blocks while keeping existing JS preview updates intact.
  - Extracted color preview cluster (background preview + swatches) and sidebar preview list into reusable classes.
  - Kept script-facing IDs unchanged (`background-preview`, `primary-color-swatch`, `secondary-color-swatch`, `accent-color-swatch`, `sidebar-preview`) for behavior parity.
  - Replaced repeated micro-inline patterns (helper text block spacing, checkbox spacing, compact selects, save section divider, login intro text) with shared utility classes.
  - Finalized remaining isolated inline styles in this file (background image preview, opacity controls, and helper text variants); current inline style count is now 0 in `admin/settings`.
- Continued in `resources/views/school/admin/courses.blade.php`:
  - Converted card/list/modal static inline style clusters to reusable classes.
  - Refactored guest preview template-string markup to class-based styles (including package cards and feature grids).
  - Current inline style count is now 0 in `admin/courses`.
- Continued in `resources/views/school/guest/dashboard.blade.php` (focused pass):
  - Extracted remaining icon sizing and small helper-text inline styles in onboarding/license/action sections to shared utility classes (`icon-*`, `icon-shrink`, `icon-inline-leading`, `license-status-note`).
  - Replaced dynamic onboarding progress width inline binding with discrete progress classes (`progress-0/25/50/75/100`) based on completed-step percentage.
  - Current inline style count is now 0 in `guest/dashboard`.
- Continued in `resources/views/school/admin/enrollment-requests/index.blade.php` (focused pass):
  - Replaced residual fixed-size SVG inline styles in stat cards and reject/cancel controls with shared utility classes (`icon-24`, `icon-14`).
  - Current inline style count is now 0 in `admin/enrollment-requests/index`.
- Continued in `resources/views/school/student/schedule.blade.php` (focused completion pass):
  - Replaced remaining static inline styles across alerts, section headers, schedule cards, queue sidebars/popups, available-schedule action rows, and confirm-dialog markup with reusable classes.
  - Refactored button enable/disable visual updates from direct inline style writes to class toggling (`book-now-btn-disabled`).
  - Current inline style count is now 0 in `student/schedule`.
- Continued in shared admin component consistency pass:
  - `resources/views/school/admin/courses.blade.php`: removed unused local alert/close-button CSS and switched modal footer actions to shared admin button variants (`btn btn-primary|btn-secondary`) with a scoped sizing utility (`course-modal-btn`).
  - `resources/views/school/admin/enrollment-requests/index.blade.php`: removed unused local `.alert` block to avoid divergence from shared admin alert/flash styles.
  - `resources/views/school/admin/settings.blade.php`: removed a duplicate early `.alert` definition block, leaving a single alert style source in-file.
  - `resources/views/school/guest/dashboard.blade.php`: replaced copy-button background style mutations with class toggling (`tc-copy-btn-copied`) for cleaner behavior parity.
- Role-specific differences explicitly retained and documented:
  - `admin/*` pages keep stronger data-density patterns (stat grids, management action bars, compact table controls).
  - `student/schedule` preserves schedule/queue-first framing and state-specific status emphasis.
  - `guest/dashboard` retains onboarding-focused hierarchy and test-credential helper modal semantics (local env only).
  - Shared primitives (alerts/buttons/modal actions) are aligned, while role workflows remain intentionally distinct.

**Acceptance criteria:**
- Significant reduction of inline style attributes on the top 5 pages.
- Shared components render consistently across admin/instructor/student/guest.

---

## Validation Checklist (for every completed item)

- [ ] `php artisan view:clear`
- [ ] `php artisan view:cache`
- [ ] Focused page-load/manual checks on modified pages
- [ ] No regressions in wording consistency
- [ ] No obvious UI breakpoints at mobile widths

---

## Execution Order Recommendation

1. Phase 1 (P0): Encoding + terminology consistency  
2. Phase 2 (P1): Navigation and scheduling flow copy  
3. Phase 3 (P1): Accessibility and readability  
4. Phase 4 (P2): Maintainability/design hardening

---

## Notes

- This plan respects already approved panel changes and avoids reverting validated work.
- Where terminology conflicts with internal route/model naming, only user-facing labels are updated.
- Keep changes small and test after each phase to avoid broad regressions.
