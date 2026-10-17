@php
    // Course outline sidebar shared by the lesson viewer and the quiz page so the
    // whole course reads as one continuous LMS. Expects:
    //   $course, $outlineModules, $completedLessonIds, $activeModuleId, $activeLessonId
    $outlineModules = $outlineModules ?? collect();
    $completedLessonIds = $completedLessonIds ?? [];
    $activeModuleId = $activeModuleId ?? null;
    $activeLessonId = $activeLessonId ?? null;

    $totalLessons = $outlineModules->sum(fn ($m) => $m->lessons ? $m->lessons->count() : 0);
    $completedCount = count($completedLessonIds);
    $outlinePct = $totalLessons > 0 ? min(100, (int) round(($completedCount / $totalLessons) * 100)) : 0;
@endphp

<aside class="lms-outline">
    <div class="lms-outline-head">
        <a class="lms-outline-back lms-nav-link" href="{{ school_route('student.my-course') }}" onclick="loadContent(this.href); return false;">
            <i class="bi bi-arrow-left"></i> My Course
        </a>
        <div class="lms-outline-course">{{ $course->title ?? 'Course' }}</div>
        <div class="lms-outline-progress-track">
            <div class="lms-outline-progress-fill" style="width: {{ $outlinePct }}%"></div>
        </div>
        <div class="lms-outline-progress-text">{{ $completedCount }}/{{ $totalLessons }} lessons completed</div>
    </div>

    <nav class="lms-outline-nav" aria-label="Course outline">
        @forelse($outlineModules as $module)
            @php
                $hasLessons = $module->lessons && $module->lessons->count() > 0;
                $hasQuiz = $module->questions && $module->questions->count() > 0;
                $moduleActive = $activeModuleId === $module->id;
            @endphp
            <div class="lms-outline-module">
                <div class="lms-outline-module-title">{{ $module->title }}</div>

                @if($hasLessons)
                    @foreach($module->lessons->sortBy('sort_order') as $lessonIndex => $lesson)
                        @php
                            $isDone = in_array($lesson->id, $completedLessonIds, true);
                            $isActive = $activeLessonId === $lesson->id;
                        @endphp
                        <a class="lms-outline-item lms-nav-link {{ $isDone ? 'is-done' : '' }} {{ $isActive ? 'is-active' : '' }}"
                           href="{{ school_route('student.courses.modules.lessons.show', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}"
                           onclick="loadContent(this.href); return false;">
                            <span class="lms-outline-mark">@if($isDone)<i class="bi bi-check-lg"></i>@else{{ $lessonIndex + 1 }}@endif</span>
                            <span class="lms-outline-label">{{ $lesson->title }}</span>
                        </a>
                    @endforeach
                @endif

                @if($hasQuiz)
                    @php $quizActive = $moduleActive && $activeLessonId === null; @endphp
                    <a class="lms-outline-item lms-outline-quiz lms-nav-link {{ $quizActive ? 'is-active' : '' }}"
                       href="{{ school_route('student.courses.modules.assessment.take', ['course' => $course->id, 'module' => $module->id]) }}"
                       onclick="loadContent(this.href); return false;">
                        <span class="lms-outline-mark"><i class="bi bi-patch-question-fill"></i></span>
                        <span class="lms-outline-label">Module Assessment</span>
                    </a>
                @endif
            </div>
        @empty
            <div class="lms-outline-empty">No course content yet.</div>
        @endforelse
    </nav>
</aside>
