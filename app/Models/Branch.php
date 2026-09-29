<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Branch extends Model
{
    use HasSchoolScope;
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'slug',
        'address',
        'contact_number',
        'email',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Auto-generate slug from name when creating
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($branch) {
            if (empty($branch->slug)) {
                $branch->slug = Str::slug($branch->name);
            }
        });
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function instructors()
    {
        return $this->hasMany(Instructor::class);
    }

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function enrollmentRequests()
    {
        return $this->hasMany(EnrollmentRequest::class);
    }

    public function admins()
    {
        return $this->hasMany(Admin::class);
    }

    public function secretaries()
    {
        return $this->hasMany(Admin::class)->where('role', Admin::ROLE_BRANCH_SECRETARY);
    }

    public function studentActionRequests()
    {
        return $this->hasMany(StudentActionRequest::class);
    }
}
