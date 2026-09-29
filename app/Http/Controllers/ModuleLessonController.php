<?php

namespace App\Http\Controllers;

use App\Models\ModuleLesson;
use App\Models\CourseModule;
use App\Models\Course;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentLessonCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ModuleLessonController extends Controller
{
    /**
     * Display a listing of lessons for a specific module
     */
    public function index(Request $request, School $school, Course $course, CourseModule $module)
    {
        // Verify course belongs to school
        if ($course->school_id !== $school->id) {
            abort(404);
        }

        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }

        $lessons = $module->lessons()->ordered()->get();

        // Student view - must be enrolled
        if (Auth::guard('student')->check()) {
            $student = Auth::guard('student')->user();
            $isEnrolled = $student->enrollments()
                ->where('course_id', $course->id)
                ->where('status', 'approved')
                ->exists();

            if (!$isEnrolled) {
                abort(403, 'You must be enrolled in this course to view lessons.');
            }

            $completedLessonIds = $student->lessonCompletions()
                ->whereIn('lesson_id', $lessons->pluck('id'))
                ->pluck('lesson_id')
                ->all();

            return view('school.student.lessons.index', compact('school', 'course', 'module', 'lessons', 'completedLessonIds'))->with('isAjax', $request->ajax());
        }

        // Instructor view
        if (Auth::guard('instructor')->check()) {
            return view('school.instructor.lessons.index', compact('school', 'course', 'module', 'lessons'))->with('isAjax', $request->ajax());
        }

        // Admin view
        if (Auth::guard('admin')->check()) {
            return view('school.admin.lessons.index', compact('school', 'course', 'module', 'lessons'))->with('isAjax', $request->ajax());
        }

        abort(403);
    }

    /**
     * Show the form for creating a new lesson
     */
    public function create(Request $request, School $school, Course $course, CourseModule $module)
    {
        $isInstructor = Auth::guard('instructor')->check();
        if (!$isInstructor) {
            abort(403, 'Only instructors can create lessons.');
        }

        // Verify course belongs to school
        if ($course->school_id !== $school->id) {
            abort(404);
        }

        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }

        $view = 'school.instructor.lessons.create';

        return view($view, compact('school', 'course', 'module'))->with('isAjax', $request->ajax());
    }

    /**
     * Store a newly created lesson
     */
    public function store(Request $request, School $school, Course $course, CourseModule $module)
    {
        $isInstructor = Auth::guard('instructor')->check();
        if (!$isInstructor) {
            abort(403, 'Only instructors can create lessons.');
        }

        // Verify course belongs to school
        if ($course->school_id !== $school->id) {
            abort(404);
        }

        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png|max:10240', // 10MB max per file
        ]);

        DB::beginTransaction();
        try {
            $attachments = [];

            // Handle file uploads
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('lessons/attachments', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getClientOriginalExtension(),
                    ];
                }
            }

            // If no sort_order provided, add to end
            $sortOrder = $request->sort_order ?? $module->lessons()->max('sort_order') + 1;

            $lesson = ModuleLesson::create([
                'school_id' => $school->id,
                'module_id' => $module->id,
                'title' => $request->title,
                'content' => $request->content,
                'video_url' => $request->video_url,
                'attachments' => $attachments,
                'sort_order' => $sortOrder,
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lesson created successfully.',
                    'lesson' => $lesson,
                ]);
            }

            return redirect()
                ->route('schools.instructor.courses.modules.show', ['school' => $school->slug, 'course' => $course->id, 'module' => $module->id])
                ->with('success', 'Lesson created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create lesson: ' . $e->getMessage(),
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'Unable to create lesson at this time. Please try again later.');
        }
    }

    /**
     * Display the specified lesson
     */
    public function show(Request $request, School $school, Course $course, CourseModule $module, ModuleLesson $lesson)
    {
        // Verify course belongs to school
        if ($course->school_id !== $school->id) {
            abort(404);
        }

        // Verify lesson belongs to module and course
        if ($lesson->module_id !== $module->id || $module->course_id !== $course->id) {
            abort(404);
        }

        // Student view - must be enrolled
        if (Auth::guard('student')->check()) {
            $student = Auth::guard('student')->user();
            $isEnrolled = $student->enrollments()
                ->where('course_id', $course->id)
                ->where('status', 'approved')
                ->exists();

            if (!$isEnrolled) {
                abort(403, 'You must be enrolled in this course to view lessons.');
            }

            // Get navigation
            $courseModuleController = app(\App\Http\Controllers\CourseModuleController::class);
            $navigation = $courseModuleController->getLearningPathNavigation($course, $module, $lesson);
            $lessonCompleted = $student->lessonCompletions()->where('lesson_id', $lesson->id)->exists();

            return view('school.student.lessons.show', compact('school', 'course', 'module', 'lesson', 'navigation', 'lessonCompleted'))->with('isAjax', $request->ajax());
        }

        // Instructor view
        if (Auth::guard('instructor')->check()) {
            return view('school.instructor.lessons.show', compact('school', 'course', 'module', 'lesson'))->with('isAjax', $request->ajax());
        }

        // Admin view
        if (Auth::guard('admin')->check()) {
            return view('school.admin.lessons.show', compact('school', 'course', 'module', 'lesson'))->with('isAjax', $request->ajax());
        }

        abort(403);
    }

    public function complete(Request $request, School $school, Course $course, CourseModule $module, ModuleLesson $lesson)
    {
        $student = Auth::guard('student')->user();

        abort_unless($course->school_id === $school->id, 404);
        abort_unless($module->course_id === $course->id && $lesson->module_id === $module->id, 404);

        $enrolled = $student->enrollments()
            ->where('course_id', $course->id)
            ->where('status', 'approved')
            ->exists();
        abort_unless($enrolled, 403, 'You must be enrolled in this course.');

        StudentLessonCompletion::firstOrCreate(
            ['student_id' => $student->id, 'lesson_id' => $lesson->id],
            ['school_id' => $school->id, 'completed_at' => now()]
        );

        return redirect()->route('schools.student.courses.modules.lessons.show', [
            'school' => $school->slug,
            'course' => $course->id,
            'module' => $module->id,
            'lesson' => $lesson->id,
        ])->with('success', 'Lesson marked as completed.');
    }

    /**
     * Show the form for editing the specified lesson
     */
    public function edit(Request $request, School $school, Course $course, CourseModule $module, ModuleLesson $lesson)
    {
        $isInstructor = Auth::guard('instructor')->check();
        if (!$isInstructor) {
            abort(403, 'Only instructors can edit lessons.');
        }

        // Verify course belongs to school
        if ($course->school_id !== $school->id) {
            abort(404);
        }

        // Verify lesson belongs to module and course
        if ($lesson->module_id !== $module->id || $module->course_id !== $course->id) {
            abort(404);
        }

        $view = 'school.instructor.lessons.edit';

        return view($view, compact('school', 'course', 'module', 'lesson'))->with('isAjax', $request->ajax());
    }

    /**
     * Update the specified lesson
     */
    public function update(Request $request, School $school, Course $course, CourseModule $module, ModuleLesson $lesson)
    {
        $isInstructor = Auth::guard('instructor')->check();
        if (!$isInstructor) {
            abort(403, 'Only instructors can update lessons.');
        }

        // Verify course belongs to school
        if ($course->school_id !== $school->id) {
            abort(404);
        }

        // Verify lesson belongs to module and course
        if ($lesson->module_id !== $module->id || $module->course_id !== $course->id) {
            abort(404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png|max:10240',
            'remove_attachments' => 'nullable|array',
            'remove_attachments.*' => 'integer',
        ]);

        DB::beginTransaction();
        try {
            $attachments = $lesson->attachments ?? [];

            // Remove selected attachments
            if ($request->remove_attachments) {
                // Sort indices descending to remove safely if processed differently
                // but since we collect all and re-index at end, just filter
                $removeIndices = array_unique($request->remove_attachments);

                foreach ($removeIndices as $index) {
                    // Range validation: ensure index is within current collection
                    if (is_numeric($index) && $index >= 0 && $index < count($attachments)) {
                        if (isset($attachments[$index])) {
                            Storage::disk('public')->delete($attachments[$index]['path']);
                            unset($attachments[$index]);
                        }
                    }
                }
                $attachments = array_values($attachments); // Re-index array
            }

            // Add new attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('lessons/attachments', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getClientOriginalExtension(),
                    ];
                }
            }

            $lesson->update([
                'title' => $request->title,
                'content' => $request->content,
                'video_url' => $request->video_url,
                'attachments' => $attachments,
                'sort_order' => $request->sort_order ?? $lesson->sort_order,
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lesson updated successfully.',
                    'lesson' => $lesson,
                ]);
            }

            $redirectRoute = 'schools.instructor.courses.modules.lessons.show';

            return redirect()
                ->route($redirectRoute, ['school' => $school->slug, 'course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id])
                ->with('success', 'Lesson updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update lesson: ' . $e->getMessage(),
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'Unable to update lesson at this time. Please try again later.');
        }
    }

    /**
     * Remove the specified lesson
     */
    public function destroy(School $school, Course $course, CourseModule $module, ModuleLesson $lesson)
    {
        $isInstructor = Auth::guard('instructor')->check();
        if (!$isInstructor) {
            abort(403, 'Only instructors can delete lessons.');
        }

        // Verify course belongs to school
        if ($course->school_id !== $school->id) {
            abort(404);
        }

        // Verify lesson belongs to module and course
        if ($lesson->module_id !== $module->id || $module->course_id !== $course->id) {
            abort(404);
        }

        DB::beginTransaction();
        try {
            // Delete attachments from storage
            if ($lesson->attachments) {
                foreach ($lesson->attachments as $attachment) {
                    Storage::disk('public')->delete($attachment['path']);
                }
            }

            $lesson->delete();

            DB::commit();

            $redirectRoute = 'schools.instructor.courses.modules.show';

            return redirect()
                ->route($redirectRoute, ['school' => $school->slug, 'course' => $course->id, 'module' => $module->id])
                ->with('success', 'Lesson deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Unable to delete lesson at this time. Please try again later.');
        }
    }

    /**
     * Reorder lessons within a module
     */
    public function reorder(Request $request, School $school, Course $course, CourseModule $module)
    {
        $isInstructor = Auth::guard('instructor')->check();
        if (!$isInstructor) {
            abort(403, 'Only instructors can reorder lessons.');
        }

        // Verify course belongs to school
        if ($course->school_id !== $school->id) {
            abort(404);
        }

        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }

        $request->validate([
            'lesson_ids' => 'required|array',
            'lesson_ids.*' => 'exists:module_lessons,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->lesson_ids as $index => $lessonId) {
                ModuleLesson::where('id', $lessonId)
                    ->where('module_id', $module->id)
                    ->update(['sort_order' => $index + 1]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lessons reordered successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder lessons: ' . $e->getMessage(),
            ], 500);
        }
    }
}
