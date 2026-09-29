<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DriveEd Hub')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: {{ $school->schoolSetting->primary_color ?? '#0076FE' }};
            --secondary-color: {{ $school->schoolSetting->secondary_color ?? '#0046CC' }};
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        main {
            flex: 1;
        }
        
        .guest-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 1.25rem 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            color: white;
            border-bottom: none;
        }
        
        .header-logo {
            height: 40px;
            width: auto;
        }
        
        .school-name {
            font-weight: 700;
            font-size: 1.25rem;
            margin-left: 0.75rem;
        }
        
        .footer {
            padding: 2rem 0;
            color: #6c757d;
            font-size: 0.875rem;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <header class="guest-header">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                @if($school->schoolSetting && $school->schoolSetting->login_logo_image)
                    <img src="{{ asset('storage/' . $school->schoolSetting->login_logo_image) }}" alt="{{ $school->name }}" class="header-logo">
                @endif
                <span class="school-name">{{ $school->name }}</span>
            </div>
            <div class="d-none d-sm-block">
                <span class="badge rounded-pill px-3 py-2 bg-white bg-opacity-10 text-white border border-white border-opacity-25" style="backdrop-filter: blur(4px); font-size: 0.75rem;">
                    <i class="fas fa-shield-alt me-1"></i> Secure Checkout
                </span>
            </div>
        </div>
    </header>

    <main>
        <div class="container mt-4">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-2 fs-5"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2 fs-5"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-2 fs-5"></i>
                        <div>{{ session('info') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
                        <ul class="mb-0 ps-3 text-start">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
        @yield('content')
    </main>

    <footer class="footer text-center">
        <div class="container">
            <p>&copy; {{ date('Y') }} {{ $school->name }}. Powered by DriveEd Hub.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
