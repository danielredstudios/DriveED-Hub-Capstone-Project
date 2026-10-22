<button type="button" class="btn-action btn-view claim-notice-trigger"
    data-student-name="{{ $claimStudent->name }}"
    data-action="{{ route('schools.admin.students.claim-notice', ['school' => $school->slug, 'student' => $claimStudent->id]) }}">Send pickup notice</button>
