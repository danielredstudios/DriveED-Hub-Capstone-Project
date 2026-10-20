# Deployment Checklist - December 3, 2025 Updates

## 🔧 Pre-Deployment Steps

### 1. Database Migration
```bash
# Run this command to add the advance_booking_days column
php artisan migrate

# To rollback if needed:
# php artisan migrate:rollback --step=1
```

**Migration File**: `database/migrations/2025_12_02_230216_add_advance_booking_days_to_school_settings.php`

**What it does**:
- Adds `advance_booking_days` column to `school_settings` table
- Type: integer, Default: 0
- Allows schools to set minimum advance notice for bookings

### 2. Clear All Caches
```bash
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 3. Verify File Changes
Confirm these files were updated:
- ✅ `resources/views/school/student/schedule.blade.php`
- ✅ `app/Models/SchoolSetting.php`
- ✅ `app/Http/Controllers/BookingController.php`
- ✅ `database/migrations/2025_12_02_230216_add_advance_booking_days_to_school_settings.php`

---

## ✅ Testing Checklist

### Functional Tests

#### Test 1: Advance Booking Validation
- [ ] Set `advance_booking_days` to 3 in database
- [ ] Try to book a lesson for tomorrow (should fail)
- [ ] Try to book a lesson 4 days from now (should succeed)
- [ ] Set `advance_booking_days` to 0
- [ ] Try to book same-day lesson (should succeed)

**SQL to test**:
```sql
-- Set advance booking to 3 days
UPDATE school_settings SET advance_booking_days = 3 WHERE school_id = 1;

-- Reset to 0 (same-day allowed)
UPDATE school_settings SET advance_booking_days = 0 WHERE school_id = 1;
```

#### Test 2: Visual Elements
- [ ] Vertical colored line appears on all booking items in My Schedule
- [ ] Line uses school's secondary color
- [ ] Course title badge displays correctly
- [ ] Vehicle type badge displays correctly
- [ ] Badges are visible and readable

#### Test 3: Schedule Display
- [ ] All 6 schedules display on Wednesday, December 3, 2025
- [ ] No schedules are cut off or hidden
- [ ] Scrolling works smoothly if more than 6 items
- [ ] Collapse/expand works correctly

#### Test 4: Mobile Responsiveness
- [ ] Queue popup opens correctly on mobile
- [ ] All booking information is readable
- [ ] Badges don't overflow on small screens
- [ ] Touch interactions work smoothly
- [ ] Sidebar is hidden on mobile (<480px)

#### Test 5: Queue Functionality
- [ ] Booking queue shows pending bookings
- [ ] Confirm button works
- [ ] Remove button works
- [ ] Auto-confirmation still functions after X days
- [ ] Queue counter is accurate

### Browser Testing
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (if available)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

### Screen Size Testing
- [ ] Desktop (1920x1080)
- [ ] Laptop (1366x768)
- [ ] Tablet (768px width)
- [ ] Mobile Large (414px width)
- [ ] Mobile Small (375px width)

---

## 🚨 Rollback Plan

If issues occur after deployment:

### Option 1: Rollback Migration Only
```bash
php artisan migrate:rollback --step=1
```
This removes the `advance_booking_days` column but keeps all other changes.

### Option 2: Rollback Code Changes
```bash
git revert <commit-hash>
git push origin main
```

### Option 3: Emergency Fix
If advance booking validation is causing issues:
```sql
-- Temporarily disable advance booking requirement for all schools
UPDATE school_settings SET advance_booking_days = 0;
```

---

## 📊 Monitoring After Deployment

### Check These Metrics (First 24 Hours)

1. **Error Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Watch for:
   - Booking validation errors
   - Database query errors
   - View compilation errors

2. **User Reports**
   - Booking failures
   - Visual display issues
   - Mobile responsiveness problems

3. **Database Queries**
   ```sql
   -- Check if advance_booking_days column exists
   SHOW COLUMNS FROM school_settings LIKE 'advance_booking_days';
   
   -- Check current settings
   SELECT school_id, advance_booking_days, enable_booking_queue, booking_queue_days 
   FROM school_settings;
   
   -- Check recent bookings
   SELECT id, student_id, status, booking_date, created_at 
   FROM bookings 
   WHERE created_at >= NOW() - INTERVAL 1 DAY
   ORDER BY created_at DESC;
   ```

---

## 🐛 Common Issues & Solutions

### Issue 1: "Column not found: advance_booking_days"
**Solution**: Run the migration
```bash
php artisan migrate
```

### Issue 2: Vertical line not showing
**Solution**: Clear view cache
```bash
php artisan view:clear
```

### Issue 3: Bookings failing with advance booking error
**Check**: 
```sql
SELECT advance_booking_days FROM school_settings WHERE school_id = ?;
```
**Solution**: Adjust the value or set to 0 to disable

### Issue 4: Badges not displaying
**Solution**: 
- Clear browser cache
- Check if `$secondaryColor` variable is defined
- Verify school settings have course data

### Issue 5: Mobile view broken
**Solution**:
- Check browser console for JavaScript errors
- Verify media queries are working
- Clear all caches

---

## 📝 Post-Deployment Tasks

### Immediate (Within 1 Hour)
- [ ] Verify migration ran successfully
- [ ] Test one booking on production
- [ ] Check error logs
- [ ] Confirm UI displays correctly

### Within 24 Hours
- [ ] Monitor error rates
- [ ] Collect user feedback
- [ ] Review booking success/failure rates
- [ ] Update admin documentation

### Within 1 Week
- [ ] Add admin UI for configuring advance_booking_days
- [ ] Create user guide for new features
- [ ] Optimize any performance issues
- [ ] Plan next iteration based on feedback

---

## 👥 Support Contacts

**Developer**: Available for 24h post-deployment  
**Database Admin**: For migration issues  
**QA Team**: For testing validation  
**Support Team**: For user-reported issues  

---

## 📚 Related Documentation

- `CHANGELOG.md` - Complete list of changes
- Migration file comments - Database schema changes
- Git commit messages - Code change details
- Controller comments - Business logic documentation

---

**Deployment Date**: [TBD]  
**Deployed By**: [TBD]  
**Environment**: [Staging/Production]  
**Status**: [ ] Success  [ ] Partial  [ ] Rollback Required
