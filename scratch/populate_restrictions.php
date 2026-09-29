<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Course;

$courses = Course::all();
foreach ($courses as $course) {
    if (stripos($course->course_type, 'theoretical') !== false && stripos($course->title, 'TDC') === false) {
         // Some courses might be labeled theoretical but are actually practical vehicle courses
    }
    
    // Logic: If it has a specific vehicle type and is NOT a pure TDC course, it might need a restriction
    if (stripos($course->title, 'Theoretical') !== false || $course->course_type === 'theoretical') {
        // TDC courses don't need restrictions per user rule
        $course->required_restriction = null;
    } else {
        if (stripos($course->vehicle_type, 'motorcycle') !== false) {
            $course->required_restriction = 'A';
        } else if (stripos($course->vehicle_type, 'car') !== false) {
            $course->required_restriction = 'B';
        }
    }
    $course->save();
    echo "Updated Course: {$course->title} (Type: {$course->course_type}) to Restriction: " . ($course->required_restriction ?? 'None') . "\n";
}
