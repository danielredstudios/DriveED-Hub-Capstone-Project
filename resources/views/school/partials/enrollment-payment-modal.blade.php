@php
    $epmSchool = $school ?? $currentSchool ?? null;
    $epmSettings = $epmSchool?->schoolSetting;
    $epmPrimaryColor = $epmSettings?->primary_color ?? '#2563eb';
    $epmGcash = $gcashSetting ?? ($epmSchool ? \App\Models\GCashSetting::getActiveSetting($epmSchool->id) : null);
@endphp

<!-- Enrollment Payment Receipt Pop-up Modal -->
<div class="modal-overlay enrollment-payment-modal" id="enrollmentPaymentModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="epmModalHeader">
    <div class="modal-dialog-content">
        <div class="modal-header-bar">
            <h5 class="modal-title" id="epmModalHeader">
                <i class="fas fa-receipt"></i>
                <span id="epmHeaderTitle">Submit Payment Receipt</span>
            </h5>
            <button type="button" class="modal-close-btn" data-epm-close aria-label="Close">&times;</button>
        </div>

        <form id="enrollmentPaymentForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="payment_method" id="epmPaymentMethod" value="gcash">

            <div class="modal-body-area">
                <!-- Course & Amount Banner -->
                <div class="epm-summary-card">
                    <div class="epm-summary-info">
                        <span class="epm-label">Enrollment Target</span>
                        <h4 class="epm-course-title" id="epmCourseTitle">Course Enrollment</h4>
                    </div>
                    <div class="epm-summary-amount">
                        <span class="epm-label">Amount Due</span>
                        <div class="epm-price">&#8369;<span id="epmAmount">0.00</span></div>
                    </div>
                </div>

                <!-- Payment Method Toggle -->
                <div class="epm-form-group">
                    <label class="epm-field-label">Payment Method <span class="required-star">*</span></label>
                    <div class="epm-method-pills">
                        <button type="button" class="epm-method-pill active" data-method="gcash" id="epmMethodGcash">
                            <i class="fas fa-mobile-alt"></i> GCash Transfer
                        </button>
                        <button type="button" class="epm-method-pill" data-method="on_site" id="epmMethodOnSite">
                            <i class="fas fa-store"></i> Pay in Person (Cash / OR)
                        </button>
                    </div>
                </div>

                <!-- GCash Payment Info Box -->
                <div class="epm-instructions-card" id="epmGcashDetails">
                    <div class="epm-gcash-grid">
                        @if($epmGcash && $epmGcash->qr_path && $epmSchool)
                        @php
                            $epmQrUrl = route('schools.guest.storage.gcash-qr', ['school' => $epmSchool->slug, 'gcashSetting' => $epmGcash->id]);
                        @endphp
                        <div class="epm-qr-column">
                            <div class="epm-qr-box qr-zoom-interactive" 
                                 onclick="openQrScanModal('{{ $epmQrUrl }}', { accountName: '{{ addslashes($epmGcash->account_name ?? 'School Official GCash') }}', accountNumber: '{{ addslashes($epmGcash->account_number ?? '') }}' })" 
                                 title="Click to open QR popup to scan" 
                                 style="cursor: zoom-in; position: relative;">
                                <img src="{{ $epmQrUrl }}" alt="GCash QR Code" class="epm-qr-img">
                                <span class="qr-zoom-hint-badge" style="font-size: 0.65rem; padding: 2px 6px;"><i class="fas fa-qrcode"></i> Scan</span>
                            </div>
                            <button type="button" 
                                    class="epm-qr-zoom-btn" 
                                    onclick="openQrScanModal('{{ $epmQrUrl }}', { accountName: '{{ addslashes($epmGcash->account_name ?? 'School Official GCash') }}', accountNumber: '{{ addslashes($epmGcash->account_number ?? '') }}' })" 
                                    title="Open QR in full-size scanner view">
                                <i class="fas fa-search-plus"></i> Tap to Zoom / Scan
                            </button>
                            <span class="epm-qr-caption">Scan with GCash</span>
                        </div>
                        @endif
                        <div class="epm-account-column">
                            <div class="epm-account-row">
                                <span class="epm-sublabel">Account Name</span>
                                <strong class="epm-acc-value">{{ $epmGcash->account_name ?? 'School Official GCash' }}</strong>
                            </div>
                            <div class="epm-account-row">
                                <span class="epm-sublabel">GCash Mobile Number</span>
                                <div class="epm-number-action">
                                    <span class="epm-acc-number" id="epmGcashNumber">{{ $epmGcash->account_number ?? '0919 345 6789' }}</span>
                                    @if($epmGcash && $epmGcash->account_number)
                                    <button type="button" class="epm-copy-btn" id="epmCopyBtn" onclick="copyEpmGcashNumber()" title="Copy GCash Number">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                    @endif
                                </div>
                            </div>
                            <p class="epm-tip">Send the exact course fee, then enter the 13-digit reference number and attach your confirmation receipt below.</p>
                        </div>
                    </div>
                </div>

                <!-- On-Site Instructions Box -->
                <div class="epm-instructions-card d-none" id="epmOnSiteDetails">
                    <div class="epm-onsite-notice">
                        <i class="fas fa-info-circle text-primary"></i>
                        <div>
                            <strong>School Cashier Payment</strong>
                            <p class="mb-0 text-muted small">Please visit the driving school administrative office to pay. Enter your official receipt (OR) number and attach a clear photo of your printed receipt below.</p>
                        </div>
                    </div>
                </div>

                <!-- Reference Number Input -->
                <div class="epm-form-group">
                    <label class="epm-field-label" id="epmRefLabel">
                        Transaction Reference Number <span class="required-star">*</span>
                    </label>
                    <input type="text" 
                           name="reference_number" 
                           id="epmRefInput" 
                           class="epm-input" 
                           placeholder="13-digit GCash reference (e.g. 1002345678901)" 
                           maxlength="13" 
                           minlength="13" 
                           pattern="[0-9]{13}" 
                           inputmode="numeric" 
                           required 
                           autocomplete="off">
                    <small class="epm-hint" id="epmRefHint">Numbers only, exactly 13 digits.</small>
                    <div class="epm-invalid-msg" id="epmRefFeedback"></div>
                </div>

                <!-- Screenshot / Receipt Photo Upload -->
                <div class="epm-form-group mb-0">
                    <label class="epm-field-label" id="epmFileLabel">
                        Upload Receipt Screenshot / Photo <span class="required-star">*</span>
                    </label>
                    <div class="epm-dropzone" id="epmDropzone">
                        <input type="file" 
                               name="screenshot" 
                               id="epmScreenshotInput" 
                               class="epm-file-input" 
                               accept="image/jpeg,image/png,image/jpg,image/webp" 
                               required>
                        <div class="epm-dropzone-content" id="epmDropzoneContent">
                            <i class="fas fa-cloud-upload-alt epm-upload-icon"></i>
                            <div class="epm-upload-text">
                                <span class="epm-upload-main">Click or drag receipt photo here</span>
                                <span class="epm-upload-sub">Supports JPG, PNG, WEBP (Max 5MB)</span>
                            </div>
                        </div>
                        <div class="epm-preview-box d-none" id="epmPreviewBox">
                            <img src="" alt="Receipt Preview" id="epmPreviewImg" class="epm-preview-thumbnail" style="cursor: zoom-in;" title="Click to inspect receipt full size" onclick="openImageLightbox(this.src, 'Uploaded Payment Receipt')">
                            <div class="epm-preview-meta">
                                <span class="epm-preview-filename" id="epmPreviewName">receipt.png</span>
                                <div style="display: flex; gap: 8px; align-items: center; margin-top: 4px;">
                                    <button type="button" class="epm-inspect-btn" onclick="openImageLightbox(document.getElementById('epmPreviewImg').src, 'Uploaded Payment Receipt')"><i class="fas fa-search-plus"></i> Zoom</button>
                                    <button type="button" class="epm-clear-file" id="epmClearFile" title="Remove file">&times; Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer-bar">
                <button type="button" class="epm-btn secondary" data-epm-close>Cancel</button>
                <button type="submit" class="epm-btn primary" id="epmSubmitBtn">
                    <i class="fas fa-check-circle"></i>
                    <span>Submit Receipt</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Enrollment Payment Modal - Flat Clean CSS */
.modal-overlay.enrollment-payment-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(4px);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    box-sizing: border-box;
}

.modal-overlay.enrollment-payment-modal.active {
    display: flex;
    animation: epmFadeIn 0.15s ease-out;
}

@keyframes epmFadeIn {
    from { opacity: 0; transform: scale(0.98); }
    to { opacity: 1; transform: scale(1); }
}

.enrollment-payment-modal .modal-dialog-content {
    background: #ffffff;
    width: 100%;
    max-width: 580px;
    max-height: 92vh;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.enrollment-payment-modal .modal-header-bar {
    background: {{ $epmPrimaryColor }};
    color: #ffffff;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.enrollment-payment-modal .modal-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 10px;
}

.enrollment-payment-modal .modal-close-btn {
    background: transparent;
    border: 0;
    color: #ffffff;
    font-size: 1.6rem;
    line-height: 1;
    cursor: pointer;
    opacity: 0.85;
    padding: 0 4px;
}

.enrollment-payment-modal .modal-close-btn:hover {
    opacity: 1;
}

.enrollment-payment-modal .modal-body-area {
    padding: 20px;
    overflow-y: auto;
    max-height: calc(92vh - 130px);
}

.enrollment-payment-modal .epm-summary-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-left: 4px solid {{ $epmPrimaryColor }};
    border-radius: 8px;
    padding: 12px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
}

.enrollment-payment-modal .epm-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748b;
    margin-bottom: 2px;
}

.enrollment-payment-modal .epm-course-title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
}

.enrollment-payment-modal .epm-summary-amount {
    text-align: right;
}

.enrollment-payment-modal .epm-price {
    font-size: 1.25rem;
    font-weight: 800;
    color: {{ $epmPrimaryColor }};
}

.enrollment-payment-modal .epm-form-group {
    margin-bottom: 16px;
}

.enrollment-payment-modal .epm-field-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 6px;
}

.enrollment-payment-modal .required-star {
    color: #dc2626;
}

.enrollment-payment-modal .epm-method-pills {
    display: flex;
    gap: 8px;
}

.enrollment-payment-modal .epm-method-pill {
    flex: 1;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #475569;
    padding: 9px 12px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.enrollment-payment-modal .epm-method-pill.active {
    background: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
}

.enrollment-payment-modal .epm-instructions-card {
    background: #f0f7ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    padding: 14px;
    margin-bottom: 16px;
}

.enrollment-payment-modal .epm-gcash-grid {
    display: flex;
    gap: 14px;
    align-items: center;
}

.enrollment-payment-modal .epm-qr-column {
    flex-shrink: 0;
    text-align: center;
}

.enrollment-payment-modal .epm-qr-box {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 4px;
    width: 108px;
    height: 108px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.enrollment-payment-modal .epm-qr-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}

.enrollment-payment-modal .epm-qr-zoom-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1d4ed8;
    border-radius: 9999px;
    font-size: 0.68rem;
    font-weight: 700;
    padding: 2px 8px;
    margin-top: 5px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.enrollment-payment-modal .epm-qr-zoom-btn:hover {
    background: #dbeafe;
    color: #1e40af;
    transform: translateY(-1px);
}

.enrollment-payment-modal .epm-inspect-btn {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #334155;
    border-radius: 4px;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 2px 6px;
    cursor: pointer;
}

.enrollment-payment-modal .epm-inspect-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.enrollment-payment-modal .epm-qr-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 4px;
}

.enrollment-payment-modal .epm-qr-caption {
    display: block;
    font-size: 0.7rem;
    font-weight: 700;
    color: #475569;
    margin-top: 4px;
}

.enrollment-payment-modal .epm-account-column {
    flex: 1;
}

.enrollment-payment-modal .epm-account-row {
    margin-bottom: 6px;
}

.enrollment-payment-modal .epm-sublabel {
    display: block;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
}

.enrollment-payment-modal .epm-acc-value {
    color: #0f172a;
    font-size: 0.95rem;
}

.enrollment-payment-modal .epm-number-action {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 2px;
}

.enrollment-payment-modal .epm-acc-number {
    font-size: 1.15rem;
    font-weight: 800;
    color: #1d4ed8;
    letter-spacing: 0.5px;
}

.enrollment-payment-modal .epm-copy-btn {
    background: #ffffff;
    border: 1px solid #93c5fd;
    color: #1d4ed8;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
}

.enrollment-payment-modal .epm-copy-btn:hover {
    background: #dbeafe;
}

.enrollment-payment-modal .epm-tip {
    margin: 8px 0 0 0;
    font-size: 0.78rem;
    color: #475569;
    line-height: 1.35;
}

.enrollment-payment-modal .epm-onsite-notice {
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.enrollment-payment-modal .epm-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.95rem;
    color: #0f172a;
    box-sizing: border-box;
    transition: border-color 0.15s ease;
}

.enrollment-payment-modal .epm-input:focus {
    border-color: #2563eb;
    outline: none;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.enrollment-payment-modal .epm-hint {
    display: block;
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 4px;
}

.enrollment-payment-modal .epm-invalid-msg {
    display: none;
    font-size: 0.75rem;
    color: #dc2626;
    margin-top: 4px;
}

.enrollment-payment-modal .epm-dropzone {
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    background: #f8fafc;
    padding: 16px;
    text-align: center;
    position: relative;
    cursor: pointer;
    transition: all 0.15s ease;
}

.enrollment-payment-modal .epm-dropzone:hover {
    border-color: #2563eb;
    background: #f0f7ff;
}

.enrollment-payment-modal .epm-file-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
}

.enrollment-payment-modal .epm-upload-icon {
    font-size: 1.8rem;
    color: #64748b;
    margin-bottom: 6px;
}

.enrollment-payment-modal .epm-upload-main {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
}

.enrollment-payment-modal .epm-upload-sub {
    display: block;
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 2px;
}

.enrollment-payment-modal .epm-preview-box {
    display: flex;
    align-items: center;
    gap: 12px;
    text-align: left;
    position: relative;
    z-index: 3;
}

.enrollment-payment-modal .epm-preview-thumbnail {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
}

.enrollment-payment-modal .epm-preview-meta {
    flex: 1;
}

.enrollment-payment-modal .epm-preview-filename {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #0f172a;
    word-break: break-all;
}

.enrollment-payment-modal .epm-clear-file {
    background: transparent;
    border: 0;
    color: #dc2626;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    padding: 0;
    margin-top: 4px;
}

.enrollment-payment-modal .modal-footer-bar {
    padding: 14px 20px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.enrollment-payment-modal .epm-btn {
    border: 0;
    border-radius: 8px;
    padding: 9px 18px;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.enrollment-payment-modal .epm-btn.secondary {
    background: #e2e8f0;
    color: #334155;
}

.enrollment-payment-modal .epm-btn.secondary:hover {
    background: #cbd5e1;
}

.enrollment-payment-modal .epm-btn.primary {
    background: #2563eb;
    color: #ffffff;
}

.enrollment-payment-modal .epm-btn.primary:hover {
    background: #1d4ed8;
}

.d-none { display: none !important; }
</style>

<script>
function copyEpmGcashNumber() {
    const numEl = document.getElementById('epmGcashNumber');
    if (!numEl) return;
    const text = numEl.textContent.trim().replace(/\s+/g, '');
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.getElementById('epmCopyBtn');
            if (btn) {
                const prev = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(() => { btn.innerHTML = prev; }, 2000);
            }
        });
    } else {
        const temp = document.createElement('textarea');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('enrollmentPaymentModal');
    const formEl = document.getElementById('enrollmentPaymentForm');
    const courseTitleEl = document.getElementById('epmCourseTitle');
    const amountEl = document.getElementById('epmAmount');
    const methodInput = document.getElementById('epmPaymentMethod');
    const gcashBtn = document.getElementById('epmMethodGcash');
    const onSiteBtn = document.getElementById('epmMethodOnSite');
    const gcashBox = document.getElementById('epmGcashDetails');
    const onSiteBox = document.getElementById('epmOnSiteDetails');
    const refInput = document.getElementById('epmRefInput');
    const refLabel = document.getElementById('epmRefLabel');
    const refHint = document.getElementById('epmRefHint');
    const refFeedback = document.getElementById('epmRefFeedback');
    const fileInput = document.getElementById('epmScreenshotInput');
    const dropzoneContent = document.getElementById('epmDropzoneContent');
    const previewBox = document.getElementById('epmPreviewBox');
    const previewImg = document.getElementById('epmPreviewImg');
    const previewName = document.getElementById('epmPreviewName');
    const clearFileBtn = document.getElementById('epmClearFile');
    const headerTitleEl = document.getElementById('epmHeaderTitle');
    const submitBtn = document.getElementById('epmSubmitBtn');

    if (!modalEl || !formEl) return;

    function setPaymentMethod(method) {
        methodInput.value = method;
        if (method === 'gcash') {
            gcashBtn.classList.add('active');
            onSiteBtn.classList.remove('active');
            gcashBox.classList.remove('d-none');
            onSiteBox.classList.add('d-none');
            refLabel.innerHTML = 'Transaction Reference Number <span class="required-star">*</span>';
            refInput.placeholder = '13-digit GCash reference (e.g. 1002345678901)';
            refInput.maxLength = 13;
            refInput.minLength = 13;
            refInput.pattern = '[0-9]{13}';
            refHint.textContent = 'Numbers only, exactly 13 digits.';
        } else {
            onSiteBtn.classList.add('active');
            gcashBtn.classList.remove('active');
            onSiteBox.classList.remove('d-none');
            gcashBox.classList.add('d-none');
            refLabel.innerHTML = 'Official Receipt (OR) Number <span class="required-star">*</span>';
            refInput.placeholder = '1 to 15 digit OR number';
            refInput.maxLength = 15;
            refInput.minLength = 1;
            refInput.pattern = '[0-9]{1,15}';
            refHint.textContent = 'Numbers only, 1–15 digits.';
        }
        validateRefInput();
    }

    if (gcashBtn) gcashBtn.addEventListener('click', () => setPaymentMethod('gcash'));
    if (onSiteBtn) onSiteBtn.addEventListener('click', () => setPaymentMethod('on_site'));

    function validateRefInput() {
        const val = refInput.value.replace(/[^0-9]/g, '');
        refInput.value = val;
        const isGcash = methodInput.value === 'gcash';
        if (isGcash && val.length > 0 && val.length !== 13) {
            refFeedback.textContent = 'GCash reference must be exactly 13 digits (' + val.length + '/13 entered).';
            refFeedback.style.display = 'block';
        } else {
            refFeedback.style.display = 'none';
        }
    }

    if (refInput) {
        refInput.addEventListener('input', validateRefInput);
    }

    // File Preview
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                previewName.textContent = file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewBox.classList.remove('d-none');
                    dropzoneContent.classList.add('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (clearFileBtn) {
        clearFileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            fileInput.value = '';
            previewBox.classList.add('d-none');
            dropzoneContent.classList.remove('d-none');
        });
    }

    function openModal(data) {
        if (data.submitUrl) formEl.action = data.submitUrl;
        if (data.courseTitle) courseTitleEl.textContent = data.courseTitle;
        if (data.amount) amountEl.textContent = data.amount;
        if (data.mode === 'reupload') {
            headerTitleEl.textContent = 'Re-upload Payment Receipt';
            if (submitBtn) submitBtn.querySelector('span').textContent = 'Re-submit Receipt';
        } else {
            headerTitleEl.textContent = 'Submit Payment Receipt';
            if (submitBtn) submitBtn.querySelector('span').textContent = 'Submit Receipt';
        }
        setPaymentMethod('gcash');
        modalEl.classList.add('active');
        modalEl.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modalEl.classList.remove('active');
        modalEl.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        formEl.reset();
        previewBox.classList.add('d-none');
        dropzoneContent.classList.remove('d-none');
        refFeedback.style.display = 'none';
    }

    // Close listeners
    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('.open-payment-modal, .open-reupload-receipt-modal');
        if (trigger) {
            e.preventDefault();
            openModal({
                submitUrl: trigger.getAttribute('data-submit-url'),
                courseTitle: trigger.getAttribute('data-course-title') || 'Course Enrollment',
                amount: trigger.getAttribute('data-amount') || '0.00',
                mode: trigger.getAttribute('data-mode') || 'initial'
            });
            return;
        }

        if (e.target.closest('[data-epm-close]')) {
            closeModal();
            return;
        }

        if (e.target === modalEl) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalEl.classList.contains('active')) {
            closeModal();
        }
    });

    // Auto-open trigger if session or URL flag is set
    const urlParams = new URLSearchParams(window.location.search);
    const autoOpen = urlParams.get('open_payment_modal') || '{{ session("open_payment_modal") }}';
    if (autoOpen) {
        const autoTrigger = document.querySelector('[data-enrollment-id="' + autoOpen + '"].open-payment-modal, [data-enrollment-id="' + autoOpen + '"].open-reupload-receipt-modal');
        if (autoTrigger) {
            autoTrigger.click();
        }
    }
});
</script>
