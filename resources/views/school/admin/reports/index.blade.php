@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Reports & Analytics')

@section('content')
    <?php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
    $accentColor = $settings?->accent_color ?? '#1e40af';
    $useGradient = $settings?->use_gradient_header ?? true;
    $headerTextColor = $settings?->header_text_color ?? '#ffffff';
    ?>

    @include('school.admin.partials.admin-styles')

    <style>
        .reports-container {
            padding: 20px;
            margin: 20px auto;
            max-width: 1600px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid
                <?php echo $primaryColor; ?>
            ;
        }

        .page-header h1 {
            color: #1f2937;
            font-size: 1.75rem;
            margin: 0;
            font-weight: 600;
        }

        .icon-inline-middle {
            vertical-align: middle;
        }

        .icon-24 {
            width: 24px;
            height: 24px;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        /* Use shared admin-styles for stat cards */
        .stat-card .subtitle {
            color: #6b7280;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        /* Collapsible Section Styles */
        .collapsible-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .section-header {
            padding: 12px 18px;
            <?php if ($useGradient): ?>
            background: linear-gradient(135deg,
                    <?php    echo $primaryColor; ?>
                    0%,
                    <?php    echo $secondaryColor; ?>
                    100%);
            <?php else: ?>
            background:
                <?php    echo $primaryColor; ?>
            ;
            <?php endif; ?>
            color:
                <?php echo $headerTextColor; ?>
            ;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s ease;
            user-select: none;
        }

        .section-header:hover {
            opacity: 0.85;
        }

        .section-header h2 {
            color:
                <?php echo $headerTextColor; ?>
            ;
            font-size: 0.95rem;
            font-weight: 600;
            margin: 0;
        }

        .collapse-icon {
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .collapse-icon.collapsed {
            transform: rotate(-90deg);
        }

        .section-content {
            padding: 20px;
            display: block;
            transition: all 0.3s ease;
        }

        .section-content.collapsed {
            display: none;
        }

        .reports-table {
            width: 100%;
            border-collapse: collapse;
        }

        .reports-table th,
        .reports-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }

        .reports-table th {
            background: #f8f9fa;
            color: #333;
            font-weight: 600;
        }

        .reports-table tr:hover {
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
        }

        /* Progress Bars in Tables */
        .progress-bar {
            background: #e5e7eb;
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
            margin-bottom: 4px;
            min-width: 80px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 10px;
            background:
                <?php echo $primaryColor; ?>
            ;
            transition: width 0.5s ease;
        }

        .progress-fill-dynamic {
            width: 0;
        }

        .progress-fill-success {
            background: #10b981;
        }

        .progress-fill-warning {
            background: #f59e0b;
        }

        .progress-bar-tight {
            margin-bottom: 2px;
        }

        /* Time Period Filter */
        .period-filter {
            display: flex;
            gap: 8px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .period-btn {
            padding: 8px 18px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            color: #555;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .period-btn:hover {
            border-color:
                <?php echo $primaryColor; ?>
            ;
            color:
                <?php echo $primaryColor; ?>
            ;
        }

        .period-btn.active {
            background:
                <?php echo $primaryColor; ?>
            ;
            border-color:
                <?php echo $primaryColor; ?>
            ;
            color: white;
        }

        /* Visual Bar Chart */
        .bar-chart {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 15px;
        }

        .bar-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .bar-label {
            min-width: 140px;
            font-size: 0.85rem;
            color: #555;
            font-weight: 500;
            text-align: right;
            flex-shrink: 0;
        }

        .bar-track {
            flex: 1;
            background: #f0f1f3;
            border-radius: 6px;
            height: 24px;
            overflow: hidden;
            position: relative;
        }

        .bar-fill {
            height: 100%;
            border-radius: 6px;
            <?php if ($useGradient): ?>
            background: linear-gradient(90deg,
                    <?php    echo $primaryColor; ?>
                    0%,
                    <?php    echo $secondaryColor; ?>
                    100%);
            <?php else: ?>
            background:
                <?php    echo $primaryColor; ?>
            ;
            <?php endif; ?>
            transition: width 0.6s ease;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 8px;
            min-width: fit-content;
        }

        .bar-fill-dynamic {
            width: 0;
        }

        .bar-value {
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
        }

        .bar-count {
            min-width: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #333;
            text-align: left;
        }

        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border-left: 3px solid
                <?php echo $primaryColor; ?>
            ;
        }

        .stat-box .label {
            color: #7f8c8d;
            font-size: 12px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .stat-box .value {
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .value-success {
            color: #10b981 !important;
        }

        .value-danger {
            color: #ef4444 !important;
        }

        .value-warning {
            color: #f59e0b !important;
        }

        .value-positive {
            color: #10b981 !important;
        }

        .value-negative {
            color: #ef4444 !important;
        }

        .value-primary {
            color:
                <?php echo $primaryColor; ?>
                !important;
        }

        .text-warning {
            color: #f59e0b;
        }

        .text-muted {
            color: #9ca3af;
        }

        .text-success {
            color: #10b981;
        }

        .text-danger {
            color: #ef4444;
        }

        .text-strong-success {
            font-weight: 600;
            color: #059669;
        }

        .text-percentage {
            font-size: 0.8rem;
            font-weight: 600;
        }

        .text-percentage-success {
            color: #10b981;
        }

        .text-percentage-warning {
            color: #f59e0b;
        }

        .section-subtitle {
            margin-top: 25px;
            margin-bottom: 15px;
        }

        .section-subtitle-compact {
            margin-bottom: 15px;
            font-size: 1rem;
            color: #555;
        }

        .section-divider {
            margin: 25px 0;
            border: none;
            border-top: 1px solid #e5e7eb;
        }

        /* Improved Table Styling */
        .reports-table th {
            background: #f1f3f5;
            color: #333;
            font-weight: 600;
        }

        .reports-table tbody tr:nth-child(even) {
            background: #fafbfc;
        }

        .detailed-reports-section {
            margin-bottom: 30px;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .detailed-reports-section h2 {
            color: #333;
            font-size: 1.3rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
        }

        .report-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .report-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            border-color:
                <?php echo $primaryColor; ?>
            ;
            background: white;
        }

        .report-icon {
            font-size: 1.8rem;
            min-width: 40px;
            text-align: center;
        }

        .report-info {
            flex: 1;
        }

        .report-title {
            font-weight: 600;
            font-size: 1rem;
            color: #2c3e50;
            margin-bottom: 3px;
        }

        .report-description {
            font-size: 0.85rem;
            color: #6c757d;
        }

        /* Export Dropdown Styles */
        .export-dropdown {
            position: relative;
            display: inline-block;
        }

        .export-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            <?php if ($useGradient): ?>
            background: linear-gradient(135deg,
                    <?php    echo $primaryColor; ?>
                    0%,
                    <?php    echo $secondaryColor; ?>
                    100%);
            <?php else: ?>
            background:
                <?php    echo $primaryColor; ?>
            ;
            <?php endif; ?>
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .export-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .export-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 5px);
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            min-width: 220px;
            z-index: 100;
            display: none;
            overflow: hidden;
        }

        .export-menu.show {
            display: block;
        }

        .export-menu-title {
            padding: 12px 16px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #f8f9fa;
            border-bottom: 1px solid #e5e7eb;
        }

        .export-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            color: #333;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.2s ease;
        }

        .export-menu a:hover {
            background: #f0f4ff;
        }

        .export-menu a span.icon {
            font-size: 1.1rem;
            min-width: 24px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .reports-container {
                padding: 15px;
            }

            .page-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .period-filter {
                gap: 6px;
            }

            .period-btn {
                padding: 6px 12px;
                font-size: 0.8rem;
            }

            .metrics-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats-summary {
                grid-template-columns: repeat(2, 1fr);
            }

            .bar-label {
                min-width: 80px;
                font-size: 0.75rem;
            }

            .reports-table {
                font-size: 0.8rem;
            }

            .reports-table th,
            .reports-table td {
                padding: 8px 6px;
            }
        }
    </style>

    <div class="reports-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Reports & Analytics</h1>
            <div class="export-dropdown">
                <button class="export-btn" onclick="toggleExportMenu(event)">
                    <svg class="icon-inline-middle" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                        fill="currentColor" viewBox="0 0 16 16">
                        <path
                            d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                        <path
                            d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                    </svg>
                    Export Data
                </button>
                <div class="export-menu" id="exportMenu">
                    <div class="export-menu-title">Download as Excel</div>
                    <a href="{{ route('schools.admin.exports.students.excel', $school) }}">
                        Students
                    </a>
                    <a href="{{ route('schools.admin.exports.instructors.excel', $school) }}">
                        Instructors
                    </a>
                    <a href="{{ route('schools.admin.exports.verify-session-completion.excel', $school) }}">
                        Schedules
                    </a>
                    <a href="{{ route('schools.admin.exports.payments.excel', $school) }}">
                        Payments
                    </a>
                    <a href="{{ route('schools.admin.exports.courses.excel', $school) }}">
                        Courses
                    </a>
                </div>
            </div>
        </div>

        <!-- Time Period Filter -->
        @php $currentPeriod = request('period', 'all'); @endphp
        @php
            $periodLabel = match ($currentPeriod) {
                'today' => 'Today',
                'week' => 'This Week',
                'month' => 'This Month',
                'year' => 'This Year',
                default => 'All Time',
            };
        @endphp
        <div class="period-filter">
            <button class="period-btn {{ $currentPeriod === 'today' ? 'active' : '' }}"
                onclick="filterPeriod('today')">Today</button>
            <button class="period-btn {{ $currentPeriod === 'week' ? 'active' : '' }}" onclick="filterPeriod('week')">This
                Week</button>
            <button class="period-btn {{ $currentPeriod === 'month' ? 'active' : '' }}" onclick="filterPeriod('month')">This
                Month</button>
            <button class="period-btn {{ $currentPeriod === 'year' ? 'active' : '' }}" onclick="filterPeriod('year')">This
                Year</button>
            <button class="period-btn {{ $currentPeriod === 'all' ? 'active' : '' }}" onclick="filterPeriod('all')">All
                Time</button>
        </div>

        <!-- Key Metrics Summary (Always Visible) -->
        <div class="metrics-grid">
            <div class="stat-card info">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Total Student Accounts</div>
                            <div class="stat-value">{{ $analytics['total_students'] }}</div>
                            <div class="subtitle text-success" style="font-weight: 600;">
                                {{ $analytics['active_enrollments'] }} Active Enrollments</div>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="stat-card growth">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Total Instructors</div>
                            <div class="stat-value">{{ $analytics['total_instructors'] }}</div>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="stat-card pending">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">This Month Schedules</div>
                            <div class="stat-value">{{ $analytics['total_bookings_this_month'] }}</div>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="stat-card active">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Completed Lessons</div>
                            <div class="stat-value">{{ $analytics['completed_lessons_this_month'] }}</div>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="stat-card {{ $analytics['completion_rate'] >= 70 ? 'active' : 'pending' }}">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Completion Rate</div>
                            <div class="stat-value">{{ number_format($analytics['completion_rate'], 1) }}%</div>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Enrollment Section (Collapsible) -->
        <div class="collapsible-section">
            <div class="section-header" onclick="toggleSection(this)">
                <h2>Student Enrollment Overview</h2>
                <span class="collapse-icon collapsed"><svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg></span>
            </div>
            <div class="section-content collapsed">
                <div class="stats-summary">
                    <div class="stat-box">
                        <div class="label">Total Students</div>
                        <div class="value">{{ $analytics['total_students'] }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Active Enrollments</div>
                        <div class="value value-success">{{ $analytics['active_enrollments'] }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="label">{{ $periodLabel }} Enrollments</div>
                        <div class="value">{{ $analytics['enrollments_this_month'] }}</div>
                    </div>
                </div>
                <div class="stat-label">{{ $periodLabel }} Schedules</div>
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalStudents = $analytics['total_students'];
                        @endphp
                        @forelse($analytics['students_by_status'] as $statusData)
                            @php
                                $percentage = $totalStudents > 0 ? ($statusData->count / $totalStudents) * 100 : 0;
                                $badgeClass = match ($statusData->status) {
                                    'active' => 'badge-success',
                                    'inactive' => 'badge-warning',
                                    'graduated' => 'badge-info',
                                    default => 'badge-secondary'
                                };
                            @endphp
                            <tr>
                                <td><span class="badge {{ $badgeClass }}">{{ ucfirst($statusData->status) }}</span></td>
                                <td>{{ $statusData->count }}</td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill progress-fill-dynamic" data-width="{{ $percentage }}"></div>
                                    </div>
                                    {{ number_format($percentage, 1) }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state">No student data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Schedule Analytics Section (Collapsible) -->
        <div class="collapsible-section">
            <div class="section-header" onclick="toggleSection(this)">
                <h2>Schedule Analytics</h2>
                <span class="collapse-icon collapsed"><svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg></span>
            </div>
            <div class="section-content collapsed">
                <div class="stats-summary">
                    <div class="stat-box">
                        <div class="label">Total Schedules</div>
                        <div class="value">{{ $analytics['total_all_bookings'] }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="label">{{ $periodLabel }}</div>
                        <div class="value">{{ $analytics['total_bookings_this_month'] }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Completed</div>
                        <div class="value value-success">{{ $analytics['completed_lessons_this_month'] }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Success Rate</div>
                        <div class="value">{{ number_format($analytics['completion_rate'], 1) }}%</div>
                    </div>
                </div>

                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalBookings = $analytics['total_all_bookings'];
                        @endphp
                        @forelse($analytics['bookings_by_status'] as $statusData)
                            @php
                                $percentage = $totalBookings > 0 ? ($statusData->count / $totalBookings) * 100 : 0;
                                $badgeClass = match ($statusData->status) {
                                    'completed' => 'badge-success',
                                    'confirmed' => 'badge-info',
                                    'pending' => 'badge-warning',
                                    'cancelled', 'no-show', 'no_show' => 'badge-danger',
                                    default => 'badge-secondary'
                                };
                            @endphp
                            <tr>
                                <td><span class="badge {{ $badgeClass }}">{{ ucfirst($statusData->status) }}</span></td>
                                <td>{{ $statusData->count }}</td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill progress-fill-dynamic" data-width="{{ $percentage }}"></div>
                                    </div>
                                    {{ number_format($percentage, 1) }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state">No schedule data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Course Performance Section (Merged Analytics & Performance) -->
        <div class="collapsible-section">
            <div class="section-header" onclick="toggleSection(this)">
                <h2>Course Performance & Analytics</h2>
                <span class="collapse-icon collapsed"><svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg></span>
            </div>
            <div class="section-content collapsed">
                @php
                    $maxEnrolled = collect($analytics['course_stats'] ?? [])->max('total_enrolled') ?: 1;
                @endphp

                <!-- Visual Enrollment Chart -->
                @if(count($analytics['course_stats'] ?? []) > 0)
                    <h3 class="section-subtitle-compact">Enrollment by Course</h3>
                    <div class="bar-chart">
                        @foreach($analytics['course_stats'] ?? [] as $course)
                            <div class="bar-row">
                                <span class="bar-label">{{ Str::limit($course->title, 20) }}</span>
                                <div class="bar-track">
                                    <div class="bar-fill bar-fill-dynamic"
                                        data-width="{{ ($course->total_enrolled / $maxEnrolled) * 100 }}">
                                        @if($course->total_enrolled > 0)
                                            <span class="bar-value">{{ $course->total_enrolled }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="bar-count">{{ $course->total_enrolled }}</span>
                            </div>
                        @endforeach
                    </div>
                    <hr class="section-divider">
                @endif

                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Enrolled</th>
                            <th>Price</th>
                            <th>Completion Rate</th>
                            <th>Rating</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analytics['course_stats'] ?? [] as $course)
                            <tr>
                                <td><strong>{{ $course->title }}</strong></td>
                                <td><span class="badge badge-info">{{ $course->total_enrolled }}</span></td>
                                <td>&#8369;{{ number_format($course->price ?? 0, 2) }}</td>
                                <td>
                                    <div class="progress-bar progress-bar-tight">
                                        <div class="progress-fill progress-fill-dynamic {{ $course->completion_rate >= 70 ? 'progress-fill-success' : 'progress-fill-warning' }}"
                                            data-width="{{ $course->completion_rate }}"></div>
                                    </div>
                                    <span
                                        class="text-percentage {{ $course->completion_rate >= 70 ? 'text-percentage-success' : 'text-percentage-warning' }}">
                                        {{ number_format($course->completion_rate, 1) }}%
                                    </span>
                                </td>
                                <td>
                                    @if($course->average_rating)
                                        <span class="text-warning">&#9733;</span> {{ number_format($course->average_rating, 1) }}
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td class="text-strong-success">&#8369;{{ number_format($course->total_revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-state">No course data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Instructor Performance Section (Collapsible) -->
        <div class="collapsible-section">
            <div class="section-header" onclick="toggleSection(this)">
                <h2>Instructor Performance</h2>
                <span class="collapse-icon collapsed"><svg width="20" height="20" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg></span>
            </div>
            <div class="section-content collapsed">
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Instructor</th>
                            <th>Total Sessions</th>
                            <th>Completed</th>
                            <th>Rating</th>
                            <th>Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analytics['top_instructors'] as $instructor)
                            <tr>
                                <td><strong>{{ $instructor->name }}</strong></td>
                                <td>{{ $instructor->total_sessions }}</td>
                                <td>{{ $instructor->completed_sessions }}</td>
                                <td>
                                    @if($instructor->average_rating)
                                        <span class="text-warning">&#9733;</span>
                                        {{ number_format($instructor->average_rating, 1) }}
                                    @else
                                        <span class="text-muted">No ratings yet</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="progress-bar progress-bar-tight">
                                        <div class="progress-fill progress-fill-dynamic {{ $instructor->completion_rate >= 80 ? 'progress-fill-success' : 'progress-fill-warning' }}"
                                            data-width="{{ $instructor->completion_rate }}"></div>
                                    </div>
                                    <span
                                        class="text-percentage {{ $instructor->completion_rate >= 80 ? 'text-percentage-success' : 'text-percentage-warning' }}">
                                        {{ number_format($instructor->completion_rate, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">No instructor data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Attendance & Performance Section (Collapsible) -->
        <div class="collapsible-section">
            <div class="section-header" onclick="toggleSection(this)">
                <h2>Attendance & Performance</h2>
                <span class="collapse-icon collapsed"><svg width="20" height="20" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg></span>
            </div>
            <div class="section-content collapsed">
                <div class="stats-summary">
                    <div class="stat-box">
                        <div class="label">Attendance Rate</div>
                        <div class="value value-success">{{ number_format($analytics['attendance']['rate'] ?? 0, 1) }}%
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Attended</div>
                        <div class="value">{{ $analytics['attendance']['attended'] ?? 0 }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Missed</div>
                        <div class="value value-danger">{{ $analytics['attendance']['missed'] ?? 0 }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Cancellations</div>
                        <div class="value value-warning">{{ $analytics['cancellations']['total'] ?? 0 }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="label">No-Shows</div>
                        <div class="value value-danger">{{ $analytics['cancellations']['no_show'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lessons Report Section (Driving + Practical Merged) -->
        <div class="collapsible-section">
            <div class="section-header" onclick="toggleSection(this)">
                <h2>Lessons Report</h2>
                <span class="collapse-icon collapsed"><svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg></span>
            </div>
            <div class="section-content collapsed">
                <div class="stats-summary">
                    <div class="stat-box">
                        <div class="label">Total Lessons</div>
                        <div class="value">{{ $analytics['total_bookings_this_month'] }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Completed</div>
                        <div class="value value-success">{{ $analytics['completed_lessons_this_month'] }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Completion Rate</div>
                        <div class="value value-primary">{{ number_format($analytics['completion_rate'], 1) }}%</div>
                    </div>
                </div>

                <h3 class="section-subtitle">Lessons by Status</h3>
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analytics['lessons_by_status'] as $statusData)
                                            <tr>
                                                <td>
                                                    <span class="badge {{ match ($statusData->status) {
                                'completed' => 'badge-success',
                                'confirmed' => 'badge-info',
                                'pending' => 'badge-warning',
                                'cancelled', 'no-show', 'no_show' => 'badge-danger',
                                default => 'badge-secondary'
                            } }}">{{ ucfirst($statusData->status) }}</span>
                                                </td>
                                                <td>{{ $statusData->count }}</td>
                                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="empty-state">No lesson data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <h3 class="section-subtitle">Lessons by Instructor</h3>
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Instructor</th>
                            <th>Total Lessons</th>
                            <th>Completed</th>
                            <th>Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analytics['lessons_by_instructor'] as $instructor)
                            <tr>
                                <td>{{ $instructor->instructor_name }}</td>
                                <td>{{ $instructor->total_lessons }}</td>
                                <td>{{ $instructor->completed_lessons }}</td>
                                <td>
                                    <span class="{{ $instructor->completion_rate >= 70 ? 'text-success' : 'text-warning' }}">
                                        {{ number_format($instructor->completion_rate, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-state">No instructor data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Schedules & Cancellations Report (Merged) -->
        <div class="collapsible-section">
            <div class="section-header" onclick="toggleSection(this)">
                <h2>Schedules & Cancellations</h2>
                <span class="collapse-icon collapsed"><svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg></span>
            </div>
            <div class="section-content collapsed">
                <div class="stats-summary">
                    <div class="stat-box">
                        <div class="label">Total Cancellations</div>
                        <div class="value value-warning">{{ $analytics['cancellations']['total'] }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="label">No-Shows</div>
                        <div class="value value-danger">{{ $analytics['cancellations']['no_show'] }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Total Issues</div>
                        <div class="value value-danger">
                            {{ $analytics['cancellations']['total'] + $analytics['cancellations']['no_show'] }}</div>
                    </div>
                </div>

                <h3 class="section-subtitle">Recent Cancellations & No-Shows</h3>
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Instructor</th>
                            <th>Course</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analytics['cancellation_details'] as $booking)
                            <tr>
                                <td>{{ $booking->scheduled_at ? $booking->scheduled_at->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ $booking->student->name ?? 'N/A' }}</td>
                                <td>{{ $booking->instructor->name ?? 'Unassigned' }}</td>
                                <td>{{ $booking->course->title ?? 'N/A' }}</td>
                                <td>
                                    <span
                                        class="badge {{ $booking->status == 'cancelled' ? 'badge-warning' : 'badge-danger' }}">
                                        {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">No cancellations or no-shows</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Financial Report Section -->
        <div class="collapsible-section">
            <div class="section-header" onclick="toggleSection(this)">
                <h2>Financial Report</h2>
                <span class="collapse-icon collapsed"><svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg></span>
            </div>
            <div class="section-content collapsed">
                <div class="stats-summary">
                    <div class="stat-box">
                        <div class="label">Gross Revenue</div>
                        <div class="value">&#8369;{{ number_format($analytics['financial']['gross_revenue'], 2) }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Refunded</div>
                        <div class="value value-danger">
                            &#8369;{{ number_format($analytics['financial']['total_refunded'], 2) }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Net Revenue (Paid)</div>
                        <div class="value value-success">
                            &#8369;{{ number_format($analytics['financial']['total_revenue'], 2) }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="label">Pending *</div>
                        <div class="value value-warning">
                            &#8369;{{ number_format($analytics['financial']['pending_payments'], 2) }}</div>
                    </div>
                </div>
                <p class="text-muted" style="font-size: 0.85rem; margin-top: 10px;">
                    * Gross Revenue includes all <strong>approved</strong> forensic payments. Net Revenue is Gross minus
                    total <strong>refunded</strong> amounts. Pending reflects payments awaiting verification.
                </p>

                <h3 class="section-subtitle">Payments by Method</h3>
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Payment Method</th>
                            <th>Total Amount</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analytics['financial']['payments_by_method'] as $payment)
                            <tr>
                                <td><strong>{{ ucfirst($payment->method ?? 'N/A') }}</strong></td>
                                <td>&#8369;{{ number_format($payment->total, 2) }}</td>
                                <td>{{ $payment->count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state">No payment data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Student Progress Report Section -->
        <div class="collapsible-section">
            <div class="section-header" onclick="toggleSection(this)">
                <h2>Student Progress Report</h2>
                <span class="collapse-icon collapsed"><svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg></span>
            </div>
            <div class="section-content collapsed">
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Total Lessons</th>
                            <th>Completed</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analytics['student_progress'] as $student)
                                            <tr>
                                                <td><strong>{{ $student->name }}</strong></td>
                                                <td>{{ $student->email }}</td>
                                                <td>
                                                    <span class="badge {{ match ($student->status) {
                                'active' => 'badge-success',
                                'inactive' => 'badge-warning',
                                'graduated' => 'badge-info',
                                default => 'badge-secondary'
                            } }}">{{ ucfirst($student->status) }}</span>
                                                </td>
                                                <td>{{ $student->total_lessons }}</td>
                                                <td>{{ $student->completed_lessons }}</td>
                                                <td>
                                                    <div class="progress-bar progress-bar-tight">
                                                        <div class="progress-fill progress-fill-dynamic {{ $student->progress_rate >= 70 ? 'progress-fill-success' : 'progress-fill-warning' }}"
                                                            data-width="{{ $student->progress_rate }}"></div>
                                                    </div>
                                                    <span
                                                        class="text-percentage {{ $student->progress_rate >= 70 ? 'text-percentage-success' : 'text-percentage-warning' }}">
                                                        {{ number_format($student->progress_rate, 1) }}%
                                                    </span>
                                                </td>
                                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-state">No student progress data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleSection(header) {
            const content = header.nextElementSibling;
            const icon = header.querySelector('.collapse-icon');

            content.classList.toggle('collapsed');
            icon.classList.toggle('collapsed');
        }

        function toggleExportMenu(event) {
            event.stopPropagation();
            const menu = document.getElementById('exportMenu');
            menu.classList.toggle('show');
        }

        function applyDynamicWidths() {
            document.querySelectorAll('.progress-fill-dynamic, .bar-fill-dynamic').forEach(element => {
                const width = element.getAttribute('data-width');
                if (width !== null) {
                    element.style.width = `${Math.max(0, Math.min(100, parseFloat(width) || 0))}%`;
                }
            });
        }

        function filterPeriod(period) {
            const url = new URL(window.location.href);
            url.searchParams.set('period', period);

            // Use AJAX navigation if available, otherwise full page load
            if (typeof loadPage === 'function') {
                loadPage(url.pathname + url.search);
            } else {
                window.location.href = url.toString();
            }
        }

        // Close menu when clicking outside
        document.addEventListener('click', function (event) {
            const menu = document.getElementById('exportMenu');
            const dropdown = document.querySelector('.export-dropdown');
            if (!dropdown.contains(event.target)) {
                menu.classList.remove('show');
            }
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', applyDynamicWidths);
        } else {
            applyDynamicWidths();
        }
    </script>

@endsection