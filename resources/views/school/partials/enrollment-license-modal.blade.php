@php
    $elmSchool = $school ?? $currentSchool ?? null;
    $elmSettings = $elmSchool?->schoolSetting;
    $elmPrimaryColor = $elmSettings?->primary_color ?? '#2563eb';
@endphp

<!-- Enrollment License Re-upload Pop-up Modal -->
<div class="modal-overlay enrollment-license-modal" id="enrollmentLicenseModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="elmModalHeader">
    <div class="modal-dialog-content">
        <div class="modal-header-bar">
            <h5 class="modal-title" id="elmModalHeader">
                <i class="fas fa-id-card"></i>
                <span>Re-upload Student Driver's License</span>
            </h5>
            <button type="button" class="modal-close-btn" data-elm-close aria-label="Close">&times;</button>
        </div>

        <form id="enrollmentLicenseForm" method="POST" action="" enctype="multipart/form-data">
            @csrf

            <div class="modal-body-area">
                <!-- Rejection Notice Box -->
                <div class="elm-notice-card" id="elmRejectionNotice">
                    <div class="elm-notice-header">
                        <i class="fas fa-exclamation-circle text-danger"></i>
                        <strong class="elm-notice-title">Document Update Required</strong>
                    </div>
                    <p class="elm-notice-text" id="elmRejectionText">
                        Your previous license document was not accepted. Please upload a clear, uncropped copy or official LTO PDF to proceed with practical course verification.
                    </p>
                </div>

                <!-- LTO Regulatory Reminder -->
                <div class="elm-advisory-box">
                    <i class="fas fa-info-circle text-primary"></i>
                    <span>Official driver's licenses and renewals are issued exclusively by the Land Transportation Office (LTO). This upload is for school course qualification records.</span>
                </div>

                <!-- File Upload Dropzone -->
                <div class="elm-form-group mb-0">
                    <label class="elm-field-label">
                        Select Valid Student Driver's License <span class="required-star">*</span>
                    </label>
                    <div class="elm-dropzone" id="elmDropzone">
                        <input type="file" 
                               name="student_license" 
                               id="elmLicenseInput" 
                               class="elm-file-input" 
                               accept=".pdf,.jpg,.jpeg,.png" 
                               required>
                        <div class="elm-dropzone-content" id="elmDropzoneContent">
                            <i class="fas fa-id-card elm-upload-icon"></i>
                            <div class="elm-upload-text">
                                <span class="elm-upload-main">Click or drag license photo / PDF here</span>
                                <span class="elm-upload-sub">Supports PDF, JPG, PNG (Maximum 5MB)</span>
                            </div>
                        </div>
                        <div class="elm-preview-box d-none" id="elmPreviewBox">
                            <div class="elm-preview-icon" id="elmPreviewIcon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="elm-preview-meta">
                                <span class="elm-preview-filename" id="elmPreviewName">license.pdf</span>
                                <button type="button" class="elm-clear-file" id="elmClearFile" title="Remove file">&times; Remove file</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer-bar">
                <button type="button" class="elm-btn secondary" data-elm-close>Cancel</button>
                <button type="submit" class="elm-btn primary" id="elmSubmitBtn">
                    <i class="fas fa-upload"></i>
                    <span>Upload License</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Enrollment License Modal - Flat Clean CSS */
.modal-overlay.enrollment-license-modal {
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

.modal-overlay.enrollment-license-modal.active {
    display: flex;
    animation: elmFadeIn 0.15s ease-out;
}

@keyframes elmFadeIn {
    from { opacity: 0; transform: scale(0.98); }
    to { opacity: 1; transform: scale(1); }
}

.enrollment-license-modal .modal-dialog-content {
    background: #ffffff;
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.enrollment-license-modal .modal-header-bar {
    background: #dc2626;
    color: #ffffff;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.enrollment-license-modal .modal-title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 10px;
}

.enrollment-license-modal .modal-close-btn {
    background: transparent;
    border: 0;
    color: #ffffff;
    font-size: 1.6rem;
    line-height: 1;
    cursor: pointer;
    opacity: 0.85;
    padding: 0 4px;
}

.enrollment-license-modal .modal-close-btn:hover {
    opacity: 1;
}

.enrollment-license-modal .modal-body-area {
    padding: 20px;
    overflow-y: auto;
}

.enrollment-license-modal .elm-notice-card {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-left: 4px solid #ef4444;
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 14px;
}

.enrollment-license-modal .elm-notice-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}

.enrollment-license-modal .elm-notice-title {
    color: #991b1b;
    font-size: 0.95rem;
}

.enrollment-license-modal .elm-notice-text {
    color: #7f1d1d;
    font-size: 0.85rem;
    line-height: 1.45;
    margin: 0;
}

.enrollment-license-modal .elm-advisory-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 16px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 0.8rem;
    color: #475569;
    line-height: 1.4;
}

.enrollment-license-modal .elm-form-group {
    margin-bottom: 16px;
}

.enrollment-license-modal .elm-field-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 6px;
}

.enrollment-license-modal .required-star {
    color: #dc2626;
}

.enrollment-license-modal .elm-dropzone {
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    background: #f8fafc;
    padding: 20px 16px;
    text-align: center;
    position: relative;
    cursor: pointer;
    transition: all 0.15s ease;
}

.enrollment-license-modal .elm-dropzone:hover {
    border-color: #dc2626;
    background: #fff5f5;
}

.enrollment-license-modal .elm-file-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
}

.enrollment-license-modal .elm-upload-icon {
    font-size: 2rem;
    color: #94a3b8;
    margin-bottom: 8px;
}

.enrollment-license-modal .elm-upload-main {
    display: block;
    font-size: 0.88rem;
    font-weight: 600;
    color: #1e293b;
}

.enrollment-license-modal .elm-upload-sub {
    display: block;
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 3px;
}

.enrollment-license-modal .elm-preview-box {
    display: flex;
    align-items: center;
    gap: 12px;
    text-align: left;
    position: relative;
    z-index: 3;
}

.enrollment-license-modal .elm-preview-icon {
    width: 44px;
    height: 44px;
    border-radius: 6px;
    background: #fee2e2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
}

.enrollment-license-modal .elm-preview-meta {
    flex: 1;
}

.enrollment-license-modal .elm-preview-filename {
    display: block;
    font-size: 0.88rem;
    font-weight: 600;
    color: #0f172a;
    word-break: break-all;
}

.enrollment-license-modal .elm-clear-file {
    background: transparent;
    border: 0;
    color: #dc2626;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    padding: 0;
    margin-top: 4px;
}

.enrollment-license-modal .modal-footer-bar {
    padding: 14px 20px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.enrollment-license-modal .elm-btn {
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

.enrollment-license-modal .elm-btn.secondary {
    background: #e2e8f0;
    color: #334155;
}

.enrollment-license-modal .elm-btn.secondary:hover {
    background: #cbd5e1;
}

.enrollment-license-modal .elm-btn.primary {
    background: #dc2626;
    color: #ffffff;
}

.enrollment-license-modal .elm-btn.primary:hover {
    background: #b91c1c;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('enrollmentLicenseModal');
    const formEl = document.getElementById('enrollmentLicenseForm');
    const fileInput = document.getElementById('elmLicenseInput');
    const dropzoneContent = document.getElementById('elmDropzoneContent');
    const previewBox = document.getElementById('elmPreviewBox');
    const previewName = document.getElementById('elmPreviewName');
    const clearFileBtn = document.getElementById('elmClearFile');
    const rejectionTextEl = document.getElementById('elmRejectionText');

    if (!modalEl || !formEl) return;

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                previewName.textContent = file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
                previewBox.classList.remove('d-none');
                dropzoneContent.classList.add('d-none');
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
        if (data.licenseUrl) formEl.action = data.licenseUrl;
        if (data.rejectionReason && data.rejectionReason.trim().length > 0) {
            rejectionTextEl.textContent = data.rejectionReason;
        } else {
            rejectionTextEl.textContent = 'Your previous license document was not accepted. Please upload a clear, uncropped copy or official LTO PDF to proceed.';
        }
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
    }

    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('.open-license-modal, .open-reupload-license-modal');
        if (trigger) {
            e.preventDefault();
            openModal({
                licenseUrl: trigger.getAttribute('data-license-url'),
                rejectionReason: trigger.getAttribute('data-rejection-reason')
            });
            return;
        }

        if (e.target.closest('[data-elm-close]')) {
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
});
</script>
