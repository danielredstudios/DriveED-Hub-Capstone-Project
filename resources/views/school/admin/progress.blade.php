@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Session Logs')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $schoolName = $school->name ?? 'Driving School';
    $primaryColor = $settings->primary_color ?? '#667eea';
@endphp

@include('school.admin.partials.admin-styles')

<style>
.progress-container {
    padding: 20px;
    margin: 20px auto;
    max-width: 1600px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 3px solid {{ $primaryColor }};
}

.page-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.page-subtitle {
    color: #6b7280;
    font-size: 0.9rem;
    margin-top: 5px;
}

/* Table Styles */
.progress-table {
    width: 100%;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.progress-table table {
    width: 100%;
    border-collapse: collapse;
}

.progress-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.progress-table th {
    padding: 15px 20px;
    text-align: left;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.progress-row {
    border-bottom: 1px solid #e5e7eb;
    cursor: pointer;
    transition: background 0.2s;
}

.progress-row:hover {
    background: #f9fafb;
}

.progress-row td {
    padding: 18px 20px;
    font-size: 0.95rem;
    color: #374151;
}

.student-name {
    font-weight: 600;
    color: #111827;
}

.session-info {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 12px;
    background: #dbeafe;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e40af;
}

.instructor-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: #f3e8ff;
    border-radius: 16px;
    font-size: 0.85rem;
    color: #6b21a8;
}

.schedule-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: #fef3c7;
    border-radius: 16px;
    font-size: 0.85rem;
    color: #92400e;
}

.expand-icon {
    display: inline-block;
    transition: transform 0.3s;
    color: #667eea;
    font-weight: bold;
    font-size: 1.2rem;
}

.progress-row.expanded .expand-icon {
    transform: rotate(90deg);
}

/* Detailed View (Expandable) */
.detail-row {
    display: none;
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
}

.detail-row.show {
    display: table-row;
}

.detail-content {
    padding: 30px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 25px;
}

.detail-section {
    background: white;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
}

.detail-section-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #667eea;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #667eea;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px dashed #e5e7eb;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    font-size: 0.85rem;
    color: #6b7280;
    font-weight: 600;
}

.detail-value {
    font-size: 0.9rem;
    color: #111827;
    font-weight: 500;
}

.progress-bar-section {
    background: white;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    grid-column: 1 / -1;
}

.progress-bar-wrapper {
    display: flex;
    align-items: center;
    gap: 15px;
}

.progress-bar-container {
    flex: 1;
    height: 35px;
    background: #e5e7eb;
    border-radius: 20px;
    overflow: hidden;
    position: relative;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    transition: width 0.5s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.9rem;
}

.progress-percentage {
    font-size: 1.5rem;
    font-weight: 700;
    color: #667eea;
    min-width: 70px;
    text-align: right;
}

.badge {
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-completed { background: #d1fae5; color: #065f46; }
.badge-in-progress { background: #dbeafe; color: #1e40af; }
.badge-not-started { background: #f3f4f6; color: #4b5563; }
.badge-confirmed { background: #d1fae5; color: #065f46; }
.badge-pending { background: #fef3c7; color: #92400e; }
.badge-cancelled { background: #fee2e2; color: #991b1b; }

.notes-section {
    background: white;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    border-left: 4px solid #f59e0b;
    grid-column: 1 / -1;
}

.notes-section-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #92400e;
    margin-bottom: 10px;
}

.notes-text {
    color: #78350f;
    line-height: 1.6;
}

.th-expand {
    width: 40px;
}

.text-muted-italic {
    color: #9ca3af;
    font-style: italic;
}

.detail-empty-assignment {
    text-align: center;
    padding: 20px;
    color: #9ca3af;
}

.progress-fill-dynamic {
    width: 0;
}

.progress-meta-row {
    margin-top: 15px;
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: #6b7280;
}

.detail-section-full {
    grid-column: 1 / -1;
    margin-top: 10px;
}

.table-scroll-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.recent-table {
    width: 100%;
    margin-top: 10px;
}

.recent-thead {
    background: #f3f4f6;
}

.recent-th {
    padding: 10px;
    text-align: left;
    font-size: 0.85rem;
}

.recent-row {
    border-bottom: 1px solid #e5e7eb;
}

.recent-td {
    padding: 10px;
    font-size: 0.85rem;
}

.empty-state-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

@media (max-width: 768px) {
    .progress-table { overflow-x: auto; }
    .detail-grid { grid-template-columns: 1fr; }
}
</style>

<div class="progress-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="bi bi-journal-text"></i>
                Session Logs
            </h1>
            <p class="page-subtitle">Track comprehensive student training hours, session grades, and curriculum completion for {{ $schoolName }}</p>
        </div>
    </div>

    {{-- Session Logs Stats --}}
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 25px;">
        <div class="stat-card" style="background: white; padding: 25px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid {{ $primaryColor }};">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div style="font-size: 0.9rem; color: #64748b; font-weight: 500;">Students Tracked</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #1e293b;">{{ $progresses->total() }}</div>
            </div>
        </div>
        <div class="stat-card" style="background: white; padding: 25px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #10b981;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="bi bi-book-half"></i>
            </div>
            <div>
                <div style="font-size: 0.9rem; color: #64748b; font-weight: 500;">Active Courses</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #1e293b;">{{ $progresses->unique('course_id')->count() }}</div>
            </div>
        </div>
    </div>

    @if($progresses->count() > 0)
    <div class="progress-table">
        <table>
            <thead>
                <tr>
                    <th class="th-expand"></th>
                    <th>Student Name</th>
                    <th>Current Session</th>
                    <th>Current Instructor</th>
                    <th>Next Schedule</th>
                </tr>
            </thead>
            <tbody>
                @foreach($progresses as $index => $progress)
                    
                    <tr class="progress-row" onclick="toggleDetails({{ $index }})">
                        <td><span class="expand-icon" id="icon-{{ $index }}">▶</span></td>
                        <td><span class="student-name">{{ $progress->student->name }}</span></td>
                        <td>
                            <span class="session-info">
                                {{ $progress->completedSessions }} / {{ $progress->totalSessions }} sessions
                            </span>
                        </td>
                        <td>
                            @if($progress->currentBooking && $progress->currentBooking->instructor)
                                <span class="instructor-badge">
                                    {{ $progress->currentBooking->instructor->name }}
                                </span>
                            @else
                                <span class="text-muted-italic">Not assigned</span>
                            @endif
                        </td>
                        <td>
                            @if($progress->nextBooking)
                                <span class="schedule-badge">
                                    {{ $progress->nextBooking->scheduled_at->format('M d, Y h:i A') }}
                                </span>
                            @else
                                <span class="text-muted-italic">No upcoming schedule</span>
                            @endif
                        </td>
                    </tr>
                    
                    <!-- Detailed View (Hidden by default) -->
                    <tr class="detail-row" id="detail-{{ $index }}">
                        <td colspan="5">
                            <div class="detail-content">
                                <div class="detail-grid">
                                    <!-- Student Information -->
                                    <div class="detail-section">
                                        <h4 class="detail-section-title">Student Information</h4>
                                        <div class="detail-item">
                                            <span class="detail-label">Full Name</span>
                                            <span class="detail-value">{{ $progress->student->name }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Email</span>
                                            <span class="detail-value">{{ $progress->student->email }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Contact</span>
                                            <span class="detail-value">{{ $progress->student->contact ?? 'N/A' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Status</span>
                                            <span class="detail-value">
                                                <span class="badge badge-{{ strtolower($progress->student->status) }}">
                                                    {{ ucfirst($progress->student->status) }}
                                                </span>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Course Information -->
                                    <div class="detail-section">
                                        <h4 class="detail-section-title">Course Information</h4>
                                        <div class="detail-item">
                                            <span class="detail-label">Course Title</span>
                                            <span class="detail-value">{{ $progress->course->title }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Course Type</span>
                                            <span class="detail-value">{{ ucfirst($progress->course->type) }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Duration</span>
                                            <span class="detail-value">{{ $progress->course->duration_hours }} hours</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Vehicle Type</span>
                                            <span class="detail-value">{{ $progress->course->vehicle_type ?? 'N/A' }}</span>
                                        </div>
                                    </div>

                                    <!-- Session Progress -->
                                    <div class="detail-section">
                                        <h4 class="detail-section-title">Session Progress</h4>
                                        <div class="detail-item">
                                            <span class="detail-label">Completed Sessions</span>
                                            <span class="detail-value">{{ $progress->completedSessions }} / {{ $progress->totalSessions }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Remaining Sessions</span>
                                            <span class="detail-value">{{ max(0, $progress->totalSessions - $progress->completedSessions) }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Total Schedules</span>
                                            <span class="detail-value">{{ $progress->bookingsList->count() }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Last Session</span>
                                            <span class="detail-value">
                                                {{ $progress->currentBooking ? $progress->currentBooking->scheduled_at->format('M d, Y') : 'N/A' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Instructor Details -->
                                    <div class="detail-section">
                                        <h4 class="detail-section-title">Instructor Details</h4>
                                        @if($progress->currentBooking && $progress->currentBooking->instructor)
                                            <div class="detail-item">
                                                <span class="detail-label">Current Instructor</span>
                                                <span class="detail-value">{{ $progress->currentBooking->instructor->name }}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Email</span>
                                                <span class="detail-value">{{ $progress->currentBooking->instructor->email }}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Contact</span>
                                                <span class="detail-value">{{ $progress->currentBooking->instructor->contact ?? 'N/A' }}</span>
                                            </div>
                                        @else
                                                <div class="detail-empty-assignment">
                                                <p>No instructor assigned yet</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Overall Progress Bar -->
                                <div class="progress-bar-section">
                                    <h4 class="detail-section-title">Overall Completion Progress</h4>
                                    <div class="progress-bar-wrapper">
                                        <div class="progress-bar-container">
                                            <div class="progress-bar-fill progress-fill-dynamic" data-width="{{ $progress->completion_percent }}">
                                                @if($progress->completion_percent > 10)
                                                    {{ number_format($progress->completion_percent, 0) }}%
                                                @endif
                                            </div>
                                        </div>
                                        <div class="progress-percentage">{{ number_format($progress->completion_percent, 0) }}%</div>
                                    </div>
                                    <div class="progress-meta-row">
                                        <span>Last Updated: {{ $progress->last_updated ? $progress->last_updated->format('M d, Y h:i A') : 'Never' }}</span>
                                        <span>
                                            @if($progress->completion_percent == 100)
                                                <span class="badge badge-completed">✓ Completed</span>
                                            @elseif($progress->completion_percent > 0)
                                                <span class="badge badge-in-progress">In Progress</span>
                                            @else
                                                <span class="badge badge-not-started">Not Started</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <!-- Instructor Notes -->
                                @if($progress->notes)
                                <div class="notes-section">
                                    <h4 class="notes-section-title">Instructor Notes</h4>
                                    <p class="notes-text">{{ $progress->notes }}</p>
                                </div>
                                @endif

                                <!-- Recent Bookings -->
                                @if($progress->bookingsList->count() > 0)
                                <div class="detail-section detail-section-full">
                                    <h4 class="detail-section-title">Recent Schedules</h4>
                                    <div class="table-scroll-wrap">
                                    <table class="recent-table">
                                        <thead class="recent-thead">
                                            <tr>
                                                <th class="recent-th">Date & Time</th>
                                                <th class="recent-th">Instructor</th>
                                                <th class="recent-th">Status</th>
                                                <th class="recent-th">Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($progress->bookingsList->take(5) as $booking)
                                            <tr class="recent-row">
                                                <td class="recent-td">{{ $booking->scheduled_at->format('M d, Y h:i A') }}</td>
                                                <td class="recent-td">{{ $booking->instructor->name ?? 'N/A' }}</td>
                                                <td class="recent-td">
                                                    <span class="badge badge-{{ strtolower($booking->status) }}">{{ ucfirst($booking->status) }}</span>
                                                </td>
                                                <td class="recent-td">{{ Str::limit($booking->notes ?? 'No notes', 50) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $progresses->links() }}
    </div>
    @else
        <div class="empty-state empty-state-card">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <p class="empty-state-title">No progress records found</p>
            <p class="empty-state-text">Student progress will appear here once training begins</p>
        </div>
    @endif
</div>

<script>
function toggleDetails(index) {
    const detailRow = document.getElementById('detail-' + index);
    const progressRow = detailRow.previousElementSibling;
    const icon = document.getElementById('icon-' + index);
    
    if (detailRow.classList.contains('show')) {
        detailRow.classList.remove('show');
        progressRow.classList.remove('expanded');
    } else {
        detailRow.classList.add('show');
        progressRow.classList.add('expanded');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.progress-fill-dynamic').forEach(function (element) {
        const width = parseFloat(element.getAttribute('data-width') || '0');
        element.style.width = width + '%';
    });
});
</script>

@endsection

