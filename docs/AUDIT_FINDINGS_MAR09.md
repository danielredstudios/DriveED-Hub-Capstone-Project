# Audit Findings — March 9, 2026 (Revised)

---

## Issue 1: Enrollment Checkboxes — Not a Bug (with caveats)

### Conclusion
The checkboxes **are fully functional**. They power bulk approve/reject via a floating action bar that appears on selection. **No removal needed.**

### Wiring Confirmed
| Component | Location |
|-----------|----------|
| Select-all checkbox | `enrollment-requests/index.blade.php` line 912 |
| Row checkboxes (pending only) | `enrollment-requests/index.blade.php` line 929 |
| Floating bulk actions bar | `enrollment-requests/index.blade.php` line 860 |
| JS: `toggleSelectAll()` | `enrollment-requests/index.blade.php` line 1379 |
| JS: `updateBulkActions()` | `enrollment-requests/index.blade.php` line 1388 |
| JS: `bulkApprove()` | `enrollment-requests/index.blade.php` line 1408 |
| JS: `bulkReject()` | `enrollment-requests/index.blade.php` line 1453 |
| Controller: `bulkApprove()` | `EnrollmentRequestController.php` line 526 |
| Controller: `bulkReject()` | `EnrollmentRequestController.php` line 617 |
| Routes | `web.php` lines 265–266 |

### Residual Edge Cases Found

| # | Issue | Severity |
|---|-------|----------|
| 1a | `bulkApprove` skips `approved` status but **does not skip `rejected` or `cancelled`** — a crafted POST could re-approve a previously rejected enrollment | Medium |
| 1b | `bulkApprove` does not null-check after `EnrollmentRequest::find($id)` — race condition between validation and execution could throw `Trying to get property of null` | Low |
| 1c | `bulkApprove` calls `$enrollment->student->update()` twice (role + course_locked) instead of once — wasteful, creates brief inconsistent state | Low |
| 1d | `bulkReject` uses raw `prompt()` with no SweetAlert confirmation, while `bulkApprove` uses `showConfirm()` — asymmetric UX | Low |
| 1e | `$errors = []` declared in `bulkApprove` (line 539) but never used — dead code (code hygiene, not operational risk) | Info |

---

## Issue 2: Admin Schedule Pagination — Confirmed (deeper than reported)

### Root Cause
`AdminController::schedules()` (line ~1118) reads `start_date`/`end_date` from query params, defaults to today → today+30 days, then loads all matching records with `->get()->groupBy('date')`.

### Gaps Found (beyond original report)

| # | Gap | Detail |
|---|-----|--------|
| 2a | **No date filter UI affordance** | The blade has no `start_date`/`end_date` inputs. The only date input (line 1724) is inside the Create Schedule modal. The range is technically adjustable via querystring (`?start_date=...&end_date=...`), but there is no UI affordance — normal users have no way to discover or use this. |
| 2b | **No input validation** | `start_date`/`end_date` query params are consumed raw — no date format validation, no ordering check (`start < end`), no max span limit. A crafted URL like `?start_date=2020-01-01&end_date=2030-12-31` would load 10 years of data. |
| 2c | **Calendar month navigation is disconnected from data filtering** | The calendar view's `changeMonth()` JS navigates via `?month=YYYY-MM` (line 2058), but the controller doesn't read a `month` param — it only reads `start_date`/`end_date`. The calendar header changes visual context (month label updates), but the underlying dataset remains the default 30-day window. This is misleading rather than fully broken — the UI suggests navigation occurred, but the data doesn't follow. |
| 2d | **Dead variables** | `$startDate` and `$endDate` are passed to the view but never referenced in the blade template. |

### Refined Proposal

1. **Add date range validation** in the controller:
   - Validate `start_date`/`end_date` as proper dates, `start_date <= end_date`
   - Cap max span to 90 days (configurable)
2. **Add date filter UI** in the blade — two date inputs + "Filter" button, pre-populated with `$startDate`/`$endDate`
3. **Wire calendar navigation** — make `changeMonth()` set `start_date=YYYY-MM-01&end_date=YYYY-MM-{last_day}` so calendar month nav actually works
4. Standard `paginate()` is an option if grouping is done after slicing (works for list view; calendar view needs full month data anyway)

**Files:** `app/Http/Controllers/AdminController.php` (~line 1118), `resources/views/school/admin/schedules.blade.php`

---

## Issue 3: Instructor Schedule `format()` on String — Confirmed

### Error
`Call to a function format() on string`

### Root Cause
`InstructorTimeSlotController.php` lines 255–256:
```php
'start' => $slot->start_time->format('H:i'),
'end' => $slot->end_time->format('H:i'),
```
The previous audit changed `TimeSlot`'s casts (line 28–29) from `datetime:H:i` to `string`. This means `$slot->start_time` returns a raw DB string (`"09:30:00"`), not a Carbon instance — calling `->format()` on it crashes.

### Time Format Audit (full codebase)

| Layer | Dominant format | Count | Notes |
|-------|----------------|-------|-------|
| **Database (MySQL TIME)** | `H:i:s` | — | Column type forces `HH:MM:SS` storage |
| **Validation (input)** | `H:i` | 7/7 rules | All `date_format:H:i` — unanimous |
| **Internal/computed** | `H:i` | ~14 uses | `Carbon::parse()->format('H:i')` |
| **Display (user-facing)** | `g:i A` | ~50 uses | Dominant display; `h:i A` has ~25 uses |
| **SchedulingConflictService gap slots** | `H:i:s` | 4 uses | Inconsistent with the rest |

**Observed usage pattern:** Storage is `H:i:s` (MySQL enforced). Application-level internal usage is predominantly `H:i` (~14 uses vs 4 `H:i:s`). Display is predominantly `g:i A` (~50 uses vs ~25 `h:i A`). However, the dominant pattern is not an architectural decision — no documented contract exists. **Decision required before implementation:** should the canonical app-layer format be `H:i` or `H:i:s`? All API consumers (including `SchedulingConflictService`) must be audited against whichever is chosen.

### Additional Risks Found

| # | Risk | Location | Severity |
|---|------|----------|----------|
| 3a | `SchedulingConflictService::getAvailableTimeSlots()` returns `H:i:s` while `checkInstructorAvailability()` returns `H:i` — consumers may break on inconsistency | `SchedulingConflictService.php` lines 118–119 vs 54–55 | Medium |
| 3b | Admin schedule blade outputs raw `$timeslot->start_time` (`09:30:00`) in `data-start-time` attributes, while student schedule formats to `H:i` first — JS consumers may behave differently | `schedules.blade.php` lines 1486–1487 | Medium |
| 3c | Display inconsistency: student/instructor views use `g:i A` (no leading zero), admin views use `h:i A` (leading zero) | Multiple files | Low |

### Refined Proposal

**Centralized fix** — add accessors to the `TimeSlot` model with explicit null/invalid guards:
```php
// In TimeSlot model — formatted accessors with null safety
public function getFormattedStartTimeAttribute(): ?string
{
    if ($this->start_time === null) {
        return null;
    }
    return \Carbon\Carbon::parse($this->start_time)->format('H:i');
}

public function getFormattedEndTimeAttribute(): ?string
{
    if ($this->end_time === null) {
        return null;
    }
    return \Carbon\Carbon::parse($this->end_time)->format('H:i');
}
```

Then in `InstructorTimeSlotController.php` line 255–256:
```php
'start' => $slot->formatted_start_time,
'end' => $slot->formatted_end_time,
```

**Safety notes:**
- Null guard prevents crash on records with missing time values
- `Carbon::parse()` is tolerant of both `H:i` and `H:i:s` input (MySQL TIME returns `H:i:s`), so this is safe for the known DB column type
- Note: `Carbon::parse()` can still throw on malformed non-null values (e.g. corrupted data). For defense-in-depth, a try/catch or pre-validation could be added, though the risk is low given values originate from a MySQL `TIME` column with validated input
- Parsing overhead is negligible for the record counts involved (schedule pages load tens, not thousands)
- The chosen output format (`H:i`) must match the decided canonical contract (see decision point above)
- Same pattern can be applied to `SessionCompletion` for `session_time`

**Separately**, normalize `SchedulingConflictService::getAvailableTimeSlots()` output to match whichever canonical format is decided — currently it returns `H:i:s` (lines 118–119, 131–132) while `checkInstructorAvailability()` returns `H:i` (lines 54–55).

**Files:** `app/Models/TimeSlot.php`, `app/Http/Controllers/InstructorTimeSlotController.php` (lines 255–256), `app/Services/SchedulingConflictService.php` (lines 118–119, 131–132)

---

## Issue 4: Instructor Students `$assignedStudentIds` — Confirmed

### Error
`Undefined variable $assignedStudentIds`

### Root Cause
`InstructorController.php` line 104 — the closure captures `$assignedStudentIds` via `use`, but the variable is never defined in `myStudents()`:
```php
$students->getCollection()->each(function ($student) use ($instructor, $assignedStudentIds) {
    $student->is_assigned = in_array($student->id, $assignedStudentIds);
```

### Existing Pattern in Codebase
`ExportController.php` (line 535) already solves this correctly:
```php
$assignedStudentIds = Booking::where('school_id', $school->id)
    ->where('instructor_id', $instructor->id)
    ->distinct()
    ->pluck('student_id')
    ->toArray();
```

### Refined Proposal

**Minimum viable fix** (matches existing `ExportController` pattern, line 535):
```php
$assignedStudentIds = Booking::where('school_id', $school->id)
    ->where('instructor_id', $instructor->id)
    ->distinct()
    ->pluck('student_id')
    ->toArray();
```

**Why this scoping is non-negotiable:**
- **`school_id`** — explicit multi-tenant guard. The `HasSchoolScope` trait provides a local query scope (`scopeForSchool(...)`) that must be called explicitly — it is not an automatic global scope, so queries without `->forSchool()` or manual `where('school_id', ...)` have no tenant filtering.
- **`instructor_id`** — core assignment relationship

**Status filtering — decision required before implementation:**

| Option | Query addition | Semantics | Tradeoff |
|--------|---------------|-----------|----------|
| A: All bookings (current ExportController pattern) | *(none)* | "Any student I've ever interacted with" | Simplest; may show stale/cancelled relationships |
| B: Exclude cancelled | `->whereIn('status', ['scheduled', 'completed'])` | "Students with active or completed sessions" | Sensible default; cancelled-only relationships hidden |
| C: Recent only | `->where('scheduled_at', '>=', now()->subMonths(N))` | "Students I've worked with recently" | Most precise; requires defining "recent" (N months) |

The blade uses `is_assigned` to show a "My Student" vs "Other" badge. This is primarily informational, not access-controlling, which lowers the risk of any option — but the choice changes what instructors see day-to-day.

**Alternative (zero extra queries):** Derive from the already-loaded booking relation:
```php
$assignedStudentIds = $students->getCollection()
    ->filter(fn($s) => $s->bookings->isNotEmpty())
    ->pluck('id')
    ->toArray();
```
Works because the `bookings` relation is already eager-loaded with `->where('instructor_id', $instructor->id)`. Tradeoff: doesn't exclude cancelled (needs relation filter change), and only reflects the current page's data (which is actually correct per-page behavior).

**File:** `app/Http/Controllers/InstructorController.php` (insert before line ~96)

---

## Summary

| # | Issue | Verdict | Severity | Ready to implement? |
|---|-------|---------|----------|---------------------|
| 1 | Enrollment checkboxes | **Not a bug** — functional, but has edge-case gaps (1a–1e) | Low–Medium | Edge cases: yes, with scoped fixes |
| 2 | Schedule page too long | **Confirmed** — deeper than first reported (no UI affordance, no validation, calendar nav disconnected from data filtering) | Medium | Needs product decision on max window |
| 3 | Instructor schedule `format()` on string | **Confirmed crash** — fix should be centralized via model accessors with null guards | High | Yes, after canonical format decision |
| 4 | Instructor students `$assignedStudentIds` | **Confirmed crash** — fix should follow `ExportController` pattern; status filter is product-dependent | High | Yes (minimum fix); status filter needs product decision |

## Decisions Required Before Implementation

| # | Decision | Options | Impact | Blocking |
|---|----------|---------|--------|----------|
| D1 | **Canonical app-layer time format** | `H:i` (dominant in codebase) or `H:i:s` (matches DB) | Determines accessor output, `SchedulingConflictService` normalization, and `data-*` attribute values | Blocks Issue 3 full fix |
| D2 | **Max schedule query window** | 31 / 60 / 90 days, or uncapped with pagination | Determines validation rule in controller and whether `paginate()` is needed | Blocks Issue 2 fix |
| D3 | **Assigned student scope** | All bookings / exclude cancelled / recent-only (see Option A/B/C table in Issue 4) | Determines `$assignedStudentIds` query and what instructors see on "My Students" page | Minimum fix (Option A) can ship without decision; B/C need product input |
| D4 | **Display format consistency** | `g:i A` (no leading zero, dominant) or `h:i A` (leading zero) | UX consistency across student/instructor/admin views | Non-blocking; cosmetic cleanup |
