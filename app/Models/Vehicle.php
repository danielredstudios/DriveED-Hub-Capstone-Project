<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory, \App\Traits\HasSchoolScope;

    protected $fillable = [
        'school_id',
        'branch_id',
        'category_id',
        'model',
        'license_plate',
        'transmission',
        'status',
        'notes',
        'image_path',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function category()
    {
        return $this->belongsTo(VehicleCategory::class, 'category_id');
    }

    public function images()
    {
        return $this->hasMany(VehicleImage::class);
    }
}
