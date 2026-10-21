@php
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#2563eb';
    $borderRadius = $settings?->border_radius ?? 12;
    $transitions = collect($bundleTransitions ?? []);
    // Only surface a card when there is something to act on.
    $pendingLicense = $transitions->firstWhere('awaiting_license', true);
    $inFlight = $transitions->first(fn ($t) => in_array($t['state'], ['pending', 'progressing'], true));
    $blocked = $transitions->firstWhere('state', 'rejected');
    $highlight = $pendingLicense ?? $blocked ?? $inFlight;
@endphp

@if($highlight)
<style>
    .bundle-transition-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        border: 2px solid {{ $primaryColor }}22;
        border-radius: {{ $borderRadius }}px;
        padding: 20px 24px;
        margin-bottom: 24px;
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .bundle-transition-copy { flex: 1 1 320px; }

    .bundle-transition-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #111827;
        margin: 0 0 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .bundle-transition-title svg { width: 20px; height: 20px; fill: {{ $primaryColor }}; }

    .bundle-transition-desc {
        font-size: 0.875rem;
        color: #6b7280;
        line-height: 1.5;
        margin: 0;
    }

    .bundle-transition-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

    .bundle-state-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .bundle-state-pending { background: #fef3c7; color: #92400e; }
    .bundle-state-progressing { background: #d1fae5; color: #065f46; }
    .bundle-state-rejected { background: #fee2e2; color: #991b1b; }

    .bundle-btn {
        border: none;
        cursor: pointer;
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 600;
        background: {{ $primaryColor }};
        color: #fff;
        transition: filter 0.2s ease;
    }

    .bundle-btn:hover { filter: brightness(1.08); }

    /* Pop-up */
    .bundle-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(17, 24, 39, 0.6);
        z-index: 1200;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .bundle-modal-backdrop.is-open { display: flex; }

    .bundle-modal {
        background: #fff;
        border-radius: {{ $borderRadius }}px;
        width: 100%;
        max-width: 520px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
    }

    .bundle-modal-head {
        padding: 20px 24px 12px;
        border-bottom: 1px solid #f3f4f6;
    }

    .bundle-modal-head h3 { margin: 0; font-size: 1.1rem; color: #111827; }

    .bundle-modal-head p { margin: 4px 0 0; font-size: 0.85rem; color: #6b7280; }

    .bundle-modal-body { padding: 20px 24px; }

    .bundle-field { margin-bottom: 16px; }

    .bundle-field label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .bundle-field input[type="file"],
    .bundle-field textarea {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 0.875rem;
        font-family: inherit;
    }

    .bundle-field textarea { resize: vertical; min-height: 70px; }

    .bundle-field-hint { font-size: 0.75rem; color: #9ca3af; margin-top: 4px; }

    .bundle-modal-error {
        display: none;
        background: #fee2e2;
        color: #991b1b;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 0.8rem;
        margin-bottom: 14px;
    }

    .bundle-modal-error.is-visible { display: block; }

    .bundle-modal-foot {
        padding: 0 24px 20px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .bundle-btn-ghost {
        background: #f3f4f6;
        color: #374151;
    }

    .bundle-btn[disabled] { opacity: 0.6; cursor: not-allowed; }
</style>

<div class="bundle-transition-card" data-enrollment-id="{{ $highlight['enrollment_id'] }}">
    <div class="bundle-transition-copy">
        <h3 class="bundle-transition-title">
            <svg viewBox="0 0 24 24"><path d="M12,2L4.5,20.29L5.21,21L12,18L18.79,21L19.5,20.29L12,2Z"/></svg>
            Bundle Transition &mdash; {{ $highlight['course_title'] }}
        </h3>

        @if($highlight['state'] === 'progressing')
            <p class="bundle-transition-desc">Your school confirmed your license. The practical phase of your bundle is unlocked.</p>
        @elseif($highlight['state'] === 'pending')
            <p class="bundle-transition-desc">Your student license was submitted and is waiting for your school to confirm the practical phase.</p>
        @elseif($highlight['state'] === 'rejected')
            <p class="bundle-transition-desc">
                Your license was not accepted{{ $highlight['rejection_reason'] ? ': ' . $highlight['rejection_reason'] : '.' }}
                Please upload a corrected copy to continue.
            </p>
        @else
            <p class="bundle-transition-desc">Upload your student driver's license so your school can confirm your transition to the practical phase of this bundle.</p>
        @endif
    </div>

    <div class="bundle-transition-actions">
        @if($highlight['state'])
            <span class="bundle-state-pill bundle-state-{{ $highlight['state'] }}">
                {{ $highlight['state_label'] }}
            </span>
        @endif

        @if($highlight['awaiting_license'] || $highlight['state'] === 'rejected')
            <button type="button" class="bundle-btn" onclick="openBundleTransitionModal()">
                {{ $highlight['state'] === 'rejected' ? 'Upload a New License' : 'Upload Student License' }}
            </button>
        @endif
    </div>
</div>

<div class="bundle-modal-backdrop" id="bundleTransitionModal" role="dialog" aria-modal="true"
    aria-labelledby="bundleTransitionTitle" data-enrollment-id="{{ ($pendingLicense ?? $blocked ?? $highlight)['enrollment_id'] }}">
    <div class="bundle-modal">
        <div class="bundle-modal-head">
            <h3 id="bundleTransitionTitle">Submit Your Student License</h3>
            <p>Your school will review this and confirm your transition to the practical phase.</p>
        </div>

        <form id="bundleTransitionForm" method="POST"
            action="{{ school_route('student.submitTransitionLicense') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="enrollment_id"
                value="{{ ($pendingLicense ?? $blocked ?? $highlight)['enrollment_id'] }}">

            <div class="bundle-modal-body">
                <div class="bundle-modal-error" id="bundleTransitionError"></div>

                <div class="bundle-field">
                    <label for="bundleLicenseFile">Student Driver's License</label>
                    <input type="file" name="student_license" id="bundleLicenseFile"
                        accept=".pdf,.jpg,.jpeg,.png" required>
                    <div class="bundle-field-hint">PDF, JPG, or PNG. Maximum 5MB.</div>
                </div>

                <div class="bundle-field">
                    <label for="bundleLicenseNotes">Notes for your school (optional)</label>
                    <textarea name="notes" id="bundleLicenseNotes" maxlength="500"
                        placeholder="Anything your school should know about this document?"></textarea>
                </div>
            </div>

            <div class="bundle-modal-foot">
                <button type="button" class="bundle-btn bundle-btn-ghost" onclick="closeBundleTransitionModal()">Cancel</button>
                <button type="submit" class="bundle-btn" id="bundleTransitionSubmit">Submit for Confirmation</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('bundleTransitionModal');
        const form = document.getElementById('bundleTransitionForm');
        const errorBox = document.getElementById('bundleTransitionError');
        const submitBtn = document.getElementById('bundleTransitionSubmit');

        if (!modal || !form) return;

        window.openBundleTransitionModal = function () {
            errorBox.classList.remove('is-visible');
            errorBox.textContent = '';
            modal.classList.add('is-open');
        };

        window.closeBundleTransitionModal = function () {
            modal.classList.remove('is-open');
        };

        modal.addEventListener('click', function (event) {
            if (event.target === modal) window.closeBundleTransitionModal();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') window.closeBundleTransitionModal();
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            errorBox.classList.remove('is-visible');
            submitBtn.disabled = true;
            const originalLabel = submitBtn.textContent;
            submitBtn.textContent = 'Submitting…';

            fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: new FormData(form),
            })
                .then(async (response) => {
                    const data = await response.json().catch(() => ({}));

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'We could not submit your license. Please try again.');
                    }

                    return data;
                })
                .then(() => {
                    window.closeBundleTransitionModal();
                    // Reload so the card reflects the new Pending state.
                    window.location.reload();
                })
                .catch((error) => {
                    errorBox.textContent = error.message;
                    errorBox.classList.add('is-visible');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalLabel;
                });
        });
    })();
</script>
@endif
