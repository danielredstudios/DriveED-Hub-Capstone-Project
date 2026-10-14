<?php

/**
 * TEST MODEL - Enhanced Course Model
 * 
 * This is the updated Course model with new fields.
 * Copy this to app/Models/Course.php after testing migration.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'title',
        'description',
        'banner_image',
        'features',
        'price',
        'duration_hours',
        'theoretical_hours_required',  // NEW
        'practical_hours_required',    // NEW
        'max_students',
        'type',
        'license_type',                // NEW
        'vehicle_type',
        'status',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_hours' => 'decimal:1',
        'theoretical_hours_required' => 'decimal:1',  // NEW
        'practical_hours_required' => 'decimal:1',    // NEW
        'features' => 'array',
        'is_featured' => 'boolean',
    ];

    /**
     * Get total training hours (theoretical + practical)
     */
    public function getTotalHoursAttribute(): float
    {
        return $this->theoretical_hours_required + $this->practical_hours_required;
    }

    /**
     * Get formatted license type for display
     */
    public function getLicenseTypeDisplayAttribute(): string
    {
        return match($this->license_type) {
            'non-professional' => 'Non-Professional',
            'professional' => 'Professional',
            default => ucfirst($this->license_type)
        };
    }

    /**
     * Get formatted vehicle type for display
     */
    public function getVehicleTypeDisplayAttribute(): string
    {
        return match($this->vehicle_type) {
            'manual' => 'Manual Transmission',
            'automatic' => 'Automatic Transmission',
            'motorcycle' => 'Motorcycle',
            'suv' => 'SUV/Light Truck',
            default => ucfirst($this->vehicle_type)
        };
    }

    /**
     * Scope: Filter by license type
     */
    public function scopeLicenseType($query, string $type)
    {
        return $query->where('license_type', $type);
    }

    /**
     * Scope: Filter by vehicle type
     */
    public function scopeVehicleType($query, string $type)
    {
        return $query->where('vehicle_type', $type);
    }

    /**
     * Scope: Professional courses only
     */
    public function scopeProfessional($query)
    {
        return $query->where('license_type', 'professional');
    }

    /**
     * Scope: Non-professional courses only
     */
    public function scopeNonProfessional($query)
    {
        return $query->where('license_type', 'non-professional');
    }

    // ==========================================
    // Existing relationships (keep as is)
    // ==========================================

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function timeSlots(): HasMany
    {
        return $this->hasMany(TimeSlot::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(CoursePackage::class);
    }

    public function enrollmentRequests(): HasMany
    {
        return $this->hasMany(EnrollmentRequest::class);
    }
}
