<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentQuestion extends Model
{
    protected $fillable = [
        'module_id',
        'question_id',
        'sort_order',
        'points',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'points' => 'integer',
    ];

    public function module()
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
