# Panelist Feedback — Priority Implementation Plan

> Compiled: February 13, 2026
> Source: Defense panel compiled feedback (39 items)

---

## Status Legend
- ✅ Already Implemented / No Work Needed
- ✔️ Completed This Session
- 🔄 In Progress
- ⏳ Pending
- ❌ Skipped / Out of Scope

---

## PRIORITY 1 — Fix Student Dashboard ✔️ COMPLETED

**Panelist Items:** #4, #5, #6, #7, #8, #9, #10, #11, #12, #13, #37

| # | Feedback | Resolution |
|---|----------|------------|
| 4 | Fix profile display (name/picture) | ✔️ Shows actual profile picture if uploaded, falls back to initial letter |
| 5 | "Lesson Completed" should show 0 out of total | ✔️ Now shows "Sessions Completed: X of Y" from real session completion records |
| 6 | Clarify progress metrics (counts, not just %) | ✔️ Shows session count + hours with totals, progress bar with % |
| 7 | Display valid lesson hours (hours consumed) | ✔️ "Hours Completed: X / Y hrs" from actual session_completions data |
| 8 | Remove/rename "hours driven" | ✔️ Renamed to "Hours Completed" — sourced from logged session hours |
| 9 | Clarify progress = theoretical lessons | ✔️ Added "Course Type" (Theoretical/Practical) and "Theoretical Status" to Enrollment Status card |
| 10 | "Status" should say Progress, not be numerical | ✔️ "Progress: X%" with visual progress bar |
| 11 | Avoid redundant sections | ✔️ Removed "Goals & Achievements" card (had fake data), replaced with "Enrollment Status" |
| 12 | Organize achievements, progress, lessons, PII | ✔️ 3-card layout: Learning Progress / Upcoming Lessons / Enrollment Status |
| 13 | Visualizations should reflect meaningful data | ✔️ Removed all hardcoded values ("Intermediate", "Skills Mastered X/10", "Test Readiness", "Est. Test Date") |
| 37 | Dashboard numbers must be real, not placeholders | ✔️ All values now from database queries (session_completions, enrollment_requests, bookings) |

**Also verified:** Admin dashboard — all stats are real (database queries, month-over-month growth calculations). No changes needed.

**Files changed:**
- `app/Http/Controllers/StudentController.php` — Rewrote dashboard() method
- `resources/views/school/student/dashboard.blade.php` — Fixed all 3 cards (desktop + mobile)

---

## PRIORITY 2 — Update Role-Specific Labels ✔️ COMPLETED

**Panelist Items:** #2, #3

| # | Feedback | Resolution |
|---|----------|------------|
| 2 | Replace "Booking" with "Schedule" | ✔️ All user-facing text updated across all roles. Backend model/DB/routes unchanged. |
| 3 | Labels should be accurate & not misleading | ✔️ Covered by the label rename — all headings, buttons, messages now use "Schedule" terminology. |

**Scope:** Page headings, sidebar nav labels, button text, flash messages, empty states, confirm dialogs. NOT the database table, model, controller, or routes.

**Files changed (11 files, labels only):**
- `layouts/app.blade.php` — Admin sidebar: "Bookings" → "Schedules"
- `admin/bookings.blade.php` — Page title, heading, subtitle, empty state, JS confirm, toast messages
- `admin/settings.blade.php` — "Booking Settings" → "Scheduling Settings", all related labels/help text
- `admin/reports/index.blade.php` — Export label, stat cards, section headings, empty states
- `admin/progress.blade.php` — "Total Bookings" → "Total Schedules", "Recent Bookings" → "Recent Schedules"
- `student/schedule.blade.php` — "Booked Schedule" → "My Schedule", "Book Now" → "Schedule Now", "Book a Lesson" → "Schedule a Lesson"
- `instructor/dashboard.blade.php` — "Student & Bookings" → "Student & Schedules", "Upcoming Bookings" → "Upcoming Schedules"
- `instructor/schedule-new.blade.php` — "student(s) booked" → "student(s) scheduled", "No students booked" → "No students scheduled" (3 instances)
- `BookingController.php` — All 18 user-facing flash/JSON messages updated
- `guest/dashboard.blade.php` — "booking system" → "scheduling system"
- `system-admin/schools.blade.php` — "bookings" → "schedules" in delete warning

---

## PRIORITY 3 — Simplify Instructor Interface ✔️ COMPLETED

**Panelist Items:** #32, #33, #34, #35, #36

| # | Feedback | Resolution |
|---|----------|------------|
| 32 | Reassess if instructors truly need a dashboard | ✔️ Simplified dashboard: removed redundant "Student & Schedules" metrics card and "Recent Progress Updates" card. Kept only essential info: 4 stat cards + Schedule Overview + Upcoming Schedules + Quick Actions. |
| 33 | Instructors may only need: student list, schedule, grades input | ✔️ Sidebar trimmed to 5 items: Dashboard, My Schedule, My Students, Session Logging, Grades. Removed Reports (admin function). |
| 34 | School admin — not instructor — should create courses | ✅ Already done — instructors only have read-only access to course modules |
| 35 | Limit instructor access to essential functions | ✔️ Removed Reports from sidebar nav. Removed dead bookings()/showBooking() controller methods. Removed 5 redundant dashboard queries. |
| 36 | Remove unnecessary features from instructor dashboard | ✔️ Dashboard reduced from 4 detail cards to 2 + Quick Actions. Removed redundant duplicate metrics. Added Quick Actions for fast navigation (My Schedule, Log Session, Grade Students, My Students). |

**Files changed:**
- `layouts/app.blade.php` — Removed "Reports" from instructor sidebar (6 items → 5)
- `instructor/dashboard.blade.php` — Rewritten: removed "Student & Schedules" metrics card (5 redundant metrics) and "Recent Progress Updates" card (redundant with Session Logging). Added Quick Actions row. Cleaned up unused CSS (~200 lines removed).
- `InstructorController.php` — Removed 5 unnecessary queries from dashboard() (totalCompleted, monthlyBookings, completedThisMonth, studentIds, recentProgress). Removed dead bookings() and showBooking() methods (no routes pointed to them).

---

## PRIORITY 4 — Itemize Lessons in Student My-Course Page ✔️ COMPLETED

**Panelist Item:** #1

| # | Feedback | Resolution |
|---|----------|------------|
| 1 | Modules should be itemized clearly, not grouped per module | ✔️ Each module now expands to show individual lesson titles with numbered indicators. Click a module header to toggle its lesson list open/closed. |

**Files changed:**
- `student/my-course.blade.php` — Modules now render as expandable accordion items. Each module shows its lesson count badge + expand arrow. Clicking reveals numbered lesson list underneath. Added CSS for `.module-header`, `.lesson-list`, `.lesson-item`, `.lesson-number`, `.module-toggle` styles. Added JS `toggleModule()` function. Also fixed sort from `sortBy('order')` to `sortBy('sort_order')` to match the DB column. No controller changes needed (lessons were already eager-loaded).

---

## PRIORITY 5 — Report Visuals & Growth Indicators ✔️ COMPLETED

**Panelist Items:** #25, #26

| # | Feedback | Resolution |
|---|----------|------------|
| 25 | Improve report visuals for easier reading | ✔️ Added: time period filter bar (Today/Week/Month/Year/All), visual horizontal bar charts for course enrollment, progress bars on all completion rate columns (courses, instructors, students), zebra-striped tables, color-accented stat boxes, proper progress-bar CSS (was missing/broken), mobile responsive layout. |
| 26 | Growth indicators should be based on valid data | ✅ Verified — admin dashboard uses real month-over-month calculations from DB queries. Reports page enrollment growth is computed from actual enrollment_date data. |

---

## PRIORITY 6 — Navigation & Interface Cleanup ⏳ PENDING

**Panelist Items:** #38, #39

| # | Feedback | Plan |
|---|----------|------|
| 38 | Navigation should be intuitive (no redundant pages) | Audit sidebar navigation across all roles, remove duplicate/redundant links |
| 39 | Keep interface minimal, essential, aligned with driving school ops | Consolidate pages where possible |

---

## ALREADY IMPLEMENTED — No Work Needed ✅

| # | Feedback | Status |
|---|----------|--------|
| 16 | Data Privacy Notice in registration | ✅ Modal popup + checkbox exists in register.blade.php |
| 17 | Terms and Conditions before account creation | ✅ Modal popup + checkbox exists in register.blade.php |
| 18 | OTP email verification | ✅ Full flow: verify-email page, OTP input, resend verification |
| 19 | Improve auth validation | ✅ Phase 1 security: rate limiting, account lockout, guard fixes, strong passwords |
| 24 | PDF export for reports | ✅ Both PDF and Excel exports exist for all major data types |
| 30 | Define user types clearly | ✅ Guest, Student, Instructor, School Admin, System Admin all exist |
| 34 | School admin creates courses, not instructor | ✅ Course CRUD is admin-only; instructors have read-only module/lesson access |

---

## SKIPPED — Out of Scope ❌

| # | Feedback | Reason |
|---|----------|--------|
| 14 | Lessons should auto-unlock sequentially (LMS) | Deferred — too complex for current phase. Noted for future implementation. |
| 15 | Completed lessons must trigger assessments | Deferred — needs quiz/assessment system. Noted for future. |
| 20 | Admin dashboard should show # of students and vehicles | Students: ✅ already shown. Vehicles: ❌ skipped (no vehicle system). |
| 21 | Add list of vehicle types (manual, auto, car models) | ❌ Skipped — vehicle management out of scope |
| 22 | Add vehicle maintenance reminders | ❌ Skipped — out of scope |
| 23 | Vehicle tracking not needed, just asset listing | ❌ Skipped — out of scope |

---

## DEFERRED — Future Consideration 🔮

| # | Feedback | Notes |
|---|----------|-------|
| 14-15 | LMS auto-unlock + assessments | Needs DB changes (lesson_completions table, quiz system). Can be Phase 2. |
| 27 | Main owner account for multiple branches | Simplest approach: add branch grouping (name, address) with branch_id on students/instructors. School Admin sees all branches with filter. |
| 28 | Branches under one driving school, one owner | Same as #27 — branch model as grouping mechanism, not separate interfaces. |
| 29 | Improve multi-tenant for multiple locations | Same as #27-28. Estimated effort: ~2-3 hours if pursued. |
| 31 | Provide role definitions in terminology section | Can add to system documentation or about page. Low priority. |

---

## Quick Reference — All 39 Items

| # | Category | Item | Status |
|---|----------|------|--------|
| 1 | A. LMS | Itemize lessons clearly | ✔️ Done |
| 2 | A. LMS | Replace "Booking" → "Schedule" | ✔️ Done |
| 3 | A. LMS | Accurate labels/terms | ✔️ Done |
| 4 | B. Student Dashboard | Fix profile display | ✔️ Done |
| 5 | B. Student Dashboard | Lessons 0 of total | ✔️ Done |
| 6 | B. Student Dashboard | Clarify progress metrics | ✔️ Done |
| 7 | B. Student Dashboard | Valid lesson hours | ✔️ Done |
| 8 | B. Student Dashboard | Remove "hours driven" | ✔️ Done |
| 9 | B. Student Dashboard | Clarify = theoretical | ✔️ Done |
| 10 | B. Student Dashboard | Status → Progress | ✔️ Done |
| 11 | B. Student Dashboard | Remove redundant sections | ✔️ Done |
| 12 | B. Student Dashboard | Organize dashboard | ✔️ Done |
| 13 | B. Student Dashboard | Meaningful visualizations | ✔️ Done |
| 14 | C. LMS Flow | Auto-unlock lessons | 🔮 Deferred |
| 15 | C. LMS Flow | Assessments between lessons | 🔮 Deferred |
| 16 | D. Registration | Data Privacy Notice | ✅ Already done |
| 17 | D. Registration | Terms and Conditions | ✅ Already done |
| 18 | D. Registration | OTP email verification | ✅ Already done |
| 19 | D. Registration | Improve auth validation | ✅ Already done |
| 20 | E. Admin Dashboard | # of students + vehicles | ✅/❌ Students shown, vehicles skipped |
| 21 | E. Admin Dashboard | Vehicle types list | ❌ Skipped |
| 22 | E. Admin Dashboard | Vehicle maintenance reminders | ❌ Skipped |
| 23 | E. Admin Dashboard | Just asset listing | ❌ Skipped |
| 24 | F. Reports | PDF export | ✅ Already done |
| 25 | F. Reports | Improve report visuals | ✔️ Done |
| 26 | F. Reports | Valid growth indicators | ✅ Verified real data |
| 27 | G. Multi-Tenancy | Main owner for branches | 🔮 Deferred |
| 28 | G. Multi-Tenancy | Branches under one owner | 🔮 Deferred |
| 29 | G. Multi-Tenancy | Multiple locations | 🔮 Deferred |
| 30 | H. Roles | Define user types | ✅ Already done |
| 31 | H. Roles | Role definitions in terms | 🔮 Deferred |
| 32 | H. Roles | Reassess instructor dashboard | ✔️ Done |
| 33 | H. Roles | Instructor: students, schedule, grades | ✔️ Done |
| 34 | H. Roles | Admin creates courses | ✅ Already done |
| 35 | I. Instructor | Limit instructor access | ✔️ Done |
| 36 | I. Instructor | Remove unnecessary features | ✔️ Done |
| 37 | J. Additional | Real dashboard numbers | ✔️ Done |
| 38 | J. Additional | Intuitive navigation | ⏳ Priority 6 |
| 39 | J. Additional | Minimal essential interface | ⏳ Priority 6 |

---

*Last Updated: February 13, 2026*
