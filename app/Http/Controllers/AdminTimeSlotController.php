<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Instructor;
use App\Models\School;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminTimeSlotController extends Controller
{
    // Display all time slots
    public function index(Request $request, School $school)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless((bool)$admin, 403);

        $query = TimeSlot::with(['instructors', 'branch'])
            ->where('school_id', '=', $school->id);

        if ($admin->isBranchSecretary() && $admin->branch_id) {
            $query->where('branch_id', '=', $admin->branch_id);
        }

        $timeSlots = $query->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $instructorsQuery = Instructor::where('school_id', '=', $school->id)
            ->where('status', '=', 'active')
            ->where('availability', '=', 'available');

        if ($admin->isBranchSecretary() && $admin->branch_id) {
            $instructorsQuery->where('branch_id', '=', $admin->branch_id);
        }

        $instructors = $instructorsQuery->orderBy('name')
            ->get(['*']);

        $courses = \App\Models\Course::where('school_id', $school->id)
            ->where('status', 'active')
            ->orderBy('title')
            ->get();

        return view($school->resolveView('admin.timeslots'), [
            'school' => $school,
            'timeSlots' => $timeSlots,
            'instructors' => $instructors,
            'courses' => $courses,
            'isAjax' => $request->ajax(),
        ]);
    }

    // Store new time slot
    public function store(Request $request, School $school)
    {
        // Check if it's bulk creation
        if ($request->has('timeslots') && $request->timeslots) {
            $timeslots = json_decode($request->timeslots, true);

            if (!is_array($timeslots) || empty($timeslots)) {
                return redirect()->back()->with('error', 'Invalid timeslots data');
            }

            $created = 0;
            foreach ($timeslots as $timeslotData) {
                // Validate each timeslot
                $validator = validator($timeslotData, [
                    'date' => 'required|date',
                    'start_time' => 'required|date_format:H:i',
                    'end_time' => [
                        'required',
                        'date_format:H:i',
                        'after:start_time',
                        function ($attribute, $value, $fail) use ($timeslotData, $school) {
                            try {
                                $start = \Carbon\Carbon::createFromFormat('H:i', $timeslotData['start_time']);
                                $end = \Carbon\Carbon::createFromFormat('H:i', $value);
                                $duration = $start->diffInMinutes($end);
                                
                                $settings = $school->schoolSetting;
                                // For bulk creation, we use a generic 60min or the TDC min if no course is specified
                                $minDuration = $settings->min_tdc_duration_minutes ?? 60;
                                
                                if ($duration < $minDuration) {
                                    $fail("The session must be at least {$minDuration} minutes long. (Detected: {$duration} mins)");
                                }
                            } catch (\Exception $e) {
                                // Handled by date_format rule
                            }
                        },
                    ],
                    'max_instructors' => 'required|integer|min:1',
                ]);

                if ($validator->fails()) {
                    continue; // Skip invalid timeslots
                }

                TimeSlot::create([
                    'school_id' => $school->id,
                    'date' => $timeslotData['date'],
                    'start_time' => $timeslotData['start_time'],
                    'end_time' => $timeslotData['end_time'],
                    'max_instructors' => $timeslotData['max_instructors'],
                    'notes' => $timeslotData['notes'] ?? null,
                    'status' => 'open',
                ]);

                $created++;
            }

            return redirect()->route('schools.admin.timeslots.index', $school)
                ->with('success', "Successfully created {$created} time slot(s)!");
        }

        // Single timeslot creation
        $request->validate([
            'course_id' => [
                'required',
                Rule::exists('courses', 'id')->where('school_id', $school->id)
            ],
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
                function ($attribute, $value, $fail) use ($request, $school) {
                    try {
                        $start = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
                        $end = \Carbon\Carbon::createFromFormat('H:i', $value);
                        $duration = $start->diffInMinutes($end);
                        
                        $course = \App\Models\Course::find($request->course_id);
                        $settings = $school->schoolSetting;
                        
                        $minDuration = 60;
                        if ($course) {
                            $minDuration = $course->course_type === 'theoretical' 
                                ? ($settings->min_tdc_duration_minutes ?? 60) 
                                : ($settings->min_pdc_duration_minutes ?? 60);
                        }

                        if ($duration < $minDuration) {
                            $fail("The session must be at least {$minDuration} minutes long. (Detected: {$duration} mins)");
                        }
                        if ($duration > 480) {
                            $fail("The session cannot exceed 8 hours (Detected: ".round($duration/60,1)."h).");
                        }
                    } catch (\Exception $e) {
                        // Handled by date_format rule
                    }
                },
            ],
            'max_instructors' => 'required|integer|min:1',
            'max_students' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:500',
            'instructor_ids' => 'nullable|array',
            'instructor_ids.*' => [
                Rule::exists('instructors', 'id')->where('school_id', $school->id)
            ],
        ]);

        $course = $school->courses()->findOrFail($request->course_id);
        $instructorIds = $request->instructor_ids ?? [];

        // PDC (Practical) Batch Logic: Each instructor gets their own 1-on-1 slot
        if ($course->course_type === 'practical' && count($instructorIds) > 1) {
            foreach ($instructorIds as $instructorId) {
                $timeSlot = TimeSlot::create([
                    'school_id' => $school->id,
                    'course_id' => $course->id,
                    'date' => $request->date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'max_instructors' => 1, // PDC is 1-on-1 per slot
                    'max_students' => 1,
                    'notes' => $request->notes,
                    'status' => 'open',
                ]);

                $timeSlot->instructors()->attach($instructorId, [
                    'school_id' => $school->id,
                    'assignment_type' => 'admin_assigned',
                ]);
            }

            return redirect()->route('schools.admin.timeslots.index', $school)
                ->with('success', 'Practical slots created for ' . count($instructorIds) . ' instructors!');
        }

        // Standard logic for TDC or single-instructor PDC
        $timeSlot = TimeSlot::create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'max_instructors' => $request->max_instructors,
            'max_students' => $course->course_type === 'theoretical' ? ($request->max_students ?? 30) : 1,
            'notes' => $request->notes,
            'status' => 'open',
        ]);

        if (!empty($instructorIds)) {
            foreach ($instructorIds as $instructorId) {
                $timeSlot->instructors()->attach($instructorId, [
                    'school_id' => $school->id,
                    'assignment_type' => 'admin_assigned',
                ]);
            }
        }

        return redirect()->route('schools.admin.timeslots.index', $school)
            ->with('success', 'Time slot created successfully!');
    }

    // Assign/unassign instructors to a time slot
    public function assignInstructors(Request $request, School $school, $id)
    {
        $timeSlot = TimeSlot::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $request->validate([
            'instructors' => 'nullable|array',
            'instructors.*' => [
                Rule::exists('instructors', 'id')->where('school_id', $school->id)
            ],
        ]);

        $requestedInstructorIds = $request->instructors ?? [];

        $newInstructors = Instructor::where('school_id', $school->id)
            ->whereIn('id', $requestedInstructorIds)
            ->pluck('id')
            ->toArray();

        $currentInstructors = $timeSlot->instructors()->pluck('instructor_id')->toArray();

        if (count($newInstructors) > $timeSlot->max_instructors) {
            return redirect()->back()
                ->with('error', "Cannot assign more than {$timeSlot->max_instructors} instructors to this slot.");
        }

        $toAdd = array_diff($newInstructors, $currentInstructors);
        $toRemove = array_diff($currentInstructors, $newInstructors);

        foreach ($toAdd as $instructorId) {
            $timeSlot->instructors()->attach($instructorId, [
                'school_id' => $school->id,
                'assignment_type' => 'admin_assigned',
            ]);
        }

        foreach ($toRemove as $instructorId) {
            $pivot = $timeSlot->instructors()
                ->wherePivot('instructor_id', $instructorId)
                ->wherePivot('school_id', $school->id)
                ->first();

            if ($pivot && $pivot->pivot->assignment_type === 'admin_assigned') {
                $timeSlot->instructors()->detach($instructorId);
            }
        }

        return redirect()->route('schools.admin.timeslots.index', $school)
            ->with('success', 'Instructors assigned successfully!');
    }

    // Delete time slot
    public function destroy(School $school, $id)
    {
        $timeSlot = TimeSlot::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $timeSlot->delete();

        return redirect()->route('schools.admin.timeslots.index', $school)
            ->with('success', 'Time slot deleted successfully!');
    }

    // Toggle time slot status (open/closed)
    public function toggleStatus(School $school, $id)
    {
        $timeSlot = TimeSlot::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $timeSlot->status = $timeSlot->status === 'open' ? 'closed' : 'open';
        $timeSlot->save();

        return redirect()->route('schools.admin.timeslots.index', $school)
            ->with('success', 'Time slot status updated!');
    }
}