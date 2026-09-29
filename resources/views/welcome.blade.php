<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveED Hub</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background-color: #053d86;
        }

        .main-container {
            display: flex;
            min-height: 100vh;
        }

        /* Left Section - Hero/Branding */
        .left-section {
            flex: 0 0 50%;
            display: flex;
            flex-direction: column;
        }

        /* Upper section - Logo image */
        .logo-section {
            flex: 0 0 50%;
            background-image: url('/images/SystemLogo1.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-color: #053d86;
        }

        /* Lower section - Driving image */
        .hero-section {
            flex: 0 0 50%;
            background-image: url('/images/DrivingGuyImage.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-color: #053d86;
            overflow: hidden;
        }

        .hero-section img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .hero-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            border-top: 2px dashed rgba(255,255,255,0.2);
            color: rgba(255,255,255,0.4);
            font-size: 0.9rem;
            text-align: center;
        }

        /* Right Section - Schools List */
        .right-section {
            flex: 0 0 50%;
            background: #053d86;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
        }

        .section-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #fbbf24;
        }

        .section-header h2 {
            font-size: 1.8rem;
            color: #ffffff;
            font-weight: bold;
        }

        .schools-list {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            overflow-y: auto;
            align-content: start;
        }

        .school-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .school-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .school-card h3 {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 12px;
            text-align: center;
            font-weight: 600;
        }

        .school-card .enter-btn {
            display: block;
            background: #fbbf24;
            color: #053d86;
            padding: 10px 20px;
            border-radius: 25px;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s;
            border: 2px solid #fbbf24;
        }

        .school-card .enter-btn:hover {
            background: #f59e0b;
            border-color: #f59e0b;
            transform: scale(1.02);
        }

        /* System Admin Link - subtle at bottom */
        .admin-link {
            text-align: center;
            margin-top: auto;
            padding-top: 20px;
        }

        .admin-link a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.3s;
        }

        .admin-link a:hover {
            color: #fbbf24;
            text-decoration: underline;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.4);
            z-index: 100;
            pointer-events: none;
        }

        .footer span {
            pointer-events: auto;
        }

        .footer a {
            color: rgba(255,255,255,0.4);
            text-decoration: none;
            transition: color 0.3s;
            pointer-events: auto;
        }

        .footer a:hover {
            color: #fbbf24;
        }

        .admin-card .admin-btn:hover {
            background: #4b5563;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .main-container {
                flex-direction: column;
            }

            .left-section {
                min-height: 400px;
                flex: 0 0 auto;
            }

            .right-section {
                flex: 0 0 auto;
                width: 100%;
                padding: 30px 20px;
            }
        }

        @media (max-width: 480px) {
            .section-header h2 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Left Section -->
        <div class="left-section">
            <!-- Upper: Logo image -->
            <div class="logo-section"></div>

            <!-- Lower: Driving image -->
            <div class="hero-section"></div>
        </div>

        <!-- Right Section - Schools List -->
        <div class="right-section">
            <div class="section-header">
                <h2>Driving Schools</h2>
            </div>

            <div class="schools-list">
                @foreach($schools as $school)
                <div class="school-card">
                    <h3>{{ $school->name }}</h3>
                    <a href="{{ route('schools.login', ['school' => $school->slug]) }}" class="enter-btn">Enter</a>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Footer with hidden admin link -->
    <div class="footer">
        <span>© 2025 DriveED Hub. All rights reserved.</span>
        <a href="{{ route('system-admin.login') }}">Admin</a>
    </div>
</body>
</html>
