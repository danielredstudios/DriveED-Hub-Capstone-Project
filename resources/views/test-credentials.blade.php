<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Credentials - {{ $school->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 1200px;
            width: 100%;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.95;
        }

        .warning-banner {
            background: #fbbf24;
            color: #78350f;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .warning-banner svg {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }

        .accounts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .account-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .account-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }

        .account-card.admin {
            border-top: 4px solid #ef4444;
        }

        .account-card.instructor {
            border-top: 4px solid #3b82f6;
        }

        .account-card.student {
            border-top: 4px solid #10b981;
        }

        .account-card.guest {
            border-top: 4px solid #f59e0b;
        }

        .account-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .account-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .account-card.admin .account-icon {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        }

        .account-card.instructor .account-icon {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        }

        .account-card.student .account-icon {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        }

        .account-card.guest .account-icon {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        }

        .account-title {
            flex: 1;
        }

        .account-title h3 {
            font-size: 1.2rem;
            color: #1f2937;
            margin-bottom: 3px;
        }

        .account-title .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .account-card.admin .badge {
            background: #fee2e2;
            color: #991b1b;
        }

        .account-card.instructor .badge {
            background: #dbeafe;
            color: #1e40af;
        }

        .account-card.student .badge {
            background: #d1fae5;
            color: #065f46;
        }

        .account-card.guest .badge {
            background: #fef3c7;
            color: #92400e;
        }

        .credential-row {
            margin-bottom: 15px;
        }

        .credential-label {
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .credential-value {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f9fafb;
            padding: 12px 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.95rem;
            color: #1f2937;
        }

        .credential-value span {
            flex: 1;
        }

        .copy-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .copy-btn:hover {
            filter: brightness(1.1);
            transform: scale(1.05);
        }

        .copy-btn.copied {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .login-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            text-align: center;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .info-box {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .info-box h3 {
            color: #1f2937;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-box ul {
            list-style: none;
            padding: 0;
        }

        .info-box li {
            padding: 10px 0;
            color: #4b5563;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-box li:last-child {
            border-bottom: none;
        }

        .info-box li strong {
            color: #1f2937;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .account-card, .info-box, .warning-banner {
            animation: fadeIn 0.5s ease forwards;
        }

        .account-card:nth-child(1) { animation-delay: 0.1s; }
        .account-card:nth-child(2) { animation-delay: 0.2s; }
        .account-card:nth-child(3) { animation-delay: 0.3s; }
        .account-card:nth-child(4) { animation-delay: 0.4s; }
        .account-card:nth-child(5) { animation-delay: 0.5s; }
        .account-card:nth-child(6) { animation-delay: 0.6s; }
        .account-card:nth-child(7) { animation-delay: 0.7s; }
        .account-card:nth-child(8) { animation-delay: 0.8s; }

        @media (max-width: 768px) {
            .accounts-grid {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Test Credentials</h1>
            <p>{{ $school->name }} - Development Testing Accounts</p>
        </div>

        <div class="warning-banner">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>Warning: DEVELOPMENT ONLY - These credentials are for testing purposes only. This page is disabled in production.</span>
        </div>

        <div class="accounts-grid">
            <!-- Admin Account -->
            <div class="account-card admin">
                <div class="account-header">
                    <div class="account-icon"></div>
                    <div class="account-title">
                        <h3>Test Admin</h3>
                        <span class="badge">Admin</span>
                    </div>
                </div>
                <div class="credential-row">
                    <div class="credential-label">Email</div>
                    <div class="credential-value">
                        <span>admin@test.com</span>
                        <button class="copy-btn" onclick="copyToClipboard('admin@test.com', this)">
                            Copy
                        </button>
                    </div>
                </div>
                <div class="credential-row">
                    <div class="credential-label">Password</div>
                    <div class="credential-value">
                        <span>password</span>
                        <button class="copy-btn" onclick="copyToClipboard('password', this)">
                            Copy
                        </button>
                    </div>
                </div>
                <a href="{{ route('schools.login', $school->slug) }}" class="login-btn">Login as Admin</a>
            </div>

            <!-- Instructor 1 -->
            <div class="account-card instructor">
                <div class="account-header">
                    <div class="account-icon"></div>
                    <div class="account-title">
                        <h3>Test Instructor</h3>
                        <span class="badge">Instructor</span>
                    </div>
                </div>
                <div class="credential-row">
                    <div class="credential-label">Email</div>
                    <div class="credential-value">
                        <span>instructor@test.com</span>
                        <button class="copy-btn" onclick="copyToClipboard('instructor@test.com', this)">
                            Copy
                        </button>
                    </div>
                </div>
                <div class="credential-row">
                    <div class="credential-label">Password</div>
                    <div class="credential-value">
                        <span>password</span>
                        <button class="copy-btn" onclick="copyToClipboard('password', this)">
                            Copy
                        </button>
                    </div>
                </div>
                <a href="{{ route('schools.login', $school->slug) }}" class="login-btn">Login as Instructor</a>
            </div>

            <!-- Instructor 2 -->
            <div class="account-card instructor">
                <div class="account-header">
                    <div class="account-icon"></div>
                    <div class="account-title">
                        <h3>John Instructor</h3>
                        <span class="badge">Instructor</span>
                    </div>
                </div>
                <div class="credential-row">
                    <div class="credential-label">Email</div>
                    <div class="credential-value">
                        <span>instructor2@test.com</span>
                        <button class="copy-btn" onclick="copyToClipboard('instructor2@test.com', this)">
                            Copy
                        </button>
                    </div>
                </div>
                <div class="credential-row">
                    <div class="credential-label">Password</div>
                    <div class="credential-value">
                        <span>password</span>
                        <button class="copy-btn" onclick="copyToClipboard('password', this)">
                            Copy
                        </button>
                    </div>
                </div>
                <a href="{{ route('schools.login', $school->slug) }}" class="login-btn">Login as Instructor</a>
            </div>

            <!-- Student 1 -->
            <div class="account-card student">
                <div class="account-header">
                    <div class="account-icon"></div>
                    <div class="account-title">
                        <h3>Test Student</h3>
                        <span class="badge">Student (Approved)</span>
                    </div>
                </div>
                <div class="credential-row">
                    <div class="credential-label">Email</div>
                    <div class="credential-value">
                        <span>student@test.com</span>
                        <button class="copy-btn" onclick="copyToClipboard('student@test.com', this)">
                            Copy
                        </button>
                    </div>
                </div>
                <div class="credential-row">
                    <div class="credential-label">Password</div>
                    <div class="credential-value">
                        <span>password</span>
                        <button class="copy-btn" onclick="copyToClipboard('password', this)">
                            Copy
                        </button>
                    </div>
                </div>
                <a href="{{ route('schools.login', $school->slug) }}" class="login-btn">Login as Student</a>
            </div>

            <!-- Student 2 -->
            <div class="account-card student">
                <div class="account-header">
                    <div class="account-icon"></div>
                    <div class="account-title">
                        <h3>Jane Student</h3>
                        <span class="badge">Student (Approved)</span>
                    </div>
                </div>
                <div class="credential-row">
                    <div class="credential-label">Email</div>
                    <div class="credential-value">
                        <span>student2@test.com</span>
                        <button class="copy-btn" onclick="copyToClipboard('student2@test.com', this)">
                            Copy
                        </button>
                    </div>
                </div>
                <div class="credential-row">
                    <div class="credential-label">Password</div>
                    <div class="credential-value">
                        <span>password</span>
                        <button class="copy-btn" onclick="copyToClipboard('password', this)">
                            Copy
                        </button>
                    </div>
                </div>
                <a href="{{ route('schools.login', $school->slug) }}" class="login-btn">Login as Student</a>
            </div>

            <!-- Guest 1 -->
            <div class="account-card guest">
                <div class="account-header">
                    <div class="account-icon"></div>
                    <div class="account-title">
                        <h3>Test Guest</h3>
                        <span class="badge">Guest (Pending)</span>
                    </div>
                </div>
                <div class="credential-row">
                    <div class="credential-label">Email</div>
                    <div class="credential-value">
                        <span>guest@test.com</span>
                        <button class="copy-btn" onclick="copyToClipboard('guest@test.com', this)">
                            Copy
                        </button>
                    </div>
                </div>
                <div class="credential-row">
                    <div class="credential-label">Password</div>
                    <div class="credential-value">
                        <span>password</span>
                        <button class="copy-btn" onclick="copyToClipboard('password', this)">
                            Copy
                        </button>
                    </div>
                </div>
                <a href="{{ route('schools.login', $school->slug) }}" class="login-btn">Login as Guest</a>
            </div>

            <!-- Guest 2 -->
            <div class="account-card guest">
                <div class="account-header">
                    <div class="account-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg></div>
                    <div class="account-title">
                        <h3>Mary Guest</h3>
                        <span class="badge">Guest (Pending)</span>
                    </div>
                </div>
                <div class="credential-row">
                    <div class="credential-label">Email</div>
                    <div class="credential-value">
                        <span>guest2@test.com</span>
                        <button class="copy-btn" onclick="copyToClipboard('guest2@test.com', this)">
                            Copy
                        </button>
                    </div>
                </div>
                <div class="credential-row">
                    <div class="credential-label">Password</div>
                    <div class="credential-value">
                        <span>password</span>
                        <button class="copy-btn" onclick="copyToClipboard('password', this)">
                            Copy
                        </button>
                    </div>
                </div>
                <a href="{{ route('schools.login', $school->slug) }}" class="login-btn">Login as Guest</a>
            </div>
        </div>

        <div class="info-box">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Testing Information
            </h3>
            <ul>
                <li><strong>All Passwords:</strong> password</li>
                <li><strong>School Slug:</strong> {{ $school->slug }}</li>
                <li><strong>Courses Available:</strong> Theoretical (₱3,000) and Practical (₱8,000)</li>
                <li><strong>Guest Enrollment Requests:</strong> 2 pending approval</li>
                <li><strong>Approved Enrollment:</strong> Test Student enrolled in Theoretical</li>
                <li><strong>Quick Tip:</strong> Use the copy buttons to quickly paste credentials</li>
            </ul>
        </div>
    </div>

    <script>
        function copyToClipboard(text, button) {
            navigator.clipboard.writeText(text).then(() => {
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                button.classList.add('copied');
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.remove('copied');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
                alert('Failed to copy to clipboard');
            });
        }

        // Display a welcome toast
        window.addEventListener('load', () => {
            console.log('%c🔒 Test Credentials Loaded!', 'color: #667eea; font-size: 20px; font-weight: bold;');
            console.log('%cAll accounts use password: "password"', 'color: #10b981; font-size: 14px;');
        });
    </script>
</body>
</html>
