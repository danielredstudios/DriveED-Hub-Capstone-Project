<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Branch;
use App\Models\School;
use App\Models\SystemLog;
use App\Models\Invitation;
use App\Mail\SystemInvitationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Rules\StrongPassword;
use Illuminate\Validation\Rule;

class AdminManagementController extends Controller
{
    /**
     * List all admins and branch secretaries for this school.
     * Only accessible by school_admin role.
     */
    public function index(Request $request, School $school)
    {
        $admin = Auth::guard('admin')->user();

        $admins = Admin::where('school_id', $school->id)
            ->whereIn('role', [Admin::ROLE_SCHOOL_ADMIN, Admin::ROLE_BRANCH_SECRETARY])
            ->with('branch')
            ->orderByRaw("CASE role WHEN 'school_admin' THEN 0 WHEN 'branch_secretary' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->get();

        $branches = Branch::where('school_id', $school->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $pendingInvitations = Invitation::where('school_id', $school->id)
            ->whereIn('role', [Admin::ROLE_SCHOOL_ADMIN, Admin::ROLE_BRANCH_SECRETARY])
            ->whereNull('used_at')
            ->with('branch')
            ->orderBy('created_at', 'desc')
            ->get();

        $branchSecretaryLimit = $this->getBranchSecretaryLimit($school);
        $branchCapacityMap = [];
        foreach ($branches as $branch) {
            $branchCapacityMap[$branch->id] = $this->buildBranchSecretaryCapacitySummary($school, (int) $branch->id);
        }

        return view('school.admin.admin-management.index', array_merge(compact(
            'school', 'admin', 'admins', 'branches', 'branchCapacityMap', 'branchSecretaryLimit', 'pendingInvitations'
        ), ['isAjax' => $request->ajax()]));
    }

    /**
     * Store a new admin or branch secretary.
     */
    public function store(Request $request, School $school)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin->canManageAdmins()) {
            return redirect()->back()->with('error', 'You do not have permission to manage administrators.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->where('school_id', $school->id),
                Rule::unique('students', 'email')->where('school_id', $school->id),
                Rule::unique('instructors', 'email')->where('school_id', $school->id),
            ],
            'contact' => ['required', 'string', 'max:13', 'regex:/^(09\d{9}|\+639\d{9}|9\d{9})$/'],
            'role' => ['required', 'in:school_admin,branch_secretary'],
            'branch_id' => ['required_if:role,branch_secretary', 'nullable', 'exists:branches,id'],
        ]);

        $selectedBranch = null;
        // If creating secretary, verify branch belongs to this school
        if ($validated['role'] === Admin::ROLE_BRANCH_SECRETARY && $validated['branch_id']) {
            $selectedBranch = Branch::where('id', $validated['branch_id'])
                ->where('school_id', $school->id)
                ->first();

            if (!$selectedBranch) {
                return redirect()->back()->with('error', 'Selected branch does not belong to this school.');
            }

            $capacity = $this->buildBranchSecretaryCapacitySummary($school, (int) $validated['branch_id']);
            if ($capacity['at_capacity']) {
                return redirect()->back()->withInput()->with(
                    'error',
                    "Cannot add another branch manager for {$selectedBranch->name}. Capacity is full ({$capacity['used']}/{$capacity['limit']})."
                );
            }
        }

        $invitationExpiryDays = max(1, (int) ($school->schoolSetting?->invitation_expiry_days ?? 7));

        DB::beginTransaction();
        try {
            $invitation = Invitation::create([
                'school_id' => $school->id,
                'branch_id' => $validated['role'] === Admin::ROLE_BRANCH_SECRETARY ? $validated['branch_id'] : null,
                'email' => trim($validated['email']),
                'role' => $validated['role'],
                'token' => \Illuminate\Support\Str::random(40),
                'payload' => [
                    'name' => trim($validated['name']),
                    'contact' => (function ($contact) {
                        $contact = trim((string) $contact);
                        if (preg_match('/^9\d{9}$/', $contact)) {
                            return '+63' . $contact;
                        } elseif (preg_match('/^09\d{9}$/', $contact)) {
                            return '+63' . substr($contact, 1);
                        }
                        return $contact;
                    })($validated['contact'] ?? ''),
                ],
                'expires_at' => now()->addDays($invitationExpiryDays),
            ]);

            // Send Invitation Mail
            Mail::to($invitation->email)->send(new SystemInvitationMail($invitation, $school));

            $roleLabel = $validated['role'] === Admin::ROLE_BRANCH_SECRETARY ? 'Branch Secretary' : 'School Admin';

            SystemLog::logInfo(
                "Account setup link sent to {$roleLabel}: {$invitation->email}",
                'user_management',
                [
                    'role' => $invitation->role,
                    'branch_id' => $invitation->branch_id,
                    'added_by' => $admin->id,
                ],
                $school->id,
                'admin_added'
            );

            DB::commit();

            return redirect()->back()->with('success', "Account setup link successfully sent to {$invitation->email}.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to send admin invitation in AdminManagementController: " . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to send invitation at this time.');
        }
    }

    /**
     * Update an existing admin/secretary.
     */
    public function update(Request $request, School $school, Admin $targetAdmin)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin->canManageAdmins()) {
            return redirect()->back()->with('error', 'You do not have permission to manage administrators.');
        }

        // Cannot edit admins from other schools
        abort_if($targetAdmin->school_id !== $school->id, 404);

        // Cannot edit yourself through this controller (use profile page)
        if ($targetAdmin->id === $admin->id) {
            return redirect()->back()->with('error', 'Use the profile page to edit your own account.');
        }

        // Cannot edit system admins
        if ($targetAdmin->isSystemAdmin()) {
            return redirect()->back()->with('error', 'System administrators cannot be edited here.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($targetAdmin->id),
            ],
            'password' => ['nullable', 'string', 'confirmed', new StrongPassword()],
            'contact' => ['required', 'string', 'max:13', 'regex:/^(09\d{9}|\+639\d{9}|9\d{9})$/'],
            'role' => ['required', 'in:school_admin,branch_secretary'],
            'branch_id' => ['required_if:role,branch_secretary', 'nullable', 'exists:branches,id'],
        ]);

        $selectedBranch = null;
        // If updating to secretary, verify branch belongs to this school
        if ($validated['role'] === Admin::ROLE_BRANCH_SECRETARY && $validated['branch_id']) {
            $selectedBranch = Branch::where('id', $validated['branch_id'])
                ->where('school_id', $school->id)
                ->first();

            if (!$selectedBranch) {
                return redirect()->back()->with('error', 'Selected branch does not belong to this school.');
            }

            // Enforce capacity when the update would keep/put an active admin into a branch secretary slot.
            if ($targetAdmin->is_active) {
                $capacity = $this->buildBranchSecretaryCapacitySummary(
                    $school,
                    (int) $validated['branch_id'],
                    $targetAdmin->id
                );

                if ($capacity['at_capacity']) {
                    return redirect()->back()->withInput()->with(
                        'error',
                        "Cannot assign another active branch manager to {$selectedBranch->name}. Capacity is full ({$capacity['used']}/{$capacity['limit']})."
                    );
                }
            }
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'contact' => (function ($contact) {
                if (empty($contact)) return null;
                $contact = trim((string) $contact);
                if (preg_match('/^9\d{9}$/', $contact)) {
                    return '+63' . $contact;
                } elseif (preg_match('/^09\d{9}$/', $contact)) {
                    return '+63' . substr($contact, 1);
                }
                return $contact;
            })($validated['contact'] ?? ''),
            'role' => $validated['role'],
            'branch_id' => $validated['role'] === Admin::ROLE_BRANCH_SECRETARY ? $validated['branch_id'] : null,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = $validated['password']; // Hashed by model
        }

        $targetAdmin->update($updateData);

        SystemLog::logInfo(
            "Admin '{$targetAdmin->name}' updated by {$admin->name}",
            'user_management',
        [
            'admin_id' => $targetAdmin->id,
            'new_role' => $targetAdmin->role,
            'branch_id' => $targetAdmin->branch_id,
            'updated_by' => $admin->id,
        ],
            $school->id,
            'admin_updated'
        );

        return redirect()->back()->with('success', "Administrator '{$targetAdmin->name}' has been updated.");
    }

    /**
     * Toggle an admin's active status (activate/deactivate).
     */
    public function toggleStatus(School $school, Admin $targetAdmin)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin->canManageAdmins()) {
            return redirect()->back()->with('error', 'You do not have permission to manage administrators.');
        }

        abort_if($targetAdmin->school_id !== $school->id, 404);

        // Cannot deactivate yourself
        if ($targetAdmin->id === $admin->id) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account.');
        }

        // Cannot modify system admins
        if ($targetAdmin->isSystemAdmin()) {
            return redirect()->back()->with('error', 'System administrators cannot be modified here.');
        }

        $isActivating = !$targetAdmin->is_active;
        if ($isActivating && $targetAdmin->role === Admin::ROLE_BRANCH_SECRETARY) {
            if (!$targetAdmin->branch_id) {
                return redirect()->back()->with('error', 'Branch managers must be assigned to a branch before activation.');
            }

            $branch = Branch::where('id', $targetAdmin->branch_id)
                ->where('school_id', $school->id)
                ->first();

            if (!$branch) {
                return redirect()->back()->with('error', 'Assigned branch is invalid for this school.');
            }

            $capacity = $this->buildBranchSecretaryCapacitySummary(
                $school,
                (int) $targetAdmin->branch_id,
                $targetAdmin->id
            );

            if ($capacity['at_capacity']) {
                return redirect()->back()->with(
                    'error',
                    "Cannot activate this branch manager. {$branch->name} is already at capacity ({$capacity['used']}/{$capacity['limit']})."
                );
            }
        }

        $targetAdmin->update([
            'is_active' => !$targetAdmin->is_active,
        ]);

        $statusLabel = $targetAdmin->is_active ? 'activated' : 'deactivated';

        SystemLog::logInfo(
            "Admin '{$targetAdmin->name}' {$statusLabel} by {$admin->name}",
            'user_management',
        [
            'admin_id' => $targetAdmin->id,
            'new_status' => $targetAdmin->is_active,
            'toggled_by' => $admin->id,
        ],
            $school->id,
            'admin_status_toggled'
        );

        return redirect()->back()->with('success', "Administrator '{$targetAdmin->name}' has been {$statusLabel}.");
    }

    /**
     * Delete an admin/secretary.
     */
    public function destroy(School $school, Admin $targetAdmin)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin->canManageAdmins()) {
            return redirect()->back()->with('error', 'You do not have permission to manage administrators.');
        }

        abort_if($targetAdmin->school_id !== $school->id, 404);

        // Cannot delete yourself
        if ($targetAdmin->id === $admin->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        // Cannot delete system admins
        if ($targetAdmin->isSystemAdmin()) {
            return redirect()->back()->with('error', 'System administrators cannot be deleted here.');
        }

        $name = $targetAdmin->name;
        $role = $targetAdmin->role;
        $targetAdmin->delete();

        SystemLog::logInfo(
            "Admin '{$name}' (role: {$role}) deleted by {$admin->name}",
            'user_management',
        [
            'deleted_admin_name' => $name,
            'deleted_role' => $role,
            'deleted_by' => $admin->id,
        ],
            $school->id,
            'admin_deleted'
        );

        return redirect()->back()->with('success', "Administrator '{$name}' has been removed.");
    }

    private function getBranchSecretaryLimit(School $school): int
    {
        return max(1, (int) ($school->schoolSetting?->branch_secretary_limit_per_branch ?? 1));
    }

    private function buildBranchSecretaryCapacitySummary(
        School $school,
        int $branchId,
        ?int $excludeAdminId = null,
        ?int $excludeInvitationId = null
    ): array {
        $limit = $this->getBranchSecretaryLimit($school);

        $activeQuery = Admin::query()
            ->where('school_id', $school->id)
            ->where('branch_id', $branchId)
            ->where('role', Admin::ROLE_BRANCH_SECRETARY)
            ->where('is_active', true);

        if ($excludeAdminId !== null) {
            $activeQuery->where('id', '!=', $excludeAdminId);
        }

        $pendingInvitationQuery = Invitation::query()
            ->where('school_id', $school->id)
            ->where('branch_id', $branchId)
            ->where('role', Admin::ROLE_BRANCH_SECRETARY)
            ->whereNull('used_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });

        if ($excludeInvitationId !== null) {
            $pendingInvitationQuery->where('id', '!=', $excludeInvitationId);
        }

        $activeCount = $activeQuery->count();
        $pendingCount = $pendingInvitationQuery->count();
        $used = $activeCount + $pendingCount;

        return [
            'limit' => $limit,
            'active' => $activeCount,
            'pending' => $pendingCount,
            'used' => $used,
            'remaining' => max(0, $limit - $used),
            'at_capacity' => $used >= $limit,
        ];
    }
}
