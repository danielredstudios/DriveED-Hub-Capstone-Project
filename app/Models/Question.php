<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use \App\Traits\HasSchoolScope;

    protected $fillable = [
        'school_id',
        'course_id',
        'lesson_id',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'default_points',
    ];

    protected $casts = [
        'options' => 'array',
        'default_points' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson()
    {
        return $this->belongsTo(ModuleLesson::class, 'lesson_id');
    }

    public function assessments()
    {
        return $this->belongsToMany(CourseModule::class, 'assessment_questions', 'question_id', 'module_id')
                    ->withPivot('sort_order', 'points')
                    ->withTimestamps();
    }
}
