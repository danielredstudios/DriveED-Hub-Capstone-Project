@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Profile')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $admin = Auth::guard('admin')->user();
@endphp

@include('school.admin.partials.admin-styles')

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .profile-page {
        background: #f5f5f5;
        padding: 40px 20px;
    }

    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .profile-page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 10px;
        padding-bottom: 8px;
        display: inline-block;
        position: relative;
    }

    .profile-page-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: -30px;
        width: calc(100% + 100px);
        height: 3px;
        background: {{ $settings->primary_color ?? '#667eea' }};
    }

    .profile-card {
        max-width: 600px;
        margin: 30px auto 0;
        background: white;
        border-radius: 12px;
        padding: 0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .profile-card-header {
        text-align: center;
        padding: 40px 30px 30px;
    }

    .profile-avatar-circle {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: #000;
        margin: 0 auto 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        cursor: pointer;
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
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
        cursor: pointer;
    }

    .avatar-upload-overlay span {
        color: white;
        font-size: 14px;
        font-weight: 600;
    }

    .profile-avatar-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-avatar-letter {
        font-size: 80px;
        font-weight: 700;
        color: white;
    }

    .profile-name {
        font-size: 24px;
        font-weight: 600;
        color: #000;
    }

    .profile-card-body {
        padding: 0 30px 30px;
    }

    .profile-field {
        display: grid;
        grid-template-columns: 140px 1fr;
        padding: 15px 0;
        border-bottom: 1px solid #e0e0e0;
        gap: 20px;
    }

    .profile-field:last-child {
        border-bottom: none;
    }

    .profile-field-label {
        font-weight: 600;
        color: #000;
        font-size: 15px;
    }

    .profile-field-value {
        color: #666;
        font-size: 15px;
    }

    .contact-input-group {
        display: flex;
        align-items: center;
        background: {{ $settings->primary_color ?? '#667eea' }}05;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.2s;
    }

    .contact-input-group:focus-within {
        border-color: {{ $settings->primary_color ?? '#667eea' }};
        background: white;
    }

    .contact-prefix {
        padding: 0 12px;
        background: #f3f4f6;
        color: #4b5563;
        font-weight: 600;
        font-size: 0.9rem;
        height: 38px;
        display: flex;
        align-items: center;
        border-right: 1px solid #ddd;
    }

    .contact-input-group input {
        flex: 1;
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        height: 38px !important;
        padding: 0 12px !important;
    }

    .field-help {
        font-size: 0.8rem;
        color: #6b7280;
        margin-top: 6px;
    }

    .profile-actions {
        text-align: center;
        padding: 20px 30px 30px;
    }

    .btn-edit-profile {
        background: #007bff;
        color: white;
        border: none;
        padding: 12px 40px;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-edit-profile:hover {
        background: #0056b3;
    }

    .edit-form {
        display: none;
        padding: 30px;
    }

    .form-field {
        margin-bottom: 20px;
    }

    .form-field label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #000;
        font-size: 14px;
    }

    .form-field input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 15px;
    }

    .form-field input:focus {
        outline: none;
        border-color: {{ $settings->primary_color ?? '#007bff' }};
    }

    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 30px;
    }

    .btn-save {
        background: {{ $settings->primary_color ?? '#007bff' }};
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-save:hover {
        opacity: 0.9;
    }

    .btn-cancel {
        background: #6c757d;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-cancel:hover {
        background: #5a6268;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 6px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .error-list {
        margin: 0;
        padding-left: 20px;
    }

    .hidden-file-input {
        display: none;
    }

    .password-toggle-row {
        margin-top: 8px;
        padding-top: 8px;
    }

    .btn-password-toggle {
        background: #f3f4f6;
        color: #1f2937;
        border: 1px solid #d1d5db;
        padding: 10px 14px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        text-align: left;
    }

    .btn-password-toggle:hover {
        background: #e5e7eb;
    }

    .password-fields {
        margin-top: 12px;
        padding: 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fafafa;
    }

    .password-fields.hidden {
        display: none;
    }

    .field-error {
        margin-top: 6px;
        color: #b91c1c;
        font-size: 13px;
    }
</style>

<div class="profile-page">
    <div class="profile-container">
        <h1 class="profile-page-title">Profile</h1>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Toast.success("{{ session('success') }}");
            @endif
            @if(session('error'))
                Toast.error("{{ session('error') }}");
            @endif
            @if($errors->any())
                @foreach($errors->all() as $error)
                    Toast.error("{{ $error }}");
                @endforeach
            @endif
        });
    </script>
    <div class="profile-card">
        <div id="profileView">
            <div class="profile-card-header">
                <div class="profile-avatar-circle" id="avatarContainer">
                    @if($admin->profile_picture && file_exists(public_path('storage/' . $admin->profile_picture)))
                        <img src="{{ asset('storage/' . $admin->profile_picture) }}" alt="{{ $admin->name }}" id="avatarImage">
                    @else
                        <span class="profile-avatar-letter" id="avatarLetter">{{ strtoupper(substr($admin->name ?? 'A', 0, 1)) }}</span>
                    @endif
                    <div class="avatar-upload-overlay" onclick="document.getElementById('profilePictureInput').click()">
                        <span>Change Photo</span>
                    </div>
                </div>
                <input type="file" id="profilePictureInput" accept="image/png,image/jpg,image/jpeg,image/webp" class="hidden-file-input" onchange="uploadProfilePicture(this)">
                <div class="profile-name">{{ $admin->name ?? "Admin's Name" }}</div>
            </div>

            <div class="profile-card-body">
                <div class="profile-field">
                    <div class="profile-field-label">Email:</div>
                    <div class="profile-field-value">{{ $admin->email ?? 'N/A' }}</div>
                </div>

                <div class="profile-field">
                    <div class="profile-field-label">Contact:</div>
                    <div class="profile-field-value">{{ $admin->contact ?? 'N/A' }}</div>
                </div>

                <div class="profile-field">
                    <div class="profile-field-label">Role:</div>
                    <div class="profile-field-value">{{ ucfirst(str_replace('_', ' ', $admin->role ?? 'Administrator')) }}</div>
                </div>

                <div class="profile-field">
                    <div class="profile-field-label">Branch Scope:</div>
                    <div class="profile-field-value">{{ $admin->branch?->name ?? 'All Branches' }}</div>
                </div>

                <div class="profile-field">
                    <div class="profile-field-label">Status:</div>
                    <div class="profile-field-value">{{ ($admin->is_active ?? false) ? 'Active' : 'Inactive' }}</div>
                </div>
            </div>

            <div class="profile-actions">
                <button type="button" class="btn-edit-profile" onclick="showEditForm()">Edit Profile</button>
            </div>
        </div>

        <div id="editForm" class="edit-form">
            <form method="POST" action="{{ school_route('admin.profile.update') }}">
                @csrf
                @method('PUT')

                <div class="form-field">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $admin->name) }}" required>
                </div>

                <div class="form-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $admin->email) }}" required>
                </div>

                <div class="form-field">
                    <label for="contact">Contact</label>
                    @if($settings->enforce_ph_contact ?? true)
                        <div class="contact-input-group">
                            <span class="contact-prefix">+63</span>
                            <input type="text" id="contact" name="contact" value="{{ old('contact', $admin->contact) }}" maxlength="10" placeholder="9123456789" inputmode="numeric">
                        </div>
                        <p class="field-help">Enter the 10-digit number after +63 (e.g., 9123456789).</p>
                    @else
                        <input type="text" id="contact" name="contact" value="{{ old('contact', $admin->contact) }}" placeholder="Enter contact number">
                    @endif
                </div>

                <div class="password-toggle-row">
                    <button type="button" class="btn-password-toggle" id="passwordToggleBtn" onclick="togglePasswordFields()">
                        Change Password
                    </button>
                </div>

                <div class="password-fields hidden" id="passwordFields">
                    <div class="form-field">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" autocomplete="current-password" placeholder="Enter current password">
                        @error('current_password')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" autocomplete="new-password" placeholder="Min 8 chars, uppercase, lowercase, number, special" oninput="handleNewPasswordInput()">
                        @error('new_password')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field" id="confirmPasswordField" style="display: none;">
                        <label for="new_password_confirmation">Confirm New Password</label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" autocomplete="new-password" placeholder="Re-enter new password">
                        @error('new_password_confirmation')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Save Changes</button>
                    <button type="button" class="btn-cancel" onclick="hideEditForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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
        const passwordFields = document.getElementById('passwordFields');
        const toggleBtn = document.getElementById('passwordToggleBtn');
        if (passwordFields) passwordFields.classList.add('hidden');
        if (toggleBtn) toggleBtn.textContent = 'Change Password';
        resetPasswordFields();
    }

    function togglePasswordFields(forceOpen = null) {
        const passwordFields = document.getElementById('passwordFields');
        const toggleBtn = document.getElementById('passwordToggleBtn');
        const shouldOpen = forceOpen === null ? passwordFields.classList.contains('hidden') : forceOpen;

        if (shouldOpen) {
            passwordFields.classList.remove('hidden');
            toggleBtn.textContent = 'Hide Password Change';
        } else {
            passwordFields.classList.add('hidden');
            toggleBtn.textContent = 'Change Password';
            resetPasswordFields();
        }

        handleNewPasswordInput();
    }

    function handleNewPasswordInput() {
        const newPassword = document.getElementById('new_password');
        const currentPassword = document.getElementById('current_password');
        const confirmField = document.getElementById('confirmPasswordField');
        const confirmInput = document.getElementById('new_password_confirmation');

        if (!newPassword || !currentPassword || !confirmField || !confirmInput) {
            return;
        }

        const hasNewPassword = newPassword.value.trim().length > 0;
        confirmField.style.display = hasNewPassword ? 'block' : 'none';
        currentPassword.required = hasNewPassword;
        confirmInput.required = hasNewPassword;
    }

    function resetPasswordFields() {
        const currentPassword = document.getElementById('current_password');
        const newPassword = document.getElementById('new_password');
        const confirmInput = document.getElementById('new_password_confirmation');
        const confirmField = document.getElementById('confirmPasswordField');

        if (currentPassword) currentPassword.value = '';
        if (newPassword) newPassword.value = '';
        if (confirmInput) {
            confirmInput.value = '';
            confirmInput.required = false;
        }
        if (currentPassword) currentPassword.required = false;
        if (confirmField) confirmField.style.display = 'none';
    }

    function uploadProfilePicture(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate file type
            const validTypes = ['image/png', 'image/jpg', 'image/jpeg', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                Toast.error('Please select a valid image file (PNG, JPG, JPEG, or WEBP)');
                return;
            }
            
            // Validate file size
            const maxFileSizeMB = {{ $settings->max_file_size_mb ?? 5 }};
            if (file.size > maxFileSizeMB * 1024 * 1024) {
                Toast.error('File size must be less than ' + maxFileSizeMB + 'MB');
                return;
            }
            
            const formData = new FormData();
            formData.append('profile_picture', file);
            formData.append('_token', '{{ csrf_token() }}');
            
            // Show loading
            const overlay = document.querySelector('.avatar-upload-overlay span');
            const originalText = overlay.textContent;
            overlay.textContent = 'Uploading...';
            
            fetch('{{ school_route("admin.profile.picture") }}', {
                method: 'POST',
                body: formData
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
                        newImg.src = '/storage/' + data.path;
                        newImg.alt = '{{ $admin->name }}';
                        newImg.id = 'avatarImage';
                        avatarContainer.insertBefore(newImg, avatarContainer.firstChild);
                    }
                    
                    overlay.textContent = originalText;
                    Toast.success(data.message);
                } else {
                    overlay.textContent = originalText;
                    Toast.error('Failed to upload profile picture');
                }
            })
            .catch(error => {
                overlay.textContent = originalText;
                console.error('Error:', error);
                Toast.error('An error occurred while uploading');
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const hasErrors = {{ $errors->any() ? 'true' : 'false' }};
        const hasPasswordErrors = {{ ($errors->has('current_password') || $errors->has('new_password') || $errors->has('new_password_confirmation')) ? 'true' : 'false' }};

        if (hasErrors) {
            showEditForm();
        }

        if (hasPasswordErrors) {
            togglePasswordFields(true);
            const confirmField = document.getElementById('confirmPasswordField');
            if (confirmField) confirmField.style.display = 'block';
        }

        handleNewPasswordInput();
    });

    @if($settings->enforce_ph_contact ?? true)
    // Auto-strip leading zero from contact inputs
    document.addEventListener('input', function(e) {
        if (e.target.getAttribute('name') === 'contact' || e.target.id === 'contact') {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('0')) {
                value = value.substring(1);
            }
            e.target.value = value;
        }
    });
    @endif
</script>

@endsection
