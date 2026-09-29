<?php

namespace App\Http\Controllers;

use App\Models\PhaseProgression;
use App\Models\School;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PhaseProgressionController extends Controller
{
    /**
     * Display all phase progression requests (Admin view)
     */
    public function index(School $school, Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        $baseQuery = PhaseProgression::with(['enrollment.student', 'enrollment.course', 'reviewedBy'])
            ->forSchool($school->id)
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('enrollment.student', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('enrollment.course', function ($cq) use ($search) {
                        $cq->where('title', 'like', "%{$search}%");
                    })
                    ->orWhere('from_phase', 'like', "%{$search}%")
                    ->orWhere('to_phase', 'like', "%{$search}%");
                });
            })
            ->latest('requested_at');

        // Branch secretary scope: filter by their branch
        if ($admin->isBranchSecretary() && $admin->branch_id) {
            $baseQuery->whereHas('enrollment', function ($q) use ($admin) {
                $q->where('branch_id', $admin->branch_id);
            });
        }

        $progressions = (clone $baseQuery)
            ->when($request->from_phase, function ($query, $phase) {
                $query->where('from_phase', $phase);
            })
            ->paginate(20)
            ->withQueryString();

        // Pending count should also be scoped for branch secretaries
        $pendingQuery = PhaseProgression::forSchool($school->id)->pending();
        if ($admin->isBranchSecretary() && $admin->branch_id) {
            $pendingQuery->whereHas('enrollment', function ($q) use ($admin) {
                $q->where('branch_id', $admin->branch_id);
            });
        }
        $pendingCount = $pendingQuery->count();

        $phaseStats = [
            'total_records' => (clone $baseQuery)->count(),
            'theoretical_records' => (clone $baseQuery)->where('from_phase', 'theoretical')->count(),
            'practical_records' => (clone $baseQuery)->where('from_phase', 'practical')->count(),
            'approved_records' => (clone $baseQuery)->where('status', 'approved')->count(),
        ];

        $isAjax = request()->ajax() || request()->header('X-Requested-With') === 'XMLHttpRequest';

        return view($school->resolveView('admin.phase_progressions.index'), compact('school', 'progressions', 'pendingCount', 'phaseStats', 'isAjax'));
    }

    /**
     * Approve a phase progression request
     */
    public function approve(School $school, PhaseProgression $phaseProgression, Request $request)
    {
        // Security: verify belongs to this school
        abort_if($phaseProgression->school_id !== $school->id, 404);

        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        // Branch secretary access check
        if ($admin->isBranchSecretary() && !$admin->canAccessBranch($phaseProgression->enrollment?->branch_id)) {
            abort(403, 'You do not have access to this progression request.');
        }

        // Cannot approve if not pending
        if (!$phaseProgression->isPending()) {
            return redirect()
                ->back()
                ->with('error', 'This progression request has already been reviewed.');
        }

        DB::beginTransaction();
        try {
            // Approve the progression request
            $phaseProgression->approve($admin->id, $request->input('admin_notes'));

            // Update the enrollment if needed (e.g., mark theoretical → practical transition)
            $enrollment = $phaseProgression->enrollment;
            if ($enrollment && $phaseProgression->to_phase === 'practical') {
                // Student is moving to practical phase — use model method to propagate to student
                $enrollment->markTheoreticalPassed($admin->id);
            }

            // If completing final phase, mark enrollment as completed
            if ($enrollment && $phaseProgression->to_phase === 'completed') {
                $enrollment->markAsCompleted();
            }

            // Send notification to student
            if ($enrollment && $enrollment->student) {
                try {
                    Notification::send(
                        $enrollment->student,
                        'phase_progression_approved',
                        'Phase Progression Approved',
                        "Your progression from {$phaseProgression->from_phase} to {$phaseProgression->to_phase} has been approved.",
                        'success',
                        "/{$school->slug}/student"
                    );
                }
                catch (\Exception $e) {
                    Log::warning('Failed to send phase progression notification: ' . $e->getMessage());
                }
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', "Phase progression approved: {$phaseProgression->getTransitionLabel()}");

        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('Phase progression approval failed: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Failed to approve phase progression. Please try again.');
        }
    }

    /**
     * Reject a phase progression request
     */
    public function reject(School $school, PhaseProgression $phaseProgression, Request $request)
    {
        // Security: verify belongs to this school
        abort_if($phaseProgression->school_id !== $school->id, 404);

        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        // Branch secretary access check
        if ($admin->isBranchSecretary() && !$admin->canAccessBranch($phaseProgression->enrollment?->branch_id)) {
            abort(403, 'You do not have access to this progression request.');
        }

        // Cannot reject if not pending
        if (!$phaseProgression->isPending()) {
            return redirect()
                ->back()
                ->with('error', 'This progression request has already been reviewed.');
        }

        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $phaseProgression->reject($admin->id, $request->input('admin_notes'));

            // Send notification to student
            $enrollment = $phaseProgression->enrollment;
            if ($enrollment && $enrollment->student) {
                try {
                    Notification::send(
                        $enrollment->student,
                        'phase_progression_rejected',
                        'Phase Progression Not Approved',
                        "Your progression from {$phaseProgression->from_phase} to {$phaseProgression->to_phase} was not approved. Reason: {$request->input('admin_notes')}",
                        'warning',
                        "/{$school->slug}/student"
                    );
                }
                catch (\Exception $e) {
                    Log::warning('Failed to send phase progression rejection notification: ' . $e->getMessage());
                }
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Phase progression request rejected.');

        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('Phase progression rejection failed: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Failed to reject phase progression. Please try again.');
        }
    }
}
