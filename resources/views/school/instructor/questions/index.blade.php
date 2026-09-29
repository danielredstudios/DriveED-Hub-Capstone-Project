@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Question Bank')

@section('content')
@include('school.partials.lms-shared-styles')

@php 
    $backUrl = request('return_url', school_route('instructor.dashboard'));
@endphp

<div class="lms-page"
    @if($module) 
        data-breadcrumb-course="{{ $module->course->title ?? '' }}"
        data-breadcrumb-module="{{ $module->title }}"
        data-breadcrumb-module-type="assessment"
        data-course-id="{{ $module->course_id }}"
        data-module-id="{{ $module->id }}"
    @endif
>
    <div class="lms-header">
        <div>
            <h1 class="lms-title">Question Bank</h1>
            <p class="lms-subtitle">
                @if($isSelecting && $module)
                    Picking questions for assessment: <strong>{{ $module->title }}</strong>
                @else
                    Manage and reuse questions across your courses.
                @endif
            </p>
        </div>
        <div class="lms-actions">
            @if($isSelecting)
                <button type="submit" form="bulkAddBankForm" class="lms-btn lms-btn-primary">
                    <i class="fas fa-plus"></i> Add Selected to Quiz
                </button>
            @endif

            <a href="{{ $backUrl }}" class="lms-btn lms-btn-muted">
                @if($isAjax && $isSelecting) Finish @else Back @endif
            </a>

            @if($isAjax)
                <button type="button" 
                        onclick="openQuizDrawer('{{ school_route('instructor.questions.create', ['course_id' => $moduleId ? ($module->course_id ?? '') : '', 'module_id' => $moduleId, 'is_selecting' => 1]) }}', 'Create New Question')" 
                        class="lms-btn lms-btn-primary">
                    <i class="fas fa-plus-circle"></i> + Create New
                </button>
            @else
                <a href="{{ school_route('instructor.questions.create', ['course_id' => $moduleId ? ($module->course_id ?? '') : '', 'module_id' => $moduleId, 'return_url' => url()->full()]) }}" 
                   class="lms-btn lms-btn-primary">
                    <i class="fas fa-plus-circle"></i> Add Question
                </a>
            @endif
        </div>
    </div>



    <!-- Filters -->
    <div class="lms-card" style="margin-bottom: 1.5rem; padding: 1rem;">
        <form action="{{ url()->current() }}" method="GET" class="lms-filter-form" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            @foreach(request()->except(['search', 'course_id', 'question_type', 'page']) as $key => $value)
                @if(is_array($value))
                    @foreach($value as $v)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <div style="flex: 1; min-width: 200px;">
                <label class="lms-label">Search Questions</label>
                <input type="text" name="search" value="{{ request('search') }}" class="lms-input" placeholder="Search question text...">
            </div>
            <div style="width: 200px;">
                <label class="lms-label">Filter by Course</label>
                <select name="course_id" class="lms-input">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width: 200px;">
                <label class="lms-label">Question Type</label>
                <select name="question_type" class="lms-input">
                    <option value="">All Types</option>
                    <option value="multiple_choice" {{ request('question_type') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                    <option value="true_false" {{ request('question_type') == 'true_false' ? 'selected' : '' }}>True or False</option>
                    <option value="enumeration" {{ request('question_type') == 'enumeration' ? 'selected' : '' }}>Enumeration</option>
                    <option value="identification" {{ request('question_type') == 'identification' ? 'selected' : '' }}>Identification</option>
                </select>
            </div>
            <div class="lms-filter-actions" style="display: flex; gap: 0.5rem;">
                <button type="submit" class="lms-btn lms-btn-primary">Apply</button>
                <a href="{{ url()->current() . '?' . http_build_query(request()->only(['module_id', 'is_selecting', 'return_url'])) }}" class="lms-btn lms-btn-muted">Clear</a>
            </div>
        </form>
    </div>

    <div class="lms-card">
        <div class="lms-card-head">
            <h2 class="lms-card-title">All Questions</h2>
            <div style="display: flex; align-items: center; gap: 1rem;">
                @if($isSelecting)
                    <label style="font-size: 0.8rem; cursor: pointer; color: #666; display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="selectAllBank"> Select All
                    </label>
                @endif
                <span class="lms-chip">{{ $questions->total() }} total</span>
            </div>
        </div>

        @if($isSelecting && $module)
            <form action="{{ school_route('instructor.courses.modules.assessments.add_multiple', ['course' => $module->course_id, 'module' => $moduleId]) }}" method="POST" id="bulkAddBankForm">
                @csrf
        @endif

        <ul class="lms-list">
            @forelse($questions as $question)
                @php $isAlreadyAttached = in_array($question->id, $attachedQuestionIds); @endphp
                <li class="lms-item {{ $isAlreadyAttached ? 'lms-item-disabled' : '' }}" style="{{ $isAlreadyAttached ? 'background: #fcfcfc;' : '' }}">
                    @if($isSelecting)
                        <div style="margin-right: 1.25rem; display: flex; align-items: center;">
                            <input type="checkbox" name="question_ids[]" value="{{ $question->id }}" class="bank-checkbox" {{ $isAlreadyAttached ? 'disabled' : '' }}>
                        </div>
                    @endif
                    <div style="flex: 1;">
                        <p class="lms-item-title">{{ Str::limit($question->question_text, 100) }}</p>
                        <p class="lms-item-meta">
                            <span class="lms-chip" style="font-size: 0.7rem; background: var(--primary-light); color: var(--primary-color);">
                                {{ str_replace('_', ' ', ucfirst($question->question_type)) }}
                            </span>
                            @if($isAlreadyAttached)
                                <span class="lms-chip" style="font-size: 0.7rem; background: #d1fae5; color: #065f46; font-weight: 600;">IN QUIZ</span>
                            @endif
                            @if($question->course)
                                <span style="margin: 0 0.5rem; opacity: 0.3;">|</span> 
                                <span style="color: #666;">Course:</span> {{ $question->course->title }}
                            @endif
                            @if($question->lesson)
                                <span style="margin: 0 0.5rem; opacity: 0.3;">|</span> 
                                <span style="color: #666;">Lesson:</span> {{ $question->lesson->title }}
                            @endif
                            <span style="margin: 0 0.5rem; opacity: 0.3;">|</span> 
                            <span style="color: #666;">Points:</span> {{ $question->default_points }}
                        </p>
                    </div>
                    <div class="lms-item-links">
                        @if($isSelecting && !$isAlreadyAttached)
                            <button type="button" 
                                data-action="quick-add" 
                                data-id="{{ $question->id }}"
                                class="lms-btn lms-btn-primary" 
                                style="padding: 0.4rem 0.9rem; font-size: 0.8rem;">Add to Quiz</button>
                        @endif
                        <a href="{{ school_route('instructor.questions.edit', ['question' => $question->id, 'return_url' => url()->full()]) }}" class="lms-link lms-link-edit">Edit</a>
                        @if(!$isSelecting)
                            <form action="{{ school_route('instructor.questions.destroy', ['question' => $question->id]) }}" method="POST" onsubmit="return confirm('Remove this question from the bank?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="lms-link lms-link-delete" style="background:none; border:none; padding:0; cursor:pointer; color: #dc3545;">Delete</button>
                            </form>
                        @endif
                    </div>
                </li>
            @empty
                <li class="lms-empty">No questions found in the bank.</li>
            @endforelse
        </ul>

        @if($isSelecting && $module)
            </form>
            <!-- Hidden form for single add -->
            <form id="singleAddBankForm" action="{{ school_route('instructor.courses.modules.assessments.add', ['course' => $module->course_id, 'module' => $moduleId]) }}" method="POST" style="display:none;">
                @csrf
                <input type="hidden" name="question_id" id="singleAddId">
            </form>
        @endif

        @if($questions->hasPages())
            <div style="padding: 1.5rem; border-top: 1px solid var(--border-color);">
                {{ $questions->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<script>
/**
 * Question Bank Standalone Page Logic
 * Uses global event delegation where possible to remain robust during AJAX transitions.
 */
(function() {
    // 1. Identify context and availability
    function getAvailableDrawerHandler() {
        // Try local drawer first (if we are in manage.blade.php)
        const localDrawer = document.getElementById('lmsQuizDrawer');
        if (localDrawer && typeof window.openQuizDrawer === 'function') {
            return (url, title) => window.openQuizDrawer(url, title);
        }

        // Try parent drawer if we are in an iframe or AJAX-loaded content that has access to parent
        if (window.parent && window.parent !== window) {
            try {
                const parentDrawer = window.parent.document.getElementById('lmsQuizDrawer');
                if (parentDrawer && typeof window.parent.openQuizDrawer === 'function') {
                    return (url, title) => window.parent.openQuizDrawer(url, title);
                }
            } catch (e) {
                // Ignore cross-origin errors
            }
        }
        return null;
    }

    /**
     * Handles navigation (filtering, pagination) within the question bank
     */
    function navigateQuestionBank(url, title = 'Question Bank') {
        const drawerHandler = getAvailableDrawerHandler();
        if (drawerHandler) {
            drawerHandler(url, title);
            return true;
        }

        if (typeof window.loadContent === 'function') {
            window.loadContent(url);
            return true;
        }

        // Fallback to standard navigation
        window.location.href = url;
        return false;
    }

    /**
     * Handles single question addition to a quiz
     */
    function submitQuickAdd(id, btn) {
        const form = document.getElementById('singleAddBankForm');
        const input = document.getElementById('singleAddId');
        if (!form || !input || !btn) return;

        // Visual feedback
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="btn-spinner" style="width:12px; height:12px; border-width:2px; display:inline-block; border:2px solid currentColor; border-right-color:transparent; border-radius:50%; animation: btn-spin 0.6s linear infinite; vertical-align:middle;"></span>';
        btn.disabled = true;
        
        input.value = id;

        // Safety timeout to prevent permanent disabled state
        setTimeout(() => {
            if (btn && btn.disabled) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }, 8000);

        // Submit via AJAX if handleFormSubmit is available, otherwise standard submit
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
        }
    }

    /**
     * Initializer for the Question Bank page.
     * Can be called multiple times; use guards to avoid double-binding.
     */
    function initQuestionBankStandalone() {
        const container = document.querySelector('.lms-page');
        if (!container || container.dataset.qbStandaloneBound === '1') return;
        
        // Mark as bound
        container.dataset.qbStandaloneBound = '1';

        // 1. Select All / Bulk handling
        const selectAll = container.querySelector('#selectAllBank');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                container.querySelectorAll('.bank-checkbox:not(:disabled)').forEach(cb => {
                    cb.checked = this.checked;
                });
            });
        }

        // 2. Filter Form AJAX handling (if loadContent is available)
        const filterForm = container.querySelector('.lms-filter-form');
        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                if (typeof window.loadContent !== 'function') return; // Let it submit normally

                const formData = new FormData(this);
                const params = new URLSearchParams();
                for (const [key, value] of formData) {
                    params.append(key, value);
                }

                const url = this.getAttribute('action') + '?' + params.toString();
                if (navigateQuestionBank(url)) {
                    e.preventDefault();
                }
            });
        }

        // 3. Delegation for dynamic buttons and links
        container.addEventListener('click', function(e) {
            // Quick Add button
            const quickAddBtn = e.target.closest('[data-action="quick-add"]');
            if (quickAddBtn) {
                e.preventDefault();
                submitQuickAdd(quickAddBtn.dataset.id, quickAddBtn);
                return;
            }

            // Pagination links
            const paginationLink = e.target.closest('.pagination a');
            if (paginationLink && typeof window.loadContent === 'function') {
                e.preventDefault();
                navigateQuestionBank(paginationLink.getAttribute('href'));
                return;
            }

            // "Clear" filter link
            const clearLink = e.target.closest('.lms-filter-form a.lms-btn-muted');
            if (clearLink && typeof window.loadContent === 'function') {
                e.preventDefault();
                navigateQuestionBank(clearLink.getAttribute('href'));
                return;
            }
        });
    }

    // Export to global scope for external calls if needed
    window.navigateQuestionBank = navigateQuestionBank;
    window.submitQuickAdd = submitQuickAdd;
    window.initQuestionBankStandalone = initQuestionBankStandalone;

    // Run immediately
    initQuestionBankStandalone();

    // Also run on DOMContentLoaded just in case of full page load timing
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initQuestionBankStandalone);
    }
})();
</script>

@endsection
