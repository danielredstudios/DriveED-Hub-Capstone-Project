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
    <title>{{ $schoolName }} - Forgot Password</title>
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
            width: 400px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .login-title { font-size: 22px; font-weight: bold; color: #1f2937; margin-bottom: 10px; }
        .login-subtitle { font-size: 13px; color: #6b7280; margin-bottom: 25px; line-height: 1.5; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; text-align: left; }
        .alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px; }
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.9);
        }
        input[type="email"]:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2); }
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
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        .back-link { margin-top: 25px; padding-top: 15px; border-top: 1px solid #e5e7eb; }
        .back-link a { color: #2563eb; text-decoration: none; font-size: 13px; font-weight: 600; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="login-container">
            <h2 class="login-title">Forgot Password</h2>
            <p class="login-subtitle">
                Enter your registered email address and we will send a secure reset link if an account exists.
            </p>



            <form method="POST" action="{{ route('schools.password.email', $school) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="your@email.com" required autofocus>
                    @error('email')
                        <span style="color: #991b1b; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit" class="login-button">Send Reset Link</button>
            </form>
            
            <div class="back-link">
                <a href="{{ route('schools.login', $school) }}">← Back to Login</a>
            </div>
        </div>
    </div>
    @include('partials.toast-notifications')
</body>
</html>
