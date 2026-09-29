@php
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#2563eb';
    $secondaryColor = $settings?->secondary_color ?? '#f59e0b';
    $headerHeight = $settings?->login_header_height ?? 60;
    $schoolNameSize = $settings?->login_school_name_size ?? 24;
    $welcomeSize = $settings?->login_welcome_size ?? 16;
    $logoSize = $settings?->login_logo_size ?? 40;
    $schoolName = $school?->name ?? 'DriveEd Hub';
    $welcomeText = $settings?->register_welcome_text ?? 'Student Registration';
    $subtitleText = $settings?->register_subtitle_text;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.school-auth-header')

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $schoolName }} - Guest Registration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Registration Page Specific Styles */
        .registration-card {
            background: rgba(255, 255, 255, 0.98);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 500px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        h2 {
            font-size: 22px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 8px;
            text-align: center;
        }

        .subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 12px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #374151;
            font-weight: 500;
            font-size: 13px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        textarea {
            width: 100%;
            padding: 9px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13px;
        }

        .password-input-wrap {
            position: relative;
        }

        .password-input-wrap input {
            padding-right: 44px;
        }

        .password-toggle-btn {
            position: absolute;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            cursor: pointer;
            color: #6b7280;
            width: 28px;
            height: 28px;
            padding: 0;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle-btn:hover {
            color: #1f2937;
            background: rgba(0, 0, 0, 0.05);
        }

        .password-toggle-btn:focus-visible {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        .password-toggle-btn svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .password-toggle-btn .icon-eye-off {
            display: none;
        }

        .password-toggle-btn[aria-pressed="true"] .icon-eye {
            display: none;
        }

        .password-toggle-btn[aria-pressed="true"] .icon-eye-off {
            display: block;
        }

        textarea {
            min-height: 60px;
            resize: vertical;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
        }

        .error {
            color: #e74c3c;
            font-size: 11px;
            margin-top: 4px;
        }

        .contact-input-group {
            display: flex;
            align-items: center;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .contact-input-group:focus-within {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
        }

        .contact-prefix {
            background: #f3f4f6;
            padding: 9px 12px;
            color: #4b5563;
            font-weight: 600;
            border-right: 1px solid #e5e7eb;
            font-size: 13px;
        }

        .contact-input-group input {
            border: none !important;
            box-shadow: none !important;
            padding: 9px !important;
        }

        .field-help {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }

        .success {
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
            text-align: left;
        }

        .alert-error {
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            text-align: left;
        }

        .submit-btn {
            width: 100%;
            padding: 10px;
            @if($settings?->use_gradient_header ?? false)
                background: linear-gradient(135deg, {{ $settings?->primary_color ?? '#2563eb' }} 0%, {{ $settings?->secondary_color ?? '#1e40af' }} 100%);
            @else
                background: {{ $settings?->primary_color ?? '#2563eb' }};
            @endif
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s ease;
        }

        .submit-btn:hover {
            opacity: 0.85;
        }

        .back-to-login {
            text-align: center;
            margin-top: 15px;
        }

        .back-to-login a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
            font-size: 12px;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }

        .modal-icon-close:focus-visible {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
            border-radius: 6px;
        }

        .consent-group {
            margin-top: 24px;
        }

        .consent-label {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            cursor: pointer;
            font-weight: normal;
        }

        .consent-checkbox {
            margin-top: 4px;
        }

        .consent-text {
            font-size: 13px;
            line-height: 1.4;
        }

        .policy-link {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .policy-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            overflow-y: auto;
        }

        .policy-modal-card {
            background: white;
            max-width: 600px;
            margin: 50px auto;
            border-radius: 8px;
            padding: 24px;
            position: relative;
        }

        .policy-modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .policy-modal-title {
            margin-bottom: 16px;
            color: var(--primary-color);
        }

        .policy-modal-content {
            line-height: 1.6;
            font-size: 14px;
            color: #333;
        }

        .policy-section-title {
            margin-top: 16px;
            margin-bottom: 8px;
        }

        .policy-list {
            margin-left: 20px;
            margin-top: 8px;
        }

        .policy-contact-email {
            margin-top: 8px;
        }

        .policy-modal-btn {
            margin-top: 20px;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
        }

        @media (max-width: 768px) {
            .login-header {
                height: {{ max(50, $headerHeight - 10) }}px;
                padding: 0 15px;
            }
            
            .header-school-name {
                font-size: {{ max(18, $schoolNameSize - 6) }}px;
            }
            
            .header-welcome {
                font-size: {{ max(14, $welcomeSize - 2) }}px;
            }
            
            .header-logo .logo-image {
                height: {{ max(32, $logoSize - 8) }}px;
            }

            .container {
                padding: 15px 10px;
                margin: {{ max(50, $headerHeight - 10) + 20 }}px auto 15px auto;
            }

            .registration-card {
                padding: 25px 20px;
                border-radius: 12px;
            }

            h2 {
                font-size: 1.4rem;
            }

            .subtitle {
                font-size: 12px;
            }

            input[type="text"],
            input[type="email"],
            input[type="password"],
            textarea {
                padding: 12px;
                font-size: 16px;
            }

            .submit-btn {
                padding: 14px;
                font-size: 16px;
            }

            .form-group label {
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .login-header {
                height: {{ max(45, $headerHeight - 15) }}px;
                padding: 0 12px;
            }
            
            .login-header-horizontal .header-right,
            .login-header-horizontal .header-center {
                display: none;
            }
            
            .header-school-name {
                font-size: {{ max(16, $schoolNameSize - 8) }}px;
            }
            
            .header-welcome {
                display: none;
            }
            
            .header-logo .logo-image {
                height: {{ max(28, $logoSize - 12) }}px;
            }

            .container {
                padding: 10px;
                margin: {{ max(45, $headerHeight - 15) + 20 }}px auto 10px auto;
            }

            .registration-card {
                padding: 14px 18px;
                width: 280px;
                max-width: 90%;
                border-radius: 8px;
            }

            h2 {
                font-size: 18px;
                margin-bottom: 6px;
            }

            .subtitle {
                font-size: 10px;
                margin-bottom: 8px;
            }

            .form-group {
                margin-bottom: 6px;
            }

            .form-group label {
                font-size: 10px;
                margin-bottom: 2px;
            }

            input[type="text"],
            input[type="email"],
            input[type="password"],
            textarea {
                padding: 6px 8px;
                font-size: 12px;
                border: 1px solid #d1d5db;
                border-radius: 5px;
            }

            textarea {
                min-height: 50px;
            }

            .error {
                font-size: 11px;
                margin-top: 2px;
            }

            .success {
                padding: 6px 8px;
                border-radius: 5px;
                margin-bottom: 6px;
                font-size: 11px;
            }

            .alert-error {
                padding: 6px 8px;
                border-radius: 5px;
                margin-bottom: 6px;
                font-size: 11px;
            }

            .submit-btn {
                padding: 8px;
                font-size: 12px;
                border-radius: 5px;
            }

            .back-to-login {
                margin-top: 10px;
            }

            .back-to-login a {
                font-size: 10px;
            }

            .password-help {
                font-size: 10px;
                color: #6b7280;
                margin-top: 5px;
            }

            .error-top-space {
                margin-top: 5px;
            }

        }

        @media (max-width: 360px) {
            .login-header {
                height: {{ max(42, $headerHeight - 18) }}px;
                padding: 0 10px;
            }
            
            .header-school-name {
                font-size: {{ max(14, $schoolNameSize - 10) }}px;
            }
            
            .header-logo .logo-image {
                height: {{ max(24, $logoSize - 16) }}px;
            }

            .registration-card {
                padding: 18px 12px;
            }

            h2 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>

    @include('partials.school-auth-header')

    <div class="main-content">
        <div class="registration-card">
            <h2>Create Your Account</h2>
            <p class="subtitle">Register to browse courses and start your driving journey</p>



            <form method="POST" action="{{ route('schools.registration.submit', $school) }}">
                @csrf

                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="contact">Contact Number *</label>
                    <div class="contact-input-group">
                        <span class="contact-prefix">+63</span>
                        <input type="text" id="contact" name="contact" value="{{ old('contact') }}" required inputmode="numeric" pattern="[0-9]*" autocomplete="tel" maxlength="10" placeholder="9123456789">
                    </div>
                    <p class="field-help">Enter the 10-digit number after +63 (e.g., 9123456789).</p>
                    @error('contact')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="{{ old('address') }}">
                    @error('address')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <div class="password-input-wrap">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="password-toggle-btn" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
                            <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <svg class="icon-eye-off" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"></path><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path><path d="M9.9 4.2A11 11 0 0 1 12 4c6.5 0 10 6 10 6a18.7 18.7 0 0 1-4 4.9"></path><path d="M6.1 6.1A18.9 18.9 0 0 0 2 12s3.5 6 10 6c1.5 0 2.9-.3 4.1-.8"></path></svg>
                        </button>
                    </div>
                    <div class="password-help">
                        Must be at least 8 characters with at least one uppercase letter, one number, and one special character.
                    </div>
                    @error('password')
                        <div class="error error-top-space">{{ $message }}</div>
                    @enderror
                </div>


                <div class="form-group">
                    <label for="password_confirmation">Confirm Password *</label>
                    <div class="password-input-wrap">
                        <input type="password" id="password_confirmation" name="password_confirmation" required>
                        <button type="button" class="password-toggle-btn" data-password-toggle="password_confirmation" aria-label="Show password" aria-pressed="false">
                            <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <svg class="icon-eye-off" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"></path><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path><path d="M9.9 4.2A11 11 0 0 1 12 4c6.5 0 10 6 10 6a18.7 18.7 0 0 1-4 4.9"></path><path d="M6.1 6.1A18.9 18.9 0 0 0 2 12s3.5 6 10 6c1.5 0 2.9-.3 4.1-.8"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Data Privacy and Terms -->
                <div class="form-group consent-group">
                    <label class="consent-label">
                        <input type="checkbox" id="accept_privacy" name="accept_privacy" required class="consent-checkbox">
                        <span class="consent-text">
                            I have read and agree to the <a href="#" onclick="showPrivacy(event)" class="policy-link">Data Privacy Policy</a>
                        </span>
                    </label>
                    @error('accept_privacy')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="consent-label">
                        <input type="checkbox" id="accept_terms" name="accept_terms" required class="consent-checkbox">
                        <span class="consent-text">
                            I agree to the <a href="#" onclick="showTerms(event)" class="policy-link">Terms and Conditions</a>
                        </span>
                    </label>
                    @error('accept_terms')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <br>
                <button type="submit" class="submit-btn">Create Account</button>
            </form>

            <div class="back-to-login">
                <a href="{{ route('schools.login', $school) }}">← Back to Login</a>
            </div>
        </div>
    </div>

    <!-- Data Privacy Modal -->
    <div id="privacyModal" class="policy-modal">
        <div class="policy-modal-card">
            <button type="button" class="modal-icon-close policy-modal-close" aria-label="Close data privacy policy" onclick="closePrivacy()">&times;</button>
            <h2 class="policy-modal-title">Data Privacy Policy</h2>
            <div class="policy-modal-content">
                <p><strong>Effective Date:</strong> {{ date('F Y') }}</p>
                <br>
                <p>At <strong>{{ $schoolName }}</strong>, we are committed to protecting your privacy in compliance with the Data Privacy Act. This policy explains how we handle your personal information.</p>
                <br>
                <h3 class="policy-section-title">1. Information We Collect</h3>
                <p>To provide quality driving instruction, we collect:</p>
                <ul class="policy-list">
                    <li><strong>Identity Data:</strong> Full name, date of birth, and gender.</li>
                    <li><strong>Contact Data:</strong> Email address, mobile number, and residential address.</li>
                    <li><strong>Government ID:</strong> Student Permit or Driver’s License details (for enrollment verification).</li>
                    <li><strong>Technical Data:</strong> IP address and login data when you use the portal.</li>
                </ul>
                <br>
                <h3 class="policy-section-title">2. Purpose of Data Collection</h3>
                <p>Your data is used specifically for:</p>
                <ul class="policy-list">
                    <li>Processing your enrollment and LTO (Land Transportation Office) certification.</li>
                    <li>Scheduling and coordinating theoretical (TDC) and practical (PDC) lessons.</li>
                    <li>Sending automated session reminders and security codes.</li>
                    <li>Internal audit and regulatory compliance.</li>
                </ul>
                <br>
                <h3 class="policy-section-title">3. Data Retention & Security</h3>
                <p>We retain your information for as long as it is required to fulfill our services or as mandated by law (typically 5 years for student records). We use encrypted storage and SSL protocols to protect your data from unauthorized access.</p>
                <br>
                <h3 class="policy-section-title">4. Third-Party Disclosure</h3>
                <p>We do not sell your data. We only share information with:</p>
                <ul class="policy-list">
                    <li><strong>LTO / Regulatory Bodies:</strong> For legal certification and licensing.</li>
                    <li><strong>Instructors:</strong> Limited to your name and schedule for lesson coordination.</li>
                </ul>
                <br>
                <h3 class="policy-section-title">5. Your Rights</h3>
                <p>Under the Data Privacy Act, you have the right to access, correct, or request the deletion of your personal data. Contact us at <strong>{{ $school->contact_email ?? 'support@driveedhub.com' }}</strong> for any privacy concerns.</p>
            </div>
            <button type="button" onclick="closePrivacy()" class="policy-modal-btn">I Understand</button>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div id="termsModal" class="policy-modal">
        <div class="policy-modal-card">
            <button type="button" class="modal-icon-close policy-modal-close" aria-label="Close terms and conditions" onclick="closeTerms()">&times;</button>
            <h2 class="policy-modal-title">Terms and Conditions</h2>
            <div class="policy-modal-content">
                <p><strong>Effective Date:</strong> {{ date('F Y') }}</p>
                <br>
                <h3 class="policy-section-title">1. Enrollment & Eligibility</h3>
                <ul class="policy-list">
                    <li>Students must be at least 16 years old for Student Permits or 18 years old for Driver’s Licenses.</li>
                    <li>All information provided during registration must be truthful and accurate.</li>
                </ul>
                <br>
                <h3 class="policy-section-title">2. Fees & Refunds</h3>
                <ul class="policy-list">
                    <li><strong>Payment:</strong> Full or partial payment is required before the start of lessons as per the school’s payment policy.</li>
                    <li><strong>Refunds:</strong> Enrollment fees are generally non-refundable once the course has commenced. Partial refunds may be considered on a case-by-case basis before the first lesson.</li>
                </ul>
                <br>
                <h3 class="policy-section-title">3. Attendance & Cancellations</h3>
                <ul class="policy-list">
                    <li><strong>Notice Period:</strong> Cancellations or rescheduling of practical lessons must be made at least <strong>24 hours</strong> in advance.</li>
                    <li><strong>No-Show Policy:</strong> Failure to attend a scheduled lesson without notice will result in the forfeiture of that session's fee.</li>
                    <li><strong>Late Policy:</strong> Instructors will wait for 15 minutes. After this, the lesson may be cancelled and marked as a no-show.</li>
                </ul>
                <br>
                <h3 class="policy-section-title">4. Code of Conduct</h3>
                <ul class="policy-list">
                    <li>Students must remain sober and not under the influence of any substance during lessons.</li>
                    <li>Inappropriate behavior toward instructors or staff will result in immediate termination of the enrollment without refund.</li>
                    <li>Mobile phone usage is strictly prohibited while operating a vehicle.</li>
                </ul>
                <br>
                <h3 class="policy-section-title">5. Vehicle Liability & Insurance</h3>
                <ul class="policy-list">
                    <li>Our vehicles are fully insured for instructional purposes.</li>
                    <li>If a vehicle breaks down during a lesson, the session will be rescheduled at no extra cost.</li>
                    <li>Students are not liable for accidental damages occurring during an instructor-led session, except in cases of gross negligence.</li>
                </ul>
                <br>
                <h3 class="policy-section-title">6. Certification</h3>
                <p>Completion of the course does not guarantee passing the LTO examination. The school provides the necessary training and certificates as required by law.</p>
            </div>
            <button type="button" onclick="closeTerms()" class="policy-modal-btn">I Agree</button>
        </div>
    </div>

    <script>
        function enforceNumericOnly(input) {
            if (!input) {
                return;
            }

            const sanitize = function() {
                input.value = input.value.replace(/\D+/g, '');
            };

            input.addEventListener('input', sanitize);
            input.addEventListener('paste', function() {
                setTimeout(sanitize, 0);
            });
        }

        function showPrivacy(e) {
            if (e && typeof e.preventDefault === 'function') {
                e.preventDefault();
            }
            document.getElementById('privacyModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closePrivacy() {
            document.getElementById('privacyModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function showTerms(e) {
            if (e && typeof e.preventDefault === 'function') {
                e.preventDefault();
            }
            document.getElementById('termsModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeTerms() {
            document.getElementById('termsModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modals when clicking outside
        document.getElementById('privacyModal')?.addEventListener('click', function(e) {
            if (e.target === this) closePrivacy();
        });

        document.getElementById('termsModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeTerms();
        });

        function togglePasswordVisibility(inputId, trigger) {
            const input = document.getElementById(inputId);
            if (!input) {
                return;
            }

            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            trigger.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            trigger.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        }

        document.querySelectorAll('[data-password-toggle]').forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                togglePasswordVisibility(trigger.getAttribute('data-password-toggle'), trigger);
            });
        });

        // Auto-strip leading zero from contact inputs
        document.getElementById('contact').addEventListener('input', function(e) {
            let val = e.target.value;
            if (val.startsWith('0')) {
                e.target.value = val.substring(1);
            }
        });

        enforceNumericOnly(document.getElementById('contact'));
    </script>
    @include('partials.toast-notifications')
</body>
</html>
