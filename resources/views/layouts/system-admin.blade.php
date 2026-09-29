<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - System Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #053d86;
            --secondary-color: #0a4a9e;
            --accent-color: #0356b3;
            --sidebar-bg: #1f2937;
            --sidebar-text: #e5e7eb;
            --sidebar-hover: #374151;
            --sidebar-active: #053d86;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .skip-link {
            position: absolute;
            left: -9999px;
            top: 0;
            background: #111827;
            color: #fff;
            padding: 10px 14px;
            border-radius: 0 0 8px 0;
            z-index: 3000;
            text-decoration: none;
            font-weight: 600;
        }

        .skip-link:focus {
            left: 0;
        }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible {
            outline: 3px solid rgba(5, 61, 134, 0.4);
            outline-offset: 2px;
        }
        
        .app-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            position: fixed;
            left: -250px;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: left 0.3s ease;
        }
        
        .sidebar.active {
            left: 0;
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
        
        .sidebar-header {
            padding: 20px;
            background: #053d86;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h1 {
            font-size: 1.25rem;
            font-weight: 600;
            color: white;
        }
        
        .sidebar-header p {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-top: 4px;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 12px 20px;
            color: var(--sidebar-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .menu-item:hover {
            background: var(--sidebar-hover);
            border-left-color: #053d86;
        }
        
        .menu-item.active {
            background: var(--sidebar-active);
            border-left-color: white;
            color: white;
        }
        
        .menu-item svg {
            width: 20px;
            height: 20px;
            margin-right: 12px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 0;
            flex: 1;
            min-height: 100vh;
        }
        
        /* Header */
        .header {
            background: white;
            padding: 16px 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .burger-menu {
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #374151;
            transition: background 0.2s;
        }
        
        .burger-menu:hover {
            background: #f3f4f6;
        }
        
        .header h2 {
            font-size: 1.5rem;
            color: #1f2937;
            margin: 0;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .user-name {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .btn-logout {
            background: var(--primary-color);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        
        .btn-logout:hover {
            background: var(--accent-color);
        }

        .inline-form {
            display: inline;
        }

        .form-group-actions {
            display: flex;
            align-items: flex-end;
        }

        .btn-full-width {
            width: 100%;
        }

        .stats-grid-two {
            grid-template-columns: repeat(2, 1fr);
            margin-bottom: 24px;
        }
        
        /* Content Area */
        .content {
            padding: 24px;
        }
        
        /* Alert Messages */
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
        
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .stat-card .subtext {
            font-size: 0.75rem;
            color: #9ca3af;
        }
        
        /* Card */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }
        
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h3 {
            font-size: 1.125rem;
            color: #1f2937;
            margin: 0;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Table */
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f9fafb;
        }
        
        th {
            padding: 12px 16px;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        td {
            padding: 12px 16px;
            border-top: 1px solid #e5e7eb;
            font-size: 0.875rem;
        }
        
        tr:hover {
            background: #f9fafb;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }
        
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .badge-secondary {
            background: #f3f4f6;
            color: #374151;
        }
        
        /* Filters */
        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        
        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--accent-color);
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            gap: 8px;
            margin-top: 20px;
        }
        
        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
            text-decoration: none;
            color: #374151;
        }
        
        .pagination a:hover {
            background: #f9fafb;
        }
        
        .pagination .active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        /* Table responsive wrapper */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* ====== Empty State Styling ====== */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: #9ca3af;
        }
        .empty-state svg {
            width: 56px;
            height: 56px;
            margin: 0 auto 16px;
            color: #d1d5db;
            display: block;
        }
        .empty-state .empty-state-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 6px;
        }
        .empty-state .empty-state-text {
            font-size: 0.9rem;
            color: #9ca3af;
        }

        /* ====== Responsive Breakpoints ====== */
        
        /* Tablets & smaller desktops */
        @media (max-width: 1024px) {
            .content {
                padding: 16px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 12px;
            }
            
            .filter-grid {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 12px;
            }
        }
        
        /* Tablets portrait */
        @media (max-width: 768px) {
            .header {
                padding: 12px 16px;
            }
            
            .header h2 {
                font-size: 1.2rem;
            }
            
            .user-name {
                display: none;
            }
            
            .content {
                padding: 12px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            
            .stat-card {
                padding: 14px;
            }
            
            .stat-card .value {
                font-size: 1.5rem;
            }
            
            th, td {
                padding: 8px 10px;
                font-size: 0.8rem;
            }
            
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .card-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
            
            /* Enlarge touch targets */
            .btn, .btn-logout {
                padding: 10px 16px;
                font-size: 0.9rem;
                min-height: 44px;
            }
            
            .badge {
                padding: 6px 12px;
                font-size: 0.75rem;
            }
        }
        
        /* Phones */
        @media (max-width: 480px) {
            .header {
                padding: 10px 12px;
            }
            
            .header h2 {
                font-size: 1rem;
            }
            
            .content {
                padding: 10px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .stat-card {
                padding: 12px;
            }
            
            .stat-card .value {
                font-size: 1.25rem;
            }
            
            .card-body {
                padding: 12px;
            }
            
            .pagination {
                flex-wrap: wrap;
                gap: 4px;
            }
            
            .pagination a,
            .pagination span {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
        }
        
        /* ====== Print Styles ====== */
        @media print {
            .sidebar, .sidebar-overlay, .burger-menu,
            .topbar, .btn, button, .dropdown { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            body { background: white !important; }
            .table-container { overflow: visible !important; }
            table { width: 100% !important; border-collapse: collapse; }
            th, td { border: 1px solid #ddd !important; padding: 6px 8px !important; font-size: 11pt; }
            .badge { border: 1px solid #999 !important; background: white !important; color: #333 !important; }
            a { text-decoration: none !important; color: #333 !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; page-break-inside: avoid; }
        }
    </style>
    @yield('styles')
    
    <style>
        :root {
            --primary-color: #2563eb; /* Default system admin blue */
        }
    </style>
</head>
<body>
    <a href="#mainContent" class="skip-link">Skip to main content</a>
    <div class="app-container">
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()" role="presentation" aria-hidden="true"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar" role="navigation" aria-label="System admin navigation">
            <div class="sidebar-header">
                <h1>System Admin</h1>
                <p>Global Management Portal</p>
            </div>
            <nav class="sidebar-menu" role="menubar">
                <a href="{{ route('system-admin.dashboard') }}" class="menu-item {{ request()->routeIs('system-admin.dashboard') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('system-admin.schools') }}" class="menu-item {{ request()->routeIs('system-admin.schools') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Schools
                </a>
                <a href="{{ route('system-admin.admins') }}" class="menu-item {{ request()->routeIs('system-admin.admins') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    School Admins
                </a>
                <a href="{{ route('system-admin.users') }}" class="menu-item {{ request()->routeIs('system-admin.users') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Users
                </a>
                <a href="{{ route('system-admin.logs') }}" class="menu-item {{ request()->routeIs('system-admin.logs*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    System Logs
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main id="mainContent" class="main-content" tabindex="-1">
            <header class="header">
                <div class="header-left">
                    <button class="burger-menu" onclick="toggleSidebar()" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="sidebar">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h2>@yield('page-title', 'Dashboard')</h2>
                </div>
                <div class="user-menu">
                    <span class="user-name">{{ Auth::guard('admin')->user()->name }}</span>
                    <form action="{{ route('system-admin.logout') }}" method="POST" class="inline-form">
                        @csrf
                        <button type="submit" class="btn-logout">Logout</button>
                    </form>
                </div>
            </header>

            <div class="content">
                @yield('content')
            </div>
        </main>
    </div>
    <script>
    (function() {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        var burger = document.querySelector('.burger-menu');

        window.toggleSidebar = function() {
            if (!sidebar || !overlay) return;
            var isActive = sidebar.classList.toggle('active');
            overlay.classList.toggle('active', isActive);
            if (burger) {
                burger.setAttribute('aria-expanded', isActive ? 'true' : 'false');
            }
        };

        window.closeSidebar = function() {
            if (!sidebar || !overlay) return;
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            if (burger) {
                burger.setAttribute('aria-expanded', 'false');
            }
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.closeSidebar();
            }
        });
    })();
    </script>
    {{-- Global: Prevent double form submissions --}}
    <style>
        .btn-submitting {
            opacity: 0.65;
            pointer-events: none;
            position: relative;
        }
        .btn-submitting::after {
            content: '';
            display: inline-block;
            width: 14px;
            height: 14px;
            margin-left: 8px;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: btn-spin 0.6s linear infinite;
            vertical-align: middle;
        }
        @keyframes btn-spin {
            to { transform: rotate(360deg); }
        }
    </style>
    <script>
    (function() {
        document.addEventListener('submit', function(e) {
            var form = e.target;
            var method = (form.method || 'GET').toUpperCase();
            if (method === 'GET') return;
            if (form.dataset.noSubmitGuard) return;

            if (form.dataset.submitting === 'true') {
                e.preventDefault();
                return;
            }

            form.dataset.submitting = 'true';

            var buttons = form.querySelectorAll('button[type="submit"], input[type="submit"], button:not([type])');
            buttons.forEach(function(btn) {
                btn.classList.add('btn-submitting');
                btn.disabled = true;
            });

            setTimeout(function() {
                form.dataset.submitting = 'false';
                buttons.forEach(function(btn) {
                    btn.classList.remove('btn-submitting');
                    btn.disabled = false;
                });
            }, 8000);
        }, true);
    })();
    </script>
    @include('partials.toast-notifications')
</body>
</html>
