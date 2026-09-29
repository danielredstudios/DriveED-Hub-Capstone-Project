@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Manage Assessment')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" data-breadcrumb-course="{{ $module->course->title ?? '' }}" data-breadcrumb-module="{{ $module->title ?? '' }}" data-breadcrumb-module-type="assessment">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">Exam Builder: {{ $module->title }}</h1>
            <p class="lms-subtitle">Assemble your exam by picking questions from the bank.</p>
        </div>
        <div class="lms-actions">
            <a href="{{ request('return_url', school_route('instructor.courses.modules.show', ['course' => $module->course_id, 'module' => $module->id])) }}" class="lms-btn lms-btn-muted">Back to Module</a>
        </div>
    </div>

    <div style="background: #eef2ff; border: 1px solid #c7d2fe; padding: 12px 18px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
        <div style="background: #6366f1; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: bold;">?</div>
        <p style="margin: 0; font-size: 0.9rem; color: #3730a3;">
            <strong>How to build your exam:</strong> Use the right side to find and <strong>Add to Quiz</strong>. Use the left side to <strong>drag and reorder</strong> the questions as they will appear to students.
        </p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Left Side: Attached Questions -->
        <div>
            <div class="lms-card">
                <div class="lms-card-head">
                    <h2 class="lms-card-title">Assessment Questions</h2>
                    <span class="lms-chip" id="questionCount">{{ $attachedQuestions->count() }} attached</span>
                </div>
                
                @if($attachedQuestions->count() > 0)
                    <p style="padding: 1rem 1.5rem 0.5rem; font-size: 0.8rem; color: #666; font-style: italic;">
                        <i class="fas fa-arrows-alt-v"></i> Drag items within this list to reorder the exam.
                    </p>
                @endif

                <ul class="lms-list" id="sortableQuestions">
                    @forelse($attachedQuestions as $question)
                        <li class="lms-item" data-id="{{ $question->id }}" style="cursor: move;">
                            <div style="flex: 1;">
                                <p class="lms-item-title">{{ Str::limit($question->question_text, 80) }}</p>
                                <p class="lms-item-meta">
                                    <span class="lms-chip" style="font-size: 0.7rem; background: #e9ecef;">{{ ucfirst($question->question_type) }}</span>
                                    | {{ $question->pivot->points ?? $question->default_points }} points
                                </p>
                            </div>
                            <div class="lms-item-links">
                                <form action="{{ school_route('instructor.courses.modules.assessments.remove', ['course' => $module->course_id, 'module' => $module->id, 'question' => $question->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="lms-link lms-link-delete" style="background:none; border:none; color: #dc3545; padding:0; cursor:pointer; font-size: 0.85rem;">Remove</button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="lms-empty" style="padding: 3rem 1.5rem;">
                            No questions added yet.<br>
                            <span style="font-size: 0.85rem; color: #888;">Select questions from the bank on the right to start building your exam.</span>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Right Side: Question Bank Suggestions -->
        <div>
            <div class="lms-card">
                <div class="lms-card-head">
                    <h2 class="lms-card-title">Add Questions</h2>
                    <button type="button" onclick="openQuizDrawer('{{ school_route('instructor.questions.create', ['course_id' => $module->course_id, 'module_id' => $module->id, 'return_url' => url()->current()]) }}', 'Create New Question')" class="lms-btn lms-btn-primary" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">+ Create New</button>
                </div>
                
                <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border-color); background: #fcfcfc;">
                    <p style="font-size: 0.85rem; color: #444; font-weight: 600; margin-bottom: 0.25rem;">Suggested Questions</p>
                    <p style="font-size: 0.75rem; color: #777;">From <strong>{{ $course->title }}</strong></p>
                </div>

                <form action="{{ school_route('instructor.courses.modules.assessments.add_multiple', ['course' => $course->id, 'module' => $module->id]) }}" method="POST" id="bulkAddForm">
                    @csrf
                    <ul class="lms-list" id="suggestedList" style="max-height: 600px; overflow-y: auto;">
                        @forelse($suggestedQuestions as $question)
                            <li class="lms-item">
                                <div style="margin-right: 1rem; display: flex; align-items: center;">
                                    <input type="checkbox" name="question_ids[]" value="{{ $question->id }}" class="question-checkbox">
                                </div>
                                <div style="flex: 1;">
                                    <p class="lms-item-title" style="font-size: 0.85rem;">{{ Str::limit($question->question_text, 70) }}</p>
                                    <p class="lms-item-meta" style="font-size: 0.7rem;">
                                        {{ str_replace('_', ' ', ucfirst($question->question_type)) }} 
                                        @if($question->lesson) | {{ $question->lesson->title }} @endif
                                    </p>
                                </div>
                                <div class="lms-item-links">
                                    <button type="button" onclick="addThisQuestion({{ $question->id }})" class="lms-btn lms-btn-primary" style="padding: 0.25rem 0.6rem; font-size: 0.75rem;">Add to Quiz</button>
                                </div>
                            </li>
                        @empty
                            <li class="lms-empty" style="padding: 2rem 1.5rem;">
                                No more suggested questions.<br>
                                <button type="button" onclick="openQuizDrawer('{{ school_route('instructor.questions.index', ['return_url' => url()->current(), 'module_id' => $module->id, 'is_selecting' => 1]) }}', 'Browse Question Bank')" class="lms-link" style="background:none; border:none; padding:0; cursor:pointer; font-size: 0.85rem;">Browse entire Question Bank &raquo;</button>
                            </li>
                        @endforelse
                    </ul>
                    
                    @if($suggestedQuestions->count() > 0)
                        <div style="padding: 1rem 1.25rem; background: #f8f9fa; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; border-radius: 0 0 10px 10px;">
                            <label style="font-size: 0.8rem; cursor: pointer; color: #555; display: flex; align-items: center; gap: 0.5rem;">
                                <input type="checkbox" id="selectAll"> Select All
                            </label>
                            <button type="submit" class="lms-btn lms-btn-primary" style="font-size: 0.8rem; padding: 0.5rem 1rem;">Add Selected</button>
                        </div>
                    @endif
                </form>
                
                @if($suggestedQuestions->count() > 0)
                    <div style="padding: 1.25rem; text-align: center; border-top: 1px solid var(--border-color); background: #fff;">
                        <button type="button" onclick="openQuizDrawer('{{ school_route('instructor.questions.index', ['return_url' => url()->current(), 'module_id' => $module->id, 'is_selecting' => 1]) }}', 'Browse Question Bank')" class="lms-link" style="background:none; border:none; padding:0; cursor:pointer; font-size: 0.85rem; font-weight: 600;">View all questions in bank &raquo;</button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for single add to avoid nested forms -->
<form id="singleAddForm" action="{{ school_route('instructor.courses.modules.assessments.add', ['course' => $course->id, 'module' => $module->id]) }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="question_id" id="singleQuestionId">
</form>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function addThisQuestion(id) {
    document.getElementById('singleQuestionId').value = id;
    document.getElementById('singleAddForm').submit();
}

document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.question-checkbox');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    const el = document.getElementById('sortableQuestions');
    if (el) {
        Sortable.create(el, {
            animation: 150,
            ghostClass: 'lms-item-ghost',
            onEnd: function() {
                const order = Array.from(el.querySelectorAll('.lms-item')).map(item => item.dataset.id);
                
                // Show a small loading indicator if needed
                el.style.opacity = '0.7';

                fetch("{{ school_route('instructor.courses.modules.assessments.reorder', ['course' => $module->course_id, 'module' => $module->id]) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ order: order })
                })
                .then(response => response.json())
                .then(data => {
                    el.style.opacity = '1';
                    if (data.success) {
                        console.log('Order updated successfully');
                    }
                })
                .catch(err => {
                    el.style.opacity = '1';
                    console.error('Error reordering questions:', err);
                    alert('Failed to save new order. Please refresh.');
                });
            }
        });
    }
});
</script>

<style>
.lms-item-ghost {
    background: #f0f7ff !important;
    border: 1px dashed var(--primary-color) !important;
}
</style>

{{-- Localized Drawer for Quiz Builder --}}
<div id="lmsQuizDrawer" class="lms-modal-overlay">
    <div class="lms-modal-window">
        <div class="lms-modal-header">
            <h3 class="lms-modal-title" id="lmsQuizDrawerTitle" style="margin:0; font-size:1.1rem; font-weight:700;">Loading...</h3>
            <button type="button" class="lms-modal-close" onclick="closeQuizDrawer()">&times;</button>
        </div>
        <div class="lms-modal-body" id="lmsQuizDrawerBody">
            <div class="lms-modal-loader">
                <div class="btn-spinner"></div> Loading content...
            </div>
        </div>
    </div>
</div>

<script>
    function setDrawerContent(html) {
        const body = document.getElementById('lmsQuizDrawerBody');
        if (!body) return;

        body.innerHTML = html;

        // Execute inline/external scripts from dynamically injected drawer content.
        const scripts = body.querySelectorAll('script');
        scripts.forEach(script => {
            const newScript = document.createElement('script');
            if (script.src) {
                newScript.src = script.src;
            } else {
                newScript.textContent = script.textContent;
            }
            document.body.appendChild(newScript);
            setTimeout(() => {
                if (newScript.parentNode) {
                    newScript.parentNode.removeChild(newScript);
                }
            }, 50);
        });

        // Allow page-specific initializers to bind events after content swap.
        if (typeof window.initQuestionBankInteractions === 'function') {
            window.initQuestionBankInteractions();
        }
    }

    function openQuizDrawer(url, title) {
        const overlay = document.getElementById('lmsQuizDrawer');
        const body = document.getElementById('lmsQuizDrawerBody');
        const titleEl = document.getElementById('lmsQuizDrawerTitle');

        titleEl.textContent = title;
        overlay.classList.add('active');
        body.innerHTML = '<div class="lms-modal-loader"><span class="btn-spinner"></span> Loading content...</div>';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => {
                if (!r.ok) throw new Error('Network response was not ok');
                return r.text();
            })
            .then(html => {
                setDrawerContent(html);
            })
            .catch(err => {
                console.error('Drawer Load Error:', err);
                body.innerHTML = '<div class="lms-empty" style="padding: 2rem;">' +
                                 '<p style="color:var(--danger-color); font-weight:600;">Failed to load content.</p>' +
                                 '<p style="font-size:0.9rem; color:#666;">' + (err.message || 'An error occurred.') + '</p>' +
                                 '<button onclick="openQuizDrawer(\'' + url + '\', \'' + title + '\')" class="lms-btn lms-btn-muted" style="margin-top:1rem;">Retry</button>' +
                                 '</div>';
            });
    }

    function showDrawerLoader() {
        const body = document.getElementById('lmsQuizDrawerBody');
        body.style.position = 'relative';
        const loader = document.createElement('div');
        loader.id = 'drawerLoaderOverlay';
        loader.style = 'position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.7); display:flex; align-items:center; justify-content:center; z-index:10; backdrop-filter:blur(2px);';
        loader.innerHTML = '<div class="lms-modal-loader" style="padding:0;"><span class="btn-spinner"></span></div>';
        body.appendChild(loader);
    }

    function closeQuizDrawer() {
        document.getElementById('lmsQuizDrawer').classList.remove('active');
        setTimeout(() => { document.getElementById('lmsQuizDrawerBody').innerHTML = ''; }, 400);
    }

    function bindDrawerListeners() {
        const overlay = document.getElementById('lmsQuizDrawer');
        const body = document.getElementById('lmsQuizDrawerBody');

        if (!overlay || !body || overlay.dataset.listenersBound === '1') {
            return;
        }

        overlay.dataset.listenersBound = '1';

        // Scoped click handling for links rendered inside the drawer.
        body.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link || !body.contains(link)) return;
            if (link.target === '_blank' || link.hasAttribute('download')) return;
            if (link.hasAttribute('onclick') || !link.href || link.href.startsWith('javascript:')) return;

            e.preventDefault();
            showDrawerLoader();
            fetch(link.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => {
                if (!r.ok) throw new Error('Network response was not ok');
                return r.text();
            })
            .then(html => {
                setDrawerContent(html);
            })
            .catch(err => {
                console.error('Drawer Link Error:', err);
                body.innerHTML = '<div class="lms-empty" style="padding: 2rem;">' +
                                 '<p style="color:var(--danger-color); font-weight:600;">Failed to load content.</p>' +
                                 '<p style="font-size:0.9rem; color:#666;">' + (err.message || 'An error occurred.') + '</p>' +
                                 '<button onclick="openQuizDrawer(\'' + link.href + '\')" class="lms-btn lms-btn-muted" style="margin-top:1rem;">Retry</button>' +
                                 '</div>';
            });
        });

        // Scoped change handling fallback for select-all checkbox within the drawer.
        body.addEventListener('change', function(e) {
            if (e.target.id !== 'selectAllBank') return;
            const isChecked = e.target.checked;
            body.querySelectorAll('.bank-checkbox:not(:disabled)').forEach(cb => {
                cb.checked = isChecked;
            });
        });

        // Scoped form submission handling for drawer forms.
        body.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form || !body.contains(form)) return;

            const method = (form.method || 'GET').toUpperCase();
            e.preventDefault();
            const formData = new FormData(form);
            let url = form.action || window.location.href;

            if (method === 'GET') {
                const params = new URLSearchParams(formData).toString();
                url = url.split('?')[0] + '?' + params;
                showDrawerLoader();
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text()).then(html => {
                    setDrawerContent(html);
                    if (typeof reinitializeJavaScript === 'function') reinitializeJavaScript();
                });
                return;
            }

            const buttons = form.querySelectorAll('button[type="submit"]');
            buttons.forEach(btn => {
                btn.classList.add('btn-submitting');
                btn.disabled = true;
            });

            showDrawerLoader();

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => {
                if (r.status === 422) {
                    return r.json().then(data => ({
                        success: false,
                        errors: data.errors,
                        message: data.message
                    }));
                }
                if (r.redirected) return { success: true, redirect: true };
                return r.json();
            })
            .then(data => {
                if (data.success) {
                    if (typeof showToast === 'function') showToast(data.message || 'Success!', 'success');
                    closeQuizDrawer();
                    setTimeout(() => {
                        if (typeof loadContent === 'function') {
                            loadContent(window.location.href);
                        } else {
                            window.location.reload();
                        }
                    }, 500);
                } else {
                    if (data.errors) {
                        displayErrorsInDrawer(data.errors, form);
                        if (typeof showToast === 'function') showToast('Please correct the errors.', 'error');
                    } else if (data.message) {
                        if (typeof showToast === 'function') showToast(data.message, 'error');
                    } else {
                        if (typeof showToast === 'function') showToast('An error occurred. Please try again.', 'error');
                    }
                }
            })
            .catch(err => {
                console.error('Drawer Submit Error:', err);
                if (typeof showToast === 'function') showToast('An error occurred. Please try again.', 'error');
            })
            .finally(() => {
                buttons.forEach(btn => {
                    btn.classList.remove('btn-submitting');
                    btn.disabled = false;
                });
            });
        });
    }

    function quickAdd(event, id, btn = null) {
        const form = document.getElementById('singleAddBankForm');
        if (!form) return;

        const targetBtn = btn || event.currentTarget;
        if (!targetBtn) return;
        
        // Show local loading state on the clicked button
        const originalHtml = targetBtn.innerHTML;
        targetBtn.innerHTML = '<span class="btn-spinner" style="width:12px; height:12px; border-width:2px;"></span>';
        targetBtn.disabled = true;

        const input = document.getElementById('singleAddId');
        if (input) input.value = id;
        
        // Safety: Reset button after 5 seconds
        setTimeout(() => {
            if (targetBtn.disabled) {
                targetBtn.innerHTML = originalHtml;
                targetBtn.disabled = false;
            }
        }, 5000);

        // Manually trigger the submit event
        form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
    }

    function navigateQuestionBank(url, title = 'Browse Question Bank') {
        openQuizDrawer(url, title);
    }

    bindDrawerListeners();

    function displayErrorsInDrawer(errors, form) {
        // Clear existing errors
        form.querySelectorAll('.lms-error-msg').forEach(e => e.remove());
        form.querySelectorAll('.lms-input-error').forEach(e => e.classList.remove('lms-input-error'));

        Object.keys(errors).forEach(field => {
            const input = form.querySelector(`[name="${field}"]`) || form.querySelector(`[name="${field}[]"]`);
            if (input) {
                input.classList.add('lms-input-error');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'lms-error-msg';
                errorDiv.style.color = '#dc3545';
                errorDiv.style.fontSize = '0.75rem';
                errorDiv.style.marginTop = '4px';
                errorDiv.style.fontWeight = '600';
                errorDiv.textContent = errors[field][0];
                input.parentNode.appendChild(errorDiv);
            }
        });
    }
</script>
@endsection
