<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GCashSetting extends Model
{
    protected $table = 'gcash_settings';

    protected $fillable = [
        'school_id',
        'branch_id',
        'account_name',
        'account_number',
        'qr_path',
        'is_active',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the active GCash setting for a branch, with school-level fallback.
     */
    public static function getActiveSetting($schoolId, $branchId = null)
    {
        // 1. Try branch level
        if ($branchId) {
            $setting = self::where('school_id', $schoolId)
                ->where('branch_id', $branchId)
                ->where('is_active', true)
                ->first();
            
            if ($setting) return $setting;
        }

        // 2. Fallback to school level (branch_id IS NULL)
        return self::where('school_id', $schoolId)
            ->whereNull('branch_id')
            ->where('is_active', true)
            ->first();
    }
}
