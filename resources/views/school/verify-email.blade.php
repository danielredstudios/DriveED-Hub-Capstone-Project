@php
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $schoolName }} - Email Verification</title>
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
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Main container with top padding for fixed header */
        .verify-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: calc(var(--header-height) + 40px) 20px 40px;
        }

        .verify-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 480px;
            width: 100%;
        }

        .verify-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .verify-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
        }

        h1 {
            color: #1f2937;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #6b7280;
            font-size: 14px;
        }

        .email-display {
            background: #f3f4f6;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #374151;
        }

        input[type="text"] {
            width: 100%;
            padding: 14px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 24px;
            text-align: center;
            letter-spacing: 8px;
            font-weight: 600;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .error {
            color: #ef4444;
            font-size: 13px;
            margin-top: 6px;
        }

        .success {
            color: #10b981;
            font-size: 13px;
            margin-top: 6px;
        }

        .info {
            color: #3b82f6;
            font-size: 13px;
            margin-top: 6px;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .resend-section {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .resend-text {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .resend-btn {
            background: none;
            border: none;
            color: var(--primary-color);
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
            font-size: 14px;
        }

        .resend-btn:hover {
            opacity: 0.8;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link form {
            display: inline;
            margin: 0;
        }

        .back-link-btn {
            background: none;
            border: none;
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            padding: 0;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .back-link-btn:hover {
            text-decoration: underline;
        }

        .dev-code-box {
            background: #fef3c7;
            border: 2px dashed #f59e0b;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .dev-code-label {
            font-size: 0.8rem;
            color: #92400e;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .dev-code-value {
            font-size: 2rem;
            font-weight: 700;
            color: #78350f;
            letter-spacing: 8px;
            font-family: monospace;
        }

        .dev-code-hint {
            font-size: 0.75rem;
            color: #a16207;
            margin-top: 6px;
        }

        .center-message {
            text-align: center;
            margin-bottom: 20px;
        }

        .inline-success-icon {
            width: 16px;
            height: 16px;
        }

        .resend-inline-form {
            display: inline;
        }

        .tc-modal-overlay {
            display: flex;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }

        .tc-modal-card {
            background: white;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            max-height: min(92vh, 760px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            overflow: hidden;
            animation: slideIn 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .tc-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px;
            text-align: center;
            color: white;
        }

        .tc-header-icon-wrap {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .tc-header-icon {
            width: 48px;
            height: 48px;
        }

        .tc-header-title {
            margin: 0;
            font-size: 1.8rem;
        }

        .tc-header-subtitle {
            margin: 10px 0 0 0;
            opacity: 0.95;
            font-size: 0.95rem;
        }

        .tc-warning-banner {
            background: #fbbf24;
            color: #78350f;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .tc-content {
            padding: 30px;
            overflow-y: auto;
        }

        .tc-content-intro {
            margin-bottom: 25px;
            color: #4b5563;
            text-align: center;
            font-size: 0.95rem;
        }

        .tc-field-group {
            margin-bottom: 20px;
        }

        .tc-field-group-last {
            margin-bottom: 25px;
        }

        .tc-field-label {
            display: block;
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .tc-field-row {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f9fafb;
            padding: 14px 16px;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
        }

        .tc-field-row-otp {
            background: #f0fdf4;
            border-color: #86efac;
        }

        .tc-input {
            flex: 1;
            background: transparent;
            border: none;
            font-size: 1rem;
            color: #1f2937;
            outline: none;
        }

        .tc-input-mono {
            font-family: 'Courier New', monospace;
        }

        .tc-input-otp {
            font-size: 1.3rem;
            color: #166534;
            font-weight: 700;
            letter-spacing: 4px;
        }

        .tc-copy-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .tc-copy-btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .tc-copy-btn-copied {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .tc-info-box {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #3b82f6;
        }

        .tc-info-row {
            display: flex;
            gap: 10px;
            align-items: start;
        }

        .tc-info-text {
            margin: 0;
            font-size: 0.85rem;
            color: #1e40af;
            line-height: 1.5;
        }

        .tc-close-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        @media (max-width: 480px) {
            .verify-container {
                padding: 24px;
            }

            h1 {
                font-size: 20px;
            }

            input[type="text"] {
                font-size: 20px;
                letter-spacing: 4px;
            }

            .login-header {
                height: 50px;
                padding: 0 15px;
            }

            .header-school-name {
                font-size: 16px;
            }

            .verify-wrapper {
                padding: 70px 15px 30px;
            }

            .tc-modal-overlay {
                padding: 8px;
            }

            .tc-modal-card {
                width: 100%;
                max-height: calc(100vh - 16px);
                border-radius: 14px;
            }

            .tc-header {
                padding: 16px;
            }

            .tc-content {
                padding: 16px;
            }

            .tc-close-btn {
                position: sticky;
                bottom: 0;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="verify-container">
        <div class="verify-header">
            <div class="verify-icon"></div>
            <h1>Verify Your Email</h1>
            <p class="subtitle">We sent a 6-digit code to:</p>
        </div>

        <div class="email-display">{{ $email ?? 'your email' }}</div>

        @if(app()->environment('local', 'development', 'testing') && (session('dev_verification_code') || session('_flash.dev_verification_code')))
            <div class="dev-code-box">
                <div class="dev-code-label">
                    DEV MODE &mdash; Verification Code
                </div>
                <div class="dev-code-value">
                    {{ session('dev_verification_code') }}
                </div>
                <div class="dev-code-hint">
                    This is only visible in local/dev environment
                </div>
            </div>
        @endif



        <form method="POST" action="{{ route('schools.verification.verify', $school) }}">
            @csrf
            
            <div class="form-group">
                <label for="code">Enter Verification Code</label>
                <input 
                    type="text" 
                    id="code" 
                    name="code" 
                    maxlength="6" 
                    pattern="[0-9]{6}"
                    placeholder="000000"
                    required 
                    autofocus
                    inputmode="numeric"
                >
                @error('code')
                    <div class="error">{{ $message }}</div>
                @enderror
                @error('error')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="submit-btn">Verify Email</button>
        </form>

        <div class="resend-section">
            <p class="resend-text">Didn't receive the code?</p>
            <form method="POST" action="{{ route('schools.verification.resend', $school) }}" class="resend-inline-form">
                @csrf
                <button type="submit" class="resend-btn">Resend Code</button>
            </form>
        </div>

        <div class="back-link">
            <form method="POST" action="{{ route('schools.logout', $school) }}">
                @csrf
                <button type="submit" class="back-link-btn">&larr; Back to Login</button>
            </form>
        </div>
    </div>
    </div>

    @if(session('test_credentials') && app()->environment('local', 'development', 'testing'))
    <!-- Test Credentials Popup Modal (Development Only) -->
    <div id="testCredentialsModal" style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center; backdrop-filter: blur(5px); padding: 12px;">
        <div style="background: white; border-radius: 20px; max-width: 500px; width: 100%; max-height: 92vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px rgba(0,0,0,0.5); overflow: hidden; animation: slideIn 0.3s ease;">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; text-align: center; color: white; position: relative;">
                <button type="button" onclick="closeModal()" aria-label="Close test credentials modal" style="position: absolute; top: 12px; right: 12px; border: none; background: rgba(255,255,255,0.2); color: white; width: 34px; height: 34px; border-radius: 999px; cursor: pointer; font-size: 1.25rem; line-height: 1;">&times;</button>
                <div style="font-size: 48px; margin-bottom: 10px;"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 48px; height: 48px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg></div>
                <h2 class="tc-header-title">Registration Successful!</h2>
                <p class="tc-header-subtitle">Testing Credentials</p>
            </div>
            
            <!-- Warning Banner -->
            <div class="tc-warning-banner">
                <span>DEVELOPMENT MODE ONLY - This popup won't appear in production</span>
            </div>
            
            <!-- Content -->
            <div style="padding: 30px; overflow-y: auto;">
                <p style="margin-bottom: 25px; color: #4b5563; text-align: center; font-size: 0.95rem;">
                    Save these credentials for testing. You can copy them with one click:
                </p>
                
                <!-- Email -->
                <div class="tc-field-group">
                    <label class="tc-field-label">Email Address</label>
                    <div class="tc-field-row">
                        <input type="text" value="{{ session('test_credentials')['email'] }}" readonly class="tc-input tc-input-mono">
                        <button type="button" onclick="copyText('{{ session('test_credentials')['email'] }}', this)" class="tc-copy-btn">
                            Copy
                        </button>
                    </div>
                </div>
                
                <!-- Password -->
                <div class="tc-field-group">
                    <label class="tc-field-label">Password</label>
                    <div class="tc-field-row">
                        <input type="text" value="{{ session('test_credentials')['password'] }}" readonly class="tc-input tc-input-mono">
                        <button type="button" onclick="copyText('{{ session('test_credentials')['password'] }}', this)" class="tc-copy-btn">
                            Copy
                        </button>
                    </div>
                </div>
                
                <!-- OTP Code -->
                <div class="tc-field-group tc-field-group-last">
                    <label class="tc-field-label">Verification Code (OTP)</label>
                    <div class="tc-field-row tc-field-row-otp">
                        <input type="text" value="{{ session('test_credentials')['otp'] }}" readonly class="tc-input tc-input-mono tc-input-otp">
                        <button type="button" onclick="copyText('{{ session('test_credentials')['otp'] }}', this)" class="tc-copy-btn tc-copy-btn-success">
                            Copy
                        </button>
                    </div>
                </div>
                
                <!-- Info Box -->
                <div class="tc-info-box">
                    <div class="tc-info-row">
                        <p class="tc-info-text">
                            <strong>Quick Tip:</strong> The verification code is already displayed above and in your email. Just copy and paste it into the form below!
                        </p>
                    </div>
                </div>
                
                <!-- Close Button -->
                <button type="button" onclick="closeModal()" class="tc-close-btn">
                    Got it! Continue to Verification
                </button>
            </div>
        </div>
    </div>

    <style>
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    </style>

    <script>
        function copyText(text, button) {
            navigator.clipboard.writeText(text).then(() => {
                const originalText = button.textContent;
                button.textContent = '\u2713 Copied!';
                button.classList.add('tc-copy-btn-copied');
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.remove('tc-copy-btn-copied');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
                alert('Failed to copy to clipboard');
            });
        }

        function closeModal() {
            document.getElementById('testCredentialsModal').style.display = 'none';
        }

        // Auto-focus on code input after closing modal
        document.getElementById('testCredentialsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('testCredentialsModal');
                if (modal && modal.style.display !== 'none') {
                    closeModal();
                }
            }
        });
    </script>
    @endif

    <script>
        // Auto-format code input
        const codeInput = document.getElementById('code');
        codeInput.addEventListener('input', function(e) {
            // Only allow numbers
            this.value = this.value.replace(/[^0-9]/g, '');
        });

    </script>
    @include('partials.toast-notifications')
</body>
</html>
