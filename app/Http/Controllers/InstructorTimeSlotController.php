<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\TimeSlot;
use App\Models\Booking;
use App\Models\InstructorRemovalRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Rules\StrongPassword;
use App\Support\DemoAccountProtection;

class InstructorTimeSlotController extends Controller
{
    // Display available time slots and instructor's selected slots
    public function index(Request $request, School $school)
    {
        $instructor = Auth::guard('instructor')->user();

        abort_unless($instructor && $instructor->school_id === $school->id, 403);

        // Get instructor's course specializations
        $instructorCourses = $instructor->course_specializations ?? [];

        $availableSlots = TimeSlot::with(['instructors', 'course', 'branch'])
            ->where('school_id', $school->id)
            ->where('status', 'open')
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $mySlots = TimeSlot::with(['instructors', 'course', 'branch'])
            ->where('school_id', $school->id)
            ->whereHas('instructors', function ($query) use ($instructor): void {
            $query->where('instructor_id', $instructor->id);
        })
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return view($school->resolveView('instructor.timeslots'), [
            'school' => $school,
            'availableSlots' => $availableSlots,
            'mySlots' => $mySlots,
            'instructorCourses' => $instructorCourses,
            'isAjax' => $request->ajax(),
        ]);
    }

    // Toggle instructor's participation in a time slot (select/leave)
    public function toggle(School $school, $id)
    {
        $instructor = Auth::guard('instructor')->user();

        abort_unless($instructor && $instructor->school_id === $school->id, 403);

        $timeSlot = TimeSlot::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $isAjax = request()->ajax() || request()->header('X-Requested-With') === 'XMLHttpRequest';

        if ($timeSlot->status !== 'open') {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'This time slot is closed and cannot be selected.'], 400);
            }
            return redirect()->back()->with('error', 'This time slot is closed and cannot be selected.');
        }

        if ($timeSlot->date->isPast()) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Cannot select past time slots.'], 400);
            }
            return redirect()->back()->with('error', 'Cannot select past time slots.');
        }

        if ($timeSlot->hasInstructor($instructor->id)) {
            $pivot = $timeSlot->instructors()
                ->wherePivot('instructor_id', $instructor->id)
                ->wherePivot('school_id', $school->id)
                ->first();

            if ($pivot && $pivot->pivot->assignment_type === 'admin_assigned') {
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => 'You cannot leave this slot as it was assigned by an admin. Please use Request Removal instead.'], 400);
                }
                return redirect()->back()->with('error', 'You cannot leave this slot as it was assigned by an admin. Please use Request Removal instead.');
            }

            // [NEW] 1-minute Grace Period & Booking Check
            $hasBookings = $timeSlot->bookings()->where('instructor_id', $instructor->id)->where('status', '!=', 'cancelled')->exists();
            $joinTime = \Carbon\Carbon::parse($pivot->pivot->created_at);
            $isGracePeriod = $joinTime->gt(now()->subMinute());

            if ($hasBookings || !$isGracePeriod) {
                $reason = $hasBookings ? 'This slot has student bookings.' : 'The 1-minute grace period for self-selected slots has expired.';
                $message = $reason . ' You must "Request Removal" so the admin can review the request.';
                
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }
                return redirect()->back()->with('error', $message);
            }

            try {
                $timeSlot->instructors()->detach($instructor->id);

                if ($isAjax) {
                    return response()->json(['success' => true, 'message' => 'You have left this time slot.', 'action' => 'left']);
                }
                return redirect()->back()->with('success', 'You have left this time slot.');
            }
            catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to leave time slot', ['error' => $e->getMessage()]);
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => 'An error occurred while leaving the slot.'], 500);
                }
                return redirect()->back()->with('error', 'An error occurred while leaving the slot.');
            }
        }

        if ($timeSlot->isFull()) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'This time slot is full.'], 400);
            }
            return redirect()->back()->with('error', 'This time slot is full.');
        }

        // Check if instructor is legally accredited to teach this specific course (LTO Compliance)
        if (!$instructor->canTeach($timeSlot->course)) {
            $msg = ($instructor->license_status === 'verified') 
                ? 'You do not have the required LTO restriction code for this course.' 
                : 'Your license is not yet verified. Please update your profile with your driver\'s license.';
            
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            return redirect()->back()->with('error', $msg);
        }

        $hasConflict = TimeSlot::where('school_id', '=', $school->id)
            ->where('id', '!=', $timeSlot->id)
            ->where('date', '=', $timeSlot->date)
            ->whereHas('instructors', function ($query) use ($instructor): void {
            $query->where('instructor_id', $instructor->id);
        })
            ->where(function ($query) use ($timeSlot): void {
            $query->where(function ($q) use ($timeSlot): void {
                    $q->where('start_time', '<=', $timeSlot->start_time)
                        ->where('end_time', '>', $timeSlot->start_time);
                }
                )
                    ->orWhere(function ($q) use ($timeSlot): void {
                $q->where('start_time', '<', $timeSlot->end_time)
                    ->where('end_time', '>=', $timeSlot->end_time);
            }
            )
                ->orWhere(function ($q) use ($timeSlot): void {
                $q->where('start_time', '>=', $timeSlot->start_time)
                    ->where('end_time', '<=', $timeSlot->end_time);
            }
            );
        })
            ->exists();

        if ($hasConflict) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'You already have a time slot that conflicts with this one.'], 400);
            }
            return redirect()->back()->with('error', 'You already have a time slot that conflicts with this one.');
        }

        try {
            $timeSlot->instructors()->attach($instructor->id, [
                'school_id' => $school->id,
                'assignment_type' => 'self_selected',
            ]);

            if ($isAjax) {
                return response()->json(['success' => true, 'message' => 'You have successfully selected this time slot!', 'action' => 'selected']);
            }
            return redirect()->back()->with('success', 'You have successfully selected this time slot!');
        }
        catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to select time slot', ['error' => $e->getMessage()]);
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
            }
            return redirect()->back()->with('error', 'An error occurred.');
        }
    }

    // View instructor's schedule/calendar
    public function mySchedule(Request $request, School $school)
    {
        $instructor = Auth::guard('instructor')->user();
        abort_unless($instructor && $instructor->school_id === $school->id, 403);

        $instructorId = $instructor->id;
        $todayDate = now()->toDateString();
        $endOfWeek = now()->endOfWeek()->toDateString();
        $minimumNoticeDays = $school->instructor_removal_notice_days ?? 7;

        // Get instructor's qualified courses
        $qualifiedCourseIds = $instructor->course_specializations ?? [];

        // Get pending removal requests
        $pendingRemovalRequests = InstructorRemovalRequest::where('instructor_id', '=', $instructorId)
            ->where('school_id', '=', $school->id)
            ->where('status', '=', 'pending')
            ->pluck('time_slot_id', 'id')
            ->toArray();

        // My slots (instructor's selected and admin-assigned slots)
        $mySlots = TimeSlot::with(['instructors', 'course', 'branch', 'bookings.student', 'bookings.course'])
            ->where('school_id', $school->id)
            ->whereHas('instructors', function ($query) use ($instructorId) {
            $query->where('instructor_id', $instructorId);
        })
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        // Group my slots by date
        $groupedMySlots = $mySlots->groupBy(function ($slot) {
            return $slot->date->format('Y-m-d');
        });

        // Today's slots
        $todaySlots = $mySlots->filter(function ($slot) use ($todayDate) {
            return $slot->date->format('Y-m-d') === $todayDate;
        });

        // Upcoming slots this week (excluding today)
        // Sort slots with active bookings first so they aren't cut off by take(5)
        $upcomingSlots = $mySlots->filter(function ($slot) use ($todayDate, $endOfWeek) {
            $slotDate = $slot->date->format('Y-m-d');
            return $slotDate > $todayDate && $slotDate <= $endOfWeek;
        })->sortBy(function ($slot) use ($instructorId) {
            $hasBookings = $slot->bookings->where('instructor_id', $instructorId)
                ->where('status', '!=', 'cancelled')->isNotEmpty();
            return [$hasBookings ? 0 : 1, $slot->date->format('Y-m-d'), $slot->start_time];
        })->take(5);

        // Available slots (not taken by this instructor)
        $availableSlots = TimeSlot::with(['instructors', 'course', 'branch'])
            ->where('school_id', $school->id)
            ->where('status', 'open')
            ->whereDoesntHave('instructors', function ($query) use ($instructorId) {
            $query->where('instructor_id', $instructorId);
        })
            ->whereRaw('(SELECT COUNT(*) FROM schedule_instructors WHERE schedule_instructors.time_slot_id = time_slots.id) < COALESCE(time_slots.max_instructors, 1)')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        // Group available slots by date
        $groupedAvailableSlots = $availableSlots->groupBy(function ($slot) {
            return $slot->date->format('Y-m-d');
        });

        // Instructor's schedule for conflict checking (slot IDs by date and time)
        $instructorSchedule = [];
        foreach ($mySlots as $slot) {
            $dateKey = $slot->date->format('Y-m-d');
            if (!isset($instructorSchedule[$dateKey])) {
                $instructorSchedule[$dateKey] = [];
            }
            $instructorSchedule[$dateKey][] = [
                'id' => $slot->id,
                'start' => $slot->formatted_start_time,
                'end' => $slot->formatted_end_time,
            ];
        }

        return view('school.instructor.schedule-new', [
            'school' => $school,
            'instructorId' => $instructorId,
            'todayDate' => $todayDate,
            'minimumNoticeDays' => $minimumNoticeDays,
            'qualifiedCourseIds' => $qualifiedCourseIds,
            'pendingRemovalRequests' => $pendingRemovalRequests,
            'mySlots' => $mySlots,
            'groupedMySlots' => $groupedMySlots,
            'todaySlots' => $todaySlots,
            'upcomingSlots' => $upcomingSlots,
            'availableSlots' => $availableSlots,
            'groupedAvailableSlots' => $groupedAvailableSlots,
            'instructorSchedule' => $instructorSchedule,
            'isAjax' => $request->ajax(),
        ]);
    }

    public function profile(Request $request, School $school)
    {
        $instructor = Auth::guard('instructor')->user();
        $instructor->load('branch');


        return view($school->resolveView('instructor.profile'), [
            'school' => $school,
            'instructor' => $instructor,
            'isAjax' => $request->ajax(),
        ]);
    }

    public function updateProfile(Request $request, School $school)
    {
        $instructor = Auth::guard('instructor')->user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'license_number' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'current_password' => 'nullable|required_with:new_password|string',
            'new_password' => ['nullable', 'confirmed', 'different:current_password', new StrongPassword()],
            'availability' => 'required|in:available,unavailable',
        ];

        if ($school->schoolSetting->enforce_ph_contact ?? true) {
            $rules['contact'] = ['required', 'string', 'max:20', 'regex:/^(09\d{9}|\+639\d{9}|9\d{9})$/'];
        } else {
            $rules['contact'] = 'required|string|max:20';
        }

        $request->validate($rules);

        $normalize = static function ($value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            return $value === '' ? null : $value;
        };


        $passwordChanged = $request->filled('new_password');

        $data = $request->only(['name', 'email', 'contact', 'license_number', 'address', 'availability']);
        
        // Normalize contact number if enforced
        if (($school->schoolSetting->enforce_ph_contact ?? true) && !empty($data['contact'])) {
            $contact = trim((string)$data['contact']);
            if (preg_match('/^9\d{9}$/', $contact)) {
                $data['contact'] = '+63' . $contact;
            } elseif (preg_match('/^09\d{9}$/', $contact)) {
                $data['contact'] = '+63' . substr($contact, 1);
            }
        }

        // Check if this is a protected demo account
        if (DemoAccountProtection::isProtectedAccount($instructor->email, 'instructor', $school)) {
            $nameChanged = trim((string)($data['name'] ?? '')) !== trim((string)$instructor->name);
            $emailChanged = strtolower(trim((string)($data['email'] ?? ''))) !== strtolower(trim((string)$instructor->email));
            
            if ($nameChanged || $emailChanged || $passwordChanged) {
                return back()->with('error', 'This demo account has locked name, email, and password.');
            }
        }

        // Check current password if user wants to change password
        if ($request->filled('new_password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $instructor->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $data['password'] = Hash::make($request->new_password);
        }

        // Use direct update for reliability
        \App\Models\Instructor::where('id', $instructor->id)->update($data);

        return redirect()
            ->route('schools.instructor.profile', $school)
            ->with('success', 'Profile updated successfully.');
    }

    public function updateProfilePicture(Request $request, School $school)
    {
        $instructor = Auth::guard('instructor')->user();

        $maxSize = ($school->schoolSetting->max_file_size_mb ?? 5) * 1024;
        $request->validate([
            'profile_picture' => "required|image|mimes:png,jpg,jpeg,webp|max:{$maxSize}|dimensions:max_width=2000,max_height=2000",
        ]);

        // Delete old profile picture if exists
        try {
            if ($instructor->profile_picture) {
                Storage::disk('public')->delete($instructor->profile_picture);
            }
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $instructor->update(['profile_picture' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully!',
                'path' => $path,
            ]);
        }
        catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update profile picture', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    public function uploadLicense(Request $request, School $school)
    {
        $instructor = Auth::guard('instructor')->user();
        abort_unless($instructor && $instructor->school_id === $school->id, 403);

        $maxSize = ($school->schoolSetting->max_file_size_mb ?? 5) * 1024;
        $request->validate([
            'license_image' => "required|image|mimes:png,jpg,jpeg,webp|max:{$maxSize}",
        ]);

        try {
            // Delete old license if exists
            if ($instructor->license_image) {
                Storage::disk('public')->delete($instructor->license_image);
            }

            $path = $request->file('license_image')->store('instructor_licenses', 'public');
            
            $instructor->update([
                'license_image' => $path,
                'license_status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'License uploaded successfully and is now pending verification.',
                'path' => $path
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to upload license', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'An error occurred during upload.'], 500);
        }
    }

    // Request removal from an admin-assigned time slot
    public function requestRemoval(Request $request, School $school, $id)
    {
        $instructor = Auth::guard('instructor')->user();

        abort_unless($instructor && $instructor->school_id === $school->id, 403);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $timeSlot = TimeSlot::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $isAjax = $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        // Check minimum notice period
        $schoolTimezone = $school->timezone;
        try {
            if (!$schoolTimezone || !in_array($schoolTimezone, \DateTimeZone::listIdentifiers())) {
                $schoolTimezone = config('app.timezone', 'UTC');
            }
        } catch (\Exception $e) {
            $schoolTimezone = 'UTC';
        }

        $now = \Carbon\Carbon::now($schoolTimezone)->startOfDay();
        $slotDate = \Carbon\Carbon::parse($timeSlot->date, $schoolTimezone)->startOfDay();
        $minimumNoticeDays = $school->instructor_removal_notice_days ?? 7;
        
        $daysUntilSlot = $now->diffInDays($slotDate, false);

        // [MODIFIED] Relax notice period for emergency testing/real emergencies
        // If it's short notice, we still allow the REQUEST, but the admin will see the timing.
        if ($daysUntilSlot < $minimumNoticeDays) {
            // Log it or add a flag if needed, but don't block the request creation
            \Illuminate\Support\Facades\Log::info('Short notice removal request', [
                'instructor_id' => $instructor->id,
                'slot_id' => $timeSlot->id,
                'days_notice' => $daysUntilSlot
            ]);
        }

        // Check if instructor is assigned to this slot
        $pivot = DB::table('schedule_instructors')
            ->where('time_slot_id', $timeSlot->id)
            ->where('instructor_id', $instructor->id)
            ->where('school_id', $school->id)
            ->first();

        if (!$pivot) {
            $message = 'You are not assigned to this time slot.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            return redirect()->back()->with('error', $message);
        }

        // [MODIFIED] allow removal requests if bookings exist, even if not adminassigned
        $hasBookings = $timeSlot->bookings()->where('instructor_id', $instructor->id)->where('status', '!=', 'cancelled')->exists();

        if ($pivot->assignment_type !== 'admin_assigned' && !$hasBookings) {
            $message = 'You can leave this slot directly as it was self-selected and has no bookings.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            return redirect()->back()->with('error', $message);
        }

        // Check if there's already a pending request
        if ($pivot->has_pending_removal_request) {
            $message = 'You already have a pending removal request for this time slot.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            return redirect()->back()->with('error', $message);
        }

        try {
            InstructorRemovalRequest::create([
                'school_id' => $school->id,
                'time_slot_id' => $timeSlot->id,
                'instructor_id' => $instructor->id,
                'schedule_instructor_id' => $pivot->id,
                'status' => 'pending',
                'reason' => $request->reason,
            ]);

            DB::table('schedule_instructors')
                ->where('id', $pivot->id)
                ->update(['has_pending_removal_request' => true]);

            $message = 'Your removal request has been submitted to the admin for review.';
            if ($isAjax) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return redirect()->back()->with('success', $message);
        }
        catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to request removal', ['error' => $e->getMessage()]);
            $message = 'An error occurred while processing your request.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }
            return redirect()->back()->with('error', $message);
        }
    }

    public function updateAttendance(School $school, Booking $booking, Request $request)
    {
        $instructor = Auth::guard('instructor')->user();
        abort_unless($instructor && $instructor->school_id === $school->id, 403);
        abort_unless($booking->school_id === $school->id, 403);
        abort_unless($booking->instructor_id === $instructor->id, 403, 'You can only mark attendance for your own assigned lessons.');

        $request->validate([
            'attendance_status' => 'nullable|in:attended,late,absent'
        ]);

        try {
            $booking->update([
                'attendance_status' => $request->attendance_status,
                'attendance_marked_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance updated successfully'
            ]);
        }
        catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update attendance', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'An error occurred updating attendance.'], 500);
        }
    }

    public function updateFeedback(School $school, Booking $booking, Request $request)
    {
        $instructor = Auth::guard('instructor')->user();
        abort_unless($instructor && $instructor->school_id === $school->id, 403);
        abort_unless($booking->school_id === $school->id, 403);
        abort_unless($booking->instructor_id === $instructor->id, 403, 'This lesson is not assigned to you');

        if ($booking->status !== 'completed' && $booking->session_status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Feedback can only be added after a completed schedule.'
            ], 422);
        }

        $request->validate([
            'instructor_feedback' => 'nullable|string|max:1000'
        ]);

        try {
            $booking->update(['instructor_feedback' => $request->instructor_feedback]);

            return response()->json([
                'success' => true,
                'message' => 'Feedback updated successfully'
            ]);
        }
        catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update feedback', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'An error occurred updating feedback.'], 500);
        }
    }

    public function getLessonDetails(School $school, Booking $booking)
    {
        $instructor = Auth::guard('instructor')->user();
        abort_unless($instructor && $instructor->school_id === $school->id, 403);
        abort_unless($booking->school_id === $school->id, 403);
        abort_unless($booking->instructor_id === $instructor->id, 403, 'This lesson is not assigned to you');

        $booking->load(['student', 'course', 'timeSlot']);

        return response()->json([
            'success' => true,
            'booking' => $booking
        ]);
    }

    public function updateLessonDetails(School $school, Booking $booking, Request $request)
    {
        $instructor = Auth::guard('instructor')->user();
        abort_unless($instructor && $instructor->school_id === $school->id, 403);
        abort_unless($booking->school_id === $school->id, 403);
        abort_unless($booking->instructor_id === $instructor->id, 403, 'This lesson is not assigned to you');

        $validated = $request->validate([
            'attendance_status' => 'sometimes|required|in:attended,late,absent',
            'session_status' => 'sometimes|required|in:done,completed,cancelled,rescheduled,no-show',
            'session_grade' => 'sometimes|nullable|numeric|min:0|max:100',
            'instructor_feedback' => 'sometimes|nullable|string|max:1000',
            'student_feedback' => 'sometimes|nullable|string|max:1000',
            'skills_practiced' => 'sometimes|nullable|array',
            'cancellation_reason' => 'sometimes|nullable|string|max:500'
        ]);

        $willBeCompleted = ($validated['session_status'] ?? null) === 'done'
            || ($validated['session_status'] ?? null) === 'completed'
            || $booking->status === 'done'
            || $booking->status === 'completed'
            || $booking->session_status === 'done'
            || $booking->session_status === 'completed';

        $isUpdatingGrade = array_key_exists('session_grade', $validated);
        $isUpdatingFeedback = array_key_exists('instructor_feedback', $validated);

        if (($isUpdatingGrade || $isUpdatingFeedback) && !$willBeCompleted) {
            return response()->json([
                'success' => false,
                'message' => 'Grades and comments can only be updated after a completed schedule.'
            ], 422);
        }

        $updateData = [];

        if (array_key_exists('attendance_status', $validated)) {
            $updateData['attendance_status'] = $validated['attendance_status'];
            $updateData['attendance_marked_at'] = now();
        }

        if (array_key_exists('session_status', $validated)) {
            $updateData['session_status'] = $validated['session_status'];
        }

        if (array_key_exists('session_grade', $validated)) {
            $updateData['session_grade'] = $validated['session_grade'];
        }

        if (array_key_exists('instructor_feedback', $validated)) {
            $updateData['instructor_feedback'] = $validated['instructor_feedback'];
        }

        if (array_key_exists('student_feedback', $validated)) {
            $updateData['student_feedback'] = $validated['student_feedback'];
        }

        if (array_key_exists('skills_practiced', $validated)) {
            $updateData['skills_practiced'] = $validated['skills_practiced'] ?? [];
        }

        if (empty($updateData)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid fields were provided to update.'
            ], 422);
        }

        // If session is being marked as cancelled, update the main booking status too
        if (($validated['session_status'] ?? null) === 'cancelled' && $booking->status !== 'cancelled') {
            $updateData['status'] = 'cancelled';
            $updateData['cancelled_by'] = 'instructor';
            $updateData['cancelled_at'] = now();
            $updateData['cancellation_reason'] = $validated['cancellation_reason'] ?? 'Session cancelled by instructor';
        }

        try {
            // [NEW LOGIC] Map session_status to booking main status
            if (isset($updateData['session_status']) && $updateData['session_status'] === 'done') {
                $updateData['status'] = Booking::STATUS_DONE;
            } elseif (isset($updateData['session_status']) && $updateData['session_status'] === 'no-show') {
                $updateData['status'] = Booking::STATUS_NO_SHOW;
            }

            $booking->update($updateData);

            $message = 'Lesson details updated successfully';
            if (($updateData['status'] ?? null) === Booking::STATUS_DONE) {
                $message = 'Lesson marked as done and submitted for admin verification.';
            } elseif (($updateData['status'] ?? null) === Booking::STATUS_NO_SHOW) {
                $message = 'Lesson marked as no-show.';
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }
        catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update lesson details', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'An error occurred updating lesson details.'], 500);
        }
    }
}
