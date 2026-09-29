<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasSchoolScope;
    use HasFactory, Notifiable;

    // Role constants
    const ROLE_SYSTEM_ADMIN = 'system_admin';
    const ROLE_SCHOOL_ADMIN = 'school_admin';
    const ROLE_BRANCH_SECRETARY = 'branch_secretary';

    protected $fillable = [
        'school_id',
        'branch_id',
        'name',
        'email',
        'password',
        'is_active',
        'role',
        'must_reset_password',
        'contact',
        'profile_picture',
        'failed_login_attempts',
        'locked_until',
        'last_login_at',
        'last_logout_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'last_logout_at' => 'datetime',
        ];
    }

    // ──────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function studentActionRequests()
    {
        return $this->hasMany(StudentActionRequest::class , 'requested_by');
    }

    public function reviewedActionRequests()
    {
        return $this->hasMany(StudentActionRequest::class , 'reviewed_by');
    }


    // ──────────────────────────────────────
    // Role helpers
    // ──────────────────────────────────────

    public function isSystemAdmin(): bool
    {
        return $this->role === self::ROLE_SYSTEM_ADMIN;
    }

    public function isSchoolAdmin(): bool
    {
        return $this->role === self::ROLE_SCHOOL_ADMIN;
    }

    public function isBranchSecretary(): bool
    {
        return $this->role === self::ROLE_BRANCH_SECRETARY;
    }

    /**
     * Check if admin is a "central" (school-level) admin — not a branch secretary.
     */
    public function isCentralAdmin(): bool
    {
        return $this->isSchoolAdmin();
    }

    /**
     * Check if admin can manage other admins/secretaries (only school_admin).
     */
    public function canManageAdmins(): bool
    {
        return $this->isSchoolAdmin();
    }

    /**
     * Check if admin can view financial reports (only school_admin).
     */
    public function canViewFinancials(): bool
    {
        return $this->isSchoolAdmin();
    }

    /**
     * Check if admin can confirm payments (school_admin + branch_secretary).
     */
    public function canConfirmPayments(): bool
    {
        return $this->isSchoolAdmin() || $this->isBranchSecretary();
    }

    /**
     * Check if admin can approve enrollments (school_admin + branch_secretary for own branch).
     */
    public function canApproveEnrollments(): bool
    {
        return $this->isSchoolAdmin() || $this->isBranchSecretary();
    }

    /**
     * Check if admin can manage instructor schedules (school_admin + branch_secretary for own branch).
     */
    public function canManageSchedules(): bool
    {
        return $this->isSchoolAdmin() || $this->isBranchSecretary();
    }

    /**
     * Check if admin can manage courses (only school_admin).
     */
    public function canManageCourses(): bool
    {
        return $this->isSchoolAdmin();
    }

    // ──────────────────────────────────────
    // Branch scoping helpers
    // ──────────────────────────────────────

    /**
     * Get the branch IDs this admin can access.
     * School admins: all branches. Branch secretaries: only their assigned branch.
     */
    public function accessibleBranchIds(): array
    {
        if ($this->isSchoolAdmin()) {
            return Branch::where('school_id', $this->school_id)->pluck('id')->toArray();
        }

        return $this->branch_id ? [$this->branch_id] : [];
    }

    /**
     * Check if admin can access a specific branch.
     */
    public function canAccessBranch(?int $branchId): bool
    {
        if ($this->isSchoolAdmin()) {
            return true; // Central admin can access all branches
        }

        if ($this->isBranchSecretary()) {
            return $this->branch_id === $branchId;
        }

        return false;
    }

    /**
     * Apply branch scoping to a query builder.
     * For school_admin: no filter. For branch_secretary: filter to their branch_id.
     */
    public function scopeToBranch($query, string $branchColumn = 'branch_id')
    {
        if ($this->isBranchSecretary() && $this->branch_id) {
            // Only apply the filter if the column actually exists in the table
            $table = $query->getModel()->getTable();
            if (\Illuminate\Support\Facades\Schema::hasColumn($table, $branchColumn)) {
                return $query->where($table . '.' . $branchColumn, $this->branch_id);
            }
        }

        return $query; // School admin or missing column (safety)
    }

    // ──────────────────────────────────────
    // Query scopes
    // ──────────────────────────────────────

    public function scopeSchoolAdmins($query)
    {
        return $query->where('role', self::ROLE_SCHOOL_ADMIN);
    }

    public function scopeBranchSecretaries($query)
    {
        return $query->where('role', self::ROLE_BRANCH_SECRETARY);
    }
}