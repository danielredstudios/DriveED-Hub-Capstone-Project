# Testing Guide: Enhanced Course Form

## 📋 What's New?

### 1. License Type Field
- **Non-Professional**: Personal use only (cannot drive for hire)
- **Professional**: Commercial/for-hire driving (Grab, taxi, delivery, etc.)

### 2. Phase-Based Hours
- **Theoretical Hours**: Classroom training on laws, signs, safety
- **Practical Hours**: Behind-the-wheel training with instructor
- **Total auto-calculated**

### 3. Smart Defaults
Form automatically suggests hours based on:
- Vehicle type (Manual: 8+20, Automatic: 8+15, Motorcycle: 6+12)
- License type (Professional adds +2 theory, +10 practical)

### 4. Two Versions Available
- **Standalone Test Page**: `course-form-enhanced.blade.php` - Full page for testing
- **Modal Version**: `course-form-modal.blade.php` - Ready for admin integration

---

## 🧪 How to Test

### Option 1: Standalone Test Form
1. Start your Laravel server: `php artisan serve`
2. Visit: `http://localhost:8000/test/course-form`
3. Test all features independently

### Option 2: Modal Version (In Admin Context)
1. Copy `course-form-modal.blade.php` content
2. Paste into your admin courses page
3. Test within actual admin interface

---
Try these scenarios:

**Test 1: Non-Pro Manual**
- License: Non-Professional
- Vehicle: Manual
- Expected hours: 8 theoretical + 20 practical = 28 total

**Test 2: Pro Automatic**
- License: Professional
- Vehicle: Automatic
- Expected hours: 10 theoretical + 25 practical = 35 total

**Test 3: Motorcycle**
- License: Non-Professional
- Vehicle: Motorcycle
- Expected hours: 6 theoretical + 12 practical = 18 total

**Test 4: Custom Hours**
- Manually change theoretical to 10 hours
- Manually change practical to 30 hours
- Check if total updates to 40 hours

### Step 3: Submit and Check Output
- Fill all required fields
- Click "Test Submit"
- Check JSON output at bottom
- Verify all fields are captured correctly

---

## 🔧 Integration Steps (After Testing)

### Quick Integration: Use Modal Version

**Step 1: Add Modal to Admin Courses Page**
```bash
# Open your admin courses view
# File: resources/views/school/admin/courses.blade.php

# At the bottom of the file (before closing </body> or @endsection), add:
@include('test-components.course-form-modal')
```

**Step 2: Update "Add Course" Button**
```html
<!-- Find your existing "Add Course" button and change it to: -->
<button onclick="openAddCourseModal()" class="btn btn-primary">
    Add New Course
</button>
```

**Step 3: Update "Edit" Buttons**
```html
<!-- In your course listing, change edit buttons to: -->
<button onclick="openEditCourseModal(@json($course))" class="btn btn-sm btn-warning">
    Edit
</button>
```

**Step 4: That's it!** The modal is ready to use.

---

### Full Integration: Database Changes

**Step 1: Run Migration**
```bash
# Copy the migration file to database/migrations/
cp test-components/migration_add_course_fields.php database/migrations/2025_12_11_000001_add_license_and_hours_to_courses.php

# Run migration
php artisan migrate
```

### Step 2: Update Course Model
```bash
# Backup current model
cp app/Models/Course.php app/Models/Course.php.backup

# Copy enhanced model (or manually add the new fields)
# Update the $fillable array and $casts
```

### Step 3: Update Course Controller
Add license_type validation in store/update methods:
```php
$validated = $request->validate([
    'title' => 'required|string|max:255',
    'license_type' => 'required|in:non-professional,professional',
    'vehicle_type' => 'required|string',
    'theoretical_hours_required' => 'required|numeric|min:0',
    'practical_hours_required' => 'required|numeric|min:0',
    // ... other fields
]);
```

### Step 4: Update Admin Course Form
Replace the current course form modal in:
`resources/views/school/admin/courses.blade.php`

With the enhanced form fields from the test component.

---

## ✅ Validation Checklist

Before integrating:
- [ ] Test form displays correctly
- [ ] All radio buttons work
- [ ] Hours auto-calculate based on selections
- [ ] Manual hour entry works
- [ ] Total hours update correctly
- [ ] Form submission captures all data
- [ ] JSON output shows correct structure
- [ ] Mobile responsive (test on phone)

---

## 🚀 Next Components to Build

After this is working:
1. **Enrollment Request Form** (with experience level)
2. **Session Completion Form** (instructor logs hours)
3. **Student Progress Display** (shows phases)
4. **Phase Progression Approval** (admin approves next phase)

Each will be built in `test-components/` folder first!

---

## 🐛 Troubleshooting

**Form doesn't display:**
- Check if route is added to web.php
- Run `php artisan route:list | grep test`

**Styling broken:**
- All CSS is inline in the blade file
- No dependencies needed

**Hours don't calculate:**
- Open browser console (F12)
- Check for JavaScript errors
- Verify input IDs match the script

---

## 📝 Notes

- **No database changes yet** - This is pure frontend testing
- **Safe to test** - Won't affect existing system
- **Easy to remove** - Just delete test-components folder and test routes
- **Component-based** - Build and test one piece at a time

---

## 💡 What This Proves

If this component works well:
- ✅ New course structure is viable
- ✅ UI/UX is user-friendly
- ✅ Data capture is complete
- ✅ Ready to integrate into main system

Then we can build the next component with confidence! 🎯
