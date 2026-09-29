<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleCategory extends Model
{
    use HasFactory, \App\Traits\HasSchoolScope;

    protected $fillable = [
        'school_id',
        'name',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'category_id');
    }
}
