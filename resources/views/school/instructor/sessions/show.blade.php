@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Session Details')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-5xl">
    <div class="mb-6">
        <a href="{{ school_route('instructor.sessions.index') }}" class="text-sm text-blue-600 hover:underline">← Back to Session Logs</a>
    </div>

    <div class="bg-white border rounded-lg shadow-sm p-6">
        <h1 class="text-2xl font-semibold mb-4">Instructor Session Details</h1>
        @if(isset($sessionCompletion))
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div><dt class="font-medium">Student</dt><dd>{{ $sessionCompletion->enrollment->student->name ?? 'N/A' }}</dd></div>
                <div><dt class="font-medium">Course</dt><dd>{{ $sessionCompletion->enrollment->course->title ?? 'N/A' }}</dd></div>
                <div><dt class="font-medium">Session Type</dt><dd>{{ ucfirst($sessionCompletion->session_type ?? 'N/A') }}</dd></div>
                <div><dt class="font-medium">Hours Completed</dt><dd>{{ $sessionCompletion->hours_completed ?? 0 }}</dd></div>
                <div><dt class="font-medium">Session Date</dt><dd>{{ optional($sessionCompletion->session_date)->format('M d, Y') ?? 'N/A' }}</dd></div>
                <div><dt class="font-medium">Status</dt><dd>{{ ucfirst($sessionCompletion->status ?? 'N/A') }}</dd></div>
            </dl>
            <div class="mt-4 text-sm text-gray-700">
                <span class="font-medium">Notes:</span> {{ $sessionCompletion->notes ?? 'No notes provided.' }}
            </div>
        @else
            <p class="text-sm text-gray-500">Session data is unavailable.</p>
        @endif
    </div>
</div>
@endsection
