# Test Components

This folder contains isolated components for testing the new system structure.

## Components:

### 1. course-form-enhanced.blade.php
Enhanced course creation form with:
- License type (Non-Pro / Professional)
- Separate theoretical and practical hours
- Vehicle type selection

### How to Test:
1. Copy component into a test route
2. Test functionality independently
3. Once working, integrate into main system

## Testing Routes (Add to web.php temporarily):

```php
// Test routes - Remove after testing
Route::get('/test/course-form', function() {
    return view('test-components.course-form-enhanced');
})->name('test.course-form');
```
