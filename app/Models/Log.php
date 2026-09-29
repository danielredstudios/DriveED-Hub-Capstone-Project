<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasSchoolScope;
    use HasFactory;

    protected $fillable = [
        'school_id',
        'admin_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the school that owns the log
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the admin that created the log
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Scope for filtering by action type
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
