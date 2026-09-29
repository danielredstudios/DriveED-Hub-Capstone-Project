<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleImage extends Model
{
    use HasFactory, \App\Traits\HasSchoolScope;

    protected $fillable = [
        'school_id',
        'vehicle_id',
        'image_path',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
