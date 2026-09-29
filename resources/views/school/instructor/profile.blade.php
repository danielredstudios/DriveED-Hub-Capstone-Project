@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Instructor Profile')

@section('content')
    @php
        $school = $school ?? $currentSchool ?? null;
        $instructor = $instructor ?? Auth::guard('instructor')->user();
        $settings = $school?->schoolSetting;
        $primaryColor = $settings?->primary_color ?? '#667eea';
        $secondaryColor = $settings?->secondary_color ?? '#764ba2';
    @endphp

    @include('school.admin.partials.admin-styles')

    <style>
        .profile-card {
            max-width: 580px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            padding-top: 20px;
        }

        .status-badge-top {
            position: absolute;
            top: 16px;
            left: 16px;
            background: #10b981;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .profile-card-header {
            text-align: center;
            padding: 40px 30px 24px;
        }

        .profile-avatar-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background:
                {{ $primaryColor }}
            ;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .profile-avatar-circle:hover .avatar-upload-overlay {
            opacity: 1;
        }

        .avatar-upload-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
            cursor: pointer;
        }

        .avatar-upload-overlay span {
            color: white;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .profile-avatar-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-avatar-letter {
            font-size: 64px;
            font-weight: 700;
            color: white;
        }

        .profile-name {
            font-size: 1.35rem;
            font-weight: 600;
            color: #1f2937;
        }

        .profile-card-body {
            padding: 0 30px 24px;
        }

        .profile-field {
            display: grid;
            grid-template-columns: 130px 1fr;
            padding: 13px 0;
            border-bottom: 1px solid #f3f4f6;
            gap: 16px;
        }

        .profile-field:last-child {
            border-bottom: none;
        }

        .profile-field-label {
            font-weight: 600;
            color: #374151;
            font-size: 0.88rem;
        }

        .profile-field-value {
            color: #6b7280;
            font-size: 0.88rem;
        }

        .profile-actions {
            text-align: center;
            padding: 16px 30px 28px;
        }

        .btn-edit-profile {
            background:
                {{ $primaryColor }}
            ;
            color: white;
            border: none;
            padding: 11px 36px;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-edit-profile:hover {
            background:
                {{ $secondaryColor }}
            ;
            transform: translateY(-1px);
        }

        .edit-form {
            display: none;
            padding: 28px;
        }

        .form-field {
            margin-bottom: 18px;
        }

        .form-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #374151;
            font-size: 0.85rem;
        }

        .form-field input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.88rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-field input:focus {
            border-color:
                {{ $primaryColor }}
            ;
        }

        .contact-input-group {
            display: flex;
            align-items: stretch;
        }

        .contact-prefix {
            display: flex;
            align-items: center;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-right: none;
            padding: 0 12px;
            border-radius: 8px 0 0 8px;
            color: #4b5563;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .contact-input-group input {
            border-radius: 0 8px 8px 0 !important;
            flex: 1;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 24px;
        }

        .btn-save-profile {
            background:
                {{ $primaryColor }}
            ;
            color: white;
            border: none;
            padding: 11px 28px;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-save-profile:hover {
            background:
                {{ $secondaryColor }}
            ;
        }

        .btn-cancel-profile {
            background: #6b7280;
            color: white;
            border: none;
            padding: 11px 28px;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-cancel-profile:hover {
            background: #4b5563;
        }

        .btn-request-profile {
            background: #d97706;
            color: white;
            border: none;
            padding: 11px 36px;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-left: 10px;
        }

        .btn-request-profile:hover {
            background: #b45309;
            transform: translateY(-1px);
        }

        .alert {
            padding: 14px 16px;
            margin-bottom: 20px;
            border-radius: 10px;
            max-width: 580px;
            margin-left: auto;
            margin-right: auto;
            font-size: 0.88rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-warning {
            background: #fff4cc;
            color: #7a5a00;
            border: 1px solid #f6d365;
        }

        .policy-note {
            max-width: 580px;
            margin: 0 auto 20px;
        }

        .error-list-compact {
            margin: 0;
            padding-left: 20px;
        }

        .hidden-file-input {
            display: none;
        }

        .password-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .password-section-title {
            margin: 0 0 15px 0;
            font-size: 0.95rem;
            color: #374151;
            font-weight: 600;
        }

        .password-section-note {
            font-weight: 400;
            color: #9ca3af;
            font-size: 0.8rem;
        }

        .password-error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-bottom: 12px;
        }

        .password-error-text {
            color: #dc2626;
            font-size: 0.8rem;
            margin-top: 4px;
            display: block;
        }

        .btn-password-toggle {
            width: 100%;
            background: #f3f4f6;
            color: #1f2937;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.86rem;
            font-weight: 600;
            text-align: left;
            cursor: pointer;
        }

        .btn-password-toggle:hover {
            background: #e5e7eb;
        }

        .password-fields.hidden {
            display: none;
        }

        .request-status-note {
            margin-top: 10px;
            color: #6b7280;
            font-size: 0.85rem;
        }

        .locked-field-note {
            margin-top: 4px;
            color: #7a5a00;
            font-size: 0.8rem;
        }

        .field-help {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }

        .form-field textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.88rem;
            min-height: 96px;
            resize: vertical;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-field textarea:focus {
            border-color:
                {{ $primaryColor }}
            ;
        }

        @media (max-width: 768px) {
            .profile-avatar-circle {
                width: 120px;
                height: 120px;
            }

            .profile-avatar-letter {
                font-size: 50px;
            }

            .profile-name {
                font-size: 1.15rem;
            }

            .profile-card-body {
                padding: 0 20px 20px;
            }

            .profile-field {
                grid-template-columns: 1fr;
                gap: 4px;
                padding: 10px 0;
            }

            .profile-field-label {
                font-size: 0.78rem;
                color: #9ca3af;
            }

            .btn-edit-profile {
                width: 100%;
            }

            .btn-request-profile {
                width: 100%;
                margin-left: 0;
                margin-top: 10px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-save-profile,
            .btn-cancel-profile {
                width: 100%;
            }
        }
    </style>

    <div class="admin-container">
        <div class="page-header">
            <div class="page-header-left">
                <h1 class="page-title">Profile</h1>
                <p class="page-subtitle">View and manage your profile information</p>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @if(session('success'))
                    showToast("{{ session('success') }}", 'success');
                @endif
                @if(session('error'))
                    showToast("{{ session('error') }}", 'error');
                @endif
                @if($errors->any())
                    @foreach($errors->all() as $error)
                        showToast("{{ $error }}", 'error');
                    @endforeach
                @endif
            });
        </script>




        <div class="profile-card">
            <div class="status-badge-top">{{ ucfirst($instructor->status ?? 'Active') }}</div>

            <div class="profile-card-header">
                <div class="profile-avatar-circle" id="avatarContainer">
                    @if($instructor->profile_picture && file_exists(public_path('storage/' . $instructor->profile_picture)))
                        <img src="{{ asset('storage/' . $instructor->profile_picture) }}" alt="{{ $instructor->name }}"
                            id="avatarImage">
                    @else
                        <span class="profile-avatar-letter"
                            id="avatarLetter">{{ strtoupper(substr($instructor->name ?? 'I', 0, 1)) }}</span>
                    @endif
                    <div class="avatar-upload-overlay" onclick="document.getElementById('profilePictureInput').click()">
                        <span>Change Photo</span>
                    </div>
                </div>
                <input type="file" id="profilePictureInput" accept="image/png,image/jpg,image/jpeg,image/webp"
                    class="hidden-file-input" onchange="uploadProfilePicture(this)">
                <div class="profile-name">{{ $instructor->name ?? "Instructor's Name" }}</div>
            </div>

            <div id="profileView">

                <div class="profile-card-body">
                    <!-- License Upload Section -->
                    <div class="profile-field align-items-center" style="padding-top: 5px;">
                        <div class="profile-field-label">License Upload:</div>
                        <div class="profile-field-value d-flex flex-column gap-2">
                            @php
                                $statusColors = [
                                    'none' => 'secondary',
                                    'pending' => 'warning text-dark',
                                    'verified' => 'success',
                                    'rejected' => 'danger'
                                ];
                                $statusLabels = [
                                    'none' => 'Not Uploaded',
                                    'pending' => 'Pending Review',
                                    'verified' => 'Verified',
                                    'rejected' => 'Rejected'
                                ];
                                $currentStatus = $instructor->license_status ?? 'none';
                            @endphp

                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-{{ $statusColors[$currentStatus] }}" style="font-size: 0.7rem;">
                                    {{ $statusLabels[$currentStatus] }}
                                </span>

                                @if($currentStatus === 'verified' && !empty($instructor->restriction_codes))
                                    <span class="badge border border-primary text-primary bg-white" style="font-size: 0.7rem;">
                                        Codes: {{ implode(', ', $instructor->restriction_codes) }}
                                    </span>
                                @endif
                            </div>

                            @if($instructor->license_status === 'rejected' && $instructor->license_rejection_reason)
                                <div class="text-danger small mt-1" style="font-size: 0.8rem;">
                                    <strong>Rejected:</strong> {{ $instructor->license_rejection_reason }}
                                </div>
                            @endif

                            @if($instructor->license_image)
                                <div class="mt-1 mb-1">
                                    <img src="{{ asset('storage/' . $instructor->license_image) }}" alt="License Preview"
                                        class="rounded border shadow-sm"
                                        style="max-width: 180px; max-height: 120px; object-fit: cover; cursor: pointer; transition: transform 0.2s;"
                                        onmouseover="this.style.transform='scale(1.02)'"
                                        onmouseout="this.style.transform='scale(1)'"
                                        onclick="showImagePreviewModal('{{ asset('storage/' . $instructor->license_image) }}')"
                                        title="Click to enlarge">
                                </div>
                            @endif

                            @if($currentStatus !== 'pending' && $currentStatus !== 'verified')
                                <div class="mt-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary fw-bold"
                                        onclick="document.getElementById('licenseUploadInput').click()"
                                        style="padding: 5px 16px; font-size: 0.8rem; border-radius: 6px;">
                                        <i class="bi bi-upload me-1"></i>
                                        {{ $instructor->license_image ? 'Update Image' : 'Upload File' }}
                                    </button>
                                    <input type="file" id="licenseUploadInput" class="hidden-file-input"
                                        accept="image/png,image/jpeg,image/jpg,image/webp" onchange="uploadLicense(this)">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="profile-field">
                        <div class="profile-field-label">Email:</div>
                        <div class="profile-field-value">{{ $instructor->email ?? 'N/A' }}</div>
                    </div>

                    <div class="profile-field">
                        <div class="profile-field-label">Contact:</div>
                        <div class="profile-field-value">{{ $instructor->contact ?? 'N/A' }}</div>
                    </div>

                    <div class="profile-field">
                        <div class="profile-field-label">Availability:</div>
                        <div class="profile-field-value">
                            @if(($instructor->availability ?? 'available') === 'available')
                                <span class="badge bg-success" style="font-size: 0.75rem; padding: 4px 10px; border-radius: 6px;">
                                    <i class="bi bi-check-circle me-1"></i> Available
                                </span>
                            @else
                                <span class="badge bg-secondary" style="font-size: 0.75rem; padding: 4px 10px; border-radius: 6px;">
                                    <i class="bi bi-dash-circle me-1"></i> Unavailable
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="profile-field">
                        <div class="profile-field-label">Address:</div>
                        <div class="profile-field-value">{{ $instructor->address ?? 'N/A' }}</div>
                    </div>

                    <div class="profile-field">
                        <div class="profile-field-label">License Number:</div>
                        <div class="profile-field-value">{{ $instructor->license_number ?? 'N/A' }}</div>
                    </div>
                    <div class="profile-field">
                        <div class="profile-field-label">Branch:</div>
                        <div class="profile-field-value">{{ $instructor->branch->name ?? 'Not Assigned' }}</div>
                    </div>
                </div>

                <div class="profile-actions">
                    <button type="button" class="btn-edit-profile" onclick="showEditForm()">Edit Profile</button>
                </div>
            </div>

            <div id="editForm" class="edit-form">
                <form method="POST" action="{{ $schoolRoute('instructor.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-field">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $instructor->name) }}" required>
                    </div>

                    <div class="form-field">
                        <label for="email">Email <span class="required-indicator">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email', $instructor->email) }}" required>
                    </div>

                    <div class="form-field">
                        <label for="contact">Contact <span class="required-indicator">*</span></label>
                        @if($settings->enforce_ph_contact ?? true)
                            <div class="contact-input-group">
                                <span class="contact-prefix">+63</span>
                                <input type="text" id="contact" name="contact"
                                    value="{{ old('contact', $instructor->contact) }}" inputmode="numeric" pattern="[0-9]*"
                                    autocomplete="tel" maxlength="10" required placeholder="9123456789">
                            </div>
                            <p class="field-help">Enter the 10-digit number after +63 (e.g., 9123456789).</p>
                        @else
                            <input type="text" id="contact" name="contact" value="{{ old('contact', $instructor->contact) }}" required placeholder="Enter contact number">
                        @endif
                    </div>

                    <div class="form-field">
                        <label for="address">Address <span class="required-indicator">*</span></label>
                        <input type="text" id="address" name="address" value="{{ old('address', $instructor->address) }}"
                            required>
                    </div>

                    <div class="form-field">
                        <label for="license_number">License Number <span class="required-indicator">*</span></label>
                        <input type="text" id="license_number" name="license_number"
                            value="{{ old('license_number', $instructor->license_number) }}" required>
                    </div>

                    <div class="form-field">
                        <label for="availability">Teaching Availability <span class="required-indicator">*</span></label>
                        <select id="availability" name="availability" class="form-control" style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 0.88rem; outline: none; background: white;" required>
                            <option value="available" {{ old('availability', $instructor->availability) === 'available' ? 'selected' : '' }}>Available (Visible for Bookings)</option>
                            <option value="unavailable" {{ old('availability', $instructor->availability) === 'unavailable' ? 'selected' : '' }}>Unavailable (Hidden from Bookings)</option>
                        </select>
                        <p class="field-help" style="margin-top: 6px; font-size: 0.8rem; color: #6b7280;">When set to "Unavailable", you will not appear in the booking schedules for students.</p>
                    </div>


                    <div class="password-section">
                        <h4 class="password-section-title">Password <span class="password-section-note">(optional)</span>
                        </h4>

                        <div class="form-field">
                            <button type="button" class="btn-password-toggle" id="instructorPasswordToggleBtn"
                                onclick="toggleInstructorPasswordFields()">
                                Change Password
                            </button>
                        </div>

                        <div class="password-fields hidden" id="instructorPasswordFields">
                            @error('current_password')
                                <div class="password-error-box">{{ $message }}</div>
                            @enderror

                            <div class="form-field">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password"
                                    placeholder="Enter current password" autocomplete="current-password">
                            </div>

                            <div class="form-field">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password"
                                    placeholder="Min 8 chars, uppercase, lowercase, number" autocomplete="new-password"
                                    oninput="handleInstructorNewPasswordInput()">
                                @error('new_password')
                                    <span class="password-error-text">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-field" id="instructorConfirmPasswordField" style="display: none;">
                                <label for="new_password_confirmation">Confirm New Password</label>
                                <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                                    placeholder="Re-enter new password" autocomplete="new-password">
                                @error('new_password_confirmation')
                                    <span class="password-error-text">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-save-profile">Save Changes</button>
                        <button type="button" class="btn-cancel-profile" onclick="hideEditForm()">Cancel</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        function enforceNumericOnly(input) {
            if (!input) return;
            const sanitize = function () {
                let val = input.value.replace(/\D+/g, '');
                if (val.startsWith('0')) {
                    val = val.substring(1);
                }
                input.value = val;
            };
            input.addEventListener('input', sanitize);
            input.addEventListener('paste', function () { setTimeout(sanitize, 0); });
        }

        function showEditForm() {
            // Strip +63 or leading 0 from contact for display
            const contactInput = document.getElementById('contact');
            if (contactInput) {
                let val = contactInput.value;
                if (val.startsWith('+63')) val = val.substring(3);
                else if (val.startsWith('0')) val = val.substring(1);
                contactInput.value = val;
            }

            document.getElementById('profileView').style.display = 'none';
            document.getElementById('editForm').style.display = 'block';
        }

        function hideEditForm() {
            document.getElementById('profileView').style.display = 'block';
            document.getElementById('editForm').style.display = 'none';
            closeInstructorPasswordFields();
        }


        function toggleInstructorPasswordFields(forceOpen = null) {
            const fields = document.getElementById('instructorPasswordFields');
            const button = document.getElementById('instructorPasswordToggleBtn');
            if (!fields || !button) return;

            const shouldOpen = forceOpen === null ? fields.classList.contains('hidden') : forceOpen;

            if (shouldOpen) {
                fields.classList.remove('hidden');
                button.textContent = 'Hide Password Change';
            } else {
                closeInstructorPasswordFields();
            }

            handleInstructorNewPasswordInput();
        }

        function handleInstructorNewPasswordInput() {
            const newPassword = document.getElementById('new_password');
            const currentPassword = document.getElementById('current_password');
            const confirmField = document.getElementById('instructorConfirmPasswordField');
            const confirmInput = document.getElementById('new_password_confirmation');

            if (!newPassword || !currentPassword || !confirmField || !confirmInput) return;

            const hasNewPassword = newPassword.value.trim().length > 0;
            confirmField.style.display = hasNewPassword ? 'block' : 'none';
            currentPassword.required = hasNewPassword;
            confirmInput.required = hasNewPassword;
        }

        function closeInstructorPasswordFields() {
            const fields = document.getElementById('instructorPasswordFields');
            const button = document.getElementById('instructorPasswordToggleBtn');
            const currentPassword = document.getElementById('current_password');
            const newPassword = document.getElementById('new_password');
            const confirmInput = document.getElementById('new_password_confirmation');
            const confirmField = document.getElementById('instructorConfirmPasswordField');

            if (fields) fields.classList.add('hidden');
            if (button) button.textContent = 'Change Password';

            if (currentPassword) {
                currentPassword.value = '';
                currentPassword.required = false;
            }
            if (newPassword) {
                newPassword.value = '';
            }
            if (confirmInput) {
                confirmInput.value = '';
                confirmInput.required = false;
            }
            if (confirmField) {
                confirmField.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            @if($settings->enforce_ph_contact ?? true)
            enforceNumericOnly(document.getElementById('contact'));
            @endif

            const hasErrors = {{ $errors->any() ? 'true' : 'false' }};
            const hasPasswordErrors = {{ ($errors->has('current_password') || $errors->has('new_password') || $errors->has('new_password_confirmation')) ? 'true' : 'false' }};

            if (hasErrors) {
                showEditForm();
            }

            if (hasPasswordErrors) {
                toggleInstructorPasswordFields(true);
                const confirmField = document.getElementById('instructorConfirmPasswordField');
                if (confirmField) confirmField.style.display = 'block';
            }

            handleInstructorNewPasswordInput();
        });

        function uploadProfilePicture(input) {
            if (!input.files || !input.files[0]) return;

            const file = input.files[0];

            // Validate file type
            const allowedTypes = ['image/png', 'image/jpg', 'image/jpeg', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                showToast('Please upload a valid image file (PNG, JPG, JPEG, or WebP).', 'error');
                input.value = '';
                return;
            }

            // Validate file size
            const maxFileSizeMB = {{ $settings->max_file_size_mb ?? 5 }};
            if (file.size > maxFileSizeMB * 1024 * 1024) {
                showToast('File size must be less than ' + maxFileSizeMB + 'MB.', 'error');
                input.value = '';
                return;
            }

            const formData = new FormData();
            formData.append('profile_picture', file);
            formData.append('_token', '{{ csrf_token() }}');

            // Show loading state
            const overlay = document.querySelector('.avatar-upload-overlay span');
            const originalText = overlay.textContent;
            overlay.textContent = 'Uploading...';

            fetch('{{ $schoolRoute('instructor.profile.picture') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update avatar image
                        const avatarContainer = document.getElementById('avatarContainer');
                        const existingImg = document.getElementById('avatarImage');
                        const existingLetter = document.getElementById('avatarLetter');

                        if (existingImg) {
                            existingImg.src = '/storage/' + data.path + '?t=' + new Date().getTime();
                        } else if (existingLetter) {
                            existingLetter.remove();
                            const newImg = document.createElement('img');
                            newImg.src = '/storage/' + data.path + '?t=' + new Date().getTime();
                            newImg.alt = '{{ $instructor->name }}';
                            newImg.id = 'avatarImage';
                            newImg.style.width = '100%';
                            newImg.style.height = '100%';
                            newImg.style.objectFit = 'cover';
                            avatarContainer.insertBefore(newImg, avatarContainer.firstChild);
                        }

                        overlay.textContent = originalText;
                        showToast(data.message, 'success');
                    } else {
                        overlay.textContent = originalText;
                        showToast(data.message || 'Failed to upload profile picture.', 'error');
                    }
                })
                .catch(error => {
                    overlay.textContent = originalText;
                    console.error('Error:', error);
                    showToast('An error occurred while uploading the profile picture.', 'error');
                })
                .finally(() => {
                    input.value = '';
                });
        }



        function uploadLicense(input) {
            if (!input.files || !input.files[0]) return;

            const file = input.files[0];

            // Validate file type
            const allowedTypes = ['image/png', 'image/jpg', 'image/jpeg', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                showToast('Please upload a valid image file (PNG, JPG, JPEG, or WebP).', 'error');
                input.value = '';
                return;
            }

            // Validate file size
            const maxFileSizeMB = {{ $settings->max_file_size_mb ?? 5 }};
            if (file.size > maxFileSizeMB * 1024 * 1024) {
                showToast('File size must be less than ' + maxFileSizeMB + 'MB.', 'error');
                input.value = '';
                return;
            }

            const formData = new FormData();
            formData.append('license_image', file);
            formData.append('_token', '{{ csrf_token() }}');

            // Show loading state
            let originalBtnText = 'Upload File';
            const btn = event.target.tagName === 'BUTTON' ? event.target : event.target.previousElementSibling;
            if (btn) {
                originalBtnText = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Uploading...';
                btn.disabled = true;
            }

            fetch('{{ $schoolRoute('instructor.uploadLicense') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccessModal('Upload Successful', data.message, file);
                    } else {
                        showToast(data.message || 'Failed to upload license.', 'error');
                        if (btn) {
                            btn.innerHTML = originalBtnText;
                            btn.disabled = false;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while uploading the license.', 'error');
                    if (btn) {
                        btn.innerHTML = originalBtnText;
                        btn.disabled = false;
                    }
                })
                .finally(() => {
                    input.value = '';
                });
        }

        function showSuccessModal(title, message, file) {
            const modalId = 'successModal_' + Date.now();
            const objectUrl = URL.createObjectURL(file);

            const modalHtml = `
                <div class="modal fade show" id="${modalId}" tabindex="-1" style="display: flex; align-items: center; justify-content: center; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.6); z-index: 99999;" aria-modal="true" role="dialog">
                    <div class="modal-dialog" style="margin: 0; width: 100%; max-width: 500px;">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                            <div class="modal-header bg-success text-white border-0 py-3">
                                <h6 class="modal-title fw-bold mb-0"><i class="bi bi-check-circle-fill me-2"></i>${title}</h6>
                            </div>
                            <div class="modal-body text-center p-4">
                                <p class="text-muted mb-4">${message}</p>
                                <div class="rounded overflow-hidden border shadow-sm mx-auto" style="max-width: 100%; max-height: 250px; background: #f8fafc;">
                                    <img src="${objectUrl}" style="width: 100%; height: 100%; object-fit: contain;" alt="Uploaded License Preview">
                                </div>
                            </div>
                            <div class="modal-footer border-0 justify-content-center bg-light py-3">
                                <button type="button" class="btn btn-success px-5 fw-bold" style="border-radius: 8px;" onclick="closeSuccessModal('${modalId}')">Got it</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        function closeSuccessModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.remove();
                // Refresh to update the UI state properly
                if (typeof loadContent === 'function') {
                    loadContent(window.location.href);
                } else {
                    window.location.reload();
                }
            }
        }
        function showImagePreviewModal(url) {
            const lightboxId = 'lightbox_' + Date.now();

            const lightboxHtml = `
                <div id="${lightboxId}" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.9); z-index: 999999; display: flex; justify-content: center; align-items: center; cursor: zoom-out; backdrop-filter: blur(5px);" onclick="document.getElementById('${lightboxId}').remove()">
                    <div style="position: absolute; top: 20px; right: 30px; color: white; font-size: 40px; cursor: pointer; line-height: 1;">&times;</div>
                    <img src="${url}" style="max-width: 90%; max-height: 90vh; object-fit: contain; border-radius: 4px; box-shadow: 0 0 40px rgba(0,0,0,0.5);" alt="License Preview" onclick="event.stopPropagation()">
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', lightboxHtml);
        }
    </script>

@endsection