@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Verify Session Completion')

@section('content')
    @php
        $school = $school ?? $currentSchool ?? null;
        $schoolName = $school->name ?? 'Driving School';
        $settings = $school?->schoolSetting;
        $primaryColor = $settings?->primary_color ?? '#667eea';
        $secondaryColor = $settings?->secondary_color ?? '#764ba2';
    @endphp

    @include('school.admin.partials.admin-styles')

    <style>
        /* Booking Cards - Using shared content-card styles */
        .bookings-list {
            display: grid;
            gap: 20px;
        }

        .booking-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
            position: relative;
            z-index: 1;
        }

        .booking-info h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 8px 0;
        }

        .booking-date {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .booking-time {
            font-weight: 600;
            color:
                {{ $primaryColor }}
            ;
        }

        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .detail-value {
            color: #1f2937;
            font-weight: 500;
        }

        .bookings-container {
            padding: 20px;
            margin: 0 auto;
            max-width: 1200px;
        }

        .booking-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .status-select {
            padding: 8px 14px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
            background: white;
            cursor: pointer;
            min-width: 160px;
        }

        /* History Table Styles */
        .history-table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .history-table thead {
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
            color: #ffffff;
        }

        .history-table th {
            background: transparent;
            color: inherit;
            padding: 15px;
            text-align: left;
            font-size: 0.95rem;
            font-weight: 600;
            text-transform: none;
            letter-spacing: 0;
            border-bottom: 2px solid #e5e7eb;
        }

        .history-table th:hover {
            background: transparent;
            color: inherit;
        }

        .history-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.9rem;
            color: #1f2937;
        }

        .history-table tbody tr:hover {
            background: #fcfcfc;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
        }

        .filter-item label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .filter-item input, .filter-item select {
            width: 100%;
            height: 42px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 0 12px;
        }

        .status-select:focus {
            outline: none;
            border-color:
                {{ $primaryColor }}
            ;
        }

        .paid-indicator {
            color: #059669;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .verify-controls {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            margin: -10px 0 20px;
        }

        .verify-controls label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .verify-sort-select {
            min-width: 280px;
            padding: 8px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            background: #fff;
            color: #1f2937;
            font-size: 0.9rem;
        }

        .verify-sort-select:focus {
            outline: none;
            border-color: {{ $primaryColor }};
        }

        @keyframes pulseReady {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); transform: scale(1); }
            50% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); transform: scale(1.02); }
        }

        .badge-ready {
            animation: pulseReady 2s infinite;
        }

        .stat-card-clickable {
            cursor: pointer;
        }

        .stat-card-active {
            box-shadow: 0 0 0 2px rgba(31, 41, 55, 0.15), 0 8px 20px rgba(0, 0, 0, 0.12) !important;
            transform: translateY(-1px);
        }

        .icon-24 {
            width: 24px;
            height: 24px;
        }

        .detail-value-amount {
            color: #059669;
            font-weight: 600;
        }

        .detail-item-full {
            grid-column: 1 / -1;
        }

        @media (max-width: 768px) {
            .booking-details {
                grid-template-columns: 1fr 1fr;
            }

            .booking-actions {
                flex-direction: column;
                width: 100%;
            }

            .booking-actions .btn,
            .booking-actions .status-select {
                width: 100%;
            }

            .verify-controls {
                justify-content: stretch;
                align-items: stretch;
                flex-direction: column;
            }

            .verify-sort-select {
                width: 100%;
                min-width: 0;
            }
        }
    </style>

    <div class="admin-container">
        <!-- Flash Messages -->


        <!-- Page Header -->
        <div class="page-header"
            style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <div class="page-header-left">
                <h1 class="page-title">Verify Session Completion</h1>
                <p class="page-subtitle">Verify instructor-logged sessions and manage training completion logs for
                    {{ $schoolName }}</p>
            </div>
            {{-- Unified Export Dropdown --}}
            <div class="export-dropdown" id="bookingExport">
                <button type="button" class="btn-export-trigger" onclick="this.parentElement.classList.toggle('open')">
                    <i class="bi bi-download"></i>
                    Export Report
                    <i class="bi bi-chevron-down chevron"></i>
                </button>
                <div class="export-dropdown-menu">
                    <div class="dropdown-header">Export Options</div>
                    <a
                        href="{{ route('schools.admin.exports.verify-session-completion.excel', ['school' => $school->slug]) }}">
                        <i class="bi bi-file-earmark-excel-fill" style="color: #10b981;"></i> Full Export (Excel)
                    </a>
                    <div class="dropdown-header" style="font-size: 0.65rem; color: #94a3b8;">Format help</div>
                    <div style="padding: 10px 16px; font-size: 0.8rem; color: #64748b; line-height: 1.4;">
                        Export contains all verified and pending completions for the current branch.
                    </div>
                </div>
            </div>
        </div>



        <!-- Main Tabs -->
        <div class="main-tabs" style="display: flex; gap: 5px; margin-bottom: 25px; background: #f3f4f6; padding: 5px; border-radius: 14px; width: fit-content;">
            <button class="tab-btn {{ ($activeTab ?? 'verify') === 'verify' ? 'active' : '' }}" 
                    onclick="switchTab('verify')"
                    style="padding: 10px 25px; border-radius: 10px; border: none; font-weight: 700; transition: all 0.2s; cursor: pointer; display: flex; align-items: center; gap: 8px; background: {{ ($activeTab ?? 'verify') === 'verify' ? 'white' : 'transparent' }}; color: {{ ($activeTab ?? 'verify') === 'verify' ? '#1f2937' : '#6b7280' }}; box-shadow: {{ ($activeTab ?? 'verify') === 'verify' ? '0 2px 8px rgba(0,0,0,0.1)' : 'none' }};">
                <i class="bi bi-clock-history"></i>
                Awaiting Verification
                <span style="background: {{ ($activeTab ?? 'verify') === 'verify' ? '#3b82f6' : '#9ca3af' }}; color: white; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem; margin-left: 5px;">
                    {{ $awaitingVerificationCount }}
                </span>
            </button>
            <button class="tab-btn {{ ($activeTab ?? 'verify') === 'history' ? 'active' : '' }}" 
                    onclick="switchTab('history')"
                    style="padding: 10px 25px; border-radius: 10px; border: none; font-weight: 700; transition: all 0.2s; cursor: pointer; display: flex; align-items: center; gap: 8px; background: {{ ($activeTab ?? 'verify') === 'history' ? 'white' : 'transparent' }}; color: {{ ($activeTab ?? 'verify') === 'history' ? '#1f2937' : '#6b7280' }}; box-shadow: {{ ($activeTab ?? 'verify') === 'history' ? '0 2px 8px rgba(0,0,0,0.1)' : 'none' }};">
                <i class="bi bi-journal-text"></i>
                Training Log (History)
            </button>
        </div>

        @if(($activeTab ?? 'verify') === 'verify')
            <!-- Statistics Cards (Consolidated 5-card View) -->
            <div class="stats-grid"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                
                <!-- All Sessions (Full View) -->
                <div class="stat-card stat-card-clickable {{ ($activeFilter ?? 'all') === 'all' ? 'stat-card-active' : '' }}" id="card-all"
                    style="border-left: 5px solid #6366f1; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); cursor: pointer;"
                    onclick="filterBookings('all')">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="color: #6b7280; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">All Sessions</div>
                            <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">{{ $allSessionsCount }}</div>
                            <div style="color: #6366f1; font-size: 0.8rem; margin-top: 5px;">Full activity log</div>
                        </div>
                        <div style="background: rgba(99, 102, 241, 0.1); color: #6366f1; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="bi bi-grid-fill"></i>
                        </div>
                    </div>
                </div>

                <!-- Awaiting Verification (Priority Focus) -->
                <div class="stat-card stat-card-clickable {{ in_array($activeFilter, ['done', 'no-show', 'no_show']) ? 'stat-card-active' : '' }}" id="card-done"
                    style="border-left: 5px solid #f59e0b; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); cursor: pointer;"
                    onclick="filterBookings('done')">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div
                                style="color: #6b7280; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">
                                Awaiting Verification</div>
                            <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">{{ $awaitingVerificationCount }}
                            </div>
                            <div style="color: #f59e0b; font-size: 0.8rem; margin-top: 5px;">Completed or No-Show Logs</div>
                        </div>
                        <div
                            style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>

                <!-- Future Schedules (Scheduled State) -->
                <div class="stat-card stat-card-clickable {{ ($activeFilter ?? 'all') === 'scheduled' ? 'stat-card-active' : '' }}"
                    style="border-left: 5px solid #6366f1; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); cursor: pointer;"
                    onclick="filterBookings('scheduled')">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div
                                style="color: #6b7280; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">
                                Future Schedules</div>
                            <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">{{ $allSessionsCount - ($awaitingVerificationCount + $verifiedSessionsCount + $flaggedIssuesCount) }}</div>
                            <div style="color: #6366f1; font-size: 0.8rem; margin-top: 5px;">Upcoming training sessions</div>
                        </div>
                        <div
                            style="background: rgba(99, 102, 241, 0.1); color: #6366f1; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                    </div>
                </div>

                <!-- Verified Sessions (Success State) -->
                <div class="stat-card stat-card-clickable {{ ($activeFilter ?? 'all') === 'completed' ? 'stat-card-active' : '' }}"
                    style="border-left: 5px solid #10b981; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); cursor: pointer;"
                    onclick="filterBookings('completed')">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div
                                style="color: #6b7280; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">
                                Verified Sessions</div>
                            <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">{{ $verifiedSessionsCount }}</div>
                            <div style="color: #10b981; font-size: 0.8rem; margin-top: 5px;">Successfully verified logs</div>
                        </div>
                        <div
                            style="background: rgba(16, 185, 129, 0.1); color: #10b981; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>
                    </div>
                </div>

                <!-- Voided Sessions (Error/Warning State) -->
                <div class="stat-card stat-card-clickable {{ ($activeFilter ?? 'all') === 'cancelled' ? 'stat-card-active' : '' }}"
                    style="border-left: 5px solid #ef4444; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); cursor: pointer;"
                    onclick="filterBookings('cancelled')">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div
                                style="color: #6b7280; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">
                                Voided Sessions</div>
                            <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">{{ $flaggedIssuesCount }}</div>
                            <div style="color: #ef4444; font-size: 0.8rem; margin-top: 5px;">Voided or invalid logs</div>
                        </div>
                        <div
                            style="background: rgba(239, 68, 68, 0.1); color: #ef4444; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>

            </div>
        @endif

        @if(($activeTab ?? 'verify') === 'verify')
            <div class="verify-controls" style="display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 15px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #e5e7eb;">
                <div style="display: flex; align-items: center; gap: 15px; flex: 1;">
                    <div style="position: relative; flex: 1; max-width: 400px;">
                        <i class="bi bi-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                        <input type="text" id="verifySearch" class="form-control" placeholder="Search student, instructor or course..." value="{{ request('search') }}" 
                               style="padding-left: 35px; height: 42px; border-radius: 8px; border: 1px solid #d1d5db;"
                               onkeyup="if(event.key === 'Enter') applyFilters()">
                    </div>
                    <button class="btn btn-primary" onclick="applyFilters()" style="height: 42px; padding: 0 20px; font-weight: 600;">Search</button>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <label for="verifySort" style="margin: 0; white-space: nowrap; font-size: 0.85rem; font-weight: 700; color: #4b5563;">SORT BY:</label>
                    <select id="verifySort" class="verify-sort-select" onchange="applyFilters()" style="margin: 0; height: 42px;">
                        <option value="audit_priority" {{ ($activeSort ?? 'audit_priority') === 'audit_priority' ? 'selected' : '' }}>Audit Priority</option>
                        <option value="session_newest" {{ ($activeSort ?? 'session_newest') === 'session_newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="session_oldest" {{ ($activeSort ?? 'session_oldest') === 'session_oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="student_az" {{ ($activeSort ?? 'student_az') === 'student_az' ? 'selected' : '' }}>Student A-Z</option>
                    </select>
                </div>
            </div>

            <!-- Bookings List (Cards) -->
            <div class="bookings-list" id="bookingsList">
                @forelse($bookings as $booking)
                    @php
                        $sessionType = $booking->timeSlot->session_type
                            ?? (($booking->course && $booking->course->course_type === 'practical') ? 'practical' : 'theoretical');

                        $sessionHours = null;
                        if ($booking->timeSlot && $booking->timeSlot->start_time && $booking->timeSlot->end_time) {
                            $start = \Carbon\Carbon::parse($booking->timeSlot->start_time);
                            $end = \Carbon\Carbon::parse($booking->timeSlot->end_time);
                            $sessionHours = round($start->diffInMinutes($end) / 60, 2);
                        }

                        if (is_null($sessionHours) || $sessionHours <= 0) {
                            $sessionHours = $booking->package->training_hours ?? $booking->course->duration_hours ?? 1;
                        }
                    @endphp
                    <div class="booking-card" data-status="{{ $booking->status }}" style="position: relative; border-left: 6px solid {{ $sessionType === 'theoretical' ? '#3b82f6' : '#8b5cf6' }};">
                        
                        <!-- Session Type Float Badge -->
                        <div style="position: absolute; top: 0; right: 0; padding: 6px 15px; background: {{ $sessionType === 'theoretical' ? '#3b82f6' : '#8b5cf6' }}; color: white; font-size: 0.75rem; font-weight: 800; border-bottom-left-radius: 12px; text-transform: uppercase; letter-spacing: 1px;">
                            {{ $sessionType === 'theoretical' ? 'TDC Session' : 'PDC Session' }}
                        </div>

                        <div class="booking-header">
                            <div class="booking-info">
                                <h3 style="margin: 0;">{{ $booking->course->title ?? 'N/A' }}</h3>
                                <span
                                    class="badge badge-{{ $booking->status === 'completed' ? 'success' : (in_array($booking->status, ['done', 'no-show', 'no_show']) ? 'warning' : ($booking->status === 'cancelled' ? 'danger' : ($booking->status === 'pending' ? 'warning' : 'info'))) }}">
                                    @if($booking->status === 'done')
                                        Awaiting Verification
                                    @elseif(in_array($booking->status, ['no-show', 'no_show']))
                                        Review No-Show
                                    @else
                                        {{ ucfirst($booking->status) }}
                                    @endif
                                </span>
                            </div>
                            <div class="booking-date">
                                @if($booking->timeSlot)
                                    <span>{{ \Carbon\Carbon::parse($booking->timeSlot->date)->format('M d, Y') }}</span>
                                    <span
                                        class="booking-time">{{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('h:i A') }}
                                        - {{ \Carbon\Carbon::parse($booking->timeSlot->end_time)->format('h:i A') }}</span>
                                @elseif($booking->scheduled_at)
                                    <span>{{ $booking->scheduled_at->format('M d, Y') }}</span>
                                    <span class="booking-time">{{ $booking->scheduled_at->format('h:i A') }}</span>
                                @else
                                    <span>{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') : 'Not scheduled' }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="booking-details" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                            <div class="detail-item">
                                <span class="detail-label">Student</span>
                                <span class="detail-value" style="font-weight: 600; color: #111827;">{{ $booking->student->name ?? 'N/A' }}</span>
                            </div>

                            @if($booking->instructor)
                                <div class="detail-item">
                                    <span class="detail-label">Instructor</span>
                                    <span class="detail-value">{{ $booking->instructor->name }}</span>
                                </div>
                            @endif

                            <div class="detail-item">
                                <span class="detail-label">Session Hours</span>
                                <span class="detail-value" style="color: {{ $sessionType === 'theoretical' ? '#3b82f6' : '#8b5cf6' }}; font-weight: 700;">{{ number_format((float) $sessionHours, 1) }} hrs</span>
                            </div>

                            <div class="detail-item">
                                <span class="detail-label">Reported Status</span>
                                <span class="detail-value">
                                    @if(in_array($booking->status, ['no-show', 'no_show']))
                                        <span style="color: #ef4444; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 5px;">
                                            <i class="bi bi-person-x-fill"></i> Student No-Show
                                        </span>
                                    @else
                                        <span style="color: #10b981; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 5px;">
                                            <i class="bi bi-check-circle-fill"></i> Normal Completion
                                        </span>
                                    @endif
                                </span>
                            </div>

                            @if($booking->notes)
                                <div class="detail-item detail-item-full" style="background: #fff; padding: 10px; border: 1px dashed #d1d5db; border-radius: 6px; margin-top: 5px;">
                                    <span class="detail-label">Instructor Notes</span>
                                    <span class="detail-value" style="font-style: italic; font-size: 0.85rem;">"{{ $booking->notes }}"</span>
                                </div>
                            @endif
                        </div>

                        <div class="booking-actions">
                            <div style="display: flex; gap: 12px; align-items: center;">
                                @php
                                    $isFinalStatus = in_array($booking->status, ['completed', 'cancelled'], true);
                                    $canCancel = in_array($booking->status, ['scheduled', 'done', 'no-show'], true);
                                @endphp
                                
                                @if($booking->status === 'done' && !$isFinalStatus)
                                    {{-- Primary Action: Verify --}}
                                    <button class="btn btn-success" 
                                            style="background: #10b981; border: none; font-weight: 700; display: flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 10px; transition: all 0.2s;" 
                                            onclick="updateStatus({{ $booking->id }}, 'completed')">
                                        <i class="bi bi-patch-check-fill"></i>
                                        Verify Session
                                    </button>

                                    {{-- Secondary Action: Void --}}
                                    <button class="btn btn-outline-danger" 
                                            style="color: #ef4444; border: 2px solid #fee2e2; background: #fff; font-weight: 600; display: flex; align-items: center; gap: 8px; padding: 10px 15px; border-radius: 10px; transition: all 0.2s;" 
                                            onclick="updateStatus({{ $booking->id }}, 'cancelled')">
                                        <i class="bi bi-x-circle"></i>
                                        Void Session
                                    </button>
                                @elseif($booking->status === 'completed')
                                    <span class="paid-indicator" style="background: #ecfdf5; padding: 8px 16px; border-radius: 20px; border: 1px solid #10b981; color: #059669; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Session Verified
                                    </span>
                                @elseif($canCancel && !$isFinalStatus)
                                    {{-- For Scheduled or No-Show, only show Void option --}}
                                    <button class="btn btn-outline-danger" 
                                            style="color: #ef4444; border: 2px solid #fee2e2; background: #fff; font-weight: 600; display: flex; align-items: center; gap: 8px; padding: 10px 15px; border-radius: 10px; transition: all 0.2s;" 
                                            onclick="updateStatus({{ $booking->id }}, 'cancelled')">
                                        <i class="bi bi-trash"></i>
                                        Void Session
                                    </button>
                                @elseif($booking->status === 'cancelled')
                                    <span style="background: #fef2f2; padding: 8px 16px; border-radius: 20px; border: 1px solid #fee2e2; color: #ef4444; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                                        <i class="bi bi-x-octagon-fill"></i>
                                        Cancelled
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
@empty
                    <div class="content-card">
                        <div class="content-card-body">
                            <div class="empty-state">
                                <div class="empty-state-title">No pending sessions found</div>
                                <div class="empty-state-text">Instructor logged sessions will appear here for verification.</div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        @else
            <!-- Advanced Filters for History -->
            <form class="filters-grid" id="historyFilterForm" method="GET" action="{{ url()->current() }}">
                <input type="hidden" name="tab" value="history">
                <div class="filter-item">
                    <label>Branch</label>
                    <select name="branch_id" onchange="this.form.submit()">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-item">
                    <label>Instructor</label>
                    <select name="instructor_id" onchange="this.form.submit()">
                        <option value="">All Instructors</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}" {{ request('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-item">
                    <label>Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()">
                </div>
                <div class="filter-item">
                    <label>Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()">
                </div>
                <div class="filter-item">
                    <label>Quick Search</label>
                    <div style="position: relative;">
                        <input type="text" name="search" placeholder="Search Student..." value="{{ request('search') }}" style="padding-left: 35px;">
                        <i class="bi bi-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    </div>
                </div>
                <div class="filter-item" style="display: flex; align-items: flex-end; gap: 8px;">
                    <button type="submit" class="btn btn-primary" style="height: 42px; flex: 1;">Apply</button>
                    <a href="?tab=history" class="btn btn-light" style="height: 42px; display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 8px; padding: 0 15px; background: #f3f4f6;">Clear</a>
                </div>
            </form>

            <!-- History Table (Image 1 Style) -->
            <div class="history-table-container">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th style="cursor: pointer;" onclick="window.location.href='{{ request()->fullUrlWithQuery(['sort' => request('sort') === 'session_newest' ? 'session_oldest' : 'session_newest']) }}'">
                                Date {!! in_array(request('sort'), ['session_newest', 'session_oldest']) ? (request('sort') === 'session_newest' ? '↓' : '↑') : '↕' !!}
                            </th>
                            <th style="cursor: pointer;" onclick="window.location.href='{{ request()->fullUrlWithQuery(['sort' => request('sort') === 'time_early' ? 'time_late' : 'time_early']) }}'">
                                Time {!! in_array(request('sort'), ['time_early', 'time_late']) ? (request('sort') === 'time_early' ? '↓' : '↑') : '↕' !!}
                            </th>
                            <th style="cursor: pointer;" onclick="window.location.href='{{ request()->fullUrlWithQuery(['sort' => request('sort') === 'student_az' ? 'student_za' : 'student_az']) }}'">
                                Student {!! in_array(request('sort'), ['student_az', 'student_za']) ? (request('sort') === 'student_az' ? '↓' : '↑') : '↕' !!}
                            </th>
                            <th style="cursor: pointer;" onclick="window.location.href='{{ request()->fullUrlWithQuery(['sort' => request('sort') === 'branch_az' ? 'branch_za' : 'branch_az']) }}'">
                                Branch {!! in_array(request('sort'), ['branch_az', 'branch_za']) ? (request('sort') === 'branch_az' ? '↓' : '↑') : '↕' !!}
                            </th>
                            <th style="cursor: pointer;" onclick="window.location.href='{{ request()->fullUrlWithQuery(['sort' => request('sort') === 'course_az' ? 'course_za' : 'course_az']) }}'">
                                Course {!! in_array(request('sort'), ['course_az', 'course_za']) ? (request('sort') === 'course_az' ? '↓' : '↑') : '↕' !!}
                            </th>
                            <th style="cursor: pointer;" onclick="window.location.href='{{ request()->fullUrlWithQuery(['sort' => request('sort') === 'instructor_az' ? 'instructor_za' : 'instructor_az']) }}'">
                                Instructor {!! in_array(request('sort'), ['instructor_az', 'instructor_za']) ? (request('sort') === 'instructor_az' ? '↓' : '↑') : '↕' !!}
                            </th>
                            <th style="cursor: pointer;" onclick="window.location.href='{{ request()->fullUrlWithQuery(['sort' => request('sort') === 'type_az' ? 'type_za' : 'type_az']) }}'">
                                Type {!! in_array(request('sort'), ['type_az', 'type_za']) ? (request('sort') === 'type_az' ? '↓' : '↑') : '↕' !!}
                            </th>
                            <th style="cursor: pointer;" onclick="window.location.href='{{ request()->fullUrlWithQuery(['sort' => request('sort') === 'hours_high' ? 'hours_low' : 'hours_high']) }}'">
                                Hours {!! in_array(request('sort'), ['hours_high', 'hours_low']) ? (request('sort') === 'hours_high' ? '↓' : '↑') : '↕' !!}
                            </th>
                            <th style="cursor: pointer;" onclick="window.location.href='{{ request()->fullUrlWithQuery(['sort' => request('sort') === 'status_az' ? 'status_za' : 'status_az']) }}'">
                                Status {!! in_array(request('sort'), ['status_az', 'status_za']) ? (request('sort') === 'status_az' ? '↓' : '↑') : '↕' !!}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            @php
                                $sessionType = $booking->timeSlot->session_type
                                    ?? (($booking->course && $booking->course->course_type === 'practical') ? 'practical' : 'theoretical');
                                
                                $start = $booking->timeSlot ? \Carbon\Carbon::parse($booking->timeSlot->start_time) : null;
                                $end = $booking->timeSlot ? \Carbon\Carbon::parse($booking->timeSlot->end_time) : null;
                                $calculatedHours = ($start && $end) ? round($start->diffInMinutes($end) / 60, 1) : 1;
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($booking->booking_date ?? $booking->scheduled_at)->format('M d, Y') }}</td>
                                <td>
                                    @if($start)
                                        {{ $start->format('h:i A') }} - {{ $end->format('h:i A') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td style="font-weight: 600;">{{ $booking->student->name ?? 'N/A' }}</td>
                                <td style="font-size: 0.8rem; color: #6b7280;">{{ $booking->branch->name ?? 'N/A' }}</td>
                                <td>{{ $booking->course->title ?? 'N/A' }}</td>
                                <td>{{ $booking->instructor->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $sessionType === 'theoretical' ? 'badge-theoretical' : 'badge-practical' }}">
                                        {{ strtoupper($sessionType === 'theoretical' ? 'TDC' : 'PDC') }}
                                    </span>
                                </td>
                                <td style="font-weight: 700;">{{ $calculatedHours }} hrs</td>
                                <td>
                                    <span class="badge badge-{{ $booking->status === 'completed' ? 'success' : ($booking->status === 'cancelled' ? 'danger' : 'warning') }}">
                                        {{ $booking->status === 'completed' ? 'Verified' : ($booking->status === 'cancelled' ? 'Voided' : ucfirst($booking->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 60px;">
                                    <div style="color: #94a3b8; font-size: 1.1rem;">No session logs found matching your filters.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        <div class="mt-4">
            <div class="admin-pagination-wrapper">
                {{ $bookings->links('vendor.pagination.drivingapp') }}
            </div>
        </div>
    </div>

    <script>
        const schoolSlug = '{{ $school->slug }}';
        const currentVerifyFilter = @json($activeFilter ?? 'all');
        const currentTab = @json($activeTab ?? 'verify');

        function buildVerifyUrl(status, sort, search, tab) {
            const url = new URL(window.location.pathname, window.location.origin);

            if (tab && tab !== 'verify') {
                url.searchParams.set('tab', tab);
            }

            if (status && status !== 'all') {
                url.searchParams.set('status', status);
            }

            if (sort && sort !== 'audit_priority') {
                url.searchParams.set('sort', sort);
            }

            if (search) {
                url.searchParams.set('search', search);
            }

            return url;
        }

        function switchTab(tab) {
            window.location.href = buildVerifyUrl('all', 'audit_priority', '', tab).toString();
        }

        function filterBookings(status) {
            const sortSelect = document.getElementById('verifySort');
            const searchInput = document.getElementById('verifySearch');
            const selectedSort = sortSelect ? sortSelect.value : 'audit_priority';
            const searchValue = searchInput ? searchInput.value : '';
            window.location.href = buildVerifyUrl(status, selectedSort, searchValue, currentTab).toString();
        }

        function applyFilters() {
            const sortSelect = document.getElementById('verifySort');
            const searchInput = document.getElementById('verifySearch');
            const selectedSort = sortSelect ? sortSelect.value : 'audit_priority';
            const searchValue = searchInput ? searchInput.value : '';
            window.location.href = buildVerifyUrl(currentVerifyFilter, selectedSort, searchValue, currentTab).toString();
        }

        function updateStatus(bookingId, status) {
            if (!status) return;

            const isVerify = status === 'completed';
            const actionTitle = isVerify ? 'Verify Training Session' : 'Void Training Session';
            const actionMsg = isVerify 
                ? 'Are you sure you want to verify this session? This will officially add it to the student\'s training log.' 
                : 'Are you sure you want to void this session? These hours will not count towards student graduation.';

            showConfirm({
                type: isVerify ? 'success' : 'warning',
                title: actionTitle,
                message: actionMsg,
                confirmText: isVerify ? 'Yes, Verify Session' : 'Yes, Void Session',
                onConfirm: () => {
                    fetch(`/${schoolSlug}/admin/verify-session-completion/${bookingId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status: status })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const successTitle = isVerify ? 'Session Verified!' : 'Session Voided';
                                const successMsg = isVerify 
                                    ? 'The training session has been successfully added to the student log.' 
                                    : 'The training session has been voided and moved to history.';
                                    
                                Toast.success(successMsg, successTitle);
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                Toast.error(data.message || 'Failed to update schedule status.', 'Update Failed');
                            }
                        })
                        .catch(error => {
                            Toast.error('An error occurred while updating the status.', 'Error');
                            console.error(error);
                        });
                }
            });
        }

    </script>
@endsection