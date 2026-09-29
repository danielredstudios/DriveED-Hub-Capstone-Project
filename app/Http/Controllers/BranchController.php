<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    /**
     * Display branch management page (or return JSON list for AJAX).
     */
    public function index(Request $request, School $school)
    {
        $query = Branch::where('school_id', $school->id)
            ->withCount(['students', 'instructors'])
            ->orderBy('sort_order')
            ->orderBy('name');

        $totalBranchesCount = (clone $query)->count();
        $activeBranchesCount = (clone $query)->where('is_active', true)->count();
        $inactiveBranchesCount = (clone $query)->where('is_active', false)->count();

        $branches = $query->paginate(10);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'branches' => $branches,
            ]);
        }

        $settings = $school->schoolSetting;

        return view('school.admin.branches', array_merge(compact('school', 'branches', 'settings', 'totalBranchesCount', 'activeBranchesCount', 'inactiveBranchesCount'), ['isAjax' => $request->ajax()]));
    }

    /**
     * Store a new branch.
     */
    public function store(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'contact_number' => 'nullable|string|max:13|regex:/^(09\d{9}|\+639\d{9}|9\d{9})$/',
            'email' => 'nullable|email|max:255',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        $validated['school_id'] = $school->id;

        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        // Normalize contact number
        if (!empty($validated['contact_number'])) {
            $contact = trim((string) $validated['contact_number']);
            if (preg_match('/^9\d{9}$/', $contact)) {
                $validated['contact_number'] = '+63' . $contact;
            } elseif (preg_match('/^09\d{9}$/', $contact)) {
                $validated['contact_number'] = '+63' . substr($contact, 1);
            }
        }

        try {
            $branch = Branch::create($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Branch created successfully!',
                    'branch' => $branch,
                ], 201);
            }

            return redirect()->back()->with('success', 'Branch created successfully!');
        }
        catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create branch', [
                'error' => $e->getMessage(),
                'school_id' => $school->id
            ]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create branch. The system administrator has been notified.',
                ], 500);
            }
            return back()->with('error', 'An error occurred while creating the branch. The system administrator has been notified.');
        }
    }

    /**
     * Update an existing branch.
     */
    public function update(Request $request, School $school, $id)
    {
        $branch = Branch::where('school_id', $school->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'contact_number' => 'nullable|string|max:13|regex:/^(09\d{9}|\+639\d{9}|9\d{9})$/',
            'email' => 'nullable|email|max:255',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        // Normalize contact number
        if (!empty($validated['contact_number'])) {
            $contact = trim((string) $validated['contact_number']);
            if (preg_match('/^9\d{9}$/', $contact)) {
                $validated['contact_number'] = '+63' . $contact;
            } elseif (preg_match('/^09\d{9}$/', $contact)) {
                $validated['contact_number'] = '+63' . substr($contact, 1);
            }
        }

        try {
            $branch->update($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Branch updated successfully!',
                    'branch' => $branch->fresh(),
                ]);
            }

            return redirect()->back()->with('success', 'Branch updated successfully!');
        }
        catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update branch', [
                'branch_id' => $branch->id,
                'error' => $e->getMessage()
            ]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update branch. The system administrator has been notified.',
                ], 500);
            }
            return back()->with('error', 'An error occurred while updating the branch. The system administrator has been notified.');
        }
    }

    /**
     * Toggle branch active status.
     */
    public function toggleActive(Request $request, School $school, $id)
    {
        $branch = Branch::where('school_id', $school->id)->findOrFail($id);

        try {
            $branch->update(['is_active' => !$branch->is_active]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $branch->is_active ? 'Branch activated.' : 'Branch deactivated.',
                    'branch' => $branch->fresh(),
                ]);
            }

            return redirect()->back()->with('success', 'Branch status updated.');
        }
        catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to toggle branch status', [
                'branch_id' => $branch->id,
                'error' => $e->getMessage()
            ]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to toggle status.'], 500);
            }
            return back()->with('error', 'An error occurred. The system administrator has been notified.');
        }
    }

    /**
     * Delete a branch (only if no users are assigned).
     */
    public function destroy(Request $request, School $school, $id)
    {
        $branch = Branch::where('school_id', $school->id)->findOrFail($id);

        // Prevent deletion if students or instructors are assigned
        $studentCount = $branch->students()->count();
        $instructorCount = $branch->instructors()->count();

        if ($studentCount > 0 || $instructorCount > 0) {
            $msg = 'Cannot delete branch: ';
            $parts = [];
            if ($studentCount > 0) {
                $parts[] = "{$studentCount} student(s)";
            }
            if ($instructorCount > 0) {
                $parts[] = "{$instructorCount} instructor(s)";
            }
            $msg .= implode(' and ', $parts) . ' still assigned.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        try {
            $branch->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Branch deleted successfully!',
                ]);
            }

            return redirect()->back()->with('success', 'Branch deleted successfully!');
        }
        catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to delete branch', [
                'branch_id' => $branch->id,
                'error' => $e->getMessage()
            ]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete branch.'], 500);
            }
            return back()->with('error', 'An error occurred while deleting the branch.');
        }
    }
}
