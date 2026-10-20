# Student & Instructor Pages — Recommended Improvements

**Date:** February 10, 2026  
**Based on:** Code review of all blade views, controllers, and cross-referencing with WORK_SESSION_FEB09_2026.md, WORK_SESSION_JAN25_2026.md, CHANGELOG.md, REVAMP_PLAN.md, PROJECT_REFERENCE.md, and PERFORMANCE_OPTIMIZATION.md

---

## Student Pages

### 1. Student Dashboard (dashboard.blade.php — 672 lines)

| # | Improvement | Rationale |
|---|------------|-----------|
| 1 | **Show theoretical status & practical enrollment eligibility** | `$hasPassedTheoretical` and `$canEnrollPractical` are passed from the controller but never rendered. REVAMP_PLAN calls for students to see their theoretical status and course restrictions. Add a "Phase Status" indicator (e.g., "Theoretical: Passed — You can now enroll in Practical"). |
| 2 | **Show active enrollment info** | `$activeEnrollments` is passed but never used. Students should see their current enrolled course prominently — course name, hours progress, next session. |
| 3 | **Replace hardcoded "Intermediate" level** | "Current Level" always shows `Intermediate` regardless of actual progress. Replace with a calculated value based on `$progressPercentage` (Beginner <30%, Intermediate 30-70%, Advanced >70%) or remove. |
| 4 | **Replace hardcoded estimated test date** | Test date always shows `now()->addWeeks(2)` if progress >= 80%. Either derive from actual course end date/schedule or remove this misleading placeholder. |
| 5 | **Add quick action links to dashboard cards** | Schedule overview and upcoming lessons cards are display-only. Add clickable links ("View Full Schedule", "Book Next Session") — CHANGELOG Dec 3 notes improving navigation from dashboard widgets. |

---

### 2. Student Courses (courses.blade.php — 474 lines)

| # | Improvement | Rationale |
|---|------------|-----------|
| 1 | **Add course type filter (Theoretical/Practical)** | REVAMP_PLAN Part 6.2 specifically calls for filtering by course type. Students need to quickly find theoretical vs practical courses. A filter dropdown or tab would suffice. |
| 2 | **Show enrollment status per course** | Students can click "Enroll Now" even if they already have a pending request or active enrollment. Add visual indicators: "Already Enrolled", "Pending Request", "Completed" badges per course card. |
| 3 | **Add AJAX layout support** | This is the only student page that doesn't support `$isAjax` — it hardcodes `@extends('layouts.app')`. This breaks sidebar AJAX navigation used on other pages. |
| 4 | **Show theoretical requirement for practical courses** | REVAMP_PLAN mandates that practical courses should indicate if theoretical completion is required. Add a notice like "Requires theoretical completion" on practical course cards for students who haven't passed theoretical. |

---

### 3. My Course (my-course.blade.php — 486 lines)

| # | Improvement | Rationale |
|---|------------|-----------|
| 1 | **Fix negative "Hours Remaining"** | If `$hoursCompleted > $hoursRequired`, the remaining hours display goes negative. Add `max(0, $hoursRequired - $hoursCompleted)`. |
| 2 | **Add session scheduling link** | REVAMP_PLAN Part 5.1 includes a "[Schedule Next Session]" button. Currently no direct link to book the next session for the enrolled course. |
| 3 | **Remove emoji from page title** | Title uses "📚 My Course" — contradicts documented user preference (WORK_SESSION_FEB09: "Removed all emoji characters from buttons"). |
| 4 | **Add enrollment action from "Available Courses" section** | When a student has no active course, the available courses section only shows "View Details" with no way to actually enroll. Add an "Enroll Now" or "Request Enrollment" button. |

---

### 4. Student Schedule (schedule.blade.php — 2,423 lines)

| # | Improvement | Rationale |
|---|------------|-----------|
| 1 | **Move database queries from view to controller** | Lines 7-60 contain raw PHP that queries `EnrollmentRequest`, `Booking`, `TimeSlot` directly in the blade file. Duplicates controller logic and violates MVC separation. CHANGELOG "General Cleanup" section mandates moving this to the controller. |
| 2 | **Add AJAX layout support** | Hardcodes `@extends('layouts.app')` — inconsistent with other student pages that support `$isAjax`. |
| 3 | **Extract duplicated sidebar into a Blade partial** | The sidebar (queued bookings + upcoming lessons) is copy-pasted 3 times (My Schedule, Available Schedules, mobile popup). CHANGELOG "General Cleanup" checklist calls for removing duplication. Extract to `@include('school.student.partials.schedule-sidebar')`. |
| 4 | **Remove duplicate CSS animation definitions** | `@keyframes fadeIn` is defined twice in the same file. Consolidate. |

---

### 5. Student Payments (payments.blade.php — 260 lines)

| # | Improvement | Rationale |
|---|------------|-----------|
| 1 | **Fix invalid HTML structure** | Mobile payment cards (`<div>`) are placed inside `<tbody>` as siblings of `<tr>` elements — invalid HTML that renders unpredictably across browsers. Move mobile cards outside the table. |
| 2 | **Add null-safe access for payment relationships** | `$payment->booking->course->title` chain will throw an error if booking or course is deleted. PROJECT_REFERENCE explicitly mandates null-safe operators for all relationship chains. |
| 3 | **Add pagination** | All payments load at once with no pagination. CHANGELOG "General Cleanup" identifies this pattern as needing enhancement. |
| 4 | **Add receipt download or export option** | WORK_SESSION_JAN25 notes that export functionality should be extended to student-facing pages. A "Download Receipt" link per payment would add value. |

---

### 6. Student Profile (profile.blade.php — 778 lines)

| # | Improvement | Rationale |
|---|------------|-----------|
| 1 | **Fix hardcoded "Active" status badge** | Always shows "Active" regardless of actual student status. Display `$student->status` instead. |
| 2 | **Enable Date of Birth saving** | The edit form has a DOB field but the controller's `$request->only()` excludes `date_of_birth` — field is visible but non-functional. Either add it to the controller or remove the field. |
| 3 | **Add password change functionality** | The controller has password change logic but the view has no password change fields. Students can't change their password. CHANGELOG "Low Priority - Student Portal" lists "Profile page - verify edit functionality works." |
| 4 | **Fix CSS typo** | `justify-center` (Tailwind syntax) used instead of valid CSS `justify-content: center`. |
| 5 | **Remove duplicate CSS blocks** | `.profile-avatar-circle` and `.edit-form` styles are each defined twice in the same file. |

---

### 7. Student Progress (progress.blade.php — 324 lines)

| # | Improvement | Rationale |
|---|------------|-----------|
| 1 | **Align progress data with enrollment system** | This page uses the `Progress` model (manually managed by instructors), while the dashboard and my-course pages calculate progress from enrollment hours. These can show conflicting information. Consider merging or clearly labeling the difference. |
| 2 | **Fix "Total Hours" label** | Shows `$progress->course->duration_hours` (course total) but labels it "Total Hours" — misleading since it's not the student's completed hours. Change label to "Course Total Hours" or display actual completed hours. |
| 3 | **Fix dead CSS** | Media queries reference `.container`, `.progress-bar`, `.progress-details`, `.stat-item` which don't exist in the HTML. Actual classes are `.progress-container`, `.progress-bar-container`, etc. |
| 4 | **Add link to course details** | No way to navigate from a progress card to the corresponding course or enrollment. Add a "View Course" link. |

---

## Instructor Pages

### 8. Instructor Dashboard (instructor/dashboard.blade.php — 720 lines)

| # | Improvement | Rationale |
|---|------------|-----------|
| 1 | **Render the Quick Actions section** | ~60 lines of CSS for quick action buttons are defined but never rendered in the HTML. Add a Quick Actions grid with links to: Log Session, View Schedule, My Students, Theoretical Completions. REVAMP_PLAN Part 4.2 calls for "Instructor Dashboard Redesign" with quick access actions. |
| 2 | **Add direct navigation links on cards** | Dashboard cards (upcoming bookings, schedule overview) are display-only. Add "View All" links to navigate to the full schedule and students pages. |
| 3 | **Add session logging shortcut** | REVAMP_PLAN calls for instructors to easily log session completions from the dashboard. Add a "Log Session" button in the Today's Lessons or Schedule Overview card. |

---

### 9. Instructor Schedule (instructor/schedule.blade.php — 2,472 lines)

| # | Improvement | Rationale |
|---|------------|-----------|
| 1 | **Remove debug `alert()` statements** | `selectTimeSlot` and `leaveTimeSlot` functions contain `alert('Select slot clicked: ' + slotId)` debug statements. CHANGELOG "General Cleanup" explicitly requires removing `console.log` and debug statements. |
| 2 | **Move database queries from view to controller** | Lines 1-80 contain complex PHP queries (`TimeSlot::where(...)`, `InstructorRemovalRequest::where(...)`) in the blade file. Same MVC violation as student schedule. |
| 3 | **Resolve schedule.blade.php vs schedule-new.blade.php duplication** | Two versions of the same page exist — `schedule.blade.php` (2,472 lines, with calendar) and `schedule-new.blade.php` (1,024 lines, cleaner but fewer features). Consolidate into one authoritative file. |
| 4 | **Extract duplicated sidebar into partial** | Sidebar markup is repeated 3 times — same issue as student schedule. |
| 5 | **Remove duplicate function definitions** | `selectSlot()` and `selectTimeSlot()` do similar things. Consolidate. |

---

### 10. Instructor Students (instructor/students.blade.php — 648 lines)

| # | Improvement | Rationale |
|---|------------|-----------|
| 1 | **Add pagination** | All students loaded at once — no server-side pagination. Problematic for schools with 100+ students. PERFORMANCE_OPTIMIZATION.md warns about loading full datasets. |
| 2 | **Fix "Grade" label inconsistency** | `avg_progress` is displayed as "Grade" — semantically wrong (progress percentage is not a grade). Use consistent terminology. |
| 3 | **Use AJAX navigation for student card clicks** | Student card onclick uses `window.location.href` (full page reload) instead of `loadContent()` which all other pages use. |
| 4 | **Fix search placeholder** | Says "Select students" instead of "Search students". |

---

### 11. Instructor Student Detail (instructor/student-detail.blade.php — 310 lines)

| # | Improvement | Rationale |
|---|------------|-----------|
| 1 | **Add action buttons** | Page is completely read-only — no ability to add notes, log a session, grade the student, or update progress directly. REVAMP_PLAN Part 4.1 calls for instructors to log sessions per student. Add at least "Log Session" and "Add Progress" buttons. |
| 2 | **Show enrollment/course information** | No current course or enrollment details shown. The instructor can't see what course the student is enrolled in or their overall progress. |
| 3 | **Fix empty 4th grid item** | Progress summary renders a 2x2 grid but only populates 3 items, leaving a visible empty slot. |

---

### 12. Instructor Progress Pages (instructor/progress*.blade.php)

| # | Improvement | Rationale |
|---|------------|-----------|
| 1 | **Add school theming** | Progress views (index, create, edit) use `var(--primary-color)` CSS variables instead of blade-injected `$settings->primary_color`. Visually inconsistent with every other instructor page. |
| 2 | **Add `$isAjax` layout support** | Progress index, create, and edit do NOT support AJAX layout. They always extend `layouts.app`, breaking the sidebar navigation pattern. |
| 3 | **Fix cancel navigation** | Cancel button on create/edit goes to `instructor.students.index` instead of `instructor.progress.index` — navigates to the wrong page. |
| 4 | **Add validation error display** | Create form has no `@error` directives — validation errors from the server are invisible to the user. |
| 5 | **Add hours_completed field to create form** | Form only has "Completion %" but no hours field. REVAMP_PLAN's session completion approach specifically tracks hours. |

---

### 13. Instructor Grades (instructor/grades.blade.php — 930 lines)

| # | Improvement | Rationale |
|---|------------|-----------|
| 1 | **Remove `location.reload()` after AJAX save** | Grade save uses AJAX `fetch()` but then reloads the entire page, defeating the purpose. Keep the page state and update the UI inline. |
| 2 | **Add pagination** | All students loaded at once with no pagination. |
| 3 | **Fix grade saving scope** | Grade can only be saved against the most recent booking (`lastSession.id`). If a student has no bookings, the save silently fails. Should warn the instructor clearly. |

---

### 14. Instructor Reports (instructor/reports.blade.php — 595 lines)

| # | Improvement | Rationale |
|---|------------|-----------|
| 1 | **Fix hardcoded `$totalHoursTaught = count * 2`** | Controller assumes all sessions are 2 hours — inaccurate. Calculate from actual `session_completions.actual_duration_hours` data. |
| 2 | **Add date range filter** | WORK_SESSION_FEB09 "Potential Improvements" specifically lists "Add date range filters to exports" and "Add charts/graphs to Reports & Analytics page". A date picker would let instructors view stats for specific periods. |
| 3 | **Add export functionality** | Instructor reports page has no export option. WORK_SESSION_JAN25 noted progress export buttons still need to be added. |

---

### 15. Instructor Profile (instructor/profile.blade.php — 584 lines)

| # | Improvement | Rationale |
|---|------------|-----------|
| 1 | **Fix hardcoded "Active" status badge** | Same issue as student profile — always shows "Active" regardless of actual instructor status. |
| 2 | **Add password change functionality** | No password change fields despite controller support. Same gap identified for students. |
| 3 | **Add `@error` directives to edit form** | Validation errors from the server are not displayed on individual fields. |
| 4 | **Move `file_exists()` check to controller** | Filesystem operation in the blade view should be in the controller and passed as a boolean. |

---

## Priority Matrix

### Quick Wins (Low effort, high impact)
- Remove debug `alert()` from instructor schedule
- Fix hardcoded "Active" badges on both profiles
- Fix "Hours Remaining" negative value on my-course
- Remove emoji from my-course title
- Fix invalid HTML in payments
- Fix search placeholder text on instructor students
- Fix cancel button navigation on progress create/edit
- Fix CSS typo in student profile
- Fix dead CSS in student progress

### Medium Effort (Significant UX improvement)
- Render unused `$hasPassedTheoretical` / `$activeEnrollments` on student dashboard
- Add course type filtering on courses page
- Add password change to both profiles
- Add pagination to payments, students, grades
- Enable Quick Actions on instructor dashboard
- Add action buttons to student detail page
- Add school theming to progress views
- Add `$isAjax` support to courses, schedule, progress pages
- Enable Date of Birth saving on student profile
- Add enrollment status indicators on course cards

### Larger Effort (Structural fixes)
- Move DB queries out of schedule blade files into controllers
- Extract duplicated sidebar into Blade partials
- Resolve schedule/schedule-new duplication
- Align Progress model data with enrollment system
- Fix hardcoded hours calculation in instructor reports

---

## Cross-Cutting Issues (Affect Multiple Pages)

| Issue | Affected Pages |
|-------|---------------|
| **AJAX layout inconsistency** — some views support `$isAjax`, some don't | courses, student schedule, instructor progress (index/create/edit) |
| **Hardcoded values** (level, status, hours formula) | student dashboard, both profiles, instructor reports |
| **View-level DB queries** bypassing controller | student schedule, instructor schedule |
| **Duplicate markup** for mobile/desktop or sidebar | student dashboard, student schedule, instructor schedule |
| **Invalid HTML** (divs in tbody) | student payments |
| **Dead CSS** (references to nonexistent classes) | student progress |
| **No password change UI** despite controller support | student profile, instructor profile |
| **No pagination** on heavy lists | student payments, instructor students, instructor grades |
| **Inconsistent navigation** (some use `loadContent()`, some `window.location.href`) | instructor students, instructor student-detail, instructor grades |
| **No `@error` validation display** | instructor progress-create, instructor profile edit |

---

*This document contains only verified findings from direct code review. All rationale references are traceable to existing project documentation.*
