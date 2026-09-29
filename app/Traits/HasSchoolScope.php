<?php

namespace App\Traits;

use App\Models\School;
use Illuminate\Database\Eloquent\Builder;

trait HasSchoolScope
{
    /**
     * Scope a query to only include records from a specific school.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|\App\Models\School $school
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSchool(Builder $query, $school): Builder
    {
        $schoolId = $school instanceof School ? $school->id : $school;
        return $query->where($this->getTable() . '.school_id', $schoolId);
    }
}
