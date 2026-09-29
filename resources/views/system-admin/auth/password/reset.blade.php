<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - System Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
        .login-header h1 {
            font-size: 1.5rem;
            color: #053d86;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #6b7280;
            font-size: 0.9rem;
        }
        .form-group {
            margin-bottom: 20px;
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
        .back-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .back-link a {
            color: #053d86;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Reset Password</h1>
            <p>Set a new secure password for your system administrator account.</p>
        </div>

        <form method="POST" action="{{ route('system-admin.password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            
            <div class="form-group">
                <label for="email">Admin Email</label>
                <input type="email" id="email" name="email" value="{{ $email ?? old('email') }}" required readonly placeholder="admin@example.com">
                @error('email')
                    <span style="color: #ef4444; font-size: 0.85rem; margin-top: 5px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required autofocus placeholder="New password">
                @error('password')
                    <span style="color: #ef4444; font-size: 0.85rem; margin-top: 5px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Confirm new password">
            </div>

            <button type="submit" class="btn">Update Password</button>
        </form>

        <div class="back-link">
            <a href="{{ route('system-admin.login') }}">← Back to Login</a>
        </div>
    </div>
    @include('partials.toast-notifications')
</body>
</html>
