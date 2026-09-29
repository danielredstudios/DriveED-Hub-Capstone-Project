@php
    // Self-contained branding logic to avoid scoping issues 
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#2563eb';
    $secondaryColor = $settings?->secondary_color ?? '#f59e0b';
    $headerHeight = $settings?->login_header_height ?? 60;
    $schoolNameSize = $settings?->login_school_name_size ?? 24;
    $welcomeSize = $settings?->login_welcome_size ?? 16;
    $logoSize = $settings?->login_logo_size ?? 40;
    $schoolName = $school?->name ?? 'DriveEd Hub';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    @include('partials.school-auth-header')

    <style>
        :root {
            --primary-color: {{ $primaryColor }};
            --secondary-color: {{ $secondaryColor }};
            --header-height: {{ $headerHeight }}px;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* Login Page Specific Styles */
        .login-container {
            background: rgba(255, 255, 255, 0.98);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            width: 380px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .login-title {
            font-size: 22px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 18px;
            padding-bottom: 12px;
            position: relative;
        }

        .login-subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .login-title::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--secondary-color)dd 100%);
            border-radius: 2px;
        }

        .alert {
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
            text-align: left;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .form-group {
            margin-bottom: 14px;
            text-align: left;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
        }

        input[type="email"],
        input[type="password"],
        .password-input-wrap input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13px;
            background: rgba(255, 255, 255, 0.9);
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
            filter: drop-shadow(0 1px 1px rgba(0,0,0,0.1));
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

        input[type="email"]:focus,
        input[type="password"]:focus,
        .password-input-wrap input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
            background: white;
        }

        input::placeholder {
            color: #9ca3af;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 12px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .remember-me input[type="checkbox"] {
            width: 14px;
            height: 14px;
            accent-color: var(--primary-color);
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 12px;
        }

        .forgot-password:hover {
            opacity: 0.8;
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            padding: 10px;
            background: var(--primary-color);
            @if($settings?->use_gradient_header ?? false)
                background: linear-gradient(135deg, {{ $settings?->primary_color ?? '#3b82f6' }} 0%, {{ $settings?->secondary_color ?? '#2563eb' }} 100%);
            @endif
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.25);
            transition: opacity 0.2s ease;
        }

        .login-button:hover {
            opacity: 0.85;
        }

        .error-message {
            color: #dc2626;
            font-size: 11px;
            margin-top: 4px;
            text-align: left;
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

            .main-content {
                padding: 15px;
                height: calc(100vh - {{ max(50, $headerHeight - 10) }}px);
                margin-top: {{ max(50, $headerHeight - 10) }}px;
            }

            .login-container {
                width: 100%;
                max-width: 400px;
                padding: 25px 20px;
                border-radius: 15px;
            }

            .login-title {
                font-size: 24px;
            }

            .login-subtitle {
                font-size: 12px;
            }

            input[type="email"],
            input[type="password"],
            .password-input-wrap input {
                padding: 14px;
                font-size: 16px;
            }

            .login-button {
                padding: 14px;
                font-size: 16px;
            }

            .form-options {
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

            .main-content {
                padding: 10px;
                height: calc(100vh - {{ max(45, $headerHeight - 15) }}px);
                margin-top: {{ max(45, $headerHeight - 15) }}px;
            }

            .login-container {
                padding: 14px 18px;
                width: 230px;
                max-width: 90%;
                border-radius: 8px;
            }

            .login-title {
                font-size: 18px;
            }

            .login-subtitle {
                font-size: 10px;
                margin-bottom: 10px;
            }

            .alert {
                font-size: 11px;
                padding: 6px 8px;
                border-radius: 5px;
                margin-bottom: 8px;
            }

            .form-group {
                margin-bottom: 8px;
            }

            input[type="email"],
            input[type="password"],
            .password-input-wrap input {
                padding: 7px 9px;
                font-size: 12px;
                border: 1px solid #d1d5db;
                border-radius: 5px;
            }

            input::placeholder {
                font-size: 11px;
            }

            .form-options {
                font-size: 10px;
                margin-bottom: 10px;
            }

            .remember-me {
                font-size: 10px;
                gap: 3px;
            }

            .remember-me input[type="checkbox"] {
                width: 12px;
                height: 12px;
            }

            .forgot-password {
                font-size: 10px;
            }

            .login-button {
                padding: 8px;
                font-size: 12px;
                border-radius: 5px;
                box-shadow: 0 1px 4px rgba(59, 130, 246, 0.2);
            }

            .error-message {
                font-size: 10px;
                margin-top: 2px;
            }

            /* Register link section for mobile */
            .register-link-wrap {
                margin-top: 14px !important;
                padding-top: 14px !important;
            }

            .register-link-text {
                font-size: 10px !important;
                margin-bottom: 8px !important;
            }

            .register-link-anchor {
                font-size: 11px !important;
            }
        }

        .register-link-wrap {
            text-align: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }

        .register-link-text {
            color: #666;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .register-link-anchor {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 20px 25px;
            }

            .login-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

    <div class="main-content">
        <div class="login-container">
            <h2 class="login-title">Login</h2>



            <form method="POST" action="{{ route('schools.login.submit', $school) }}">
                @csrf
                <div class="form-group">
                    <label for="email" style="display: block; font-size: 0.9rem; font-weight: 600; color: #374151; margin-bottom: 6px;">Email Address</label>
                    <input 
                        id="email"
                        type="email" 
                        name="email" 
                        placeholder="Email Address" 
                        value="{{ old('email') }}"
                        required>
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password" style="display: block; font-size: 0.9rem; font-weight: 600; color: #374151; margin-bottom: 6px;">Password</label>
                    <div class="password-input-wrap">
                        <input 
                            id="password"
                            type="password" 
                            name="password" 
                            placeholder="Password" 
                            required>
                        <button type="button" class="password-toggle-btn" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
                            <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <svg class="icon-eye-off" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"></path><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path><path d="M9.9 4.2A11 11 0 0 1 12 4c6.5 0 10 6 10 6a18.7 18.7 0 0 1-4 4.9"></path><path d="M6.1 6.1A18.9 18.9 0 0 0 2 12s3.5 6 10 6c1.5 0 2.9-.3 4.1-.8"></path></svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="{{ route('schools.password.request', $school) }}" class="forgot-password">Forgot Password?</a>
                </div>
                <button type="submit" class="login-button">Log In</button>
            </form>
            
            <div class="register-link-wrap">
                <p class="register-link-text">Don't have an account?</p>
                <a href="{{ route('schools.registration.form', $school) }}" class="register-link-anchor" style="display:inline-flex; align-items:center; justify-content:center; gap:4px;">
                    Register for Student Account 
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </div>

    <script>
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
    </script>
    @include('partials.toast-notifications')
</body>
</html>
