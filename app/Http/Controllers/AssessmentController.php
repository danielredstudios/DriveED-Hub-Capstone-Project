<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\School;
use App\Models\CourseModule;
use App\Models\AssessmentQuestion;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AssessmentController extends Controller
{
    /**
     * Resolve authenticated user role
     */
    private function resolveAuthRole(): string
    {
        if (Auth::guard('admin')->check()) return 'admin';
        if (Auth::guard('instructor')->check()) return 'instructor';
        abort(403);
    }

    /**
     * Manage questions for a specific assessment module
     */
    public function manage(School $school, Course $course, CourseModule $module)
    {
        // Enforce ownership
        if ($course->school_id !== $school->id) abort(404);
        if ($module->course_id !== $course->id) abort(404);

        $role = $this->resolveAuthRole();
        
        if ($module->module_type !== 'assessment') {
            $redirectRoute = $role === 'admin' 
                ? 'schools.admin.courses.modules.show' 
                : 'schools.instructor.courses.modules.show';
                
            return redirect()->route($redirectRoute, [
                'school' => $school->slug, 
                'course' => $course->id, 
                'module' => $module->id
            ])->with('error', 'This module is not an assessment.');
        }

        $attachedQuestions = $module->questions()->get();
        
        // Suggest questions from the same course that are not yet attached
        $suggestedQuestions = Question::where('course_id', $course->id)
            ->whereNotIn('id', $attachedQuestions->pluck('id'))
            ->get();

        return view($school->resolveView('instructor.assessments.manage'), compact('school', 'course', 'module', 'attachedQuestions', 'suggestedQuestions', 'role'));
    }

    /**
     * Attach a question from the bank to an assessment
     */
    public function addQuestion(Request $request, School $school, Course $course, CourseModule $module)
    {
        // Enforce ownership
        if ($course->school_id !== $school->id) abort(404);
        if ($module->course_id !== $course->id) abort(404);

        $this->resolveAuthRole();

        $validated = $request->validate([
            'question_id' => [
                'required',
                Rule::exists('questions', 'id')
                    ->where('school_id', $school->id)
                    ->where(function ($query) use ($course) {
                        $query->where('course_id', $course->id)
                              ->orWhereNull('course_id');
                    })
            ],
            'points' => 'nullable|integer|min:1',
        ]);

        $exists = AssessmentQuestion::where('module_id', $module->id)
            ->where('question_id', $validated['question_id'])
            ->exists();

        if ($exists) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Question is already part of this assessment.'
                ], 422);
            }
            return redirect()->back()->with('error', 'Question is already part of this assessment.');
        }

        $sortOrder = AssessmentQuestion::where('module_id', $module->id)->max('sort_order') + 1;

        AssessmentQuestion::create([
            'module_id' => $module->id,
            'question_id' => $validated['question_id'],
            'points' => $validated['points'] ?? Question::find($validated['question_id'])->default_points,
            'sort_order' => $sortOrder,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Question added to assessment.'
            ]);
        }

        return redirect()->back()->with('success', 'Question added to assessment.');
    }

    /**
     * Attach multiple questions from the bank at once
     */
    public function addMultipleQuestions(Request $request, School $school, Course $course, CourseModule $module)
    {
        // Enforce ownership
        if ($course->school_id !== $school->id) abort(404);
        if ($module->course_id !== $course->id) abort(404);

        $this->resolveAuthRole();

        $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => [
                Rule::exists('questions', 'id')
                    ->where('school_id', $school->id)
                    ->where(function ($query) use ($course) {
                        $query->where('course_id', $course->id)
                              ->orWhereNull('course_id');
                    })
            ],
        ]);

        $addedCount = 0;
        $maxSort = AssessmentQuestion::where('module_id', $module->id)->max('sort_order') ?? 0;

        foreach ($request->question_ids as $questionId) {
            $exists = AssessmentQuestion::where('module_id', $module->id)
                ->where('question_id', $questionId)
                ->exists();

            if (!$exists) {
                // Scoped lookup
                $question = $school->questions()->find($questionId);
                if (!$question) continue;

                AssessmentQuestion::create([
                    'module_id' => $module->id,
                    'question_id' => $questionId,
                    'points' => $question->default_points,
                    'sort_order' => ++$maxSort,
                ]);
                $addedCount++;
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "$addedCount questions added to assessment."
            ]);
        }

        return redirect()->back()->with('success', "$addedCount questions added to assessment.");
    }

    /**
     * Remove a question from an assessment (doesn't delete from bank)
     */
    public function removeQuestion(Request $request, School $school, Course $course, CourseModule $module, Question $question)
    {
        // Enforce ownership
        if ($course->school_id !== $school->id) abort(404);
        if ($module->course_id !== $course->id) abort(404);
        if ($question->school_id !== $school->id) abort(403);

        $this->resolveAuthRole();
        
        AssessmentQuestion::where('module_id', $module->id)
            ->where('question_id', $question->id)
            ->delete();

        return redirect()->back()->with('success', 'Question removed from assessment.');
    }

    /**
     * Update sort order of questions in an assessment
     */
    public function reorder(Request $request, School $school, Course $course, CourseModule $module)
    {
        // Enforce ownership
        if ($course->school_id !== $school->id) abort(404);
        if ($module->course_id !== $course->id) abort(404);

        $this->resolveAuthRole();

        $order = $request->order; // Array of question IDs
        
        if (!is_array($order)) {
            return response()->json(['success' => false, 'message' => 'Invalid order data.'], 400);
        }

        foreach ($order as $index => $questionId) {
            AssessmentQuestion::where('module_id', $module->id)
                ->where('question_id', $questionId)
                ->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
