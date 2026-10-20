# DriveED Hub v1.5b - Testing Guide

## Setup

1. Open XAMPP, start **MySQL** and **Apache**
2. In terminal: `php artisan serve`
3. Open browser: **http://localhost:8000**

---

## Test School

Use **Smart Driving School**

- Homepage: `http://localhost:8000/smart-driving`
- Login credentials: `http://localhost:8000/smart-driving/test-credentials`

---

## How to Test

### 1. Guest Pages (no login needed)

- Visit the homepage
- Check the courses page
- Check instructor profiles
- Try registering as a new student
- Try submitting an enrollment request for a course

---

### 2. Student Portal

Login as a student (get credentials from test-credentials page)

- Check the dashboard
- Go to schedule page, see available slots
- Try booking a session
- Check "My Course" page
- View and edit your profile
- Check payments page
- Check progress page
- Test the sidebar menu
- Logout

---

### 3. Instructor Portal

Login as an instructor

- Check the dashboard
- View your schedule
- View your assigned students
- Click on a student to see their details
- Check availability settings
- Test sidebar menu
- Logout

---

### 4. Admin Portal

Login as an admin

- Check dashboard stats and charts

**Try these pages:**
- Students - view, add, edit, delete, search, export
- Instructors - view, add, edit, delete, export
- Courses - view, add, edit, delete
- Bookings - view, filter, approve/reject
- Payments - view, record new, export
- Enrollment Requests - approve/reject
- Time Slots - create slots, assign instructors
- Reports - check if charts load
- Settings - change colors, upload logo, save

---

### 5. Password Reset

- Go to login page
- Click "Forgot Password"
- Enter a student email
- Use the reset link (shows in success message)
- Set new password
- Login with new password

---

### 6. Mobile

Open on phone or resize browser window small - make sure nothing breaks

---

## That's it!

Let me know what's broken 👍
