# DriveED Hub - Complete System Revamp Plan

## Version: v1.6 | Last Updated: February 9, 2026

---

## 📋 RECENT UPDATES (February 9, 2026)

### ✅ Testing Infrastructure Complete
- **8 comprehensive test files created** covering authentication and core features
- **64 tests implemented** with organized screenshot capture
- **Screenshot organization:** `Test {number} - {Name}/{Role}/{step}.png` format
- **45 tests passing** across Admin, Instructor, and Student flows
- **Multi-role testing** implemented in StudentPagesTest (student, admin, instructor, guest)
- **Server configuration:** Migrated to port 9000 (`.env` and `.env.dusk.local` updated)
- **Database seeded:** All 3 schools populated with test accounts via UnifiedSeeder

### Test Files Created
1. **AdminAuthTest.php** - Tests 001-005 (Authentication flows)
2. **InstructorAuthTest.php** - Tests 006-010 (Authentication flows)
3. **StudentAuthTest.php** - Tests 011-015 (Authentication flows)
4. **AdminDashboardTest.php** - Tests 056-060 (Dashboard features)
5. **AdminUserManagementTest.php** - Tests 061-075 (User management)
6. **AdminCoursesTest.php** - Tests 081-095 (Course management)
7. **StudentPagesTest.php** - Tests 260-302, 350-352 (Student features + guest flow)
8. **InstructorPagesTest.php** - Tests 198-259 (Instructor features)

### Test Accounts Available
- **System Admin:** systemadmin@gmail.com / sysadmin123!
- **School Admin:** schooladmin@gmail.com / password123
- **Instructor:** instructor@gmail.com / password123
- **Student:** student@gmail.com / password123

---

## 🎯 CORE CONCEPT

**Focus:** Simple course enrollment with content access and session tracking

**Key Changes:**
- Course types: Theoretical OR Practical (independent courses)
- License types: Non-Professional vs Professional
- Instructor logs session completion (not automatic)
- Student locked to one course at a time
- **NO prerequisite checking** - students can enroll in any available course
- **NO vehicle management** - keep it simple
- Each course has its own content/files that enrolled students can access

---

## 📦 PART 1: DATABASE RESTRUCTURE

### 1.1 Course Table Updates
**Add Fields:**
```sql
- course_type: enum('theoretical', 'practical')
- license_type: enum('non-professional', 'professional')
- hours_required: decimal(5,1) -- total hours for this course
```

**Migration File:** `2025_12_12_000001_add_course_type_fields_to_courses_table.php`

**Status:** ✅ DONE (Migration created and run)

---

### 1.2 Enrollment System (NEW TABLE)
**Purpose:** Track student's current course enrollment and hours progress

**Table:** `enrollments`
```sql
- id
- student_id (foreign key)
- course_id (foreign key)
- school_id (foreign key)
- status: enum('active', 'completed', 'cancelled', 'on_hold')

// Progress tracking
- hours_completed: decimal(5,1) default 0
- completed_at: timestamp nullable

// Timestamps
- started_at: timestamp
- created_at, updated_at
```

**Migration File:** `2025_12_12_000006_create_enrollments_table.php`

**Status:** ✅ DONE (Migration created and run)

---

### 1.3 Session Completions (NEW TABLE)
**Purpose:** Instructor logs each completed session

**Table:** `session_completions`
```sql
- id
- booking_id (links to existing bookings, nullable)
- enrollment_id (foreign key)
- instructor_id (foreign key)
- student_id (foreign key)
- course_id (foreign key)
- school_id (foreign key)

// Session details
- scheduled_duration_hours: decimal(4,1)
- actual_duration_hours: decimal(4,1)
- completion_status: enum('completed', 'incomplete', 'no_show')

// Notes
- instructor_notes: text nullable

// Timestamps
- session_date: date
- completed_at: timestamp
- created_at, updated_at
```

**Migration File:** `2025_12_12_000007_create_session_completions_table.php`

**Status:** ✅ DONE (Migration created and run)

---

### 1.4 Phase Progression Requests (NEW TABLE)
**Purpose:** Admin approval for phase advancement

### 1.4 Course Content (NEW TABLE)
**Purpose:** Store course-specific content/files that enrolled students can access

**Table:** `course_contents`
```sql
- id
- course_id (foreign key)
- school_id (foreign key)
- title (varchar)
- description (text nullable)
- content_type: enum('document', 'video', 'link', 'text')
- file_path (varchar nullable) -- for uploaded files
- video_url (varchar nullable) -- for YouTube/Vimeo embeds
- external_link (varchar nullable) -- for external resources
- text_content (longtext nullable) -- for text/HTML content
- sort_order (integer default 0)
- is_active (boolean default true)
- created_at, updated_at
```

**Migration File:** `2025_12_12_000004_create_course_modules_table.php` + `2025_12_12_000005_create_module_lessons_table.php`

**Status:** ✅ DONE (Implemented as CourseModules + ModuleLessons tables)

---

### 1.5 Student Table Updates
**Add Fields:**
```sql
- active_enrollment_id: foreign key to enrollments (nullable)
- is_course_locked: boolean default false
```

**Migration File:** `2025_12_12_000002_add_theoretical_status_to_students_table.php` + `2026_01_29_000007_add_enrollment_lock_to_students_table.php`

**Status:** ✅ DONE (Migration created and run)

---

## 📦 PART 2: MODELS & RELATIONSHIPS

### 2.1 New Models to Create
- `Enrollment.php` ✅
- `SessionCompletion.php` ✅
- `CourseModule.php` ✅ (replaces CourseContent)
- `ModuleLesson.php` ✅

### 2.2 Update Existing Models
- `Course.php` - Add course_type, license_type, hours_required fields, scopes ✅
- `Student.php` - Add enrollment relationship ✅
- `Instructor.php` - Add session completions relationship ✅
- `EnrollmentRequest.php` - Extended with verification fields ✅

### 2.3 Key Relationships
```php
// Student
- hasOne(Enrollment, 'active_enrollment_id')
- hasMany(Enrollment) // historical enrollments
- hasMany(SessionCompletion)

// Enrollment
- belongsTo(Student)
- belongsTo(Course)
- hasMany(SessionCompletion)

// Course
- hasMany(Enrollment)
- hasMany(CourseModule)
- scope: courseType(), licenseType(), theoretical(), practical()

// CourseModule
- belongsTo(Course)
- hasMany(ModuleLesson)

// ModuleLesson
- belongsTo(CourseModule)

// Instructor
- hasMany(SessionCompletion)

// SessionCompletion
- belongsTo(Enrollment)
- belongsTo(Instructor)
- belongsTo(Student)
- belongsTo(Booking) // optional link to scheduled booking
```

**Status:** ✅ DONE (All models created with relationships)

---

## 📦 PART 3: ADMIN COMPONENTS

### 3.1 Enhanced Course Form
**File:** `admin/course-form-modal.blade.php`

**Features:**
- Course type selection (theoretical/practical)
- License type selection (non-professional/professional)
- Hours required input
- Price input
- Description

**Status:** ⏸️ Need to update for simplified fields

---

### 3.2 Course Content Manager
**Component:** `admin/course-content-manager.blade.php`

**Features:**
- Add/edit/delete course content items
- Upload documents (PDF, images)
- Add video URLs (YouTube/Vimeo)
- Add external links
- Reorder content items
- Preview content

**Status:** ⏸️ Not created yet

---

### 3.3 Enrollment Management
**Component:** `admin/enrollment-management.blade.php`

**Features:**
- View all enrollments
- Filter by status (active, completed, cancelled)
- View student progress (hours completed)
- Mark enrollment as complete
- Cancel enrollment

**Status:** ⏸️ Not created yet

---

### 3.4 Admin Dashboard Redesign
**Component:** `admin/dashboard-revamped.blade.php`

**Sections:**
1. **Pending Actions** (Priority)
   - Enrollment requests awaiting approval
   - Pending payments

2. **Today's Overview**
   - Sessions scheduled today
   - Active students count
   - Instructors on duty

3. **Quick Stats**
   - Total active students
   - Total courses
   - This week's completions

4. **Quick Access**
   - Manage Students
   - Manage Instructors
   - Manage Courses

**Status:** ⏸️ Not created yet

---

## 📦 PART 4: INSTRUCTOR COMPONENTS

### 4.1 Session Completion Form
**Component:** `instructor/session-completion-modal.blade.php`

**Features:**
- Auto-filled from booking
- Actual hours completed input
- Status selection (completed/incomplete/no-show)
- Instructor notes textarea
- Submit to log completion

**Form Fields:**
- Student name (read-only)
- Course (read-only)
- Scheduled duration (read-only)
- **Actual duration** (editable)
- **Status** (dropdown)
- **Notes** (textarea)

**On Submit:**
- Create SessionCompletion record
- Update Enrollment hours_completed

**Status:** ⏸️ Not created yet

---

### 4.2 Instructor Dashboard Redesign
**Component:** `instructor/dashboard-revamped.blade.php`

**Sections:**
1. **Today's Schedule**
   - List of today's sessions with students
   - Start/Complete buttons
   - Student info + course

2. **Upcoming This Week**
   - Quick view of future sessions

3. **Recent Completions**
   - Sessions logged today/yesterday
   - Quick access to edit notes

4. **Quick Stats**
   - Sessions this week
   - Total hours taught this month

**Status:** ⏸️ Not created yet

---

## 📦 PART 5: STUDENT COMPONENTS

### 5.1 Student Progress Display
**Component:** `student/progress-card.blade.php`

**Features:**
- Show current enrolled course
- Progress bar for hours completed
- Access to course content/files
- Upcoming sessions
- Recent completed sessions with instructor notes

**Layout:**
```
┌─────────────────────────────────┐
│ Traffic Rules & Road Safety     │
│ Status: Active                  │
├─────────────────────────────────┤
│ Progress: ▓▓▓▓▓▓░░░░ 6/10 hrs  │
│                                 │
│ [View Course Materials]         │
│ [Schedule Next Session]         │
└─────────────────────────────────┘

Upcoming Sessions:
• Dec 15 - 2:00 PM - Juan Cruz
• Dec 18 - 10:00 AM - Maria Santos

Recent Sessions:
• Dec 10 - 2 hrs - "Good progress"
• Dec 8 - 2.5 hrs - "Review road signs"
```

**Status:** ⏸️ Not created yet

---

### 5.2 Course Content Viewer
**Component:** `student/course-content.blade.php`

**Features:**
- List all content items for enrolled course
- View/download documents
- Watch embedded videos
- Open external links
- Read text content

**Status:** ⏸️ Not created yet

---

### 5.3 Student Dashboard Redesign
**Component:** `student/dashboard-revamped.blade.php`

**Sections:**
1. **Current Course** (If enrolled)
   - Progress display
   - View course materials button
   - Schedule button

2. **Upcoming Sessions**
   - Next 3 sessions

3. **Recent Activity**
   - Completed sessions with notes

4. **Course Complete** (If finished)
   - Congratulations message
   - Enroll in new course option

**Status:** ⏸️ Not created yet

---

## 📦 PART 6: GUEST COMPONENTS

### 6.1 Enrollment Request Form
**Component:** `guest/enrollment-request-form.blade.php`

**Features:**
- Browse and select any available course
- Personal information
- Contact details
- Submit application

**Form Flow:**
```
1. Browse Available Courses
   - Show all courses (theoretical and practical)
   - Filter by course type if desired
   - Show course details (hours, price)
   
2. Personal Information
   - Full name
   - Email
   - Phone
   
3. Submit Application
```

**Note:** NO prerequisite checking - students can apply for any course they want

**Status:** ⏸️ Not created yet

---

### 6.2 Browse Courses Page
**Component:** `guest/browse-courses.blade.php`

**Features:**
- **Filter by course type:**
  - All Courses
  - Theoretical Courses
  - Practical Courses
  
- **Course cards show:**
  - Course name
  - Course type badge (THEORETICAL / PRACTICAL)
  - License type (Non-Professional / Professional)
  - Hours required
  - Price
  
**Example Display:**
```
THEORETICAL COURSES

┌─────────────────────────────────────┐
│ Traffic Rules & Road Safety         │
│ [THEORETICAL]                       │
│                                     │
│ 8 hours | Non-Professional          │
│ No prerequisites                    │
│ PHP 3,000                           │
│                                     │
│ [Enroll Now]                        │
└─────────────────────────────────────┘

PRACTICAL COURSES

┌─────────────────────────────────────┐
│ Manual Transmission Driving         │
│ [PRACTICAL] ⚠ Requires Theoretical  │
│                                     │
│ 16 hours | Non-Professional         │
│ Vehicle: Manual Sedan               │
│ PHP 8,000                           │
│                                     │
│ [Enroll Now]                        │
└─────────────────────────────────────┘
```

**Status:** ⏸️ Not created yet

---

### 6.3 Guest Dashboard
**Component:** `guest/dashboard.blade.php`

**Sections:**
1. **Welcome Message**
   - "Start Your Driving Journey"
   - Brief explanation of process
   
2. **Steps to Get Started:**
   ```
   Step 1: Apply for Theoretical Course
   Step 2: Complete Theoretical Training
   Step 3: Apply for Practical Course
   Step 4: Complete Practical Training
   Step 5: Take LTO Exam
   ```

3. **My Applications**
   - List enrollment requests with status
   - Status badges: Pending / Approved / Rejected

**Status:** ⏸️ Not created yet

---

## 📦 PART 7: CONTROLLERS & LOGIC

### 7.1 New Controllers to Create
- `EnrollmentController.php`
- `SessionCompletionController.php`
- `TheoreticalCompletionController.php` // NEW - Mark theoretical as passed

### 7.2 Update Existing Controllers
- `CourseController.php` - Add course_type, license_type, hours validation
- `EnrollmentRequestController.php` - Add theoretical prerequisite check
- `AdminController.php` - Add theoretical completion marking
- `InstructorController.php` - Add session logging + mark as passed
- `StudentController.php` - Show theoretical status, course restrictions

### 7.3 Key Methods Needed

**EnrollmentController:**
- `store()` - Create enrollment (validate theoretical prerequisite for practical courses)
- `show()` - Display enrollment details & session history
- `validateEnrollment()` - Check if student can enroll (theoretical status for practical)
- `complete()` - Mark enrollment as complete

**SessionCompletionController:**
- `store()` - Log completed session
- `update()` - Edit session notes
- `index()` - List sessions (for instructor/admin)

**PhaseProgressionController:**
- `requestProgression()` - Auto-create when hours met
- `approve()` - Admin approves progression
- `reject()` - Admin rejects with reason
- `index()` - List pending approvals

**Status:** ⏸️ Not created yet

---

## 📦 PART 8: ROUTES STRUCTURE

### 8.1 Admin Routes (Add)
```php
// Enrollment verification
Route::get('/enrollment-requests', ...)->name('enrollment-requests.index');
Route::post('/enrollment-requests/{id}/verify', ...)->name('enrollment-requests.verify');
Route::post('/enrollment-requests/{id}/reject', ...)->name('enrollment-requests.reject');

// Phase progression
Route::get('/phase-progressions', ...)->name('phase-progressions.index');
Route::post('/phase-progressions/{id}/approve', ...)->name('phase-progressions.approve');
Route::post('/phase-progressions/{id}/reject', ...)->name('phase-progressions.reject');

// Enrollments
Route::resource('enrollments', EnrollmentController::class);
```

### 8.2 Instructor Routes (Add)
```php
// Session completions
Route::post('/sessions/{booking}/complete', ...)->name('sessions.complete');
Route::get('/sessions/history', ...)->name('sessions.history');
Route::put('/sessions/{completion}/update', ...)->name('sessions.update');
```

### 8.3 Student Routes (Add)
```php
// View enrollment progress
Route::get('/my-course', ...)->name('student.enrollment');
Route::get('/my-progress', ...)->name('student.progress');
```

### 8.4 Guest Routes (Update)
```php
// Enhanced enrollment request
Route::post('/enrollment-request', ...)->name('guest.enrollment-request.store');
// (add file upload handling)
```

**Status:** ⏸️ Not planned yet

---

## 📦 PART 9: BUSINESS LOGIC & AUTOMATION

### 9.1 Auto-trigger Phase Progression
**When:** After instructor logs session completion

**Logic:**
```php
// In SessionCompletionController@store
1. Update enrollment hours_completed
2. Check: hours_completed >= hours_required?
3. If yes → Create PhaseProgressionRequest (status: pending)
4. Notify admin of pending approval
```

### 9.2 External Credential Verification
**When:** Admin reviews enrollment request from experienced driver

**Logic:**
```php
// In EnrollmentRequestController@verify
1. Review uploaded proof
2. Set credited hours (theoretical/practical)
3. Create enrollment with pre-credited hours
4. Set current_phase based on credits:
   - If theoretical complete → start at practical
   - If both complete → skip to assessment
5. Mark verification_status as 'verified'
```

### 9.3 Student Course Lock
**When:** Enrollment created

**Logic:**
```php
// In EnrollmentController@store
1. Check: student has active_enrollment_id?
2. If yes → prevent new enrollment
3. If no → create enrollment, set active_enrollment_id
4. Set is_course_locked = true
```

### 9.4 Unlock After Completion
**When:** Enrollment marked complete

**Logic:**
```php
// In EnrollmentController@complete
1. Set enrollment status = 'completed'
2. Set enrollment completed_at = now()
3. Set student is_course_locked = false
4. Clear student active_enrollment_id
5. Student can now enroll in new course
```

**Status:** ⏸️ Not planned yet

---

## 📦 PART 10: NOTIFICATIONS

### 10.1 Student Notifications
- Enrollment request approved
- Phase progression approved
- Session scheduled
- Session completed (with instructor notes)
- Course completed

### 10.2 Instructor Notifications
- New session assigned
- Session reminder (1 day before)

### 10.3 Admin Notifications
- New enrollment request (needs verification)
- Phase progression pending approval
- Student completed course

**Status:** ⏸️ Not planned yet

---

## 🗂️ IMPLEMENTATION ORDER

### Priority 1: Core Structure
1. ✅ Part 1.1: Course table migration (READY)
2. Part 1.2: Enrollments table
3. Part 1.3: Session completions table
4. Part 2: Models & relationships
5. Part 3.1: Enhanced course form (INTEGRATE)

### Priority 2: Student Flow
6. Part 1.5: Student table updates
7. Part 1.6: Enrollment requests updates
8. Part 6.1: Enhanced enrollment form
9. Part 5.1: Student progress display
10. Part 5.2: Student dashboard

### Priority 3: Instructor Tools
11. Part 4.1: Session completion form
12. Part 4.2: Instructor dashboard
13. Part 7.2: Session completion controller

### Priority 4: Admin Tools
14. Part 1.4: Phase progression table
15. Part 3.2: Enrollment verification
16. Part 3.3: Phase progression approval
17. Part 3.4: Admin dashboard
18. Part 7.3: Admin controllers

### Priority 5: Polish
19. Part 9: Business logic automation
20. Part 10: Notifications
21. Testing & bug fixes
22. Documentation

---

## 📝 TESTING STRATEGY

### Component Testing (Current Approach)
- Build each component in `test-components/`
- Test standalone before integration
- Use test routes for live testing

### Integration Testing
- Test one flow at a time:
  1. Course creation → Enrollment request → Approval
  2. Session booking → Completion → Hours update
  3. Phase completion → Approval → Next phase
  4. Course completion → Unlock student

### User Acceptance Testing
- Test as each role:
  - Guest: Browse → Request → Wait
  - Student: Enroll → Schedule → Complete
  - Instructor: Schedule → Complete → Notes
  - Admin: Verify → Approve → Manage

---

## 🎯 SUCCESS CRITERIA

### Must Have:
- ✅ Courses have license types and phase hours
- ✅ Students locked to one course at a time
- ✅ Instructor logs session completion manually
- ✅ Admin approves phase progression
- ✅ External credentials can be verified

### Should Have:
- Auto-trigger progression requests
- Clear progress visualization
- Mobile-responsive components
- Notification system

### Nice to Have:
- Bulk approval actions
- Analytics/reports per phase
- Certificate generation
- SMS notifications

---

## 📊 CURRENT STATUS

**Last Updated:** January 29, 2026

### ✅ Completed (Foundation Layer)
- ✅ All database migrations created and run (Part 1)
- ✅ All core models created with relationships (Part 2)
- ✅ Multi-tenant school_id compliance verified
- ✅ Course form (standalone + modal version)
- ✅ Student my-course route and view
- ✅ Security vulnerabilities fixed (PHP packages updated, Excel → CSV)
- ✅ Test suite passing (2/2 tests)

### 🔄 In Progress
- Course content management views
- Enrollment management views
- Session completion forms

### ⏳ Pending
- Admin dashboard redesign
- Instructor dashboard redesign
- Student dashboard redesign
- Notification system

**Estimated Phase:** Foundation complete, UI components next

---

## 💾 BACKUP PLAN

Before major changes:
1. ✅ Backup database
2. ✅ Create git branch: `feature/course-enrollment-revamp`
3. ✅ Document current system behavior
4. Keep old code commented for reference
5. Test migrations on copy of production data

---

## 📚 ADDITIONAL FEATURES TO CONSIDER

### A. Theoretical Learning Management

**Problem:** Theoretical phase is just "classroom hours" but no actual content delivery in the system.

**Solution Options:**

#### Option 1: Simple Lesson Posts (RECOMMENDED for MVP)
**What:**
- Admin/Instructor can post lessons (like a blog)
- Students read and mark as complete
- Track which lessons student has viewed

**Features:**
- Lesson title & content (rich text editor)
- Attachments (PDFs, images)
- Mark as complete checkbox
- Required vs Optional lessons
- Student sees: "You've completed 5/8 required lessons"

**Pros:**
- Simple to build
- Easy for instructors to use
- Students have reference materials
- Tracks engagement

**Cons:**
- No quiz/assessment
- Honor system for completion

**Database:**
```sql
// lessons table
- id
- school_id
- course_id (nullable - can be shared across courses)
- title
- content (text/HTML)
- lesson_type: enum('theoretical', 'practical_prep', 'reference')
- attachments (json array of file paths)
- is_required: boolean
- sort_order
- created_by (admin/instructor_id)
- created_at, updated_at

// lesson_completions table
- id
- lesson_id
- student_id
- enrollment_id (track per course enrollment)
- completed_at
- time_spent_seconds (optional tracking)
```

---

#### Option 2: Full LMS (Learning Management System)
**What:**
- Structured curriculum with modules
- Videos, PDFs, quizzes
- Progress tracking per module
- Certificates upon completion

**Features:**
- Modules → Lessons → Quizzes
- Video hosting/embedding
- Quiz builder with auto-grading
- Discussion forums
- Assignment submissions
- Gradebook

**Pros:**
- Professional learning experience
- Enforces theoretical learning
- Auto-grading saves time
- Better tracking

**Cons:**
- HUGE amount of work
- Complex to build and maintain
- Overkill for driving school?
- Instructors need to create lots of content

**My Opinion:** TOO MUCH for now. This is a capstone project, not a full LMS.

---

#### Option 3: Hybrid Approach (RECOMMENDED)
**What:**
- Start with Option 1 (simple lessons)
- Add basic quizzes later (Phase 2)
- Students must pass quiz to complete theoretical phase

**Features Now:**
- Post lessons with attachments
- Students read and mark complete
- Track completion percentage

**Features Later (Optional):**
- Add simple quiz at end of theoretical
- Must score 80% to proceed to practical
- Admin creates quiz questions

**Pros:**
- Manageable scope
- Can expand later
- Provides value immediately
- Not overwhelming

**My Recommendation:** Start with **Option 1**, then add quizzes if needed.

---

### B. File Sharing System

**Problem:** Instructors/admins need to share documents with students.

**Solution Options:**

#### Option 1: Lesson Attachments Only (SIMPLE)
**What:**
- Files attached to specific lessons
- Students download from lesson page
- No separate file library

**Pros:**
- Simple, contextual
- No extra pages needed
- Files tied to learning content

**Cons:**
- Can't share general files (e.g., forms, schedules)
- No file organization

---

#### Option 2: Document Library (RECOMMENDED)
**What:**
- Dedicated "Documents" section
- Organized by categories
- Everyone can access

**Features:**
- Admin uploads files
- Categories: Forms, Schedules, Policies, Study Materials, etc.
- Filter by category
- Download tracking (optional)

**Database:**
```sql
// documents table
- id
- school_id
- title
- description
- file_path
- file_type (pdf, doc, image, etc.)
- file_size
- category: enum('forms', 'schedules', 'study_materials', 'policies', 'other')
- visibility: enum('all', 'students_only', 'instructors_only')
- uploaded_by (admin_id)
- created_at, updated_at

// document_downloads (optional tracking)
- id
- document_id
- user_id
- user_type (student/instructor/admin)
- downloaded_at
```

**UI:**
```
Documents
├── Forms
│   └── Medical Certificate Form.pdf
├── Study Materials
│   └── Traffic Signs Guide.pdf
├── Schedules
│   └── Holiday Schedule 2025.pdf
└── Policies
    └── Cancellation Policy.pdf
```

**Pros:**
- Centralized file management
- Easy to organize
- Can track downloads
- Useful for all users

**Cons:**
- Another page to build
- File storage management

**My Recommendation:** Build this. It's useful and not too complex.

---

#### Option 3: Per-Student File Sharing (COMPLEX)
**What:**
- Instructor can share files with individual students
- Like Google Drive with permissions
- Students have personal folder

**Features:**
- Upload to specific student
- Student-only access
- Folder structure per student
- Share feedback documents

**Pros:**
- Private file sharing
- Personalized learning
- Great for progress reports

**Cons:**
- Complex permissions system
- Storage intensive
- Overkill for most use cases

**My Opinion:** NOT worth it. Use general document library + optional attachments to sessions.

---

### C. My Overall Recommendation

**Build This (Phase 1 - Core):**
1. ✅ Enhanced course structure (in progress)
2. ✅ Enrollment with phase tracking
3. ✅ Session completion logging
4. 📚 **Course Modules/Lessons** (NEW APPROACH)
   - Each course has modules/lessons built-in
   - Modules unlock progressively (or all at once)
   - Students access via enrolled course
   - Track completion per student
5. 📁 **Document Library** (General school resources)
   - Forms, policies, general materials
   - Separate from course content

**Add Later (Phase 2 - Enhancement):**
6. 📝 Basic quizzes for theoretical phase
7. 📊 Analytics dashboard (which lessons viewed most)
8. 💬 Announcement system (instead of full notifications)
9. 📅 Better calendar view for schedules

**Skip (Not Worth Effort):**
- Full LMS with modules/videos
- Per-student private file sharing
- Discussion forums
- Complex quiz builder

---

### D. Updated Implementation Plan

**PART 11: COURSE CONTENT & LEARNING SYSTEM** ⭐ UPDATED APPROACH

### Concept: Hybrid Learning Model

**Theoretical Phase:**
- In-person sessions at driving school (scheduled like practical sessions)
- System provides reference materials (lessons, videos, PDFs for review)
- Instructor logs theoretical session completion
- Instructor/Admin marks student as passed in system
- System unlocks practical course enrollment

**Practical Phase:**
- Instructor-led driving sessions with logging
- Track hours and session completion
- Mark course as complete

**Student accesses through:** "My Course" → Course materials (reference only) + Session scheduling

---

#### 11.1 Learning Materials (Reference Only)

**Database Structure:**

```sql
// course_modules table (simplified - reference materials)
- id
- course_id (foreign key)
- title (e.g., "Traffic Laws & Regulations")
- description
- module_type: enum('theoretical', 'practical_prep', 'reference')
- sort_order
- created_at, updated_at

// module_lessons table (reference materials)
- id
- module_id (foreign key)
- title (e.g., "Understanding Road Signs")
- content (rich text/HTML)
- attachments (json array - PDFs, videos, images)
- video_url (nullable - YouTube/Vimeo embeds)
- sort_order
- created_at, updated_at

// REMOVED: student_lesson_progress (not tracking completion in system)
// Students view materials for study, but completion tracked via sessions
```

**How It Works:**

**Admin Sets Up Course Materials (Reference):**
```
Course: Manual Transmission Driving
├── Module 1: Traffic Laws
│   ├── Lesson 1.1: Road Signs
│   │   └── Attachments: road-signs.pdf, video-embed
│   ├── Lesson 1.2: Right of Way Rules
│   └── Lesson 1.3: Speed Limits
│
├── Module 2: Vehicle Controls
│   ├── Lesson 2.1: Dashboard & Instruments
│   ├── Lesson 2.2: Clutch & Gears (video tutorial)
│   └── Lesson 2.3: Braking Systems
│
├── Module 3: Practical Prep
│   ├── Lesson 3.1: Pre-driving Checklist
│   ├── Lesson 3.2: Parking Techniques (video demo)
│   └── Lesson 3.3: Highway Driving Tips
│
└── Module 4: Assessment Prep
    ├── Sample Questions (PDF)
    └── Tips for Driving Test
```

**Student Experience:**
```
My Course: Manual Transmission Driving

Theoretical Phase: IN PROGRESS
Sessions Attended: 6/8 hours
Next Session: Dec 15, 2025 2:00 PM

Study Materials (Available for review):

Module 1: Traffic Laws
   - Lesson 1.1: Road Signs (PDF + Video)
   - Lesson 1.2: Right of Way Rules
   - Lesson 1.3: Speed Limits

Module 2: Vehicle Controls
   - Lesson 2.1: Dashboard & Instruments (Video)
   - Lesson 2.2: Clutch & Gears
   - Lesson 2.3: Braking Systems

[Schedule Theoretical Session]
[Upload Completion Proof]

Status: Awaiting theoretical completion certificate
```

**Component Files:**

**Admin:**
- `admin/course-modules-manager.blade.php` - Manage modules
- `admin/module-lessons-editor.blade.php` - Create/edit lessons
- `admin/course-content-settings.blade.php` - Set unlock conditions

**Student:**
- `student/course-content.blade.php` - View all modules
- `student/lesson-viewer.blade.php` - Read lesson + download attachments
- `student/course-progress.blade.php` - Overall progress dashboard

**Status:** ⏸️ Planned

---

#### 11.2 Unlock Logic & Conditions

**Unlock Conditions Available:**

1. **Immediate** - Unlocked as soon as student enrolls
2. **AfterTheoretical Session Scheduling & Completion

**New Approach: In-Person Theoretical Sessions**

**How Theoretical Phase Works:**

1. **Student Enrolls** → System assigns to theoretical phase
2. **Student Schedules Theoretical Sessions** → Same booking system as practical
3. **Student Attends In-Person** → At driving school classroom
4. **Instructor Logs Session** → Mark theoretical hours completed
5. **Student Uploads Proof** → Certificate/document from school
6. **Admin Reviews Proof** → Verifies and approves
7. **System Unlocks Practical Phase**

**Database Addition for Proof Upload:**

```sql
// theoretical_completion_proofs table
- id
- enrollment_id (foreign key)
- proof_file_path (varchar - uploaded document/photo)
- uploaded_at (timestamp)
- verified_by (foreign key -> admins.id, nullable)
- verification_status (enum: pending, approved, rejected)
- verified_at (timestamp, nullable)
- admin_notes (text, nullable)
- created_at, updated_at
```

**Student Upload Interface:**
```
Theoretical Phase Completion

Hours Logged: 8/8 hours ✓
Sessions Attended: 8 sessions ✓

Upload Completion Proof:
┌─────────────────────────────────────┐
│ [Choose File] theoretical-cert.jpg  │
│                                     │
│ Accepted formats: PDF, JPG, PNG     │
│ Max size: 5MB                       │
│                                     │
│ [Upload Proof]                      │
└─────────────────────────────────────┘

Status: Pending Admin Verification
```

**Admin Verification Interface:**
```
PeSimplified Logic:**

Before allowing practical phase, student must:
1. ✅ Complete required theoretical hours (logged by instructor)
2. ✅ Upload proof of theoretical completion
3. ✅ Get admin approval on proof

**Validation Check:**
```php
function canProgressToPractical($enrollment) {
    // Check hours logged
    if ($enrollment->theoretical_hours_completed < $enrollment->course->theoretical_hours_required) {
        return false;
    }
    
    // Check if proof uploaded
    $proof = $enrollment->theoreticalCompletionProof;
    if (!$proof) {
        return false; // No proof uploaded
    }
    
    // Check if proof verified
    if ($proof->verification_status !== 'approved') {
        return false; // Not yet approved
    }
    
    return true; // All requirements met
}
```

**Student Progress Display:**
```
Ready for Practical Phase?

✅ Theoretical Hours: 8/8 completed
✅ Completion Proof: Uploaded
⏳ Admin Verification: Pending

Status: Awaiting admin approval
```

**After Approval:**
```
Ready for Practical Phase?

✅ Theoretical Hours: 8/8 completed
✅ Completion Proof: Verified
✅ Admin Approval: Approved on Dec 12, 2025

[Request Progression to Practical]
    }
    
    return true; // All requirements met
}
```

**Student Progress Display:**
```
Ready for Practical Phase?

✅ Theoretical Hours: 8/8 completed
✅ Required Lessons: 10/10 completed
✅ All modules unlocked and viewed

[Request Progression to Practical] ← Button appears
```

**Status:** ⏸️ Planned

---

#### 11.2 Document Library
**Component:** `shared/documents-library.blade.php`

**Features:**
- Upload documents (admin only)
- Categorize files
- Search/filter
- Download tracking
- Set visibility (all/students/instructors)

**Available to:** All users (based on visibility)

**Database Table:**
- `documents`
- `document_downloads` (optional)

**Status:** ⏸️ Planned

---

#### 11.3 Theoretical Phase Requirements
**Update enrollment logic:**

Before allowing practical phase:
1. Check: All required lessons completed?
2. Check: Theoretical hours logged?
3. Optional: Check quiz score if implemented?

**Validation:**
```php
// Can progress to practical?
if (
    $enrollment->theoretical_hours_completed >= $enrollment->course->theoretical_hours_required
    && $enrollment->hasCompletedRequiredLessons()
) {
    // Allow progression request
}
```

**Status:** ⏸️ Planned

---

### E. Revised Priority Order

**Phase 1 (Foundation):**
1. Database migrations (Parts 1.1-1.6)
2. Models (Part 2)
3. Enhanced course form integration (Part 3.1)
4. 📚 Course modules/lessons structure (Part 11.1)
5. 🔓 Unlock logic system (Part 11.2)
6. 📁 Document library (Part 11.4)

**Phase 2 (Core Flows):**
7. Student enrollment flow (Parts 5-6)
8. Course content viewer for students
9. Lesson completion tracking
10. Instructor session completion (Part 4)
11. Admin approvals (Part 3.2-3.3)

**Phase 3 (Content Management):**
12. Admin: Module/lesson creation interface
13. Admin: Unlock condition settings
14. File upload for lesson attachments
15. Student: Course content navigation

**Phase 4 (Dashboards):**
16. Student dashboard with course progress
17. Instructor dashboard
18. Admin dashboard

**Phase 5 (Enhancement):**
19. Quizzes (optional)
20. Analytics on lesson engagement
21. Announcements

---

### F. Architecture Decision

**Page Redesign Strategy:**

Instead of fixing old pages, **rebuild from scratch** with:

✅ **Consistent Design System:**
- Same card styles
- Same button styles
- Same color scheme
- Same spacing/padding
- Mobile-first approach

✅ **Component-Based:**
- Reusable components (cards, modals, tables)
- Put in `resources/views/components/`
- Use `@include()` or Blade components

✅ **Better UX:**
- Less clutter
- Clear call-to-actions
- Progressive disclosure (show what's needed)
- Loading states
- Empty states ("No lessons yet")

**Example Component Structure:**
```
resources/views/
├── components/
│   ├── card.blade.php
│   ├── modal.blade.php
│   ├── table.blade.php
│   ├── progress-bar.blade.php
│  Reference materials system (lessons with PDFs/videos for student review)
3. Theoretical session scheduling (same as practical booking)
4. Theoretical session logging by instructor (same as practical)
5. Proof of completion upload by student
6. Admin verification of theoretical completion proof
7. Practical session scheduling & logging (as planned)
8. Phase progression approval (after proof verified)

## 🎯 FINAL RECOMMENDATION - UPDATED

**Build These Features:**
1. ✅ Course structure with course types (theoretical/practical) & license types
2. 📚 **Course modules with lessons** (content lives IN courses)
3. 🔓 **Progressive unlock system** (unlock based on hours/completion)
4. 📎 **LFlow (UPDATED):**
```
Admin Creates Course
    ↓
Add Reference Materials (modules with lessons, PDFs, videos)
    ↓
Student Enrolls → Gets access to study materials
    ↓
Student Schedules Theoretical Sessions (in-person at school)
    ↓
Student Attends Sessions → Instructor Logs Hours
    ↓
Student Uploads Proof of Completion (certificate from school)
    ↓
Admin Verifies Proof
    ↓
System Unlocks Practical Phase
    ↓
Student Schedules Practical Sessions
    ↓
Instructor Logs Practical Hours
    ↓
Both Phases Complete → Ready for LTO Exam., "Road Signs")
    ↓
Set Unlock Conditions (immediate/after hours/etc.)
    ↓
Attach Files to Lessons (PDFs, images)
    ↓
Student Enrolls → Modules unlock progressively
    ↓
Student completes lessons + logs hours
    ↓
System checks: All requirements met?
    ↓
AlloHandles both theoretical and practical scheduling
- ✅ Reference materials available for student review
- ✅ Proof-based verification (realistic for schools)
- ✅ No complex quiz system needed
- ✅ Instructor logs both phases consistently
- ✅ Admin verifies completion before progressionlar courses (optional)
- ✅ Progressive unlock keeps students engaged
- ✅ Tracks (UPDATED):**
- **Week 1-2:** Database + Models + Course form + Reference materials structure
- **Week 3:** Module/lesson manager for study materials + Session scheduling (both phases)
- **Week 4:** Student material viewer + Session logging (instructor)
- **Week 5:** Proof upload system + Admin verification interface
- **Week 6:** Phase progression logic + Approvals
- **Week 7-8:** Dashboards + Polish + Testing

**Next Steps:**
1. Create database migrations (courses, modules, lessons, proofs, sessions)
2. Build reference material manager (admin uploads study content)
3. Build session scheduler (works for both theoretical and practical)
4. Build session logger (instructor logs hours for both phases)
5. Build proof upload interface (student)
6. Build verification interface (admin)course modules/lessons
2. Build module manager component (admin)
3. Build lesson viewer component (student)
4. Implement unlock logic
5. Test with sample course content

---

## COMPLETE OVERHAUL ASSESSMENT

### Pages That Need Complete Rebuild

#### ADMIN PAGES

**1. Admin Dashboard**
- **Current State:** Basic stats display
- **New Requirements:**
  - Course progress tracking across all students
  - Enrollment pipeline visualization (pending, theoretical, practical)
  - Instructor utilization metrics
  - Recent session completions
  - Pending approvals counter
  - Quick actions panel
- **Priority:** HIGH

**2. Courses Management**
- **Current State:** Simple course list with CRUD
- **New Requirements:**
  - Course list with license type filters
  - Manage course modules per course
  - Manage lessons per module
  - Set unlock conditions
  - Preview course structure
  - Duplicate/template courses
- **Priority:** HIGH

**3. Students Management**
- **Current State:** Student list with basic info
- **New Requirements:**
  - Filter by enrollment status (none, theoretical, practical, completed)
  - Show current phase and progress percentage
  - View lesson completion per student
  - Hours logged (theoretical/practical)
  - Quick access to student's course content
  - Override/manual unlock modules
- **Priority:** MEDIUM

**4. Instructors Management**
- **Current State:** Instructor list with assignments
- **New Requirements:**
  - Session logs per instructor
  - Student assignments with progress
  - Availability calendar view
  - Performance metrics (sessions logged, students taught)
  - Quick session logger access
- **Priority:** MEDIUM

**5. Enrollments & Requests**
- **Current State:** Simple enrollment request approval
- **New Requirements:**
  - Pending enrollment requests (with experience level visible)
  - Active enrollments list (with phase status)
  - Phase progression approval queue
  - Request details with credential verification for experienced drivers
  - Bulk approval actions
  - Enrollment analytics
- **Priority:** HIGH

**6. Reports Page**
- **Current State:** Basic reports
- **New Requirements:**
  - Lesson completion rates
  - Hour tracking reports (theoretical vs practical)
  - Phase progression timeline
  - Student retention metrics
  - Instructor productivity
  - Revenue per course/license type
  - Exportable reports (PDF/CSV)
- **Priority:** LOW

---

#### INSTRUCTOR PAGES

**1. Instructor Dashboard**
- **Current State:** Basic overview
- **New Requirements:**
  - Assigned students list with progress bars
  - Upcoming scheduled sessions
  - Quick session log entry form
  - Recent activity feed
  - Performance summary (hours logged this week/month)
  - Alerts for students needing attention
- **Priority:** HIGH

**2. My Students Page**
- **Current State:** List of assigned students
- **New Requirements:**
  - Student cards with progress visualization
  - Current phase status clearly shown
  - Hours logged vs required
  - Lessons completed
  - Next recommended action per student
  - Filter by phase/status
  - Quick log session button per student
- **Priority:** HIGH

**3. Session Logger (NEW PAGE)**
- **Current State:** Does not exist
- **New Requirements:**
  - Select student from dropdown
  - Select session type (theoretical/practical)
  - Enter actual hours (with decimal support)
  - Date and time picker
  - Notes field for session details
  - Submit and create new workflow
  - Session history log
- **Priority:** HIGH

**4. Schedule/Availability**
- **Current State:** Basic schedule view
- **New Requirements:**
  - Calendar view with booked sessions
  - Set availability blocks
  - View student bookings
  - Accept/decline booking requests
  - Recurring availability settings
- **Priority:** MEDIUM

---

#### STUDENT PAGES

**1. Student Dashboard**
- **Current State:** Basic info display
- **New Requirements:**
  - Current course card with progress ring
  - Phase status (theoretical/practical) with visual indicator
  - Unlocked modules count
  - Next steps/call-to-action (e.g., "Complete 3 more lessons to unlock Module 2")
  - Hours logged (theoretical/practical) with progress bars
  - Upcoming booked sessions
  - Recent notifications
- **Priority:** HIGH

**2. My Course (NEW PAGE)**
- **Current State:** Does not exist
- **New Requirements:**
  - Course overview header (name, license type, total hours)
  - Overall progress visualization
  - Module list with lock/unlock status
  - Lessons per module (collapsed/expandable)
  - PStudy Materials Viewer (NEW PAGE)**
- **Current State:** Does not exist
- **New Requirements:**
  - Browse course reference materials
  - View lesson content (text + videos)
  - Download attachments (PDFs, images)
  - Embedded video player (YouTube/Vimeo)
  - Module organization
  - Breadcrumb navigation (Course > Module > Lesson)
  - Note: No completion tracking needed (materials are reference only)
- **Priority:** MEDIUMon display
  - Mark as complete button
  - Previous/Next lesson navigation
  - Breadcrumb navigation (Course > Module > Lesson)
  - Time tracking (how long student spends on lesson)
- **Priority:** HIGH

**4. My Progress Page**
- **Current State:** May not exist or very basic
- **New Requirements:**
  - Timeline visualization of enrollment journey
  - Hours logged breakdown (theoretical vs practical)
  - Session history with instructor notes
  - Phase completion status
  - Proof upload status (theoretical completion)
  - Ready for practical phase indicator
- **Priority:** MEDIUM

**5. Book Sessions Page (UPDATED)**
- **Current State:** May exist with basic booking
- **New Requirements:**
  - Select session type (theoretical OR practical based on current phase)
  - Theoretical booking: In-person classroom sessions
  - Practical booking: Driving sessions with instructor
  - View instructor availability calendar
  - Request specific date/time slots
  - View booked sessions
  - Cancel/reschedule functionality
  - Booking confirmation emails
- **Priority:** HIGH

---

#### GUEST/ENROLLMENT PAGES

**1. Browse Courses Page**
- **Current State:** Basic course listing
- **New Requirements:**
  - Filter by license type (non-professional, professional)
  - Course cards showing hours required, module count
  - Preview course structure (modules/lessons outline)
  - Price display
  - Vehicle type icons/badges
  - "Enroll Now" call-to-action
- **Priority:** MEDIUM

**2. Enrollment Form**
- **Current State:** Basic form
- **New Requirements:**
  - Experience level selection (new driver vs experienced)
  - License type selection
  - If experienced: upload existing license/credentials
  - Personal information
  - Contact preferences
  - Payment options
  - Terms and conditions
  - Application review before submit
- **Priority:** HIGH

**3. Application Status Page**
- **Current State:** May not exist
- **New Requirements:**
  - Status tracker (submitted, under review, approved, enrolled)
  - Admin notes/feedback visible
  - Estimated review time
  - Notification when status changes
  - Next steps once approved
- **Priority:** LOW

---

### What We Need to Store (Database)

#### NEW STUFF TO TRACK

**1. course_modules**
```
- id (primary key)
- course_id (foreign key -> courses.id)
- title (varchar)
- description (text, nullable)
- module_type (enum: theoretical, practical_prep, reference)
- sort_order (integer)
- is_required (boolean, default true)
- unlock_condition (enum: immediate, after_previous_module, after_theoretical_hours, after_practical_hours, manual)
- unlock_after_hours (decimal, nullable)
- created_at
- updated_at
```

**2. module_lessons**
```
- id (primary key)
- module_id (foreign key -> course_modules.id)
- title (varchar)
- content (longtext)
- attachments (json, nullable - array of file paths)
- estimated_duration_minutes (integer, nullable)
- sort_order (integer)
- is_required (boolean, default true)
- created_at
- updated_at
```
theoretical_completion_proofs**
```
- id (primary key)
- enrollment_id (foreign key -> enrollments.id)
- proof_file_path (varchar - uploaded image/PDF)
- uploaded_at (timestamp)
- verified_by (foreign key -> admins.id, nullable)
- verification_status (enum: pending, approved, rejected)
- verified_at (timestamp, nullable)
- admin_notes (textllable)
- last_accessed_at (timestamp, nullable)
- created_at
- updated_at
```

**4. session_completions (UPDATED)**
```
- id (primary key)
- enrollment_id (foreign key -> enrollments.id)
- instructor_id (foreign key -> instructors.id)
- session_type (enum: theoretical, practical) // Matches course type
- hours_completed (decimal)
- session_date (date)
- session_time (time, nullable)
- notes (text, nullable)
- logged_by (foreign key -> users.id)
- created_at
- updated_at
```

**5. phase_progression_requests (REMOVED)**
```
// NO LONGER NEEDED - Theoretical completion marked directly by instructor/admin
// No separate approval/request system needed
```

**6. phase_progression_requests**
```
- id (primary key)
- enrollment_id (foreign key -> enrollments.id)
- from_phase (enum: theoretical, practical)
- to_phase (enum: practical, completed)
- requested_at (timestamp)
- reviewed_at (timestamp, nullable)
- reviewed_by (foreign key -> admins.id, nullable)
- status (enum: pending, approved, rejected)
- admin_notes (text, nullable)
- created_at
- updated_at
```

**7. documents (OPTIONAL - General Library)**
```
- id (primary key)
- school_id (foreign key -> schools.id)
- title (varchar)
- description (text, nullable)
- file_path (varchar)
- file_type (varchar)
- category (enum: forms, policies, guides, other)
- uploaded_by (foreign key -> users.id)
- is_public (boolean, default false)
- created_at
- updated_at
```

#### TABLES TO UPDATE

**1. courses table - ADD COLUMNS**
```
- license_type (enum: non_professional, professional) AFTER course_type
- theoretical_hours_required (decimal, default 8) AFTER duration
- practical_hours_required (decimal, default 16) AFTER theoretical_hours_required
```

**2. students table - ADD COLUMNS**
```
- experience_level (enum: new_driver, experienced) AFTER status
- has_passed_theoretical (boolean, default false) // Global flag
- theoretical_passed_at (timestamp, nullable)
```
course_type (enum: theoretical, practical) // NEW - identifies course category
- license_type (enum: non_professional, professional) AFTER course_type
- hours_required (decimal, default 8) // Replaces theoretical/practical hours - just total hours for this course
- requested_license_type (enum: non_professional, professional) AFTER course_id
- experience_level (enum: new_driver, experienced) AFTER requested_license_type
- credentials_file_path (varchar, nullable) AFTER experience_level
```

---

### Navigation & Layout Changes

#### ADMIN SIDEBAR NAVIGATION

**New Structure:**
```
- Dashboard
- Courses
  ├── All Courses
  └── Modules & Lessons
- Students
- Instructors
- Enrollments
  ├── Pending Requests
  ├── Active Enrollments
  └── Phase Progressions
- Documents Library
- Reports
- Settings
```

#### INSTRUCTOR SIDEBAR NAVIGATION

**New Structure:**
```
- Dashboard
- My Students
- Log Session (highlighted/quick access)
- Schedule
- Profile
```

#### STUDENT SIDEBAR NAVIGATION

**New Structure:**
```
- Dashboard
- My Course
  ├── Course Content
  └── My Progress
- Book Sessions
- Documents
- Profile
```

---

### Design System Requirements

#### STANDARD COMPONENTS TO CREATE

**1. Progress Bar Component**
- Used for: hours logged, lesson completion, overall progress
- Variants: linear bar, circular ring
- Shows: current/total, percentage
- Color-coded by phase

**2. Status Badge Component**
- Pending Enrollment (yellow background, dark text)
- Theoretical Phase (blue background, white text)
- Practical Phase (green background, white text)
- Completed (gray background, white text)
- Blocked/Issue (red background, white text)

**3. Card Components**
- Course Card (image, title, license type, hours, modules count)
- Student Card (name, photo, progress ring, phase status)
- Lesson Card (title, duration, completion status, lock icon)
- Module Card (collapsible, lesson count, unlock status)

**4. Data Table Component**
- Sortable columns
- Filterable rows
- Pagination
- Row actions dropdown
- Bulk selection checkboxes
- Export button

**5. Form Components**
- Text inputs (with validation states)
- Select dropdowns (with search)
- File upload (with preview)
- Date/time pickers
- Rich text editor (for lesson content)
- Toggle switches
- Radio button groups

**6. Modal Component**
- Small (confirm actions)
- Medium (forms)
- Large (detailed views)
- Full-screen (lesson viewer)

**7. Alert/Notification Component**
- Success (green)
- Error (red)
- Warning (yellow)
- Info (blue)
- Toast notifications (auto-dismiss)

#### COLOR SCHEME STANDARDS

**Phase Status Colors:**
- Pending Enrollment: #FFC107 (amber)
- Theoretical Phase: #2196F3 (blue)
- Practical Phase: #4CAF50 (green)
- Completed: #9E9E9E (gray)
- Issues/Blocked: #F44336 (red)

**Action Colors:**
- Primary: #1976D2 (blue)
- Secondary: #757575 (gray)
- Success: #388E3C (green)
- Danger: #D32F2F (red)
- Warning: #F57C00 (orange)

**Background Colors:**
- Main background: #F5F5F5
- Card background: #FFFFFF
- Sidebar: #263238 (dark blue-gray)
- Hover: #ECEFF1

---

### What Stays (Working Fine)

**Authentication System:**
- Login/logout functionality
- Password reset
- Session management
- Multi-tenant authentication

**Multi-tenant Structure:**
- School-based data isolation
- School settings
- School admin roles

**Core Models (just need updates):**
- User model
- Admin model
- Instructor model
- Student model
- School model

**File Storage:**
- Public storage setup
- Image uploads
- Document storage

**Basic CRUD Operations:**
- Standard create/read/update/delete patterns
- Form validation
- Error handling

---

### Summary of Biggest Changes

**1. Course Structure Transformation**
- FROM: Flat course with duration
- TO: Course with modules containing lessons with unlock conditions

**2. Enrollment Flow Redesign**
- FROM: Simple request and approval
- TO: Phase-based progression with hour tracking and lesson completion requirements

**3. Hour Tracking System**
- FROM: Manual/self-reported or estimated
- TO: Instructor-logged sessions with actual hours and notes

**4. Student Experience Shift**
- FROM: Booking-focused (schedule sessions)
- TO: Learning-focused (complete lessons, track progress, then book sessions)

**5. Admin Workflow Enhancement**
- FROM: Approve enrollments only
- TO: Manage course content, approve enrollments, oversee phase progressions, review analytics

**6. Instructor Role Expansion**
- FROM: Accept bookings and teach
- TO: Log sessions with actual hours, track student progress, provide feedback

---

### Implementation Priority Order

**PHASE 1 - FOUNDATION (Weeks 1-2)** ✅ DONE
1. Database migrations (all new tables + table updates)
2. Create/update all models
3. Course form with new fields
4. Module manager component (study materials)
5. Lesson content editor component

**PHASE 2 - CORE ENROLLMENT FLOW (Weeks 3-4)** 🔄 IN PROGRESS
6. Student enrollment system
7. Enrollment validation (course prerequisites)
8. Session booking system
9. Instructor session logger
10. Study materials viewer for students

**PHASE 3 - INSTRUCTOR/ADMIN TOOLS (Week 5)** ⏳ PENDING
11. Instructor interface features
12. Admin review and approval interface
13. Student progress tracking display
14. Phase progression approval system

**PHASE 4 - DASHBOARDS (Week 6)** ⏳ PENDING
15. Student dashboard (enrollment status, progress, next steps)
16. Instructor dashboard (assigned students, session logging)
17. Admin dashboard (enrollments, completions, metrics)

**PHASE 5 - SUPPORTING FEATURES (Week 7)** ⏳ PENDING
18. Document library
19. Reports page
20. Notifications system

**PHASE 6 - POLISH & TESTING (Week 8)**
21. UI/UX consistency pass
22. Mobile responsiveness
23. End-to-end testing (enroll theoretical → attend → marked passed → enroll practical → complete)
24. Bug fixes
25. Deployment

---

### ✅ COMPLETED: Testing Infrastructure (February 9, 2026)

**Laravel Dusk Test Suite - COMPLETE**
- ✅ **8 test files** covering Admin, Instructor, Student authentication and features
- ✅ **64 comprehensive tests** with screenshot documentation
- ✅ **Screenshot organization:** Role-based folders (`Test XXX - Name/{Role}/{step}.png`)
- ✅ **Multi-role testing:** StudentPagesTest includes student, admin, instructor, and guest accounts
- ✅ **45 tests passing** across all authentication flows and core features
- ✅ **Server configuration:** Port 9000 (`.env` and `.env.dusk.local` configured)
- ✅ **Database seeding:** UnifiedSeeder ran successfully (3 schools, multiple test accounts)
- ✅ **Test documentation:** COMPREHENSIVE_TEST_PLAN.md details all 400+ planned tests

**Test Coverage Areas:**
- Authentication (Login/Logout/Session persistence for all roles)
- Admin Dashboard & User Management
- Course Management (Admin)
- Student Pages (Dashboard, Courses, Schedule, Progress, Payments, Profile)
- Instructor Pages (Dashboard, Schedule, Students, Progress, Reports, Grades)
- Guest Registration Flow

**Next Testing Steps:**
- Complete remaining 355+ tests from comprehensive test plan
- Add tests for enrollment flows, phase progressions, session completions
- Implement tests for course content (modules/lessons)
- Test instructor session logging features
- Test admin approval workflows

---

### Timeline Overview

- **Week 1-2:** Foundation (Database migrations, models) - ✅ DONE
- **Week 3:** Course management + Enrollment system - ✅ MOSTLY DONE
- **Week 4:** Student flow + progress tracking - 🔄 IN PROGRESS
- **Week 5:** Instructor tools + session completion - ⏳ PENDING
- **Week 6:** Admin tools + approvals - ⏳ PENDING
- **Week 7-8:** Dashboards + Polish + Testing - 🔄 TESTING INFRASTRUCTURE COMPLETE

---

*This is a living document. Last updated: February 9, 2026*

