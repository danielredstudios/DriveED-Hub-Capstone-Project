<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Branch;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentActionRequest;
use App\Models\SystemLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class StudentActionRequestController extends Controller
{
    /**
     * List student action requests.
     * Secretary: only their branch. School admin: all branches.
     */
    public function index(Request $request, School $school)
    {
        $admin = Auth::guard('admin')->user();

        $query = StudentActionRequest::where('school_id', $school->id)
            ->with(['branch', 'requestedBy', 'student', 'reviewedBy'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc');

        // Branch secretaries only see their own requests
        if ($admin->isBranchSecretary()) {
            $query->where('branch_id', $admin->branch_id);
        }

        $requests = $query->get();
        $branches = Branch::where('school_id', $school->id)->where('is_active', true)->orderBy('name')->get();

        return view('school.admin.student-action-requests.index', array_merge(compact(
            'school', 'admin', 'requests', 'branches'
        ), ['isAjax' => $request->ajax()]));
    }

    /**
     * Secretary creates a request to add a student to their branch.
     */
    public function storeAddRequest(Request $request, School $school)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin->isBranchSecretary()) {
            return redirect()->back()->with('error', 'Only branch secretaries can submit student action requests.');
        }

        $validated = $request->validate([
            'student_id' => [
                'nullable',
                Rule::exists('students', 'id')->where('school_id', $school->id)
            ],
            'student_name' => ['required_without:student_id', 'nullable', 'string', 'max:255'],
            'student_email' => ['required_without:student_id', 'nullable', 'email', 'max:255'],
            'student_contact' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        // If student_id provided, verify they belong to this school
        if (!empty($validated['student_id'])) {
            $student = Student::where('id', $validated['student_id'])
                ->where('school_id', $school->id)
                ->first();

            if (!$student) {
                return redirect()->back()->with('error', 'Student not found in this school.');
            }
        }

        StudentActionRequest::create([
            'school_id' => $school->id,
            'branch_id' => $admin->branch_id,
            'requested_by' => $admin->id,
            'student_id' => $validated['student_id'] ?? null,
            'action' => 'add',
            'reason' => $validated['reason'],
            'student_name' => $validated['student_name'] ?? null,
            'student_email' => $validated['student_email'] ?? null,
            'student_contact' => $validated['student_contact'] ?? null,
            'status' => 'pending',
        ]);

        SystemLog::logInfo(
            "Branch secretary {$admin->name} requested to add a student to branch",
            'user_management',
            [
                'branch_id' => $admin->branch_id,
                'student_id' => $validated['student_id'] ?? null,
                'student_name' => $validated['student_name'] ?? null,
                'action' => 'add',
            ],
            $school->id,
            'student_action_request_created'
        );

        return redirect()->back()->with('success', 'Student add request submitted. Awaiting central admin approval.');
    }

    /**
     * Secretary creates a request to remove a student from their branch.
     */
    public function storeRemoveRequest(Request $request, School $school)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin->isBranchSecretary()) {
            return redirect()->back()->with('error', 'Only branch secretaries can submit student action requests.');
        }

        $validated = $request->validate([
            'student_id' => [
                'required',
                Rule::exists('students', 'id')->where('school_id', $school->id)
            ],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $student = Student::where('id', $validated['student_id'])
            ->where('school_id', $school->id)
            ->where('branch_id', $admin->branch_id)
            ->first();

        if (!$student) {
            return redirect()->back()->with('error', 'Student not found in your branch.');
        }

        StudentActionRequest::create([
            'school_id' => $school->id,
            'branch_id' => $admin->branch_id,
            'requested_by' => $admin->id,
            'student_id' => $student->id,
            'action' => 'remove',
            'reason' => $validated['reason'],
            'student_name' => $student->name,
            'student_email' => $student->email,
            'status' => 'pending',
        ]);

        SystemLog::logInfo(
            "Branch secretary {$admin->name} requested to remove student {$student->name} from branch",
            'user_management',
            [
                'branch_id' => $admin->branch_id,
                'student_id' => $student->id,
                'student_name' => $student->name,
                'action' => 'remove',
            ],
            $school->id,
            'student_action_request_created'
        );

        return redirect()->back()->with('success', 'Student removal request submitted. Awaiting central admin approval.');
    }

    /**
     * Central admin approves a student action request.
     */
    public function approve(Request $request, School $school, StudentActionRequest $actionRequest)
    {
        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();

        if (!$admin->isSchoolAdmin()) {
            return redirect()->back()->with('error', 'Only school administrators can approve requests.');
        }

        abort_if($actionRequest->school_id !== $school->id, 404);
        abort_if(!$actionRequest->isPending(), 422, 'This request has already been processed.');

        $validated = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            // Execute the action
            if ($actionRequest->isAddRequest()) {
                if ($actionRequest->student_id) {
                    // Move existing student to the requested branch
                    Student::where('id', $actionRequest->student_id)->update([
                        'branch_id' => $actionRequest->branch_id,
                    ]);
                } else {
                    // Create new student at the branch
                    $student = Student::create([
                        'school_id' => $school->id,
                        'branch_id' => $actionRequest->branch_id,
                        'name' => $actionRequest->student_name,
                        'email' => $actionRequest->student_email,
                        'contact' => $actionRequest->student_contact,
                        'password' => Hash::make('temporary-' . uniqid()), // Temp password
                        'status' => 'active',
                    ]);
                    $student->role = 'guest';
                    $student->save();
                    $actionRequest->update(['student_id' => $student->id]);
                }
            } elseif ($actionRequest->isRemoveRequest()) {
                // Remove student from branch (set branch_id to null, not delete)
                if ($actionRequest->student_id) {
                    Student::where('id', $actionRequest->student_id)->update([
                        'branch_id' => null,
                    ]);
                }
            }

            $actionRequest->approve($admin, $validated['review_notes'] ?? null);

            // Notify the requesting secretary
            $studentDisplayName = $actionRequest->student_name ?? $actionRequest->student?->name ?? 'Unknown';
            try {
                Notification::send(
                    $actionRequest->requestedBy,
                    'action_request_approved',
                    'Student Action Request Approved',
                    "Your request to {$actionRequest->action} student '{$studentDisplayName}' has been approved.",
                    'enrollment',
                    "/{$school->slug}/admin/student-action-requests"
                );
            } catch (\Exception $e) {
                Log::warning('Failed to send action request notification: ' . $e->getMessage());
            }

            SystemLog::logInfo(
                "Central admin {$admin->name} approved student {$actionRequest->action} request #{$actionRequest->id}",
                'user_management',
                [
                    'request_id' => $actionRequest->id,
                    'action' => $actionRequest->action,
                    'branch_id' => $actionRequest->branch_id,
                    'student_id' => $actionRequest->student_id,
                    'approved_by' => $admin->id,
                ],
                $school->id,
                'student_action_request_approved'
            );

            return redirect()->back()->with('success', 'Student action request approved and executed.');

        } catch (\Exception $e) {
            Log::error('Failed to approve student action request: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to process request. Please try again.');
        }
    }

    /**
     * Central admin denies a student action request.
     */
    public function deny(Request $request, School $school, StudentActionRequest $actionRequest)
    {
        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();

        if (!$admin->isSchoolAdmin()) {
            return redirect()->back()->with('error', 'Only school administrators can deny requests.');
        }

        abort_if($actionRequest->school_id !== $school->id, 404);
        abort_if(!$actionRequest->isPending(), 422, 'This request has already been processed.');

        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'max:1000'],
        ]);

        $actionRequest->deny($admin, $validated['review_notes']);

        // Notify the requesting secretary
        $studentDisplayName = $actionRequest->student_name ?? $actionRequest->student?->name ?? 'Unknown';
        $denyReason = $validated['review_notes'];
        try {
            Notification::send(
                $actionRequest->requestedBy,
                'action_request_denied',
                'Student Action Request Denied',
                "Your request to {$actionRequest->action} student '{$studentDisplayName}' has been denied. Reason: {$denyReason}",
                'enrollment',
                "/{$school->slug}/admin/student-action-requests"
            );
        } catch (\Exception $e) {
            Log::warning('Failed to send action request notification: ' . $e->getMessage());
        }

        SystemLog::logInfo(
            "Central admin {$admin->name} denied student {$actionRequest->action} request #{$actionRequest->id}",
            'user_management',
            [
                'request_id' => $actionRequest->id,
                'action' => $actionRequest->action,
                'branch_id' => $actionRequest->branch_id,
                'review_notes' => $validated['review_notes'],
            ],
            $school->id,
            'student_action_request_denied'
        );

        return redirect()->back()->with('success', 'Student action request denied.');
    }
}
