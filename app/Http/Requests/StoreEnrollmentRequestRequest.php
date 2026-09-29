<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Support\EnrollmentValidator;

class StoreEnrollmentRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Guest enrollment is public
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $school = $this->route('school');
        $schoolId = $school instanceof \App\Models\School ? $school->id : $school;
        $course = $this->route('course');
        $courseId = $course instanceof \App\Models\Course ? $course->id : $course;

        return [
            'experience_level' => ['required', 'in:new_driver,experienced'],
            'package_id' => [
                'nullable',
                \Illuminate\Validation\Rule::exists('course_packages', 'id')
                ->where('course_id', $courseId)
            ],
            'branch_id' => [
                'nullable',
                'integer',
                \Illuminate\Validation\Rule::exists('branches', 'id')->where('school_id', $schoolId)
            ],
            'credential_file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120' // 5MB in kilobytes
            ],
            'student_license' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120'
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
            'requested_dl_code' => ['nullable', 'string', 'in:A,A1,B,B1,B2,C,D,BE,CE'],
        ];
    }

    /**
     * Get custom validation messages
     *
     * @return array<string, string>
     */
     public function messages(): array
    {
        return [
            'experience_level.required' => 'Please indicate your experience level.',
            'experience_level.in' => 'Invalid experience level selected.',
            'package_id.exists' => 'The selected package does not exist.',
            'credential_file.mimes' => 'Credentials must be a PDF, JPG, or PNG file.',
            'credential_file.max' => 'Credentials file must not exceed 5MB.',
            'student_license.mimes' => 'License must be a PDF, JPG, or PNG file.',
            'student_license.max' => 'License file must not exceed 5MB.',
            'notes.max' => 'Notes must not exceed 1000 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Get course from route parameter (not from form field)
            $course = $this->route('course');
            $courseId = $course instanceof \App\Models\Course ? $course->id : $course;

            // Validate enrollment request using our custom validator
            $validation = EnrollmentValidator::validateEnrollmentRequest([
                'course_id' => $courseId,
                'experience_level' => $this->experience_level,
                'credentials_file_path' => $this->hasFile('credential_file') ? 'present' : null,
            ]);

            if (!$validation['valid']) {
                $validator->errors()->add('course_id', $validation['message']);
            }

            // If file is present, validate it (support both field names)
            $fileToValidate = $this->hasFile('credential_file') ? $this->file('credential_file') : ($this->hasFile('student_license') ? $this->file('student_license') : null);
            if ($fileToValidate) {
                $fileValidation = EnrollmentValidator::validateCredentialFile($fileToValidate);
                $field = $this->hasFile('credential_file') ? 'credential_file' : 'student_license';
                if (!$fileValidation['valid']) {
                    $validator->errors()->add($field, $fileValidation['message']);
                }
            }

        // Credentials are now optional for all enrollment requests.
        // Even experienced drivers on practical courses can enroll first and provide proof later.
        /* 
         if ($this->experience_level === 'experienced' && !$this->hasFile('credential_file')) {
         $courseModel = $course instanceof \App\Models\Course ? $course : \App\Models\Course::find($courseId);
         if ($courseModel && $courseModel->isPractical()) {
         $validator->errors()->add('credential_file', 'Experienced drivers must upload proof of theoretical completion when applying for practical courses.');
         }
         }
         */
        });
    }
}
