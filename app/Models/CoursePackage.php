<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoursePackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'vehicle_category_id',
        'name',
        'transmission_type',
        'vehicle_type',
        'price',
        'features',
        'description',
        'training_hours',
        'tdc_hours',
        'pdc_hours',
        'is_popular',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'training_hours' => 'decimal:1',
        'tdc_hours' => 'decimal:1',
        'pdc_hours' => 'decimal:1',
        'features' => 'array',
        'is_popular' => 'boolean',
    ];

    /**
     * Get the course that owns the package.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the vehicle category for this package.
     */
    public function vehicleCategory(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }
}
