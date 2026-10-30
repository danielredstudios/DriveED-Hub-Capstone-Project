<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSessionCompletionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only instructors can log sessions
        return \Illuminate\Support\Facades\Auth::guard('instructor')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH') || $this->route('sessionCompletion');

        return [
            'enrollment_id' => [$isUpdate ? 'nullable' : 'required', 'exists:enrollment_requests,id'],
            'session_type' => [$isUpdate ? 'nullable' : 'required', 'in:theoretical,practical'],
            'hours_completed' => ['required', 'numeric', 'min:0.5', 'max:8'],
            'session_date' => ['required', 'date', 'before_or_equal:today'],
            'session_time' => ['required', 'date_format:H:i'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'status' => ['nullable', 'in:scheduled,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'session_grade' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'attendance_status' => ['nullable', 'in:attended,late,absent'],
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
            'enrollment_id.required' => 'Please select an enrollment.',
            'enrollment_id.exists' => 'The selected enrollment does not exist.',
            'session_type.required' => 'Please select the session type.',
            'session_type.in' => 'Invalid session type selected.',
            'hours_completed.required' => 'Please enter the hours completed.',
            'hours_completed.numeric' => 'Hours completed must be a number.',
            'hours_completed.min' => 'Minimum session duration is 0.5 hours (30 minutes).',
            'hours_completed.max' => 'Maximum session duration is 8 hours.',
            'session_date.required' => 'Please enter the session date.',
            'session_date.before_or_equal' => 'Session date cannot be in the future.',
            'session_time.required' => 'Please enter the session time.',
            'session_time.date_format' => 'Session time must be in HH:MM format (e.g., 14:30).',
            'start_time.date_format' => 'Start time must be in HH:MM format (e.g., 14:30).',
            'end_time.date_format' => 'End time must be in HH:MM format (e.g., 16:30).',
            'end_time.after' => 'End time must be after start time.',
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
            $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH') || $this->route('sessionCompletion');
            if ($isUpdate) {
                return;
            }

            if (!$this->filled('enrollment_id')) {
                return;
            }

            $enrollment = \App\Models\EnrollmentRequest::find($this->enrollment_id);

            if ($enrollment) {
                // Verify session type matches course type
                $courseType = $enrollment->course->course_type;
                
                if (in_array($courseType, ['theoretical', 'practical'], true) && $this->filled('session_type') && $this->session_type !== $courseType) {
                    $validator->errors()->add('session_type', "Session type must match the course type ({$courseType}).");
                }

                // Check if enrollment is active (approved status)
                if ($enrollment->status !== 'approved') {
                    $validator->errors()->add('enrollment_id', 'Cannot log sessions for inactive enrollments.');
                }

                // Verify instructor is authorized for this enrollment
                $user = $this->user();
                if ($user instanceof \App\Models\Instructor) {
                    // Optional: Add logic to verify instructor is assigned to this student/course
                    // This would require checking instructor assignments
                }
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'enrollment_id' => 'enrollment',
            'session_type' => 'session type',
            'hours_completed' => 'hours completed',
            'session_date' => 'session date',
            'session_time' => 'session time',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Ensure hours_completed is properly formatted as decimal
        if ($this->has('hours_completed')) {
            $this->merge([
                'hours_completed' => (float) $this->hours_completed,
            ]);
        }

        // Normalize session_time if provided with seconds (e.g., 14:30:00 -> 14:30)
        if ($this->filled('session_time')) {
            $time = trim((string) $this->session_time);
            if (preg_match('/^(\d{1,2}:\d{2})(:\d{2})?$/', $time, $matches)) {
                $parts = explode(':', $matches[1]);
                $formattedTime = sprintf('%02d:%02d', (int) $parts[0], (int) $parts[1]);
                $this->merge(['session_time' => $formattedTime]);
            }
        }
    }
}
