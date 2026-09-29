@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Enrolled Course')

@section('content')
@php
    $schoolName = $school->name ?? 'Driving School';
    $settings = $school->schoolSetting;
    $primaryColor = $settings->primary_color ?? '#667eea';
    $secondaryColor = $settings->secondary_color ?? '#764ba2';
@endphp

<style>
.my-course-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 4px solid {{ $primaryColor }};
}

.page-title {
    font-size: 2rem;
    color: #111827;
    margin: 0;
    font-weight: 400;
}

.course-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 25px;
}

.course-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
}

.course-title {
    font-size: 1.5rem;
    color: #333;
    margin: 0;
}

.course-type-badge {
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
}

.course-type-theoretical {
    background: #dbeafe;
    color: #1e40af;
}

.course-type-practical {
    background: #dcfce7;
    color: #166534;
}

.progress-section {
    margin: 25px 0;
}

.progress-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-weight: 500;
}

.progress-bar-container {
    width: 100%;
    height: 25px;
    background: #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    background: {{ $primaryColor }};
    display: flex;
    align-items: center;
    justify-content: center;
    transition: width 0.5s ease;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
}

.course-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.stat-box {
    background: #f9fafb;
    padding: 15px;
    border-radius: 10px;
    text-align: center;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: {{ $primaryColor }};
}

.stat-label {
    font-size: 0.85rem;
    color: #666;
    margin-top: 5px;
}

/* Module/Content Section */
.modules-section {
    margin-top: 30px;
}

.section-title {
    font-size: 1.3rem;
    color: #333;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e5e7eb;
}

.module-item {
    background: #f9fafb;
    border-radius: 10px;
    margin-bottom: 12px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.module-header {
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: background 0.2s;
}

.module-header:hover {
    background: #f0f1f3;
}

.module-info h4 {
    margin: 0 0 5px 0;
    font-size: 1rem;
    color: #333;
}

.module-info p {
    margin: 0;
    font-size: 0.85rem;
    color: #666;
}

.module-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}

.lesson-count {
    background: {{ $primaryColor }};
    color: white;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
}

.module-toggle {
    font-size: 0.8rem;
    color: #999;
    transition: transform 0.3s;
}

.module-toggle.expanded {
    transform: rotate(180deg);
}

.lesson-list {
    display: none;
    border-top: 1px solid #e5e7eb;
    padding: 0;
    margin: 0;
    list-style: none;
}

.lesson-list.show {
    display: block;
}

.lesson-item {
    padding: 15px 20px;
    border-bottom: 1px solid #f0f1f3;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.lesson-top {
    display: flex;
    align-items: center;
    gap: 12px;
}

.lesson-number {
    background: {{ $primaryColor }}20;
    color: {{ $primaryColor }};
    min-width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    flex-shrink: 0;
}

.lesson-title {
    font-weight: 600;
    color: #1f2937;
    font-size: 1rem;
}

.lesson-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-left: 40px;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-video {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.btn-video:hover {
    background: #fecaca;
}

.btn-download {
    background: #dcfce7;
    color: #16a34a;
    border: 1px solid #bbf7d0;
}

.btn-download:hover {
    background: #bbf7d0;
}

.btn-read {
    background: #f3f4f6;
    color: #4b5563;
    border: 1px solid #e5e7eb;
}

.expiry-warning {
    background: #fff7ed;
    border: 1px solid #fb923c;
    color: #9a3412;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.9rem;
}

/* Session History */
.sessions-section {
    margin-top: 30px;
}

.session-item {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 10px;
}

.session-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.session-date {
    font-weight: 600;
    color: #333;
}

.session-hours {
    background: #dcfce7;
    color: #166534;
    padding: 4px 10px;
    border-radius: 10px;
    font-size: 0.85rem;
}

.session-details {
    font-size: 0.9rem;
    color: #666;
}

/* No Course State */
.no-course-card {
    text-align: center;
    padding: 50px 30px;
}

.no-course-icon {
    font-size: 4rem;
    margin-bottom: 20px;
}

.no-course-title {
    font-size: 1.5rem;
    color: #333;
    margin-bottom: 10px;
}

.no-course-text {
    color: #666;
    margin-bottom: 25px;
}

/* Pending Requests */
.pending-section {
    margin-top: 25px;
}

.pending-item {
    background: #fef3c7;
    border: 1px solid #f59e0b;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pending-badge {
    background: #f59e0b;
    color: white;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
}

/* Available Courses */
.available-courses {
    display: grid;
    gap: 15px;
    margin-top: 20px;
}

.available-course-item {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.available-course-item:hover {
    border-color: {{ $primaryColor }};
}

.course-info h4 {
    margin: 0 0 5px 0;
    color: #333;
}

.course-info p {
    margin: 0;
    font-size: 0.9rem;
    color: #666;
}

.enroll-btn {
    background: {{ $primaryColor }};
    color: white;
    padding: 10px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
}

.enroll-btn:hover {
    opacity: 0.9;
}

.course-description-text {
    color: #666;
    margin-bottom: 20px;
}

.materials-note {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.no-course-icon-svg {
    width: 48px;
    height: 48px;
    color: #9ca3af;
}

.pending-submitted {
    color: #666;
}

.course-type-compact {
    font-size: 0.7rem;
    padding: 3px 8px;
}

@media (max-width: 768px) {
    .course-header {
        flex-direction: column;
    }
    
    .course-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<div class="my-course-container">
    <div class="page-header">
        <h1 class="page-title">Enrolled Course</h1>
    </div>

    @if($course)
        {{-- 24h Expiry Warning for Completed Courses --}}
        @if($activeEnrollment && $activeEnrollment->status === 'completed')
            <div class="expiry-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <strong>Access Limited:</strong> You have completed this course. Your access to these materials will expire in 
                    {{ \Carbon\Carbon::parse($activeEnrollment->completed_at)->addDay()->diffForHumans() }}.
                </div>
            </div>
        @endif

        {{-- Active Course Card --}}
        <div class="course-card">
            <div class="course-header">
                <h2 class="course-title">{{ $course->title }}</h2>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <span class="course-type-badge course-type-{{ $course->course_type ?? 'theoretical' }}">
                        {{ $course->course_type === 'theoretical' ? 'Theoretical' : 'Practical' }}
                    </span>
                </div>
            </div>

            @if($course->description)
                <p class="course-description-text">{{ $course->description }}</p>
            @endif

            {{-- Progress Bar --}}
            <div class="progress-section">
                <div class="progress-label">
                    <span>Progress</span>
                    <span>{{ $hoursCompleted }} / {{ $hoursRequired }} hours</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill progress-fill" data-progress="{{ $progressPercentage }}">
                        @if($progressPercentage > 10)
                            {{ $progressPercentage }}%
                        @endif
                    </div>
                </div>
            </div>

            {{-- Course Stats --}}
            <div class="course-stats">
                <div class="stat-box">
                    <div class="stat-value">{{ $hoursCompleted }}</div>
                    <div class="stat-label">Hours Completed</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ $hoursRequired }}</div>
                    <div class="stat-label">Hours Required</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ $sessionCompletions->count() }}</div>
                    <div class="stat-label">Logs Verified</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ max(0, $hoursRequired - $hoursCompleted) }}</div>
                    <div class="stat-label">Hours Remaining</div>
                </div>
            </div>
        </div>

        {{-- Progress Overview + Course Materials with visual lesson sequence --}}
        @php
            $totalLessons = $modules->sum(fn($m)=>$m->lessons ? $m->lessons->count():0);
            $lessonProgressPct = $totalLessons>0 ? min(100, round(($progressPercentage))) : $progressPercentage;
            // Approximate completed lessons from overall progress
            $completedLessonIds = $completedLessonIds ?? [];
            $completedCount = count($completedLessonIds);
            $allLessonsOrdered = $modules->sortBy('sort_order')->flatMap(fn($m)=> $m->lessons ? $m->lessons->sortBy('sort_order') : collect())->values();
        @endphp
        @if($modules->count() > 0)
            <div class="course-card">
                <h3 class="section-title">Course Materials <span style="font-weight:400;font-size:0.9rem;color:#6b7280;">— {{ $completedCount }}/{{ $totalLessons }} lessons completed ({{ $lessonProgressPct }}%)</span></h3>
                <div style="background:#e5e7eb;border-radius:10px;height:10px;overflow:hidden;margin-bottom:12px;">
                    <div style="height:100%;background:{{ $primaryColor }};width:{{ $lessonProgressPct }}%;transition:width .5s;"></div>
                </div>
                <p class="materials-note">
                    Lessons are ordered. Completed lessons turn green, next lesson is highlighted. Click a module to view its lessons.
                </p>
                
                <div class="modules-section">
                    @foreach($modules->sortBy('sort_order') as $module)
                        <div class="module-item">
                            <div class="module-header" onclick="toggleModule(this)">
                                <div class="module-info">
                                    <h4>{{ $module->title }}</h4>
                                    @if($module->description)
                                        <p>{{ Str::limit($module->description, 80) }}</p>
                                    @endif
                                </div>
                                <div class="module-meta">
                                    @if($module->lessons && $module->lessons->count() > 0)
                                        <span class="lesson-count">{{ $module->lessons->count() }} {{ Str::plural('lesson', $module->lessons->count()) }}</span>
                                    @endif
                                    <span class="module-toggle">&#9660;</span>
                                </div>
                            </div>
                            @if($module->lessons && $module->lessons->count() > 0)
                                <ul class="lesson-list">
                                    @foreach($module->lessons->sortBy('sort_order') as $index => $lesson)
                                        @php $globalIndex = $allLessonsOrdered->search(fn($l)=>$l->id===$lesson->id); $isCompleted = in_array($lesson->id, $completedLessonIds, true); $isNext = !$isCompleted && $globalIndex === $completedCount && $completedCount < $totalLessons; @endphp
                                        <li class="lesson-item" style="@if($isCompleted) border-left:4px solid #10b981;background:#f0fdf4; @elseif($isNext) border-left:4px solid {{ $primaryColor }};background:#eff6ff; @endif">
                                            <div class="lesson-top">
                                                <span class="lesson-number" style="@if($isCompleted) background:#10b981;color:white; @elseif($isNext) background:{{ $primaryColor }};color:white; @endif">@if($isCompleted) ✓ @else {{ $index + 1 }} @endif</span>
                                                <span class="lesson-title">{{ $lesson->title }} @if($isCompleted) <span style="font-size:0.75rem;color:#065f46;font-weight:600;">— Done</span> @elseif($isNext) <span style="font-size:0.75rem;color:{{ $primaryColor }};font-weight:600;">— Next</span> @endif</span>
                                            </div>
                                            
                                            <div class="lesson-actions">
                                                @if($lesson->video_url)
                                                    <a href="{{ $lesson->video_url }}" target="_blank" class="action-btn btn-video">
                                                        <i class="bi bi-play-circle-fill"></i> Watch Video
                                                    </a>
                                                @endif

                                                @if(!empty($lesson->attachments))
                                                    @foreach($lesson->attachments as $attachment)
                                                        @php 
                                                            $fileName = is_array($attachment) ? ($attachment['name'] ?? 'Attachment') : basename($attachment);
                                                            $filePath = is_array($attachment) ? ($attachment['path'] ?? $attachment) : $attachment;
                                                        @endphp
                                                        <a href="{{ Storage::url($filePath) }}" target="_blank" class="action-btn btn-download">
                                                            <i class="bi bi-file-earmark-arrow-down-fill"></i> {{ $fileName }}
                                                        </a>
                                                    @endforeach
                                                @endif

                                                @if($lesson->content)
                                                    <button type="button" class="action-btn btn-read" onclick="toggleReadModal(@js($lesson->title), @js($lesson->content))">
                                                        <i class="bi bi-book-fill"></i> Read
                                                    </button>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach

                                    {{-- If module has both lessons and questions --}}
                                    @if($module->questions && $module->questions->count() > 0)
                                        <li class="lesson-item" style="background: #eff6ff; border-top: 2px solid #bfdbfe;">
                                            <div class="lesson-top">
                                                <span class="lesson-number" style="background: #3b82f6; color: white;">?</span>
                                                <span class="lesson-title">Module Assessment</span>
                                            </div>
                                            <div class="lesson-actions">
                                                <a href="{{ school_route('student.courses.modules.assessment.take', ['course' => $course->id, 'module' => $module->id]) }}" class="action-btn" style="background: #3b82f6; color: white; border: none; padding: 8px 20px;">
                                                    Start Quiz
                                                </a>
                                            </div>
                                        </li>
                                    @endif
                                </ul>
                            @elseif($module->module_type === 'assessment' || ($module->questions && $module->questions->count() > 0))
                                <div class="lesson-list" style="padding: 20px; text-align: center; background: #eff6ff;">
                                    <div style="margin-bottom: 15px; color: #1e40af; font-weight: 600;">
                                        This module is an assessment to test your knowledge.
                                    </div>
                                    <a href="{{ school_route('student.courses.modules.assessment.take', ['course' => $course->id, 'module' => $module->id]) }}" class="enroll-btn" style="background: #3b82f6; display: inline-flex; align-items: center; gap: 8px;">
                                        <i class="bi bi-pencil-square"></i> Take Assessment Quiz
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Handshake Status (Schedules & Verification) --}}
        @php
            $activeSchedules = ($activeEnrollment && $activeEnrollment->bookings) 
                ? $activeEnrollment->bookings->whereIn('status', ['scheduled', 'done'])->sortBy('scheduled_at') 
                : collect();
        @endphp

        @if($activeSchedules->count() > 0)
            <div class="course-card">
                <h3 class="section-title">Session Schedule & Verification</h3>
                <p class="materials-note">Track your upcoming lessons and their verification status.</p>
                
                <div class="sessions-section">
                    @foreach($activeSchedules as $booking)
                        <div class="session-item" style="border-left: 4px solid {{ $booking->status === 'done' ? '#f59e0b' : $primaryColor }};">
                            <div class="session-header">
                                <span class="session-date">
                                    {{ \Carbon\Carbon::parse($booking->scheduled_at)->format('M d, Y') }} at {{ \Carbon\Carbon::parse($booking->scheduled_at)->format('g:i A') }}
                                </span>
                                <span class="badge" style="background: {{ $booking->status === 'done' ? '#fef3c7' : '#dbeafe' }}; color: {{ $booking->status === 'done' ? '#92400e' : '#1e40af' }}; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                                    {{ $booking->status === 'done' ? 'Reviewing (Instructor Done)' : 'Confirmed' }}
                                </span>
                            </div>
                            <div class="session-details">
                                <strong>Instructor:</strong> {{ $booking->instructor->name ?? 'TBD' }}<br>
                                @if($booking->status === 'done')
                                    <small style="color: #92400e;"><i class="bi bi-clock-history"></i> Awaiting final admin verification to log your hours.</small>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Session History --}}
        @if($sessionCompletions->count() > 0)
            <div class="course-card">
                <h3 class="section-title">Official Session Completions</h3>
                <p class="materials-note">These are your officially verified and logged training hours.</p>
                    @foreach($sessionCompletions->sortByDesc('session_date')->take(10) as $session)
                        <div class="session-item">
                            <div class="session-header">
                                <span class="session-date">
                                    {{ \Carbon\Carbon::parse($session->session_date)->format('M d, Y') }}
                                </span>
                                <span class="session-hours">{{ $session->hours_completed ?? 1 }} hour(s)</span>
                            </div>
                            <div class="session-details">
                                @if($session->instructor)
                                    <strong>Instructor:</strong> {{ $session->instructor->name ?? 'N/A' }}<br>
                                @endif
                                @if($session->feedback)
                                    <strong>Feedback:</strong> {{ $session->feedback }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
        @endif

    @else
        {{-- No Active Course --}}
        <div class="course-card no-course-card">
            <div class="no-course-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="no-course-icon-svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <h2 class="no-course-title">No Active Course</h2>
            <p class="no-course-text">
                You are not currently enrolled in any course. 
                Browse available courses below and submit an enrollment request to get started.
            </p>
        </div>

        {{-- Pending Enrollment Requests --}}
        @if($pendingRequests->count() > 0)
            <div class="course-card">
                <h3 class="section-title">Pending Enrollment Requests</h3>
                <div class="pending-section">
                    @foreach($pendingRequests as $request)
                        <div class="pending-item">
                            <div>
                                <strong>{{ $request->course->title ?? 'Unknown Course' }}</strong>
                                <br>
                                <small class="pending-submitted">Submitted: {{ $request->created_at->format('M d, Y') }}</small>
                            </div>
                            <span class="pending-badge">Pending Review</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Available Courses --}}
        @if($availableCourses->count() > 0)
            <div class="course-card">
                <h3 class="section-title">Available Courses</h3>
                <div class="available-courses">
                    @foreach($availableCourses as $availableCourse)
                        <div class="available-course-item">
                            <div class="course-info">
                                <h4>{{ $availableCourse->title }}</h4>
                                <p>
                                    <span class="course-type-badge course-type-{{ $availableCourse->course_type ?? 'theoretical' }} course-type-compact">
                                        {{ ucfirst($availableCourse->course_type ?? 'Theoretical') }}
                                    </span>
                                    &nbsp;•&nbsp;
                                    {{ $availableCourse->duration_hours ?? $availableCourse->hours_required ?? '?' }} hours
                                    @if($availableCourse->price)
                                        &nbsp;•&nbsp; ₱{{ number_format($availableCourse->price, 2) }}
                                    @endif
                                </p>
                            </div>
                            <a href="{{ school_route('student.courses.show', ['course' => $availableCourse->id]) }}" class="enroll-btn">
                                View Details
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    <!-- Read Lesson Modal -->
    <div class="modal" id="readLessonModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
        <div class="modal-content" style="background: white; border-radius: 15px; max-width: 800px; width: 100%; max-height: 90vh; overflow-y: auto; position: relative; padding: 0;">
            <div class="modal-header" style="background: {{ $primaryColor }}; color: white; padding: 20px; position: sticky; top: 0; display: flex; justify-content: space-between; align-items: center;">
                <h3 id="modalLessonTitle" style="margin: 0; font-size: 1.25rem;">Lesson Title</h3>
                <button type="button" onclick="closeReadModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div id="modalLessonContent" style="padding: 30px; line-height: 1.8; color: #374151; font-size: 1.1rem;">
                <!-- Content goes here -->
            </div>
            <div style="padding: 20px; border-top: 1px solid #e5e7eb; text-align: right; position: sticky; bottom: 0; background: white;">
                <button type="button" class="enroll-btn" onclick="closeReadModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleReadModal(title, content) {
    const modal = document.getElementById('readLessonModal');
    const modalContent = document.getElementById('modalLessonContent');
    const rawContent = content || '';
    const hasHtml = /<\s*\/?\s*[a-z][^>]*>/i.test(rawContent);

    document.getElementById('modalLessonTitle').innerText = title;

    if (hasHtml) {
        modalContent.style.whiteSpace = '';
        modalContent.innerHTML = rawContent;
    } else {
        modalContent.style.whiteSpace = 'pre-line';
        modalContent.textContent = rawContent;
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeReadModal() {
    const modal = document.getElementById('readLessonModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
}
function toggleModule(header) {
    const moduleItem = header.closest('.module-item');
    const lessonList = moduleItem.querySelector('.lesson-list');
    const toggle = header.querySelector('.module-toggle');
    
    if (lessonList) {
        lessonList.classList.toggle('show');
        toggle.classList.toggle('expanded');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.progress-fill').forEach(function (bar) {
        const value = Number(bar.dataset.progress || 0);
        const clamped = Math.max(0, Math.min(100, value));
        bar.style.width = clamped + '%';
    });
});
</script>
@endsection
