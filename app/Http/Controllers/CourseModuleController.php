<?php

namespace App\Http\Controllers;

use App\Models\CourseModule;
use App\Models\Course;
use App\Models\ModuleLesson;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseModuleController extends Controller
{
    /**
     * Resolve authenticated user role from multi-guard system
     */
    private function resolveAuthRole(): string
    {
        if (Auth::guard('admin')->check()) return 'admin';
        if (Auth::guard('instructor')->check()) return 'instructor';
        if (Auth::guard('student')->check()) return 'student';
        abort(403);
    }

    /**
     * Display a role-based course materials hub (list of courses).
     */
    public function materialsHub(Request $request, School $school)
    {
        $role = $this->resolveAuthRole();

        if (!in_array($role, ['admin', 'instructor'], true)) {
            abort(403);
        }

        $courses = $school->courses()
            ->select('id', 'title', 'type', 'course_type', 'status', 'hours_required', 'duration_hours')
            ->withCount('modules')
            ->orderBy('title')
            ->get()
            ->map(function ($course) {
                $canonicalType = strtolower(trim((string) ($course->course_type ?? '')));
                $legacyType = strtolower(trim((string) ($course->type ?? '')));

                $validTypes = ['theoretical', 'practical', 'combo'];

                $effectiveType = in_array($canonicalType, $validTypes, true)
                    ? $canonicalType
                    : null;

                // Backward compatibility: some older rows still carry practical/combo only in the legacy `type` column.
                if (in_array($legacyType, ['practical', 'combo'], true) && $effectiveType === 'theoretical') {
                    $effectiveType = $legacyType;
                }

                if ($effectiveType === null) {
                    $effectiveType = in_array($legacyType, $validTypes, true)
                        ? $legacyType
                        : 'theoretical';
                }

                $course->effective_course_type = $effectiveType;

                return $course;
            });

        $practicalCount = $courses->whereIn('effective_course_type', ['practical', 'combo'])->count();
        $theoreticalCount = $courses->where('effective_course_type', 'theoretical')->count();

        $viewPath = $role === 'admin'
            ? 'admin.modules.courses'
            : 'instructor.modules.courses';

        return view(
            $school->resolveView($viewPath),
            compact('school', 'courses', 'practicalCount', 'theoreticalCount')
        )->with('isAjax', $request->ajax());
    }

    /**
     * Display a listing of modules for a specific course
     */
    public function index(Request $request, School $school, Course $course)
    {
        $role = $this->resolveAuthRole();
        
        $modules = $course->modules()
            ->with(['lessons' => function($query) {
                $query->ordered();
            }])
            ->ordered()
            ->get();
        
        // Different views based on role
        $viewPath = match($role) {
            'student' => 'student.modules.index',
            'instructor' => 'instructor.modules.index',
            'admin' => 'admin.modules.index',
            default => abort(403)
        };
        
        return view($school->resolveView($viewPath), compact('school', 'course', 'modules'))->with('isAjax', $request->ajax());
    }

    /**
     * Show the form for creating a new module
     */
    public function create(Request $request, School $school, Course $course)
    {
        $role = $this->resolveAuthRole();
        
        if (!in_array($role, ['admin', 'instructor'], true)) {
            abort(403);
        }
        
        $viewPath = $role === 'admin' ? 'admin.modules.create' : 'instructor.modules.create';
        return view($school->resolveView($viewPath), compact('school', 'course'))->with('isAjax', $request->ajax());
    }

    /**
     * Store a newly created module
     */
    public function store(Request $request, School $school, Course $course)
    {
        $role = $this->resolveAuthRole();
        
        if (!in_array($role, ['admin', 'instructor'], true)) {
            abort(403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'module_type' => 'required|in:lesson,reading,video,assessment',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        
        DB::beginTransaction();
        try {
            // If no sort_order provided, add to end
            $sortOrder = $request->sort_order ?? $course->modules()->max('sort_order') + 1;
            
            $module = CourseModule::create([
                'school_id' => $school->id,
                'course_id' => $course->id,
                'title' => $request->title,
                'description' => $request->description,
                'module_type' => $request->module_type,
                'sort_order' => $sortOrder,
            ]);
            
            DB::commit();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Module created successfully.',
                    'module' => $module,
                ]);
            }
            
            $redirectRoute = $role === 'admin'
                ? 'schools.admin.courses.modules.index'
                : 'schools.instructor.courses.modules.index';

            return redirect()
                ->route($redirectRoute, ['school' => $school->slug, 'course' => $course->id])
                ->with('success', 'Module created successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create module: ' . $e->getMessage(),
                ], 500);
            }
            
            return back()
                ->withInput()
                ->with('error', 'Failed to create module: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified module with its lessons
     */
    public function show(Request $request, School $school, Course $course, CourseModule $module)
    {
        $role = $this->resolveAuthRole();
        
        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        $module->load(['lessons' => function($query) {
            $query->ordered();
        }, 'questions']);
        
        $viewPath = match($role) {
            'student' => 'student.modules.show',
            'instructor' => 'instructor.modules.show',
            'admin' => 'admin.modules.show',
            default => abort(403)
        };
        
        return view($school->resolveView($viewPath), compact('school', 'course', 'module'))->with('isAjax', $request->ajax());
    }

    /**
     * Allow students to take an assessment
     */
    public function takeAssessment(Request $request, School $school, Course $course, CourseModule $module)
    {
        $role = $this->resolveAuthRole();
        
        if ($role !== 'student') {
            abort(403);
        }

        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }

        // Verify student is enrolled
        $student = Auth::guard('student')->user();
        $isEnrolled = $student->enrollments()
            ->where('course_id', $course->id)
            ->where('status', 'approved')
            ->exists();

        if (!$isEnrolled) {
            abort(403, 'You must be enrolled in this course to take assessments.');
        }

        $questions = $module->questions;

        // Get navigation
        $navigation = $this->getLearningPathNavigation($course, $module);

        return view($school->resolveView('student.modules.assessment'), compact('school', 'course', 'module', 'questions', 'navigation'))
            ->with('isAjax', $request->ajax());
    }

    /**
     * Get the next and previous items in the learning path
     * @param Course $course
     * @param CourseModule $module Current module
     * @param ModuleLesson|null $lesson Current lesson (if any)
     */
    public function getLearningPathNavigation(Course $course, CourseModule $module, ?ModuleLesson $lesson = null)
    {
        $prev = null;
        $next = null;

        // If we are in a lesson
        if ($lesson) {
            // Find next lesson in same module
            $nextLesson = $module->lessons()->where('sort_order', '>', $lesson->sort_order)->ordered()->first();
            if ($nextLesson) {
                $next = [
                    'type' => 'lesson',
                    'title' => $nextLesson->title,
                    'url' => school_route('student.courses.modules.lessons.show', ['course' => $course->id, 'module' => $module->id, 'lesson' => $nextLesson->id])
                ];
            } else {
                // End of lessons in this module. Is there an assessment in this module?
                if ($module->module_type === 'assessment') {
                    $next = [
                        'type' => 'assessment',
                        'title' => 'Module Assessment',
                        'url' => school_route('student.courses.modules.assessment.take', ['course' => $course->id, 'module' => $module->id])
                    ];
                } else {
                    // No assessment. Look for first lesson of next module
                    $nextModule = $course->modules()->where('sort_order', '>', $module->sort_order)->ordered()->first();
                    if ($nextModule) {
                        $firstLesson = $nextModule->lessons()->ordered()->first();
                        if ($firstLesson) {
                            $next = [
                                'type' => 'lesson',
                                'title' => $firstLesson->title,
                                'url' => school_route('student.courses.modules.lessons.show', ['course' => $course->id, 'module' => $nextModule->id, 'lesson' => $firstLesson->id])
                            ];
                        } elseif ($nextModule->module_type === 'assessment') {
                            $next = [
                                'type' => 'assessment',
                                'title' => 'Next Module Assessment',
                                'url' => school_route('student.courses.modules.assessment.take', ['course' => $course->id, 'module' => $nextModule->id])
                            ];
                        }
                    }
                }
            }

            // Find prev lesson in same module
            $prevLesson = $module->lessons()->where('sort_order', '<', $lesson->sort_order)->orderBy('sort_order', 'desc')->first();
            if ($prevLesson) {
                $prev = [
                    'type' => 'lesson',
                    'title' => $prevLesson->title,
                    'url' => school_route('student.courses.modules.lessons.show', ['course' => $course->id, 'module' => $module->id, 'lesson' => $prevLesson->id])
                ];
            } else {
                // First lesson of this module. Look at previous module
                $prevModule = $course->modules()->where('sort_order', '<', $module->sort_order)->orderBy('sort_order', 'desc')->first();
                if ($prevModule) {
                    if ($prevModule->module_type === 'assessment') {
                        $prev = [
                            'type' => 'assessment',
                            'title' => 'Previous Module Quiz',
                            'url' => school_route('student.courses.modules.assessment.take', ['course' => $course->id, 'module' => $prevModule->id])
                        ];
                    } else {
                        $lastLesson = $prevModule->lessons()->orderBy('sort_order', 'desc')->first();
                        if ($lastLesson) {
                            $prev = [
                                'type' => 'lesson',
                                'title' => $lastLesson->title,
                                'url' => school_route('student.courses.modules.lessons.show', ['course' => $course->id, 'module' => $prevModule->id, 'lesson' => $lastLesson->id])
                            ];
                        }
                    }
                }
            }
        } 
        // If we are in an assessment
        else {
            // Next is first lesson of next module
            $nextModule = $course->modules()->where('sort_order', '>', $module->sort_order)->ordered()->first();
            if ($nextModule) {
                $firstLesson = $nextModule->lessons()->ordered()->first();
                if ($firstLesson) {
                    $next = [
                        'type' => 'lesson',
                        'title' => $firstLesson->title,
                        'url' => school_route('student.courses.modules.lessons.show', ['course' => $course->id, 'module' => $nextModule->id, 'lesson' => $firstLesson->id])
                    ];
                } elseif ($nextModule->module_type === 'assessment') {
                    $next = [
                        'type' => 'assessment',
                        'title' => 'Next Assessment',
                        'url' => school_route('student.courses.modules.assessment.take', ['course' => $course->id, 'module' => $nextModule->id])
                    ];
                }
            }

            // Prev is last lesson of current module
            $lastLesson = $module->lessons()->orderBy('sort_order', 'desc')->first();
            if ($lastLesson) {
                $prev = [
                    'type' => 'lesson',
                    'title' => $lastLesson->title,
                    'url' => school_route('student.courses.modules.lessons.show', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lastLesson->id])
                ];
            } else {
                // No lessons in this module? Look at previous module
                $prevModule = $course->modules()->where('sort_order', '<', $module->sort_order)->orderBy('sort_order', 'desc')->first();
                if ($prevModule) {
                    if ($prevModule->module_type === 'assessment') {
                        $prev = [
                            'type' => 'assessment',
                            'title' => 'Previous Quiz',
                            'url' => school_route('student.courses.modules.assessment.take', ['course' => $course->id, 'module' => $prevModule->id])
                        ];
                    } else {
                        $prevLastLesson = $prevModule->lessons()->orderBy('sort_order', 'desc')->first();
                        if ($prevLastLesson) {
                            $prev = [
                                'type' => 'lesson',
                                'title' => $prevLastLesson->title,
                                'url' => school_route('student.courses.modules.lessons.show', ['course' => $course->id, 'module' => $prevModule->id, 'lesson' => $prevLastLesson->id])
                            ];
                        }
                    }
                }
            }
        }

        return (object) ['prev' => $prev, 'next' => $next];
    }

    /**
     * Show the form for editing the specified module
     */
    public function edit(Request $request, School $school, Course $course, CourseModule $module)
    {
        $role = $this->resolveAuthRole();
        
        if (!in_array($role, ['admin', 'instructor'], true)) {
            abort(403);
        }
        
        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        $viewPath = $role === 'admin' ? 'admin.modules.edit' : 'instructor.modules.edit';
        return view($school->resolveView($viewPath), compact('school', 'course', 'module'))->with('isAjax', $request->ajax());
    }

    /**
     * Update the specified module
     */
    public function update(Request $request, School $school, Course $course, CourseModule $module)
    {
        $role = $this->resolveAuthRole();
        
        if (!in_array($role, ['admin', 'instructor'], true)) {
            abort(403);
        }
        
        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'module_type' => 'required|in:lesson,reading,video,assessment',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        
        $module->update([
            'title' => $request->title,
            'description' => $request->description,
            'module_type' => $request->module_type,
            'sort_order' => $request->sort_order ?? $module->sort_order,
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Module updated successfully.',
                'module' => $module,
            ]);
        }
        
        $redirectRoute = $role === 'admin'
            ? 'schools.admin.courses.modules.show'
            : 'schools.instructor.courses.modules.show';

        return redirect()
            ->route($redirectRoute, ['school' => $school->slug, 'course' => $course->id, 'module' => $module->id])
            ->with('success', 'Module updated successfully.');
    }

    /**
     * Remove the specified module
     */
    public function destroy(School $school, Course $course, CourseModule $module)
    {
        $role = $this->resolveAuthRole();
        
        if (!in_array($role, ['admin', 'instructor'], true)) {
            abort(403);
        }
        
        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        DB::beginTransaction();
        try {
            // Delete all lessons in this module first
            $module->lessons()->delete();
            
            // Delete the module
            $module->delete();
            
            DB::commit();
            
            $redirectRoute = $role === 'admin'
                ? 'schools.admin.courses.modules.index'
                : 'schools.instructor.courses.modules.index';

            return redirect()
                ->route($redirectRoute, ['school' => $school->slug, 'course' => $course->id])
                ->with('success', 'Module and all its lessons deleted successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete module: ' . $e->getMessage());
        }
    }

    /**
     * Reorder modules
     */
    public function reorder(Request $request, School $school, Course $course)
    {
        $role = $this->resolveAuthRole();
        
        if (!in_array($role, ['admin', 'instructor'], true)) {
            abort(403);
        }
        
        $request->validate([
            'module_ids' => 'required|array',
            'module_ids.*' => 'exists:course_modules,id',
        ]);
        
        DB::beginTransaction();
        try {
            foreach ($request->module_ids as $index => $moduleId) {
                CourseModule::where('id', $moduleId)
                    ->where('course_id', $course->id)
                    ->update(['sort_order' => $index + 1]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Modules reordered successfully.',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder modules: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Duplicate a module with all its lessons
     */
    public function duplicate(School $school, Course $course, CourseModule $module)
    {
        $role = $this->resolveAuthRole();
        
        if (!in_array($role, ['admin', 'instructor'], true)) {
            abort(403);
        }
        
        // Verify module belongs to course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        DB::beginTransaction();
        try {
            // Create duplicate module
            $newModule = $module->replicate();
            $newModule->title = $module->title . ' (Copy)';
            $newModule->sort_order = $course->modules()->max('sort_order') + 1;
            $newModule->save();
            
            // Duplicate all lessons
            foreach ($module->lessons as $lesson) {
                $newLesson = $lesson->replicate();
                $newLesson->module_id = $newModule->id;
                $newLesson->save();
            }
            
            DB::commit();
            
            $redirectRoute = $role === 'admin'
                ? 'schools.admin.courses.modules.show'
                : 'schools.instructor.courses.modules.show';

            return redirect()
                ->route($redirectRoute, ['school' => $school->slug, 'course' => $course->id, 'module' => $newModule->id])
                ->with('success', 'Module duplicated successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to duplicate module: ' . $e->getMessage());
        }
    }

}
