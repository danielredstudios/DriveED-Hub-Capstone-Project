<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Administrator Login - DriveED Hub</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #053d86;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            max-width: 420px;
            width: 100%;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.4);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header .icon {
            width: 70px;
            height: 70px;
            background: #053d86;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.8rem;
        }

        .login-header-icon-svg {
            width: 48px;
            height: 48px;
        }

        .login-header h1 {
            font-size: 1.5rem;
            color: #053d86;
            margin-bottom: 5px;
        }

        .login-header p {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .remember-group {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
            margin-bottom: 25px;
        }

        .remember-checkbox {
            width: 18px !important;
            height: 18px;
            cursor: pointer;
            margin: 0;
        }

        .remember-label {
            display: inline !important;
            margin-bottom: 0 !important;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.9rem;
            color: #4b5563;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #053d86;
            box-shadow: 0 0 0 3px rgba(5, 61, 134, 0.1);
        }

        .form-group input.is-invalid {
            border-color: #ef4444;
        }

        .invalid-feedback {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: #fbbf24;
            color: #053d86;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn:hover {
            background: #f59e0b;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(251, 191, 36, 0.4);
        }

        .login-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .login-footer a {
            color: #053d86;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 25px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#667eea" class="login-header-icon-svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>
            <h1>System Administrator</h1>
            <p>Secure Access Portal</p>
        </div>



            <form method="POST" action="{{ route('system-admin.login.submit') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="@error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        required 
                        autofocus
                        placeholder="admin@example.com"
                    >
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="@error('password') is-invalid @enderror"
                        required
                        placeholder="Enter your password"
                    >
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group remember-group" style="margin-bottom: 15px;">
                    <input type="checkbox" name="remember" id="remember" class="remember-checkbox">
                    <label for="remember" class="remember-label">Remember me on this browser</label>
                </div>

                <div style="text-align: right; margin-bottom: 25px;">
                    <a href="{{ route('system-admin.password.request') }}" style="color: #4b5563; text-decoration: none; font-size: 0.85rem; font-weight: 500;">
                        Forgot Password?
                    </a>
                </div>

                <button type="submit" class="btn">

                    Sign In
                </button>
            </form>

            <div class="login-footer">
                <a href="{{ route('welcome') }}" style="display:inline-flex; align-items:center; justify-content:center; gap:4px;">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Home
                </a>
            </div>
        </div>
    @include('partials.toast-notifications')
</body>
</html>
