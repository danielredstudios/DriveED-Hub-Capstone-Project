# DriveED Hub - Complete System Documentation
**Version:** v1.5b  
**Last Updated:** February 2, 2026

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Technology Stack](#2-technology-stack)
3. [User Roles](#3-user-roles)
4. [Database Schema](#4-database-schema)
5. [Modules & Features](#5-modules--features)
6. [Routes & URLs](#6-routes--urls)
7. [File Structure](#7-file-structure)

---

## 1. System Overview

**DriveED Hub** is a multi-tenant driving school management system. Each driving school gets their own isolated environment with their own students, instructors, courses, and settings.

### Key Concepts

- **Multi-tenant:** One system, multiple schools. Each school has a unique URL slug (e.g., `/smart-driving`, `/lyspeed-driving`)
- **School isolation:** Data is separated by `school_id` - students from School A cannot see School B's data
- **Role-based access:** Four user types with different permissions

---

## 2. Technology Stack

| Component | Technology |
|-----------|------------|
| Backend | Laravel 12.x (PHP 8.2+) |
| Database | MySQL |
| Frontend | Blade templates, Custom CSS |
| PDF Export | DomPDF |
| CSV Export | Native PHP (Response::stream) |
| Auth | Laravel built-in (multi-guard) |
| Build Tool | Vite |
| Server | Apache (XAMPP) |

---

## 3. User Roles

### 3.1 System Admin
- Manages ALL schools
- Can create/delete schools
- Views system-wide logs
- URL: `/system-admin`

### 3.2 School Admin
- Manages ONE school
- Full control over students, instructors, courses, bookings, payments
- Manages school settings and branding
- URL: `/{school}/admin`

### 3.3 Instructor
- Views assigned students
- Logs session completions
- Manages own schedule/availability
- Marks theoretical completion
- URL: `/{school}/instructor`

### 3.4 Student
- Views enrolled courses
- Books driving sessions
- Views progress and payments
- URL: `/{school}/student`

### 3.5 Guest (Unauthenticated)
- Browses courses
- Registers for account
- Submits enrollment requests
- URL: `/{school}`

---

## 4. Database Schema

### 4.1 Core Tables

#### `schools`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | School name |
| slug | varchar | URL slug (unique) |
| timezone | varchar | School timezone |
| branding | json | Logo, colors, etc. |
| settings | json | School settings |
| instructor_removal_notice_days | int | Notice period for instructor removal |

#### `admins`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| school_id | bigint | FK to schools |
| name | varchar | Admin name |
| email | varchar | Login email |
| password | varchar | Hashed password |
| role | enum | super_admin, admin, staff |
| status | enum | active, inactive |
| profile_picture | varchar | Profile image path |

#### `instructors`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| school_id | bigint | FK to schools |
| name | varchar | Instructor name |
| email | varchar | Login email |
| password | varchar | Hashed password |
| contact | varchar | Phone number |
| status | enum | active, inactive |
| availability | json | Available days/hours |
| course_specializations | json | Courses they can teach |
| profile_picture | varchar | Profile image path |

#### `students`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| school_id | bigint | FK to schools |
| name | varchar | Student name |
| email | varchar | Login email |
| password | varchar | Hashed password |
| contact | varchar | Phone number |
| address | text | Address |
| status | enum | active, inactive |
| has_passed_theoretical | boolean | Passed theoretical exam |
| theoretical_passed_at | timestamp | When they passed |
| active_enrollment_id | bigint | Current enrollment |
| is_course_locked | boolean | Locked to one course |
| email_verified_at | timestamp | Email verification |

---

### 4.2 Course Tables

#### `courses`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| school_id | bigint | FK to schools |
| title | varchar | Course name |
| description | text | Course description |
| banner_image | varchar | Course image |
| features | json | Feature list |
| price | decimal | Course price |
| duration_hours | decimal | Total hours |
| course_type | enum | theoretical, practical |
| license_type | enum | non-professional, professional |
| hours_required | decimal | Hours to complete |
| vehicle_type | enum | manual, automatic |
| status | enum | active, inactive |
| is_featured | boolean | Featured on homepage |

#### `course_modules`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| school_id | bigint | FK to schools |
| course_id | bigint | FK to courses |
| title | varchar | Module title |
| description | text | Module description |
| sort_order | int | Display order |

#### `module_lessons`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| school_id | bigint | FK to schools |
| course_module_id | bigint | FK to course_modules |
| title | varchar | Lesson title |
| content | longtext | Lesson content (HTML) |
| video_url | varchar | YouTube/Vimeo link |
| attachments | json | File attachments |
| sort_order | int | Display order |

#### `course_packages`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| course_id | bigint | FK to courses |
| name | varchar | Package name |
| hours | decimal | Hours included |
| price | decimal | Package price |
| description | text | Package description |
| sort_order | int | Display order |

---

### 4.3 Enrollment & Booking Tables

#### `enrollment_requests`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| school_id | bigint | FK to schools |
| student_id | bigint | FK to students |
| course_id | bigint | FK to courses |
| status | enum | pending, approved, rejected, completed, cancelled |
| payment_status | enum | pending, partial, paid |
| hours_completed | decimal | Hours done so far |
| started_at | timestamp | When started |
| completed_at | timestamp | When completed |
| rejected_reason | text | Why rejected |
| verified_by | bigint | Admin who approved |
| verification_notes | text | Admin notes |

#### `enrollments`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| school_id | bigint | FK to schools |
| student_id | bigint | FK to students |
| course_id | bigint | FK to courses |
| status | enum | active, completed, cancelled |
| hours_completed | decimal | Hours completed |
| started_at | timestamp | Start date |
| completed_at | timestamp | Completion date |

#### `bookings`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| school_id | bigint | FK to schools |
| student_id | bigint | FK to students |
| instructor_id | bigint | FK to instructors |
| course_id | bigint | FK to courses |
| time_slot_id | bigint | FK to time_slots |
| scheduled_at | datetime | Booking date/time |
| duration_hours | decimal | Session length |
| status | enum | pending, confirmed, completed, cancelled, no_show |
| attendance_status | enum | present, absent, late |
| instructor_feedback | text | Instructor notes |
| cancellation_reason | text | Why cancelled |
| cancelled_by | varchar | Who cancelled |

#### `time_slots`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| school_id | bigint | FK to schools |
| instructor_id | bigint | FK to instructors |
| date | date | Slot date |
| start_time | time | Start time |
| end_time | time | End time |
| status | enum | available, booked, blocked |
| max_bookings | int | Max students |

#### `session_completions`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| school_id | bigint | FK to schools |
| enrollment_id | bigint | FK to enrollments |
| booking_id | bigint | FK to bookings (nullable) |
| instructor_id | bigint | FK to instructors |
| student_id | bigint | FK to students |
| course_id | bigint | FK to courses |
| session_date | date | Session date |
| scheduled_duration_hours | decimal | Planned hours |
| actual_duration_hours | decimal | Actual hours |
| completion_status | enum | completed, incomplete, no_show |
| instructor_notes | text | Session notes |
| completed_at | timestamp | Completion time |

---

### 4.4 Payment & Progress Tables

#### `payments`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| booking_id | bigint | FK to bookings |
| amount | decimal | Payment amount |
| method | enum | cash, card, bank_transfer, gcash |
| reference | varchar | Reference number |
| status | enum | pending, paid, refunded |
| paid_on | date | Payment date |
| notes | text | Payment notes |

#### `progresses`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| school_id | bigint | FK to schools |
| student_id | bigint | FK to students |
| course_id | bigint | FK to courses |
| instructor_id | bigint | FK to instructors |
| lesson_date | date | Lesson date |
| skills_covered | json | Skills practiced |
| performance_rating | int | 1-5 rating |
| notes | text | Instructor notes |
| areas_for_improvement | text | What to work on |

---

### 4.5 Other Tables

#### `school_settings`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| school_id | bigint | FK to schools |
| primary_color | varchar | Brand color |
| secondary_color | varchar | Secondary color |
| logo_path | varchar | Logo image |
| address | text | School address |
| contact_number | varchar | Phone |
| email | varchar | Email |
| operating_hours | json | Open hours |
| advance_booking_days | int | Days in advance to book |
| booking_queue_days | int | Queue hold days |
| enable_booking_queue | boolean | Enable queue system |
| instructor_selection_mode | enum | auto_assign, student_chooses |

#### `instructor_removal_requests`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| time_slot_id | bigint | FK to time_slots |
| instructor_id | bigint | FK to instructors |
| reason | text | Removal reason |
| status | enum | pending, approved, rejected |
| admin_notes | text | Admin response |

#### `system_logs`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| school_id | bigint | FK to schools (nullable) |
| user_type | varchar | admin, instructor, student, system |
| user_id | bigint | User who did action |
| action | varchar | Action taken |
| category | enum | auth, booking, payment, etc. |
| description | text | Details |
| ip_address | varchar | IP address |
| user_agent | text | Browser info |
| severity | enum | info, warning, error, critical |

#### `reports`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| school_id | bigint | FK to schools |
| title | varchar | Report title |
| type | enum | booking, payment, student, instructor |
| generated_by | bigint | Admin who generated |
| parameters | json | Report filters |
| file_path | varchar | Generated file |

---

## 5. Modules & Features

### 5.1 Authentication Module

**Features:**
- Multi-guard authentication (admin, instructor, student)
- Login/logout
- Password reset via email
- Email verification for new students
- Session management
- "Remember me" functionality

**Files:**
- `AuthController.php`
- `PasswordResetController.php`
- `login.blade.php`
- `register.blade.php`

---

### 5.2 School Admin Module

**Features:**

| Feature | Description |
|---------|-------------|
| Dashboard | Stats overview, charts, pending actions |
| Student Management | CRUD students, view details, export |
| Instructor Management | CRUD instructors, set availability |
| Course Management | CRUD courses, modules, lessons, packages |
| Booking Management | View/approve/reject bookings |
| Payment Management | Record payments, view history, export |
| Enrollment Requests | Approve/reject enrollment applications |
| Time Slot Management | Create schedules, assign instructors |
| Reports | Generate various reports |
| Settings | School branding, colors, logo |
| User Management | View all users by role |
| Theoretical Completion | Mark students who passed theory |

**Files:**
- `AdminController.php`
- `views/school/admin/*.blade.php`

---

### 5.3 Instructor Module

**Features:**

| Feature | Description |
|---------|-------------|
| Dashboard | Today's schedule, stats |
| My Schedule | View upcoming sessions |
| Students | View assigned students |
| Session Completion | Log completed sessions with hours/notes |
| Progress Records | Create progress reports for students |
| Theoretical Marking | Mark students who passed theory |
| Profile | Update personal info, picture |
| Availability | Set available days/times |

**Files:**
- `InstructorController.php`
- `SessionCompletionController.php`
- `views/school/instructor/*.blade.php`

---

### 5.4 Student Module

**Features:**

| Feature | Description |
|---------|-------------|
| Dashboard | Current course, upcoming sessions |
| Schedule | View available slots, book sessions |
| My Course | View enrolled course, progress |
| Course Content | Access modules and lessons |
| Payments | View payment history |
| Progress | View instructor feedback |
| Profile | Update personal info, picture |

**Files:**
- `StudentController.php`
- `views/school/student/*.blade.php`

---

### 5.5 Guest Module

**Features:**

| Feature | Description |
|---------|-------------|
| Homepage | School info, featured courses |
| Course Browsing | View all available courses |
| Registration | Create student account |
| Enrollment Request | Apply for a course |
| Email Verification | Verify email address |

**Files:**
- `GuestController.php`
- `RegistrationController.php`
- `views/school/guest/*.blade.php`

---

### 5.6 System Admin Module

**Features:**

| Feature | Description |
|---------|-------------|
| Dashboard | System-wide stats |
| Schools Management | Create/edit/delete schools |
| Users Overview | View all users across schools |
| System Logs | View/filter/resolve logs |
| Admin Accounts | Create system admin accounts |

**Files:**
- `SystemAdminController.php`
- `views/system-admin/*.blade.php`

---

### 5.7 Export Module

**Features:**

| Export Type | Formats |
|-------------|---------|
| Students List | PDF, CSV |
| Instructors List | PDF, CSV |
| Payments Report | PDF, CSV |
| Courses List | PDF |
| Enrollments Report | PDF |
| Schedules | PDF |
| Student Progress | PDF |

**Files:**
- `ExportController.php`

---

### 5.8 Course Content Module

**Features:**
- Create course modules
- Add lessons to modules
- Rich text content with HTML
- Video embeds (YouTube/Vimeo)
- File attachments
- Drag-and-drop reordering
- Duplicate modules

**Files:**
- `CourseModuleController.php`
- `ModuleLessonController.php`

---

## 6. Routes & URLs

### 6.1 Public Routes

| URL | Description |
|-----|-------------|
| `/` | Welcome page (school selection) |
| `/{school}` | School homepage/login |
| `/{school}/register` | Student registration |
| `/{school}/forgot-password` | Password reset request |
| `/{school}/reset-password/{token}` | Password reset form |
| `/{school}/guest/courses` | Browse courses |
| `/{school}/guest/dashboard` | Guest dashboard |

### 6.2 Student Routes

| URL | Description |
|-----|-------------|
| `/{school}/student` | Student dashboard |
| `/{school}/student/schedule` | View/book schedules |
| `/{school}/student/my-course` | Current enrollment |
| `/{school}/student/courses` | Browse courses |
| `/{school}/student/courses/{id}/modules` | Course modules |
| `/{school}/student/payments` | Payment history |
| `/{school}/student/progress` | Progress records |
| `/{school}/student/profile` | Edit profile |

### 6.3 Instructor Routes

| URL | Description |
|-----|-------------|
| `/{school}/instructor` | Instructor dashboard |
| `/{school}/instructor/my-schedule` | My schedule |
| `/{school}/instructor/students` | My students |
| `/{school}/instructor/students/{id}` | Student detail |
| `/{school}/instructor/sessions` | Session completions |
| `/{school}/instructor/sessions/create` | Log new session |
| `/{school}/instructor/progress` | Progress records |
| `/{school}/instructor/theoretical` | Mark theoretical pass |
| `/{school}/instructor/profile` | Edit profile |

### 6.4 Admin Routes

| URL | Description |
|-----|-------------|
| `/{school}/admin` | Admin dashboard |
| `/{school}/admin/user-management` | All users |
| `/{school}/admin/courses` | Manage courses |
| `/{school}/admin/courses/{id}/modules` | Course modules |
| `/{school}/admin/bookings` | Manage bookings |
| `/{school}/admin/payments` | Manage payments |
| `/{school}/admin/enrollments` | Enrollment requests |
| `/{school}/admin/schedules` | Time slots |
| `/{school}/admin/sessions` | Session completions |
| `/{school}/admin/theoretical` | Theoretical status |
| `/{school}/admin/reports` | Reports |
| `/{school}/admin/settings` | School settings |
| `/{school}/admin/profile` | Admin profile |
| `/{school}/admin/exports/*` | Export endpoints |

### 6.5 System Admin Routes

| URL | Description |
|-----|-------------|
| `/system-admin` | System admin dashboard |
| `/system-admin/login` | System admin login |
| `/system-admin/schools` | Manage schools |
| `/system-admin/users` | All users |
| `/system-admin/admins` | System admins |
| `/system-admin/logs` | System logs |

---

## 7. File Structure

```
DrivingApp/
├── app/
│   ├── Console/Commands/          # Artisan commands
│   ├── Http/
│   │   ├── Controllers/           # All controllers
│   │   │   ├── AdminController.php
│   │   │   ├── AuthController.php
│   │   │   ├── BookingController.php
│   │   │   ├── CourseController.php
│   │   │   ├── CourseModuleController.php
│   │   │   ├── EnrollmentRequestController.php
│   │   │   ├── ExportController.php
│   │   │   ├── GuestController.php
│   │   │   ├── InstructorController.php
│   │   │   ├── ModuleLessonController.php
│   │   │   ├── PasswordResetController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── ProgressController.php
│   │   │   ├── SessionCompletionController.php
│   │   │   ├── StudentController.php
│   │   │   ├── SystemAdminController.php
│   │   │   └── TheoreticalCompletionController.php
│   │   └── Middleware/            # Auth middleware
│   ├── Models/                    # Eloquent models
│   │   ├── Admin.php
│   │   ├── Booking.php
│   │   ├── Course.php
│   │   ├── CourseModule.php
│   │   ├── CoursePackage.php
│   │   ├── Enrollment.php
│   │   ├── EnrollmentRequest.php
│   │   ├── Instructor.php
│   │   ├── ModuleLesson.php
│   │   ├── Payment.php
│   │   ├── Progress.php
│   │   ├── School.php
│   │   ├── SchoolSetting.php
│   │   ├── SessionCompletion.php
│   │   ├── Student.php
│   │   ├── SystemLog.php
│   │   └── TimeSlot.php
│   ├── Providers/
│   └── Support/
│       ├── CacheHelper.php
│       └── helpers.php
│
├── database/
│   ├── migrations/                # 43 migration files
│   └── seeders/                   # Database seeders
│
├── resources/
│   └── views/
│       ├── school/
│       │   ├── admin/             # Admin views
│       │   │   ├── dashboard.blade.php
│       │   │   ├── courses.blade.php
│       │   │   ├── bookings.blade.php
│       │   │   ├── payments.blade.php
│       │   │   ├── settings.blade.php
│       │   │   └── ...
│       │   ├── instructor/        # Instructor views
│       │   │   ├── dashboard.blade.php
│       │   │   ├── students.blade.php
│       │   │   ├── schedule.blade.php
│       │   │   └── ...
│       │   ├── student/           # Student views
│       │   │   ├── dashboard.blade.php
│       │   │   ├── schedule.blade.php
│       │   │   ├── my-course.blade.php
│       │   │   └── ...
│       │   ├── guest/             # Guest views
│       │   ├── login.blade.php
│       │   └── register.blade.php
│       ├── system-admin/          # System admin views
│       ├── layouts/               # Layout templates
│       ├── components/            # Blade components
│       └── exports/               # PDF templates
│
├── routes/
│   └── web.php                    # All routes (196 total)
│
├── public/
│   ├── images/                    # Uploaded images
│   └── build/                     # Compiled assets
│
├── config/                        # Laravel config files
├── storage/                       # Logs, uploads, cache
└── vendor/                        # Composer packages
```

---

## Summary

| Metric | Count |
|--------|-------|
| Database Tables | 18 |
| Models | 23 |
| Controllers | 21 |
| Routes | 196 |
| User Roles | 5 |
| Migration Files | 43 |

**Core Features:**
- Multi-tenant school management
- Student enrollment & course management
- Booking & scheduling system
- Payment tracking
- Session completion logging
- Progress tracking
- PDF/CSV exports
- Course content (modules/lessons)
- Email notifications
- Password reset
- Mobile responsive UI

---

*End of Documentation*
