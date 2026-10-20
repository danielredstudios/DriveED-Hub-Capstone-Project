# DriveED Hub — System Guide

**Version:** v2.1 | **Updated:** March 26, 2026

---

## What Is DriveED Hub?

DriveED Hub is a **multi-tenant driving school management system**. It provides a complete digital platform for driving schools to manage their students, instructors, courses, enrollments, schedules, payments, and training progress — all from one system.

**Multi-tenant** means multiple driving schools can run on the same system, each with a completely isolated environment. Students from School A cannot see or interact with School B's data. Each school gets its own URL (e.g., `/smart-driving`, `/lyspeed-driving`).

---

## System Goals

1. **Streamline enrollment** — From guest registration to student activation, the entire process is managed digitally with admin approval gates.
2. **Manage driving training** — Schedule sessions, track hours, log completions, record progress, and manage theoretical + practical phases.
3. **Handle payments** — Track payment submissions, verify receipts, and maintain an audit trail.
4. **Enable multi-branch operations** — Schools with multiple branches can delegate day-to-day operations to branch secretaries while maintaining central oversight.
5. **Provide transparency** — Each role (student, instructor, admin) has a tailored dashboard showing exactly what they need.

---

## User Roles & What They Do

### System Administrator
The **super-user** of the entire platform. Manages all schools from a global panel.

- Creates and configures new driving schools
- Manages system-wide admin accounts
- Views audit logs across all schools
- Has no involvement in day-to-day school operations

### School Administrator
The **owner/manager** of a single driving school. Full control over everything within their school.

- Approves or rejects student enrollment requests
- Manages instructors, students, courses, and schedules
- Reviews and verifies payment submissions
- Generates reports (students, payments, enrollments, progress)
- Configures school branding, settings, and payment methods (GCash)
- Can manage all branches within their school

### Branch Secretary
A **delegated admin** restricted to a single branch. Handles daily operations under the School Admin's oversight.

- Can do most things a School Admin does, but **only for their assigned branch**
- Cannot manage other admins, branches, or school-wide settings
- Cannot approve "student action requests" — those require School Admin sign-off
- Creates student action requests that the School Admin reviews

### Instructor
The **teacher** who delivers driving lessons and tracks student performance.

- Views their assigned students and upcoming sessions
- Logs completed driving sessions (date, duration, notes)
- Creates progress reports with performance ratings (1–5 scale)
- Marks students as having passed theoretical training
- Manages their own schedule and availability
- Browses course content (LMS)

### Student
An **active, approved learner** enrolled in a driving course.

- Views their enrolled course and training progress
- Books driving sessions from available time slots
- Views payment history
- Accesses course content (modules, lessons, videos)
- Manages their profile

### Guest
A **newly registered user** who hasn't been approved yet. Every new account starts here.

- Browses available courses and pricing
- Submits an enrollment request for a chosen course
- Uploads their student driver's license
- Submits GCash payment proof during checkout
- Tracks the status of their enrollment request
- **Cannot** access student features until an admin approves their enrollment

---

## How the Roles Interact

### The Enrollment Workflow (Core Business Flow)

This is the central workflow that ties all roles together:

```
┌──────────────┐     ┌──────────────────┐     ┌──────────────────┐
│   GUEST      │     │   SCHOOL ADMIN   │     │    STUDENT       │
│              │     │                  │     │                  │
│ 1. Registers │────▶│                  │     │                  │
│ 2. Browses   │     │                  │     │                  │
│    courses   │     │                  │     │                  │
│ 3. Enrolls   │────▶│ 4. Reviews       │     │                  │
│ 4. Pays via  │     │    enrollment    │     │                  │
│    GCash     │     │ 5. Verifies      │     │                  │
│ 5. Waits...  │     │    payment +     │     │                  │
│              │     │    license       │     │                  │
│              │     │ 6. APPROVES      │────▶│ 7. Now a Student │
│              │     │    (atomic       │     │ 8. Books sessions│
│              │     │    promotion)    │     │ 9. Starts course │
└──────────────┘     └──────────────────┘     └──────────────────┘
```

**Key rule:** When an admin approves an enrollment, the guest is **immediately and atomically** promoted to student. This happens in one transaction — there is no in-between state.

### The Training Workflow

Once a student is enrolled:

```
┌──────────────┐     ┌──────────────────┐     ┌──────────────────┐
│   STUDENT    │     │   INSTRUCTOR     │     │   SCHOOL ADMIN   │
│              │     │                  │     │                  │
│ 1. Books a   │────▶│ 2. Sees booking  │     │                  │
│    session   │     │    on schedule   │     │                  │
│              │     │ 3. Conducts      │     │                  │
│              │     │    lesson        │     │                  │
│              │     │ 4. Logs session  │────▶│ 5. Reviews       │
│              │     │    completion    │     │    completion    │
│              │     │ 5. Rates         │     │                  │
│              │◀────│    progress      │     │                  │
│ 6. Views     │     │                  │     │                  │
│    progress  │     │                  │     │                  │
└──────────────┘     └──────────────────┘     └──────────────────┘
```

Students repeat this cycle until they've completed the required hours for their course.

### The Payment Workflow

```
┌──────────────┐     ┌──────────────────┐
│   GUEST /    │     │   SCHOOL ADMIN   │
│   STUDENT    │     │                  │
│              │     │                  │
│ 1. Sees the  │     │                  │
│    amount    │     │                  │
│ 2. Pays via  │     │                  │
│    GCash     │     │                  │
│ 3. Uploads   │────▶│ 4. Sees payment  │
│    receipt   │     │    in records    │
│              │     │ 5. Verifies via  │
│              │     │    Quick-Verify  │
│              │     │    Modal         │
│              │     │                  │
│              │     │ NOTE: Payments   │
│              │     │ page is READ-ONLY│
│              │     │ (no approve/     │
│              │     │  reject buttons) │
└──────────────┘     └──────────────────┘
```

**Key rule:** The Payments module is a **read-only ledger**. Payment verification happens through the Enrollment module's Quick-Verify Modal, not the Payments page. There are no refunds in the system.

### Branch Secretary Workflow

```
┌──────────────────┐     ┌──────────────────┐
│ BRANCH SECRETARY │     │   SCHOOL ADMIN   │
│                  │     │                  │
│ 1. Manages daily │     │                  │
│    operations    │     │                  │
│ 2. Handles       │     │                  │
│    enrollments   │     │                  │
│ 3. Creates       │────▶│ 4. Reviews and   │
│    student       │     │    approves/     │
│    action        │     │    denies        │
│    requests      │     │    request       │
│                  │     │                  │
│ ❌ Cannot manage │     │ ✅ Full control  │
│    settings,     │     │    over school   │
│    branches,     │     │                  │
│    or admins     │     │                  │
└──────────────────┘     └──────────────────┘
```

---

## System Modules

### Course Management
Schools create courses with titles, descriptions, pricing, duration, and vehicle type (manual/automatic). Each course can have **modules** and **lessons** (a built-in LMS) with rich text content and video embeds. Courses can also have **packages** (bundles at different price points).

### Scheduling & Bookings
Admins create **time slots** and assign instructors. Students browse available slots and book sessions. The system detects **scheduling conflicts** to prevent double-booking instructors. Schools can require minimum advance notice for bookings.

### Session Completion & Progress
After each driving lesson, instructors log a **session completion** with hours, notes, and a performance rating (1–5). These build the student's training record and count toward their required hours.

### Theoretical Training
Some courses require passing a theoretical exam before practical lessons. Instructors or admins mark students as "passed" which unlocks the next phase.

### Reports & Exports
Admins can generate PDF and CSV reports for students, instructors, payments, enrollments, and progress records.

### Notification System
In-app notifications keep all users informed about enrollment approvals, session reminders, and other events. Email notifications are sent via Resend for critical events like enrollment approval.

### Event Attendance
A public attendance portal allows event check-in with photo capture. Uses signed links for security and server-side timestamping for forensic integrity.

---

## Key Business Rules

1. **One enrollment at a time** — Students are "course-locked" when enrolled. They can enroll in a new course only after completing or cancelling the current one.
2. **Enrollment = the only promotion path** — Guest-to-student promotion happens only when an admin approves an enrollment. No other action in the system can change a user's role.
3. **Standardized Storage Directive** — All new payment receipts MUST be stored on the `local` disk under `receipts/{school_id}/`. 
    - Use `ReceiptStorageService` for all uploads. 
    - `screenshots/payments/` on the `public` disk is for legacy read-access only.
4. **Payments are history only** — The payment module records and displays transactions. It cannot approve, reject, or refund anything.
5. **School isolation** — Every query is scoped by school. Cross-school data access is impossible except for system admins.
6. **No refunds** — The system does not support payment refunds.

---

*For technical implementation details, see `PROJECT_REFERENCE.md`.  
For March 2026 changes, see `MARCH_2026_CHANGES.md`.*
