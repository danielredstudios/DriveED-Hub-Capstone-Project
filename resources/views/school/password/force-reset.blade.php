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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $schoolName }} - Set Your Password</title>
    <style>
        :root {
            --primary-color: {{ $primaryColor }};
            --secondary-color: {{ $secondaryColor }};
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.98);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            width: 450px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .login-title { font-size: 24px; font-weight: bold; color: #1f2937; margin-bottom: 10px; }
        .login-subtitle { font-size: 14px; color: #6b7280; margin-bottom: 25px; line-height: 1.5; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; text-align: left; }
        .alert-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .alert-info { background-color: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px; }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.9);
        }
        input[type="password"]:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2); }
        .login-button {
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
            margin-top: 10px;
        }
        .login-button:hover { opacity: 0.9; }
        .requirements { font-size: 11px; color: #6b7280; margin-top: 8px; line-height: 1.4; }
        .strength-bar { height: 4px; background: #e5e7eb; border-radius: 2px; margin-top: 8px; overflow: hidden; }
        .strength-fill { height: 100%; transition: all 0.3s ease; width: 0; }
        .strength-weak { background: #dc2626; width: 33%; }
        .strength-medium { background: #f59e0b; width: 66%; }
        .strength-strong { background: #10b981; width: 100%; }
        
        .welcome-badge {
            display: inline-block;
            padding: 6px 14px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary-color);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 15px;
        }
    text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

    <div class="main-content">
        <div class="login-container">
            <div class="welcome-badge">First-Time Setup</div>
            <h2 class="login-title">Secure Your Account</h2>
            <p class="login-subtitle">Welcome, <strong>{{ $user->name }}</strong>. For security reasons, you must change your temporary password before accessing the dashboard.</p>



            <form method="POST" action="{{ route('schools.password.force-reset.update', $school) }}">
                @csrf
                
                <div class="form-group">
                    <label class="form-label" for="password">Choose New Password</label>
                    <input type="password" id="password" name="password" placeholder="Min. 8 characters" required autofocus autocomplete="new-password">
                    <div class="strength-bar"><div id="strengthBar" class="strength-fill"></div></div>
                    <div class="requirements">
                        Use at least 8 characters with a mix of letters, numbers, and symbols for a strong password.
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repeat new password" required autocomplete="new-password">
                </div>

                <button type="submit" class="login-button">Save Password & Continue</button>
            </form>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            strengthBar.className = 'strength-fill';
            if (password.length > 0) {
                if (strength <= 2) strengthBar.classList.add('strength-weak');
                else if (strength === 3) strengthBar.classList.add('strength-medium');
                else strengthBar.classList.add('strength-strong');
            }
        });
    </script>
    @include('partials.toast-notifications')
</body>
</html>
