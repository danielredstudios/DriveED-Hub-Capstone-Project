Driving School Management System - Modules Overview

This document lists all modules in the system organized by user role, for system testing purposes.


1. System Admin Modules

Module                  | Key Functionality
----------------------- | -----------------
Admin Login             | Separate global authentication
School Management       | Create, update, delete, and toggle schools
Admin Accounts          | Create and delete admin users, toggle status
Global Users            | View and manage all users across schools
System Logs             | Audit trail, search, resolve, and cleanup


2. School Admin Modules

Module                  | Key Functionality
----------------------- | -----------------
Dashboard               | KPI overview, quick actions, activity feed
User Management         | Manage students, instructors, and admin or staff
Courses                 | Create, read, update, delete courses, pricing, activate or deactivate
Course Packages         | Bundle courses, promotional pricing
Branches                | Multi-location management
Schedules               | Time slots, instructor assignment, conflict detection
Bookings                | Manage student-instructor session bookings
Payments                | Record and track payments, statistics
Reports                 | Generate reports for students, instructors, bookings, payments, courses
Settings                | School branding, colors, timezone
Admin Profile           | Personal account management
LMS Course Modules      | Create, edit, reorder, duplicate modules within courses
LMS Module Lessons      | Create, edit, reorder lessons, attach media
LMS Lesson Editor       | Rich text and HTML content editing
Enrollment Requests     | Approve or reject student enrollments, bulk operations
Theoretical Training    | Track exams, mark passed or failed, revoke
Session Completions     | Review driving sessions logged by instructors
Phase Progressions      | Approve or reject student phase advancement
Instructor Removal      | Manage instructor removal requests
Export System           | PDF and Excel exports for all reports


3. Instructor Modules

Module                  | Key Functionality
----------------------- | -----------------
Dashboard               | Assigned students, upcoming sessions, stats
My Schedule             | View and toggle availability, request removal, attendance
My Students             | View assigned students and sessions
Student Details         | Full profile, course info, session history
Progress Tracking       | Create, edit, delete progress notes, rate performance from 1 to 5
Session Logging         | Log driving sessions with date, time, duration, conflict detection
Theoretical Training    | Mark students passed or not passed, statistics
Grades                  | View student grades and ratings
Reports                 | Personal analytics, student engagement
Course Content LMS      | Browse modules and lessons
Profile                 | Update profile, change picture
Exports                 | PDF and Excel for students, sessions, grades, reports


4. Student Modules

Module                  | Key Functionality
----------------------- | -----------------
Dashboard               | Enrollment, next session, progress, payments overview
My Courses              | Browse available and enrolled courses
Course Details          | Packages, pricing, module structure
Course Content LMS      | View modules and lessons
My Current Course       | Active enrollment details and progress
Schedule                | View slots, book sessions, manage queue, confirm
My Progress             | Instructor feedback, ratings, completion status
Payments                | Payment history, outstanding balance, invoices
Profile                 | Update profile, change picture, manage credentials


5. Guest and Public Modules

Module                  | Key Functionality
----------------------- | -----------------
Public Registration     | Sign up, email verification, password enforcement
Email Verification      | Verify code, resend, rate limited
Guest Dashboard         | Course browsing, license upload
Browse Courses          | View available courses and packages
Submit Enrollments      | Request enrollment, upload driver license
Track Requests          | View enrollment status and rejection reasons
Forgot Password         | Request password reset link
Reset Password          | Enter new password via token link


6. Cross-Cutting and System-Wide Features

Feature                 | Scope
----------------------- | -----
Notification System     | All authenticated users, in-app notifications
Activity Logging        | School-level and system-level audit trails
Export System           | PDF via DomPDF and Excel via Laravel
Multi-Guard Auth        | Admin, instructor, and student authentication guards
Security                | Rate limiting, 5 failed login lockout, strong password policy
Conflict Detection      | Automatic schedule and availability checking
Multi-Tenant Isolation  | All data scoped by school
