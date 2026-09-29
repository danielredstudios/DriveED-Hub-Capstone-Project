<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    @php
        $school = $school ?? $currentSchool ?? null;
        $slug = $school?->slug ?? 'default';
        $settings = $school?->schoolSetting;
        
        $schoolName = $school->name ?? 'DriveEd Hub';
        $primaryColor = $settings?->primary_color ?? '#2563eb';
        $secondaryColor = $settings?->secondary_color ?? '#60a5fa';
        
        // Brand logic
        $headerBgColor = $primaryColor;
        $pageBgColor = '#f8fafc';
    @endphp
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $schoolName }} - Account Activation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: {{ $pageBgColor }};
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .header {
            background-color: {{ $headerBgColor }};
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }
        .form-card {
            background: white;
            width: 100%;
            max-width: 500px;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        .title {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
            text-align: center;
        }
        .subtitle {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 32px;
            text-align: center;
        }
        .form-group { margin-bottom: 20px; }
        .label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #334155;
        }
        input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s;
        }
        input:focus {
            outline: none;
            border-color: {{ $primaryColor }};
            box-shadow: 0 0 0 3px {{ $primaryColor }}20;
        }
        .btn {
            width: 100%;
            padding: 14px;
            background-color: {{ $primaryColor }};
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
            margin-top: 10px;
        }
        .btn:hover { opacity: 0.9; }
        .error-text { color: #dc2626; font-size: 12px; margin-top: 4px; }
        
        /* Password Requirements UI */
        .password-requirements {
            margin-top: 12px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            font-size: 12px;
            color: #94a3b8;
        }
        .requirement { display: flex; align-items: center; gap: 6px; }
        .requirement.valid { color: #10b981; }
        .requirement i { font-style: normal; }

        .contact-input-group {
            display: flex;
            align-items: center;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .contact-input-group:focus-within {
            border-color: {{ $primaryColor }};
            box-shadow: 0 0 0 3px {{ $primaryColor }}20;
        }

        .contact-prefix {
            background: #f1f5f9;
            padding: 12px 16px;
            color: #475569;
            font-weight: 600;
            border-right: 1px solid #e2e8f0;
            font-size: 15px;
        }

        .contact-input-group input {
            border: none !important;
            box-shadow: none !important;
            padding: 12px 16px !important;
        }

        .field-help {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>{{ $schoolName }}</h1>
    </header>

    <div class="container">
        <div class="form-card">
            <h2 class="title">Complete Your Profile</h2>
            <p class="subtitle">Set up your account as a {{ ucfirst(str_replace('_', ' ', $invitation->role)) }}</p>

            @if(session('error'))
                <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                    {{ session('error') }}
                </div>
            @endif

            @php
                $invitePayload = is_array($invitation->payload ?? null) ? $invitation->payload : [];
            @endphp

            <form action="{{ route('schools.onboarding.submit', ['school' => $school->slug, 'token' => $invitation->token]) }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label class="label">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="John Doe" required>
                    @error('name') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="label">Email Address</label>
                    <input type="email" value="{{ $invitation->email }}" disabled style="background-color: #f1f5f9; cursor: not-allowed;">
                    <p style="font-size: 11px; color: #64748b; margin-top: 4px;">This email is linked to your account setup and cannot be changed.</p>
                </div>

                @if($invitation->role === 'student' || $invitation->role === 'instructor')
                <div class="form-group">
                    <label class="label">Contact Number</label>
                    <div class="contact-input-group">
                        <span class="contact-prefix">+63</span>
                        <input type="text" id="contact" name="contact" value="{{ old('contact', $invitePayload['contact'] ?? '') }}" placeholder="9123456789" required maxlength="10">
                    </div>
                    <p class="field-help">Enter the 10-digit number after +63 (e.g., 9123456789).</p>
                    @error('contact') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="label">Address</label>
                    <input type="text" name="address" value="{{ old('address', $invitePayload['address'] ?? '') }}" placeholder="123 Street, City" required>
                    @error('address') <p class="error-text">{{ $message }}</p> @enderror
                </div>
                @endif

                <div class="form-group" style="margin-top: 32px;">
                    <label class="label">Create Password</label>
                    <input type="password" name="password" id="password" placeholder="••••••••" required>
                    @error('password') <p class="error-text">{{ $message }}</p> @enderror
                    
                    <div class="password-requirements" id="requirements">
                        <div class="requirement" data-req="length"><i>○</i> 8+ characters</div>
                        <div class="requirement" data-req="upper"><i>○</i> 1 Uppercase</div>
                        <div class="requirement" data-req="number"><i>○</i> 1 Number</div>
                        <div class="requirement" data-req="special"><i>○</i> 1 Special</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="label">Confirm Password</label>
                    <input type="password" name="password_confirmation" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn">Activate Account</button>
            </form>
        </div>
    </div>

    <script>
        const password = document.getElementById('password');
        const requirements = {
            length: val => val.length >= 8,
            upper: val => /[A-Z]/.test(val),
            number: val => /[0-9]/.test(val),
            special: val => /[!@#$%^&*(),.?":{}|<>]/.test(val)
        };

        password.addEventListener('input', e => {
            const val = e.target.value;
            Object.keys(requirements).forEach(key => {
                const isValid = requirements[key](val);
                const el = document.querySelector(`[data-req="${key}"]`);
                if (isValid) {
                    el.classList.add('valid');
                    el.querySelector('i').innerText = '●';
                } else {
                    el.classList.remove('valid');
                    el.querySelector('i').innerText = '○';
                }
            });
        });

        // Auto-strip leading zero from contact inputs
        const contactInput = document.getElementById('contact');
        if (contactInput) {
            contactInput.addEventListener('input', function(e) {
                let val = e.target.value;
                if (val.startsWith('0')) {
                    e.target.value = val.substring(1);
                }
                // Allow only numbers
                e.target.value = e.target.value.replace(/\D/g, '');
            });
        }
    </script>
</body>
</html>
