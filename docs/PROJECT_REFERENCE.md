# DriveED Hub - Project Reference Guide

**Last Updated:** March 27, 2026  
**Status:** Stabilized (Post-Audit March Rework)  
**Purpose:** Quick reference for database structure, accounts, views, and development preferences

---

## 📋 COMPLETE DATABASE TABLES

| Table Name | Primary Purpose | Key Fields |
|------------|-----------------|------------|
| `schools` | Multi-tenant schools | id, name, slug, branding (json), settings (json) |
| `branches` | School subsidiaries | school_id, name, slug, address, is_active |
| `admins` | Admin users | school_id, branch_id (null=school admin), name, email, is_active |
| `instructors` | Instructor users | school_id, name, email, license_number, status, availability |
| `students` | Students & Guests | school_id, branch_id, name, email, role (guest/student), is_active |
| `courses` | Available courses | school_id, title, course_type, license_type, price, status |
| `course_packages` | Course bundles | course_id, name, price, sort_order |
| `enrollment_requests` | Course enrollments | school_id, learner_id, course_id, status, payment_status, price |
| `bookings` | Schedule bookings | school_id, student_id, instructor_id, slot_id, status |
| `payments` | **Read-Only Ledger** | school_id, payer_user_id, amount, method, status, reference |
| `gcash_settings` | GCash Credentials | school_id, branch_id, gcash_number, qr_path, is_active |
| `payment_status_logs` | Forensic Audit | payment_id, status, action_by_admin_id, notes |
| `session_completions` | Driving sessions | enrollment_id, instructor_id, hours_completed, status |
| `progresses` | Student performance | student_id, instructor_id, description, rating (1-5) |
| `logs` | Activity logs | school_id, action, description, user_type, user_id |

---

## 📁 VIEW STRUCTURE & PAGE NAMES

### System Admin Views (`resources/views/system-admin/`)
| File | Page Title | URL Pattern |
|------|------------|-------------|
| `login.blade.php` | System Admin Login | `/system-admin/login` |
| `dashboard.blade.php` | System Dashboard | `/system-admin/` |
| `schools.blade.php` | Manage Schools | `/system-admin/schools` |
| `admins.blade.php` | Manage Admins | `/system-admin/admins` |
| `users.blade.php` | All Users | `/system-admin/users` |
| `students.blade.php` | All Students | `/system-admin/students` |
| `instructors.blade.php` | All Instructors | `/system-admin/instructors` |
| `index.blade.php` | All Schools | `/system-admin/schools` |
| `admins.blade.php` | All Admins | `/system-admin/admins` |
| `users.blade.php` | All Users | `/system-admin/users` |
| `logs/index.blade.php` | System Logs | `/system-admin/logs` |
| `logs/show.blade.php` | Log Detail | `/system-admin/logs/{id}` |

### School Admin Views (`resources/views/school/admin/`)
| File | Page Title | URL Pattern |
|------|------------|-------------|
| `dashboard.blade.php` | Admin Dashboard | `/{school}/admin/` |
| `user-management.blade.php` | User Management | `/{school}/admin/user-management` |
| `courses.blade.php` | Courses | `/{school}/admin/courses` |
| `schedules.blade.php` | Schedules | `/{school}/admin/schedules` |
| `bookings/index.blade.php` | Bookings | `/{school}/admin/bookings` |
| `payments/index.blade.php` | Payments | `/{school}/admin/payments` |
| `settings.blade.php` | School Settings | `/{school}/admin/settings` |
| `branches/index.blade.php` | Branches | `/{school}/admin/branches` |
| `enrollments/index.blade.php` | Enrollments | `/{school}/admin/enrollments` |
| `theoretical/index.blade.php` | Theoretical | `/{school}/admin/theoretical` |
| `reports/index.blade.php` | Reports | `/{school}/admin/reports` |

### Instructor Views (`resources/views/school/instructor/`)
| File | Page Title | URL Pattern |
|------|------------|-------------|
| `dashboard.blade.php` | Instructor Dashboard | `/{school}/instructor/` |
| `schedule.blade.php` | My Schedule | `/{school}/instructor/my-schedule` |
| `schedule-new.blade.php` | Schedule (New) | `/{school}/instructor/my-schedule` |
| `students.blade.php` | My Students | `/{school}/instructor/students` |
| `student-detail.blade.php` | Student Detail | `/{school}/instructor/students/{id}` |
| `progress.blade.php` | Progress List | `/{school}/instructor/progress` |
| `progress-create.blade.php` | Log Progress | `/{school}/instructor/progress/create` |
| `progress-edit.blade.php` | Edit Progress | `/{school}/instructor/progress/{id}/edit` |
| `progress-show.blade.php` | View Progress | `/{school}/instructor/progress/{id}` |
| `grades.blade.php` | Grades | `/{school}/instructor/grades` |
| `reports.blade.php` | My Reports | `/{school}/instructor/reports` |
| `profile.blade.php` | Instructor Profile | `/{school}/instructor/profile` |
| `sessions/*.blade.php` | Session Management | `/{school}/instructor/sessions` |
| `theoretical/*.blade.php` | Theoretical Sessions | `/{school}/instructor/theoretical` |

### Student Views (`resources/views/school/student/`)
| File | Page Title | URL Pattern |
|------|------------|-------------|
| `dashboard.blade.php` | Student Dashboard | `/{school}/student/dashboard` |
| `courses.blade.php` | My Courses | `/{school}/student/courses` |
| `schedule.blade.php` | My Schedule | `/{school}/student/schedule` |
| `payments.blade.php` | My Payments | `/{school}/student/payments` |
| `progress.blade.php` | My Progress | `/{school}/student/progress` |
| `profile.blade.php` | Student Profile | `/{school}/student/profile` |

### Guest Views (`resources/views/school/guest/`)
| File | Page Title | URL Pattern |
|------|------------|-------------|
| `dashboard.blade.php` | Guest Dashboard | `/{school}/guest/dashboard` |
| `courses.blade.php` | Available Courses | `/{school}/guest/courses` |
| `enrollment-requests.blade.php` | My Requests | `/{school}/guest/enrollment-requests` |

### Auth Views (`resources/views/school/`)
| File | Page Title | URL Pattern |
|------|------------|-------------|
| `login.blade.php` | School Login | `/{school}/login` |
| `register.blade.php` | Register | `/{school}/register` |
| `verify-email.blade.php` | OTP Verification | `/{school}/verify-email` |
| `password/forgot.blade.php` | Forgot Password | `/{school}/forgot-password` |
| `password/reset.blade.php` | Reset Password | `/{school}/reset-password/{token}` |

### Layout & Partial Views
| File | Purpose |
|------|---------|
| `layouts/app.blade.php` | Main layout with sidebar |
| `layouts/ajax.blade.php` | AJAX response layout |
| `partials/*.blade.php` | Reusable components |
| `exports/*.blade.php` | PDF export templates |
| `emails/*.blade.php` | Email templates |

---

## 🏫 EXISTING SCHOOLS (PRESERVE THESE!)

**⚠️ IMPORTANT:** Always keep these 3 schools intact. They are seeded by `OldSchoolsSeeder` and `Alpha2TestSeeder`.

1. **Smart Driving School**
   - Slug: `smart-driving`
   - Timezone: Asia/Manila
   - Colors: Primary #3b82f6, Secondary #1e40af, Accent #f59e0b

2. **LySpeed Driving School**
   - Slug: `lyspeed-driving`
   - Timezone: Asia/Manila
   - Colors: Primary #8b5cf6, Secondary #6d28d9, Accent #ec4899

3. **Alpha 2 LMS School** (Created by Alpha2TestSeeder)
   - Slug: TBD (check in database)
   - Used for testing LMS features

---

## 📊 DATABASE SCHEMA REFERENCE

### **schools** Table
```php
id                              // bigint primary key
name                           // string
slug                           // string, unique
timezone                       // string, default 'UTC'
branding                       // json (logo, colors)
settings                       // json (contact, email, address, allow_self_registration)
instructor_removal_notice_days // int, default 7
timestamps                     // created_at, updated_at
```

**❌ DOES NOT HAVE:** email, phone, address, is_active (these are in JSON fields)

---

### **school_settings** Table
```php
id                           // bigint primary key
school_id                    // foreign key (unique, one-to-one)
primary_color                // string(7), default '#2563eb'
secondary_color              // string(7), default '#fbbf24'
accent_color                 // string(7), default '#1e40af'
background_type              // string, default 'color'
background_color             // string, default '#f5f5f5'
background_image             // string, nullable
background_opacity           // int, default 100
sidebar_bg_color             // string(7), default '#ffffff'
sidebar_text_color           // string(7), default '#333333'
sidebar_hover_color          // string(7), default '#f5f5f5'
use_gradient_header          // boolean, default true
header_text_color            // string(7), default '#ffffff'
login_header_layout          // string, default 'horizontal'
login_logo_image             // string, nullable
login_logo_position          // string, default 'left'
login_logo_size              // int, default 40
login_school_name_text       // string, nullable
login_show_school_name       // boolean, default true
login_school_name_position   // string, default 'left'
login_school_name_size       // int, default 24
login_welcome_text           // string, default 'Welcome!'
login_show_welcome_text      // boolean, default true
login_welcome_position       // string, default 'right'
login_welcome_size           // int, default 16
timestamps
```

**❌ DOES NOT HAVE:** logo_path (use login_logo_image instead)

---

### **admins** Table
```php
id                    // bigint primary key
school_id             // foreign key, nullable (null = system admin)
branch_id             // foreign key, nullable (Branch Secretary scope)
name                  // string
email                 // string, unique (globally unique for admins)
password              // string (hashed)
role                  // string, default 'school_admin'
is_active             // boolean, default true
timestamps
```

---

### **instructors** Table
```php
id                       // bigint primary key
school_id                // foreign key, required
name                     // string
email                    // string
password                 // string (hashed)
license_number           // string, nullable
status                   // string, default 'active'
availability             // enum('available', 'unavailable'), default 'available'
profile_picture          // string, nullable
timestamps
```

---

### **students** Table
```php
id                    // bigint primary key
school_id             // foreign key, required
branch_id             // foreign key, nullable
name                  // string
email                 // string
password              // string (hashed)
contact               // string, nullable
address               // string, nullable
location              // string, nullable
profile_picture       // string, nullable
role                  // enum('guest', 'student'), default 'guest'
is_active             // boolean, default true
status                // string, default 'active'

// Student License (Forensic Documents)
student_license_path             // string, path to uploaded file
student_license_status           // string (pending, verified, rejected)
student_license_verified_by      // foreignId to admins
student_license_verified_at      // timestamp

// Progress Tracking
has_passed_theoretical           // boolean, default false
theoretical_passed_at            // timestamp
active_enrollment_id             // foreignId to enrollment_requests
is_course_locked                 // boolean (safety lock)

// Security & Verification
verification_code                // string(6) OTP
verification_attempts            // int
verification_code_expires_at     // timestamp
email_verified_at                // timestamp
failed_login_attempts            // int
locked_until                     // timestamp
last_login_at                    // timestamp
timestamps
```

---

### **enrollment_requests** Table
```php
id                           // bigint primary key
school_id                    // foreign key, required
branch_id                    // foreign key, nullable
learner_id                   // foreign key to students, required
course_id                    // foreign key to courses, required
package_id                   // foreign key to course_packages, nullable
status                       // enum('pending', 'approved', 'completed', 'cancelled', 'rejected')

// Financial Snapshot & Feedback
price                        // decimal(10,2) snapshotted at enrollment
payment_status               // enum('pending', 'paid', 'partial', 'on_hold')
payment_method               // string (gcash, on_site)
payment_reference            // string (Digits only)
payment_proof_path           // string (Path to screenshot)
remarks                      // text (Internal timeline)

// Verification Audit
approved_at                  // timestamp
approved_by                  // foreign key to admins
payment_confirmed_at         // timestamp
payment_confirmed_by         // foreign key to admins
rejection_reason             // text
rejected_at                  // timestamp

// Theoretical Phase
theoretical_passed           // boolean, default false
theoretical_passed_at        // timestamp
theoretical_passed_by        // foreignId to admins
timestamps
```

---

### **courses** Table
```php
id               // bigint primary key
school_id        // foreign key, required
title            // string
description      // text, nullable
course_type      // enum('theoretical', 'practical')
license_type     // enum('non_professional', 'professional')
price            // decimal(10,2)
hours_required   // decimal(10,2)
duration_hours   // decimal(5,1)
vehicle_type     // string (manual, automatic, sedan, suv)
status           // string (active, inactive, archived)
is_featured      // boolean
sort_order       // int
```

---

### **payments** Table (Read-Only Ledger)
```php
id                       // bigint primary key
school_id                // foreign key, required
branch_id                // foreign key, nullable
booking_id               // foreign key (XOR with enrollment_request_id)
enrollment_request_id    // foreign key (XOR with booking_id)

// Identity (Enforced XOR)
payer_user_id            // foreign key to students (for registered users)
guest_identity_token     // string (for guest sessions)

// Forensic Data
amount                   // decimal(10,2)
method                   // enum('gcash', 'on_site')
reference                // string (Raw input)
normalized_reference     // string (Alphanumeric uppercase for uniqueness)
or_number                // string (Official Receipt number for on-site)
proof_of_payment_path    // string (Path to receipt image)
status                   // enum('pending', 'approved', 'rejected', 'refunded')

// Verification Audit
received_at              // timestamp
received_by_admin_id     // foreignId to admins
refunded_at              // timestamp
refunded_by_admin_id     // foreignId to admins
timestamps

UNIQUE: (school_id, normalized_reference)
```

---

### **gcash_settings** Table
```php
id               // bigint primary key
school_id        // foreign key, required
branch_id        // foreign key, nullable (null = school-wide default)
account_name     // string
gcash_number     // string
qr_path          // string (Path to QR image)
is_active        // boolean
timestamps
```

---

### **payment_status_logs** Table
```php
id                   // bigint primary key
payment_id           // foreign key, required
status               // string (The target status)
action_by_admin_id   // foreign key to admins (null = automated)
notes                // text
timestamps
```

---

### **branches** Table
```php
id               // bigint primary key
school_id        // foreign key, required
name             // string
slug             // string (unique per school)
address          // string, nullable
is_active        // boolean
timestamps
```

---

### **password_reset_tokens** Table
```php
email       // string, primary key
token       // string (hashed)
user_type   // string (admin, instructor, student)
school_id   // foreign key, nullable
created_at  // timestamp (60-minute expiry)
```

---

## 🔐 ACCOUNT STRUCTURE

### Authentication Guards
1. **admin** → Admin model
2. **instructor** → Instructor model
3. **student** → Student model (includes guests)

### Account Types
1. **System Admin** → `admins.school_id = NULL`, can access all schools
2. **School Admin** → `admins.school_id = X`, manages one school
3. **Instructor** → School-specific, teaches courses
4. **Student** → School-specific, `role='student'`, enrolled in courses
5. **Guest** → School-specific, `role='guest'`, waiting for approval

---

## 🔑 EXISTING TEST ACCOUNTS (From OldSchoolsSeeder)

### Smart Driving School (smart-driving)
- **Admin:** schooladmin@gmail.com / password
- **Admin:** systemadmin@gmail.com / password
- **Instructor:** instructor1@smart.com / password
- **Instructor:** instructor2@smart.com / password
- **Student:** student1@smart.com / password
- **Student:** student2@smart.com / password

### LySpeed Driving School (lyspeed-driving)
- **Admin:** lyspeed.admin@gmail.com / password
- **Admin:** lyspeed.system@gmail.com / password
- **Instructor:** instructor1@lyspeed.com / password
- **Instructor:** instructor2@lyspeed.com / password
- **Student:** student1@lyspeed.com / password
- **Student:** student2@lyspeed.com / password

---

## 🛠️ IMPORTANT CODE PATTERNS

### 1. Always Use Null-Safe Operators for School Settings
```php
// ✅ CORRECT
$school = School::where('slug', $slug)->first();
$settings = $school?->schoolSetting;
$primaryColor = $settings?->primary_color ?? '#2563eb';

// ❌ WRONG - Will cause null reference errors
$settings = $school->schoolSetting;
$primaryColor = $settings->primary_color;
```

### 2. Use learner_id, Not student_id
```php
// ✅ CORRECT
EnrollmentRequest::where('learner_id', $student->id)->get();

// ❌ WRONG
EnrollmentRequest::where('student_id', $student->id)->get();
```

### 3. Check Role, Not Just Authentication
```php
// ✅ CORRECT - Check if approved student
if ($student->role === 'student') {
    // Full student features
} else {
    // Guest dashboard only
}

// ❌ WRONG - Guests can also be authenticated
if (Auth::guard('student')->check()) {
    // This includes guests!
}
```

### 4. School-Specific Queries
```php
// ✅ CORRECT - Always filter by school
$students = Student::where('school_id', $school->id)->get();

// ❌ WRONG - Will show all schools' data
$students = Student::all();
```

### 5. Transaction-Safe Bulk Operations
```php
// ✅ CORRECT
DB::transaction(function () use ($enrollments, $school) {
    foreach ($enrollments as $enrollment) {
        // Update enrollment
        // Send email (with try-catch)
        // Update student role
    }
});

// ❌ WRONG - No rollback on failure
foreach ($enrollments as $enrollment) {
    $enrollment->update(...);
    Mail::send(...); // If this fails, previous updates remain
}
```

### 6. Payments = Read-Only Ledger
All payment records are for audit purposes only. **Never** delete a payment. If a payment is invalid, mark its status as `rejected` and provide a `rejection_reason_note`. If a payment is returned, use the `refunded_at` and `refunded_amount` fields.

### 7. Standardized Storage Directive
All new uploads **MUST** be stored in the architectural root `receipts/` or `student-licenses/` on the `local` disk. 
- **GCash Receipts**: `receipts/{school_id}/` (via `ReceiptStorageService`)
- **Student Licenses**: `student-licenses/` (via `StorageController`)
- **Credentials**: `credentials/` (via `StorageController`)
- **Legacy**: `screenshots/payments/` on `public` disk (Read-only access only)

---

## 📧 EMAIL SYSTEM

**Current Mode:** Development (MAIL_MAILER=log)
- Emails are **NOT sent** to real addresses
- All emails logged to `storage/logs/laravel.log`
- Safe for testing without spamming users

**Implemented Mailables:**
1. `EnrollmentApproved` → Sent when admin approves enrollment request
2. `EnrollmentRequestReceived` → (Placeholder, not implemented)
3. `SessionReminder` → (Placeholder, not implemented)

---

## 🚀 SEEDERS

### DatabaseSeeder (Main)
Calls: `OldSchoolsSeeder`, `Alpha2TestSeeder`

### OldSchoolsSeeder
- Creates Smart Driving School + LySpeed Driving School
- Creates admins, instructors, students for both schools
- **Always preserve this data!**

### Alpha2TestSeeder
- Creates test data for LMS features
- Uses `EnrollmentRequest` model (not old Enrollment)

### SystemAdminSeeder
- Creates system-wide admin accounts
- Run with: `php artisan db:seed --class=SystemAdminSeeder`

### QuickTestSeeder (New - In Progress)
- Backdoor seeder for easy testing
- Simple credentials: all use "password"
- Creates test-school with admin/instructor/student/guest accounts
- **View credentials in browser:** http://localhost:8000/test/credentials/test-school

---

## 🎨 FRONTEND PATTERNS

### Modal Implementation
**Always follow existing modal pattern:**
```html
<!-- Modal trigger button -->
<button class="btn-action" onclick="openModal('modalId')">Open</button>

<!-- Modal structure -->
<div class="modal-backdrop" id="modalId-backdrop" style="display: none;" onclick="closeModal('modalId')"></div>
<div class="modal" id="modalId" style="display: none;">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Modal Title</h3>
            <span class="modal-close" onclick="closeModal('modalId')">&times;</span>
        </div>
        <div class="modal-body">
            <!-- Content -->
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
    document.getElementById(modalId + '-backdrop').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.getElementById(modalId + '-backdrop').style.display = 'none';
}
</script>
```

### Custom CSS (No Bootstrap JS)
- Use custom CSS classes: `.btn-primary`, `.btn-secondary`, `.btn-danger`, `.card`, `.badge`
- All styles in `resources/css/app.css` and `resources/css/dashboard.css`
- Built with Vite: `npm run build` or `npm run dev`

---

## 📦 RECENTLY ADDED FEATURES

### 1. Password Reset System
**Files:**
- Migration: `2026_01_17_092844_create_password_reset_tokens_table.php`
- Controller: `PasswordResetController.php`
- Views: `resources/views/school/password/forgot.blade.php`, `reset.blade.php`
- Routes: `password.request`, `password.email`, `password.reset`, `password.update`

**How it works:**
- User enters email + user type (admin/instructor/student)
- System finds user in correct table
- Sends reset link (logged to laravel.log in dev mode)
- User clicks link, sets new password
- Token expires after 60 minutes

### 2. Email Notifications
**Files:**
- Mailable: `app/Mail/EnrollmentApproved.php`
- Template: `resources/views/emails/enrollment-approved.blade.php`

**Triggered when:**
- Admin approves enrollment request
- Email sent to learner's email address
- Includes enrollment details, school branding, gradient header

### 3. Bulk Operations
**Files:**
- Controller: `EnrollmentRequestController.php` (bulkApprove, bulkReject methods)
- Routes: `enrollments.bulkApprove`, `enrollments.bulkReject`

**Features:**
- Approve/reject multiple enrollment requests at once
- Transaction-safe (all-or-nothing)
- Sends individual emails for each approval
- Returns success/failure counts

**UI:** ⚠️ Not yet added (needs checkboxes + buttons in views)

### 4. Export Functionality
**Files:**
- Controller: `ExportController.php`
- Views: `resources/views/exports/students-pdf.blade.php`, `enrollments-pdf.blade.php`
- Routes: `exports.students.pdf`, `exports.students.excel`, `exports.enrollments.pdf`, `exports.student.progress.pdf`

**Packages:**
- barryvdh/laravel-dompdf v3.1.1 (PDF generation)
- maatwebsite/excel v1.1.5 (Excel exports)

**UI:** ⚠️ Not yet added (needs export buttons in views)

---

## ⚙️ DEVELOPMENT PREFERENCES

### Do's ✅
1. **Always preserve the 3 existing schools** (Smart, LySpeed, Alpha 2 LMS)
2. **Use null-safe operators** for all relationship chains
3. **Check the actual database schema** before writing seeders
4. **Use learner_id** in enrollment_requests queries
5. **Follow existing modal patterns** (don't reinvent)
6. **Test with QuickTestSeeder** for fast iteration
7. **Use transactions** for multi-step database operations
8. **Log errors** with context (use `Log::warning()`, `Log::error()`)

### Don'ts ❌
1. **Don't delete or modify** existing school data
2. **Don't assume column names** - check migrations first
3. **Don't use student_id** - it's `learner_id`
4. **Don't create new modal patterns** - use existing ones
5. **Don't send real emails** in development
6. **Don't forget to rebuild Vite** after CSS/JS changes: `npm run build`
7. **Don't use Bootstrap JS** - we use custom JavaScript

---

## 📁 STORAGE ARCHITECTURE (March 27)

### **Directive: Single-Root Standardization**
All system uploads are being consolidated into a single architectural root for better maintainability and backup isolation.

| Component | Standard Path | Storage Disk | Service / Controller |
|-----------|---------------|--------------|----------------------|
| **GCash Receipts** | `receipts/{school_id}/` | `local` | `ReceiptStorageService` |
| **Licenses** | `student-licenses/` | `local` | `StorageController` |
| **Credentials** | `credentials/` | `local` | `StorageController` |
| **Legacy Receipts** | `screenshots/payments/` | `public` | Read-only access |

### **Key Retrieval Rule**
The `StorageController@streamReceipt` and `EnrollmentRequestController@viewPaymentProof` methods MUST support dual-disk lookups (checking both `local` and `public` disks) to ensure that historical data migrated from previous versions remains accessible.

---

## 🧪 TESTING WORKFLOW

1. **Create test accounts:**
   ```bash
   php artisan db:seed --class=QuickTestSeeder
   ```

2. **Access test URLs:**
   - http://localhost:8000/test-school/admin/dashboard
   - http://localhost:8000/test-school/instructor/dashboard
   - http://localhost:8000/test-school/student/dashboard
   - http://localhost:8000/test-school/guest/dashboard

3. **Check emails in logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Test new features:**
   - Refer to `TESTING_GUIDE_NEW_FEATURES.md`
   - Password reset, bulk operations, exports, email notifications

---

## 📚 DOCUMENTATION FILES

- **ADMIN_ROLES_IMPLEMENTATION.md** - Admin role system documentation
- **CHANGELOG.md** - Version history and changes
- **DEPLOYMENT_CHECKLIST.md** - Production deployment guide
- **MULTI_TENANT_CONVERSION_STATUS.md** - Multi-tenancy implementation status
- **MULTI_TENANT_VIEWS_SETUP.md** - View structure for multi-tenancy
- **PERFORMANCE_OPTIMIZATION.md** - Performance tips and optimizations
- **REDUNDANCIES_FIXED.md** - Code cleanup and refactoring notes
- **SYSTEM_LOGGING_README.md** - Logging system documentation
- **TESTING_GUIDE_NEW_FEATURES.md** - Testing guide for groupmates

---

## 🔧 COMMON COMMANDS

```bash
# Start development server
php artisan serve

# Build frontend assets
npm run build   # Production
npm run dev     # Development (watch mode)

# Run migrations
php artisan migrate

# Seed database (full reset)
php artisan migrate:fresh --seed

# Seed specific seeder
php artisan db:seed --class=QuickTestSeeder

# Check routes
php artisan route:list

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Check for errors
php artisan optimize
```

---

## 🎯 NEXT PRIORITIES (After Testing)

1. **UI Integration:** Add export buttons and bulk operation checkboxes to views
2. **Security:** Rate limiting, input validation, CSRF protection
3. **Performance:** Query optimization, caching, database indexing
4. **Features:** Payment integration, SMS notifications, reporting dashboard
5. **Testing:** PHPUnit tests, automated testing
6. **UX:** Responsive design improvements, loading states, error handling

---

**Remember:** This is a multi-tenant system. Every query should be school-specific!

---

## 🎨 CSS CLASS REFERENCE (Common UI Elements)

### Buttons
| Class | Purpose | Example |
|-------|---------|---------|
| `.btn-primary` | Primary action | Save, Submit |
| `.btn-secondary` | Secondary action | Cancel, Back |
| `.btn-danger` | Destructive action | Delete, Remove |
| `.btn-action` | Icon/small button | Edit, View |
| `.btn-export` | Export buttons | PDF, Excel |
| `.quick-action-btn` | Dashboard quick actions | New Student |

### Cards & Containers
| Class | Purpose |
|-------|---------|
| `.admin-container` | Main page container |
| `.card` | Generic card component |
| `.stat-card` | Dashboard statistics card |
| `.page-header` | Page title section |
| `.page-title` | Main heading |
| `.page-subtitle` | Subheading text |

### Tables
| Class | Purpose |
|-------|---------|
| `.data-table` | Main data table |
| `.table-row` | Table row |
| `.table-cell` | Table cell |
| `.status-badge` | Status indicator |

### Forms
| Class | Purpose |
|-------|---------|
| `.form-group` | Form field wrapper |
| `.form-label` | Input label |
| `.form-input` | Text input |
| `.form-select` | Select dropdown |
| `.form-textarea` | Textarea |

### Modals
| Class | Purpose |
|-------|---------|
| `.modal-backdrop` | Background overlay |
| `.modal` | Modal container |
| `.modal-content` | Modal body |
| `.modal-header` | Modal header |
| `.modal-close` | Close button |

### Status Badges
| Class | Status |
|-------|--------|
| `.badge-success` | Active, Approved, Completed |
| `.badge-warning` | Pending, In Progress |
| `.badge-danger` | Inactive, Rejected, Cancelled |
| `.badge-info` | Guest, Scheduled |

### Flash Messages
| Class | Type |
|-------|------|
| `.flash-message.success` | Success message |
| `.flash-message.error` | Error message |
| `.flash-message.warning` | Warning message |

---

## 🧪 COMPLETE SYSTEM TESTING GUIDE

### Step 1: Environment Setup
```powershell
# Navigate to project
cd "C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp"

# Install dependencies (if needed)
composer install
npm install

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Run migrations
php artisan migrate

# Seed test data
php artisan db:seed --class=QuickTestSeeder

# Build frontend
npm run build

# Start server
php artisan serve
```

### Step 2: Test Credentials

**Test School (slug: test-school)**
| Role | Email | Password | URL |
|------|-------|----------|-----|
| Admin | admin@test.com | password | /test-school/admin/ |
| Instructor | instructor@test.com | password | /test-school/instructor/ |
| Instructor 2 | instructor2@test.com | password | /test-school/instructor/ |
| Student | student@test.com | password | /test-school/student/dashboard |
| Student 2 | student2@test.com | password | /test-school/student/dashboard |
| Guest | guest@test.com | password | /test-school/guest/dashboard |
| Guest 2 | guest2@test.com | password | /test-school/guest/dashboard |

**System Admin**
| Email | Password | URL |
|-------|----------|-----|
| systemadmin@gmail.com | password | /system-admin/ |

**Smart Driving School (slug: smart-driving)**
| Role | Email | Password |
|------|-------|----------|
| Admin | schooladmin@gmail.com | password |
| Instructor | instructor1@smart.com | password |
| Student | student1@smart.com | password |

### Step 3: Feature Testing Checklist

#### Authentication ✅
- [ ] Login with correct credentials
- [ ] Login with wrong password (should show "X attempts remaining")
- [ ] Attempt 5 failed logins (should lock account for 30 minutes)
- [ ] Password reset request
- [ ] Email verification for new guests

#### Admin Dashboard ✅
- [ ] View dashboard statistics
- [ ] Navigate to User Management
- [ ] Navigate to Courses
- [ ] Navigate to Schedules
- [ ] Navigate to Settings

#### User Management ✅
- [ ] View students list
- [ ] View instructors list
- [ ] Create new instructor
- [ ] Toggle user status (active/inactive)
- [ ] Edit user details

#### Course Management ✅
- [ ] View courses list
- [ ] Create new course (theoretical/practical)
- [ ] Edit course details
- [ ] Delete course

#### Enrollment Management ✅
- [ ] View pending enrollment requests
- [ ] Approve enrollment request (guest → student)
- [ ] Reject enrollment request
- [ ] Bulk approve multiple requests
- [ ] Bulk reject multiple requests

#### Theoretical Completion ✅
- [ ] View students awaiting theoretical completion
- [ ] Mark student as passed
- [ ] View passed students list

#### Session Management ✅
- [ ] View logged sessions
- [ ] Create new session (as instructor)
- [ ] Check for scheduling conflicts

#### Export Features ✅
- [ ] Export students as PDF
- [ ] Export students as Excel
- [ ] Export enrollments as PDF
- [ ] Export student progress as PDF

#### Instructor Features ✅
- [ ] View instructor dashboard
- [ ] View my students
- [ ] Log new session/progress
- [ ] View schedule

#### Student Features ✅
- [ ] View student dashboard
- [ ] View enrolled courses
- [ ] View progress
- [ ] Update profile

#### Guest Features ✅
- [ ] Register as new guest
- [ ] View available courses
- [ ] Submit enrollment request
- [ ] View my enrollment requests

### Step 4: Security Testing

#### Password Strength ✅
Test these passwords (should all FAIL):
- `password` (no uppercase, number, or special char)
- `Password` (no number or special char)
- `Password1` (no special char)
- `Pass1!` (too short)

Test this password (should PASS):
- `Password123!`

#### Account Lockout ✅
1. Go to login page
2. Enter valid email with wrong password
3. Repeat 5 times
4. On 5th attempt: Account locked for 30 minutes
5. Try again: See "locked for X minutes" message

### Step 5: Error Scenarios

- [ ] Access admin route without login → Redirect to login
- [ ] Access student route as guest → Redirect to guest dashboard
- [ ] Access other school's data → 404 error
- [ ] Submit invalid form data → Validation errors shown

---

## 📦 SHARING THE PROJECT (Google Drive/USB)

### Before Sharing - Delete These Files:
```
/bootstrap/cache/*.php     ← CRITICAL! Contains absolute paths
/storage/framework/views/* 
/storage/framework/cache/*
/storage/framework/sessions/*
/storage/logs/*.log
/vendor/                   ← Optional (they can run composer install)
/node_modules/             ← Optional (they can run npm install)
.env                       ← Share .env.example instead
```

### After Receiving - Run These Commands:
```powershell
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
copy .env.example .env

# Generate app key
php artisan key:generate

# Edit .env with correct database settings
# DB_DATABASE=drivingapp
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations
php artisan migrate

# Seed test data
php artisan db:seed --class=QuickTestSeeder

# Build frontend
npm run build

# Start server
php artisan serve
```

---

## 🎯 DEADLINE: FEBRUARY 20, 2026

### Priority Tasks:
1. ✅ Security features (password strength, account lockout)
2. ✅ Scheduling conflict prevention
3. ⚠️ UI testing on all pages
4. ⚠️ Mobile responsiveness check
5. ⚠️ Error handling review
6. ⚠️ Final documentation
