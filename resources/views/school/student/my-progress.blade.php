@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Progress')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-6xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">My Progress</h1>
            <p class="text-sm text-gray-500">Track your active enrollment and completed training history.</p>
        </div>
        <a href="{{ school_route('student.my-course') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-sm font-medium">
            Back to My Course
        </a>
    </div>

    @if($activeEnrollment && $progressData)
        <div class="bg-white rounded-lg shadow-sm border p-5 mb-6">
            <h2 class="text-lg font-semibold mb-3">Active Enrollment</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div><span class="font-medium">Course:</span> {{ $progressData['course']->title ?? 'N/A' }}</div>
                <div><span class="font-medium">Hours:</span> {{ $progressData['hours_completed'] ?? 0 }} / {{ $progressData['hours_required'] ?? 0 }}</div>
                <div><span class="font-medium">Progress:</span> {{ $progressData['progress_percentage'] ?? 0 }}%</div>
                <div><span class="font-medium">Theoretical:</span> {{ ($progressData['theoretical_passed'] ?? false) ? 'Passed' : 'Pending' }}</div>
            </div>
            <div class="mt-3 w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full progress-fill" data-progress="{{ $progressData['progress_percentage'] ?? 0 }}"></div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border p-5 mb-6 text-sm text-gray-600">
            No active enrollment progress available yet.
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-sm border p-5">
            <h2 class="text-lg font-semibold mb-3">Recent Phase Progressions</h2>
            <ul class="divide-y">
                @forelse($phaseProgressions as $phase)
                    <li class="py-3 text-sm">
                        <div class="font-medium">{{ ucfirst($phase->to_phase ?? 'Phase Update') }}</div>
                        <div class="text-gray-500">Status: {{ ucfirst($phase->status ?? 'pending') }}</div>
                    </li>
                @empty
                    <li class="py-3 text-sm text-gray-500">No phase progressions yet.</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-5">
            <h2 class="text-lg font-semibold mb-3">Enrollment History</h2>
            <ul class="divide-y">
                @forelse($enrollmentHistory as $enrollment)
                    <li class="py-3 text-sm">
                        <div class="font-medium">{{ $enrollment->course->title ?? 'Course' }}</div>
                        <div class="text-gray-500">Status: {{ ucfirst($enrollment->status) }}</div>
                    </li>
                @empty
                    <li class="py-3 text-sm text-gray-500">No enrollment history found.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.progress-fill').forEach(function (bar) {
        const value = Number(bar.dataset.progress || 0);
        const clamped = Math.max(0, Math.min(100, value));
        bar.style.width = clamped + '%';
    });
});
</script>
@endsection
