@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Schedule')

@section('content')
@php
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#3498db';
    $secondaryColor = $settings?->secondary_color ?? '#2ecc71';
    $useGradient = $settings?->use_gradient_header ?? false;
    $buttonRadius = $settings?->button_border_radius ?? 10;
    $cardRadius = $settings?->border_radius ?? 12;
    
    // Header/Button Primary Style
    $primaryStyle = $useGradient 
        ? "linear-gradient(135deg, $primaryColor 0%, $secondaryColor 100%)" 
        : $primaryColor;
@endphp




<style>
    :root {
        --primary-color: {{ $primaryColor }};
        --secondary-color: {{ $secondaryColor }};
        --primary-gradient: {{ $primaryStyle }};
        --button-radius: {{ $buttonRadius }}px;
        --card-radius: {{ $cardRadius }}px;
        --primary-text: {{ $settings?->button_primary_text ?? '#ffffff' }};
    }

    /* Container */
    .admin-container {
        padding: 20px;
        margin: 0 auto;
        max-width: 1600px;
    }

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e5e7eb;
    }

    .page-header-left {
        flex: 1;
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 0.875rem;
        margin-top: 4px;
    }

    .header-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    /* Standard Card Pattern (Matches Session Logs) */
    .content-box {
        background: white;
        border-radius: var(--card-radius);
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .main-toggle {
        display: flex;
        background: #f3f4f6;
        padding: 4px;
        border-radius: calc(var(--button-radius) + 2px);
    }
    
    .main-toggle-btn {
        padding: 10px 24px;
        background: transparent;
        color: #6b7280;
        border: none;
        cursor: pointer;
        font-size: 0.95rem;
        font-weight: 600;
        border-radius: var(--button-radius);
        transition: all 0.2s;
    }
    
    .main-toggle-btn.active {
        background: var(--primary-gradient);
        color: var(--primary-text);
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    
    .main-toggle-btn:hover:not(.active) {
        color: #1f2937;
    }

    .mobile-only-tab-btn {
        display: none;
    }
    
    .main-view-section {
        display: none;
    }
    
    .main-view-section.active {
        display: block;
    }
    
    .schedule-grid {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 25px;
        align-items: start;
        margin-top: 20px;
    }
    
    @media (max-width: 992px) {
        .schedule-grid {
            grid-template-columns: 1fr;
        }
        .schedule-sidebar {
            order: -1;
        }
    }
    
    .schedule-item {
        margin-bottom: 20px;
        background: white;
        border-radius: var(--card-radius);
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .schedule-date-header {
        background: {{ $primaryColor }};
        color: white;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .schedule-date-header:hover {
        opacity: 0.9;
    }
    
    .schedule-date-header .date-text {
        flex: 1;
        font-weight: 500;
    }
    
    .schedule-date-header .toggle-icon {
        transition: transform 0.3s;
    }
    
    .schedule-date-header.collapsed .toggle-icon {
        transform: rotate(-90deg);
    }
    
    .schedule-slots {
        background: white;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }
    
    .schedule-slots.collapsed {
        max-height: 0 !important;
        padding: 0;
    }
    
    .slot-item {
        border-bottom: 1px solid #dee2e6;
        padding: 16px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    
    .slot-item:last-child {
        border-bottom: none;
    }
    
    .slot-indicator {
        width: 4px;
        border-radius: 2px;
        flex-shrink: 0;
        align-self: stretch;
        min-height: 60px;
    }
    
    .slot-indicator.my-slot { background: #28a745; }
    .slot-indicator.available { background: {{ $primaryColor }}; }
    .slot-indicator.admin-assigned { background: #ff9800; }
    
    .slot-details {
        flex: 1;
    }
    
    .slot-time {
        font-weight: 600;
        color: #000;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .slot-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.725rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .badge-my-slot { background: #d4edda; color: #155724; }
    .badge-admin { background: #fff3cd; color: #856404; }
    .badge-available { background: #cce5ff; color: #004085; }
    .badge-pending { background: #f8d7da; color: #721c24; }
    .badge-qualified { background: #d4edda; color: #155724; }
    .badge-not-qualified { background: #fff3cd; color: #856404; }
    
    .slot-info {
        color: #6c757d;
        font-size: 14px;
        margin-bottom: 4px;
    }
    
    .slot-actions {
        margin-top: 8px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .btn-leave {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        border-radius: var(--button-radius);
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        background: #ef4444;
        color: white;
        border: none;
        transition: all 0.2s;
    }
    
    .btn-leave:hover { 
        background: #dc2626; 
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
    }
    
    .btn-select {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        border-radius: var(--button-radius);
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        background: var(--primary-gradient);
        color: var(--primary-text);
        border: none;
        transition: all 0.2s;
    }
    
    .btn-select:hover { 
        opacity: 0.95; 
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .btn-select-compact {
        padding: 6px 12px;
    }
    
    .btn-request {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        border-radius: var(--button-radius);
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        background: #f59e0b;
        color: white;
        border: none;
        transition: all 0.2s;
    }
    
    .btn-request:hover { 
        background: #d97706; 
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
    }

    .btn-request-disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .filter-bar {
        margin-bottom: 15px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .filter-bar label {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        font-size: 14px;
        color: #495057;
    }
    
    .filter-bar input[type="checkbox"] {
        width: 16px;
        height: 16px;
    }
    
    .empty-state {
        text-align: center;
        color: #6c757d;
        padding: 40px;
    }
    
    /* Sidebar */
    .schedule-sidebar {
        display: flex;
        flex-direction: column;
        gap: 16px;
        position: sticky;
        top: 20px;
    }
    
    .sidebar-section {
        background: white;
        border-radius: var(--card-radius);
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        padding: 20px;
    }
    
    .sidebar-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #000;
        margin: 0 0 12px 0;
        padding-bottom: 8px;
        border-bottom: 2px solid {{ $primaryColor }};
    }
    
    .today-lesson-card {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-left: 4px solid #28a745;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
    }
    
    .today-lesson-card:last-child { margin-bottom: 0; }
    
    .lesson-time {
        font-weight: 600;
        color: #000;
        margin-bottom: 8px;
    }
    
    .student-item {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 8px;
    }
    
    .student-item:last-child { margin-bottom: 0; }
    
    .student-name {
        font-weight: 600;
        color: #000;
        margin-bottom: 4px;
    }
    
    .student-course {
        color: #6c757d;
        font-size: 13px;
    }

    .student-meta {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 6px;
    }

    .student-status-pill {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.2px;
    }

    .status-scheduled { background: #e0f2fe; color: #0c4a6e; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-done { background: #fef9c3; color: #854d0e; }
    .status-completed { background: #dcfce7; color: #166534; }
    .status-no-show { background: #fee2e2; color: #991b1b; }

    .attendance-attended { background: #dcfce7; color: #166534; }
    .attendance-late { background: #ffedd5; color: #9a3412; }
    .attendance-absent { background: #fee2e2; color: #991b1b; }

    .lesson-action-row {
        margin-top: 8px;
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    .btn-mark-done {
        border: none;
        border-radius: 6px;
        padding: 7px 10px;
        font-size: 12px;
        font-weight: 600;
        color: white;
        background: #2563eb;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .btn-mark-done:hover {
        background: #1d4ed8;
    }

    .btn-mark-done:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .btn-mark-no-show {
        border: none;
        border-radius: 6px;
        padding: 7px 10px;
        font-size: 12px;
        font-weight: 600;
        color: white;
        background: #dc2626;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .btn-mark-no-show:hover {
        background: #b91c1c;
    }

    .btn-mark-no-show:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .lesson-action-note {
        margin-top: 8px;
        font-size: 12px;
        color: #6b7280;
        font-weight: 600;
    }

    .lesson-action-note.success {
        color: #166534;
    }
    
    .mini-schedule-card {
        background: white;
        border: 1px solid #dee2e6;
        border-left: 3px solid {{ $primaryColor }};
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 8px;
        font-size: 13px;
    }

    .mini-schedule-card-admin {
        border-left-color: #ff9800;
    }

    .mini-schedule-card-my {
        border-left-color: #28a745;
    }
    
    .mini-schedule-card:last-child { margin-bottom: 0; }
    
    .mini-schedule-date {
        font-weight: 600;
        color: #000;
        margin-bottom: 4px;
    }
    
    .mini-schedule-info {
        color: #6c757d;
    }

    .mini-schedule-students {
        margin-top: 4px;
        font-size: 12px;
        color: #374151;
    }

    .mini-schedule-students-empty {
        color: #9ca3af;
    }
    
    .no-lessons {
        text-align: center;
        color: #6c757d;
        padding: 20px;
        font-size: 14px;
    }
    
    /* Alert Messages */
    .alert {
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .alert-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    
    .alert-error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
    
    .alert-close {
        background: none;
        border: none;
        font-size: 1.2rem;
        cursor: pointer;
        color: inherit;
    }
    
    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s;
    }
    
    .modal-overlay.active {
        display: flex !important;
        opacity: 1;
        visibility: visible;
    }
    
    .modal-content {
        background: white;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow: hidden;
    }
    
    .modal-header {
        padding: 16px 20px;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: {{ $primaryColor }};
        color: white;
    }
    
    .modal-header h2 {
        margin: 0;
        font-size: 1.25rem;
    }
    
    .modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
    }
    
    .modal-body {
        padding: 20px;
        max-height: calc(80vh - 80px);
        overflow-y: auto;
    }

    .is-hidden {
        display: none;
    }

    .branch-text {
        color: #3730a3;
    }

    .not-specialty-text {
        color: #856404;
    }

    .min-notice-text {
        font-size: 11px;
        color: #dc3545;
    }

    .no-students-text {
        color: #6c757d;
        font-size: 13px;
        margin: 0;
    }

    .slot-students {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px dashed #e2e8f0;
    }

    .slot-student-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 4px 0;
        font-size: 13px;
        flex-wrap: wrap;
    }

    .slot-student-name {
        font-weight: 600;
        color: #1e293b;
    }

    .slot-student-course {
        color: #64748b;
        font-size: 12px;
    }

    .modal-help-text {
        color: #666;
        margin-bottom: 15px;
    }

    .modal-reason-label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .modal-required {
        color: #dc3545;
    }

    .modal-reason-input {
        width: 100%;
        min-height: 100px;
        padding: 10px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        font-family: inherit;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 15px;
    }

    .modal-cancel-btn {
        background: #e0e0e0;
        color: #666;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
    }

    .mobile-lessons-stack {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-top: 20px;
    }
    
    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .admin-container { padding: 10px; }
        .schedule-header h1 { font-size: 1.3rem; }
        .main-toggle-btn { padding: 10px 16px; font-size: 13px; }
        .slot-item { padding: 12px; }
        .schedule-sidebar { display: none; }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .header-actions {
            width: 100%;
        }

        .main-toggle {
            width: 100%;
            overflow-x: auto;
        }

        .mobile-only-tab-btn {
            display: inline-block;
        }
    }
    

    /* Confirmation Modal */
    .confirm-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10001;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s;
    }

    .confirm-modal.active {
        display: flex !important;
        opacity: 1;
        visibility: visible;
    }

    .confirm-dialog {
        background: white;
        border-radius: 16px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        transform: scale(0.9);
        transition: transform 0.3s;
        overflow: hidden;
        padding-bottom: 0;
    }

    .confirm-modal.active .confirm-dialog {
        transform: scale(1);
    }

    .confirm-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        margin: 30px auto 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    .confirm-icon.warning { background: #fef3c7; color: #d97706; }
    .confirm-icon.danger { background: #fee2e2; color: #dc2626; }
    .confirm-icon.info { background: #dbeafe; color: #2563eb; }
    .confirm-icon.success { background: #d1fae5; color: #10b981; }

    .confirm-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 10px;
        padding: 0 30px;
    }

    .confirm-message {
        color: #6b7280;
        font-size: 0.95rem;
        padding: 0 30px;
        margin-bottom: 25px;
        line-height: 1.5;
    }

    .confirm-actions {
        display: flex;
        border-top: 1px solid #e5e7eb;
    }

    .confirm-btn {
        flex: 1;
        padding: 16px;
        border: none;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
    }

    .confirm-btn-cancel { background: #f9fafb; color: #6b7280; }
    .confirm-btn-cancel:hover { background: #f3f4f6; }

    .confirm-btn-confirm { color: white; }
    .confirm-btn-confirm.warning { background: #f59e0b; }
    .confirm-btn-confirm.warning:hover { background: #d97706; }
    .confirm-btn-confirm.danger { background: #ef4444; }
    .confirm-btn-confirm.danger:hover { background: #dc2626; }
    .confirm-btn-confirm.success { background: #10b981; }
    .confirm-btn-confirm.success:hover { background: #059669; }
</style>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Session Schedule</h1>
            <p class="page-subtitle">View and manage your assigned training sessions</p>
        </div>
        <div class="header-actions">
            <div class="main-toggle">
                <button type="button" class="main-toggle-btn active" data-view="my-slots" onclick="switchMainView('my-slots')">My Sessions</button>
                <button type="button" class="main-toggle-btn" data-view="available" onclick="switchMainView('available')">Open Sessions</button>
                <button type="button" class="main-toggle-btn mobile-only-tab-btn" data-view="mobile-lessons" onclick="switchMainView('mobile-lessons')">Today &amp; Upcoming</button>
            </div>
        </div>
    </div>
    

    
    <!-- My Slots View -->
    <div id="my-slots-view" class="main-view-section active">
        <div class="schedule-grid">
            <div class="schedule-main">
                <div class="filter-bar">
{{-- 
                    <label>
                        <input type="checkbox" id="show-past-my" onchange="toggleShowPastMy(this)"> Show Past Slots
                    </label>
--}}
                    <label>
                        <input type="checkbox" id="collapse-all-my" onchange="toggleCollapseAllMy(this)"> Collapse All
                    </label>
                </div>
                
                @forelse($groupedMySlots as $date => $dateSlots)
                    @php $isPast = $date < $todayDate; @endphp
                    <div class="schedule-item {{ $isPast ? 'is-hidden' : '' }}" data-is-past="{{ $isPast ? 'true' : 'false' }}">
                        <div class="schedule-date-header" onclick="toggleDate(this)">
                            <span class="date-text">{{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</span>
                            <span class="toggle-icon">&#x25BC;</span>
                        </div>
                        <div class="schedule-slots">
                            @foreach($dateSlots as $slot)
                                @php
                                    $instructor = $slot->instructors->firstWhere('id', $instructorId);
                                    $isAdminAssigned = $instructor && $instructor->pivot->assignment_type === 'admin_assigned';
                                    $hasPendingRequest = in_array($slot->id, $pendingRemovalRequests);
                                    $slotBookings = $slot->bookings->where('instructor_id', $instructorId)->where('status', '!=', 'cancelled');
                                    $daysUntilSlot = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($slot->date)->startOfDay(), false);
                                    $canRequestRemoval = $daysUntilSlot >= $minimumNoticeDays;
                                @endphp
                                <div class="slot-item">
                                    <div class="slot-indicator {{ $isAdminAssigned ? 'admin-assigned' : 'my-slot' }}"></div>
                                    <div class="slot-details">
                                        <div class="slot-time">
                                            {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                            @if($hasPendingRequest)
                                                <span class="slot-badge badge-pending">Removal Requested</span>
                                            @elseif($isAdminAssigned)
                                                <span class="slot-badge badge-admin">Admin Assigned</span>
                                            @else
                                                <span class="slot-badge badge-my-slot">My Slot</span>
                                            @endif
                                        </div>
                                        <div class="slot-info">
                                            {{ $slot->course->title ?? 'General' }} &bull; {{ $slot->instructors->count() }}/{{ $slot->max_instructors ?? 1 }} instructors
                                            @if($slotBookings->count() > 0)
                                                &bull; {{ $slotBookings->count() }} student(s) scheduled
                                            @endif
                                            @if($slot->branch_id && $slot->branch)
                                                &bull; <span class="branch-text">{{ $slot->branch->name }}</span>
                                            @endif
                                        </div>
                                        @if($slot->notes)
                                            <div class="slot-info">{{ $slot->notes }}</div>
                                        @endif
                                        
                                        @if(!$hasPendingRequest)
                                        <div class="slot-actions">
                                            @php
                                                $hasAnyBookings = $slotBookings->count() > 0;
                                                $pivot = $instructor ? $instructor->pivot : null;
                                                $isGracePeriod = false;
                                                
                                                if ($pivot) {
                                                    $joinTime = \Carbon\Carbon::parse($pivot->created_at);
                                                    $isGracePeriod = $joinTime->gt(now()->subMinute());
                                                }
                                                
                                                $shouldRequest = $isAdminAssigned || $hasAnyBookings || !$isGracePeriod;
                                            @endphp
                                            
                                            @if($shouldRequest)
                                                <button type="button" class="btn-request" onclick="showRemovalModal({{ $slot->id }})">
                                                    Request Removal
                                                </button>
                                                @if(!$canRequestRemoval && $isAdminAssigned && !$hasAnyBookings)
                                                    <span class="min-notice-text">(Short notice)</span>
                                                @endif
                                            @else
                                                <button type="button" class="btn-leave" onclick="leaveSlot({{ $slot->id }}, this)">Leave Slot</button>
                                            @endif
                                        </div>
                                        @endif

                                        @if($slotBookings->isNotEmpty())
                                        <div class="slot-students">
                                            @foreach($slotBookings as $booking)
                                                @php
                                                    $bookingStatus = strtolower((string) ($booking->status ?? 'scheduled'));
                                                @endphp
                                                <div class="slot-student-item">
                                                    <span class="slot-student-name">{{ $booking->student->name ?? 'Student' }}</span>
                                                    <span class="slot-student-course">{{ $booking->course->title ?? '' }}</span>
                                                    <span class="student-status-pill status-{{ $bookingStatus === 'no-show' ? 'no-show' : $bookingStatus }}">
                                                        {{ strtoupper(str_replace('-', ' ', $bookingStatus)) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <p>No slots selected yet. Go to "Available Slots" to select time slots.</p>
                    </div>
                @endforelse
            </div>
            
            <!-- Sidebar -->
            <div class="schedule-sidebar">
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Today's Lessons</h3>
                    @if($todaySlots->isNotEmpty())
                        @foreach($todaySlots->sortBy('start_time') as $slot)
                            @php
                                $slotBookings = $slot->bookings->where('instructor_id', $instructorId)->where('status', '!=', 'cancelled');
                            @endphp
                            <div class="today-lesson-card">
                                <div class="lesson-time">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                </div>
                                @if($slotBookings->isNotEmpty())
                                    @foreach($slotBookings as $booking)
                                        @php
                                            $bookingStatus = strtolower((string) ($booking->status ?? 'scheduled'));
                                            $canMarkDone = !in_array($bookingStatus, ['done', 'completed', 'cancelled', 'no-show'], true);
                                        @endphp
                                        <div class="student-item">
                                            <div class="student-name">{{ $booking->student->name ?? 'Student' }}</div>
                                            <div class="student-course">{{ $booking->course->title ?? 'Course' }}</div>
                                            <div class="student-meta">
                                                <span class="student-status-pill status-{{ $bookingStatus === 'no-show' ? 'no-show' : $bookingStatus }}">
                                                    {{ strtoupper(str_replace('-', ' ', $bookingStatus)) }}
                                                </span>
                                                @if($booking->attendance_status)
                                                    <span class="student-status-pill attendance-{{ strtolower((string) $booking->attendance_status) }}">
                                                        {{ ucfirst((string) $booking->attendance_status) }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if($canMarkDone)
                                                <div class="lesson-action-row">
                                                    <button type="button" class="btn-mark-done" onclick="markLessonStatus({{ $booking->id }}, 'done', this)">Mark Done</button>
                                                    <button type="button" class="btn-mark-no-show" onclick="markLessonStatus({{ $booking->id }}, 'no-show', this)">No-Show</button>
                                                </div>
                                            @elseif($bookingStatus === 'done')
                                                <div class="lesson-action-note">Submitted for admin verification</div>
                                            @elseif($bookingStatus === 'completed')
                                                <div class="lesson-action-note success">Verified and logged</div>
                                            @elseif($bookingStatus === 'no-show')
                                                <div class="lesson-action-note">Marked no-show</div>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <p class="no-students-text">No students scheduled</p>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="no-lessons">No lessons scheduled for today</div>
                    @endif
                </div>
                
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Upcoming This Week</h3>
                    @forelse($upcomingSlots as $slot)
                        @php
                            $instructor = $slot->instructors->firstWhere('id', $instructorId);
                            $isAdmin = $instructor && $instructor->pivot->assignment_type === 'admin_assigned';
                            $upcomingStudents = $slot->bookings
                                ->where('instructor_id', $instructorId)
                                ->whereIn('status', ['scheduled', 'confirmed', 'done', 'completed'])
                                ->pluck('student.name')
                                ->filter()
                                ->unique()
                                ->values();
                        @endphp
                        <div class="mini-schedule-card {{ $isAdmin ? 'mini-schedule-card-admin' : 'mini-schedule-card-my' }}">
                            <div class="mini-schedule-date">{{ \Carbon\Carbon::parse($slot->date)->format('D, M d') }}</div>
                            <div class="mini-schedule-info">
                                {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                            </div>
                            @if($upcomingStudents->isNotEmpty())
                                <div class="mini-schedule-students">Students: {{ $upcomingStudents->implode(', ') }}</div>
                            @else
                                <div class="mini-schedule-students mini-schedule-students-empty">No student booked yet</div>
                            @endif
                        </div>
                    @empty
                        <div class="no-lessons">No upcoming slots</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    <!-- Available Slots View -->
    <div id="available-view" class="main-view-section">
        <div class="schedule-grid">
            <div class="schedule-main">
                <div class="filter-bar">
{{-- 
                    <label>
                        <input type="checkbox" id="show-past-available" onchange="toggleShowPastAvailable(this)"> Show Past Slots
                    </label>
--}}
                    <label>
                        <input type="checkbox" id="collapse-all-available" onchange="toggleCollapseAllAvailable(this)"> Collapse All
                    </label>
{{-- 
                    <label>
                        <input type="checkbox" id="show-all-courses" onchange="toggleShowAllCourses(this)"> Show All Courses
                    </label>
--}}
                </div>
                
                @forelse($groupedAvailableSlots as $date => $dateSlots)
                    @php 
                        $isPast = $date < $todayDate;
                        $hasVisibleSlots = $dateSlots->filter(function($slot) use ($qualifiedCourseIds) {
                            return empty($qualifiedCourseIds) || in_array($slot->course_id, $qualifiedCourseIds);
                        })->count() > 0;
                    @endphp
                    <div class="schedule-item {{ $isPast || !$hasVisibleSlots ? 'is-hidden' : '' }}" data-is-past="{{ $isPast ? 'true' : 'false' }}" data-has-visible="{{ $hasVisibleSlots ? 'true' : 'false' }}">
                        <div class="schedule-date-header" onclick="toggleDate(this)">
                            <span class="date-text">{{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</span>
                            <span class="toggle-icon">&#x25BC;</span>
                        </div>
                        <div class="schedule-slots">
                            @foreach($dateSlots as $slot)
                                @php
                                    $isQualified = empty($qualifiedCourseIds) || in_array($slot->course_id, $qualifiedCourseIds);
                                    $spotsLeft = ($slot->max_instructors ?? 1) - $slot->instructors->count();
                                @endphp
                                <div class="slot-item {{ !$isQualified ? 'is-hidden' : '' }}" data-qualified="{{ $isQualified ? 'true' : 'false' }}">
                                    <div class="slot-indicator available"></div>
                                    <div class="slot-details">
                                        <div class="slot-time">
                                            {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                            <span class="slot-badge badge-available">Available</span>
                                            @if($isQualified)
                                                <span class="slot-badge badge-qualified">{{ $slot->course->title ?? 'General' }} &#10003;</span>
                                            @else
                                                <span class="slot-badge badge-not-qualified">{{ $slot->course->title ?? 'General' }}</span>
                                            @endif
                                        </div>
                                        <div class="slot-info">
                                            {{ $slot->instructors->count() }}/{{ $slot->max_instructors ?? 1 }} instructors &bull; {{ $spotsLeft }} spot(s) left
                                            @if(!$isQualified)
                                                &bull; <span class="not-specialty-text">Not your specialty</span>
                                            @endif
                                        </div>
                                        @if($slot->notes)
                                            <div class="slot-info">{{ $slot->notes }}</div>
                                        @endif
                                        <div class="slot-actions">
                                            <button type="button" class="btn-select" onclick="selectSlot({{ $slot->id }}, this)">Select Slot</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <p>No available time slots at the moment.</p>
                    </div>
                @endforelse
            </div>
            
            <!-- Sidebar (same as My Slots) -->
            <div class="schedule-sidebar">
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Today's Lessons</h3>
                    @if($todaySlots->isNotEmpty())
                        @foreach($todaySlots->sortBy('start_time') as $slot)
                            @php
                                $slotBookings = $slot->bookings->where('instructor_id', $instructorId)->where('status', '!=', 'cancelled');
                            @endphp
                            <div class="today-lesson-card">
                                <div class="lesson-time">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                </div>
                                @if($slotBookings->isNotEmpty())
                                    @foreach($slotBookings as $booking)
                                        @php
                                            $bookingStatus = strtolower((string) ($booking->status ?? 'scheduled'));
                                            $canMarkDone = !in_array($bookingStatus, ['done', 'completed', 'cancelled', 'no-show'], true);
                                        @endphp
                                        <div class="student-item">
                                            <div class="student-name">{{ $booking->student->name ?? 'Student' }}</div>
                                            <div class="student-course">{{ $booking->course->title ?? 'Course' }}</div>
                                            <div class="student-meta">
                                                <span class="student-status-pill status-{{ $bookingStatus === 'no-show' ? 'no-show' : $bookingStatus }}">
                                                    {{ strtoupper(str_replace('-', ' ', $bookingStatus)) }}
                                                </span>
                                                @if($booking->attendance_status)
                                                    <span class="student-status-pill attendance-{{ strtolower((string) $booking->attendance_status) }}">
                                                        {{ ucfirst((string) $booking->attendance_status) }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if($canMarkDone)
                                                <div class="lesson-action-row">
                                                    <button type="button" class="btn-mark-done" onclick="markLessonStatus({{ $booking->id }}, 'done', this)">Mark Done</button>
                                                    <button type="button" class="btn-mark-no-show" onclick="markLessonStatus({{ $booking->id }}, 'no-show', this)">No-Show</button>
                                                </div>
                                            @elseif($bookingStatus === 'done')
                                                <div class="lesson-action-note">Submitted for admin verification</div>
                                            @elseif($bookingStatus === 'completed')
                                                <div class="lesson-action-note success">Verified and logged</div>
                                            @elseif($bookingStatus === 'no-show')
                                                <div class="lesson-action-note">Marked no-show</div>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <p class="no-students-text">No students scheduled</p>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="no-lessons">No lessons scheduled for today</div>
                    @endif
                </div>
                
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Your Schedule</h3>
                    @forelse($upcomingSlots as $slot)
                        @php
                            $upcomingStudents = $slot->bookings
                                ->where('instructor_id', $instructorId)
                                ->whereIn('status', ['scheduled', 'confirmed', 'done', 'completed'])
                                ->pluck('student.name')
                                ->filter()
                                ->unique()
                                ->values();
                        @endphp
                        <div class="mini-schedule-card">
                            <div class="mini-schedule-date">{{ \Carbon\Carbon::parse($slot->date)->format('D, M d') }}</div>
                            <div class="mini-schedule-info">
                                {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                            </div>
                            @if($upcomingStudents->isNotEmpty())
                                <div class="mini-schedule-students">Students: {{ $upcomingStudents->implode(', ') }}</div>
                            @else
                                <div class="mini-schedule-students mini-schedule-students-empty">No student booked yet</div>
                            @endif
                        </div>
                    @empty
                        <div class="no-lessons">No upcoming slots</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Lessons View -->
    <div id="mobile-lessons-view" class="main-view-section">
        <div class="mobile-lessons-stack">
            <div class="sidebar-section">
                <h3 class="sidebar-title">Today's Lessons</h3>
                @if($todaySlots->isNotEmpty())
                    @foreach($todaySlots->sortBy('start_time') as $slot)
                        @php
                            $slotBookings = $slot->bookings->where('instructor_id', $instructorId)->where('status', '!=', 'cancelled');
                        @endphp
                        <div class="today-lesson-card">
                            <div class="lesson-time">
                                {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                            </div>
                            @if($slotBookings->isNotEmpty())
                                @foreach($slotBookings as $booking)
                                    @php
                                        $bookingStatus = strtolower((string) ($booking->status ?? 'scheduled'));
                                        $canMarkDone = !in_array($bookingStatus, ['done', 'completed', 'cancelled', 'no-show'], true);
                                    @endphp
                                    <div class="student-item">
                                        <div class="student-name">{{ $booking->student->name ?? 'Student' }}</div>
                                        <div class="student-course">{{ $booking->course->title ?? 'Course' }}</div>
                                        <div class="student-meta">
                                            <span class="student-status-pill status-{{ $bookingStatus === 'no-show' ? 'no-show' : $bookingStatus }}">
                                                {{ strtoupper(str_replace('-', ' ', $bookingStatus)) }}
                                            </span>
                                            @if($booking->attendance_status)
                                                <span class="student-status-pill attendance-{{ strtolower((string) $booking->attendance_status) }}">
                                                    {{ ucfirst((string) $booking->attendance_status) }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($canMarkDone)
                                            <div class="lesson-action-row">
                                                <button type="button" class="btn-mark-done" onclick="markLessonStatus({{ $booking->id }}, 'done', this)">Mark Done</button>
                                                <button type="button" class="btn-mark-no-show" onclick="markLessonStatus({{ $booking->id }}, 'no-show', this)">No-Show</button>
                                            </div>
                                        @elseif($bookingStatus === 'done')
                                            <div class="lesson-action-note">Submitted for admin verification</div>
                                        @elseif($bookingStatus === 'completed')
                                            <div class="lesson-action-note success">Verified and logged</div>
                                        @elseif($bookingStatus === 'no-show')
                                            <div class="lesson-action-note">Marked no-show</div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <p class="no-students-text">No students scheduled</p>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="no-lessons">No lessons scheduled for today</div>
                @endif
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">Upcoming This Week</h3>
                @forelse($upcomingSlots as $slot)
                    @php
                        $instructor = $slot->instructors->firstWhere('id', $instructorId);
                        $isAdmin = $instructor && $instructor->pivot->assignment_type === 'admin_assigned';
                        $upcomingStudents = $slot->bookings
                            ->where('instructor_id', $instructorId)
                            ->whereIn('status', ['scheduled', 'confirmed', 'done', 'completed'])
                            ->pluck('student.name')
                            ->filter()
                            ->unique()
                            ->values();
                    @endphp
                    <div class="mini-schedule-card {{ $isAdmin ? 'mini-schedule-card-admin' : 'mini-schedule-card-my' }}">
                        <div class="mini-schedule-date">{{ \Carbon\Carbon::parse($slot->date)->format('D, M d') }}</div>
                        <div class="mini-schedule-info">
                            {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                        </div>
                        @if($upcomingStudents->isNotEmpty())
                            <div class="mini-schedule-students">Students: {{ $upcomingStudents->implode(', ') }}</div>
                        @else
                            <div class="mini-schedule-students mini-schedule-students-empty">No student booked yet</div>
                        @endif
                    </div>
                @empty
                    <div class="no-lessons">No upcoming slots</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="confirm-modal" id="confirmModal">
    <div class="confirm-dialog">
        <div class="confirm-icon warning" id="confirmIcon">
            <span id="confirmIconText">!</span>
        </div>
        <h3 class="confirm-title" id="confirmTitle">Are you sure?</h3>
        <p class="confirm-message" id="confirmMessage">This action cannot be undone.</p>
        <div class="confirm-actions">
            <button type="button" class="confirm-btn confirm-btn-cancel" onclick="closeConfirmModal()">Cancel</button>
            <button type="button" class="confirm-btn confirm-btn-confirm warning" id="confirmBtn" onclick="executeConfirm()">Confirm</button>
        </div>
    </div>
</div>

<!-- Removal Request Modal -->
<div class="modal-overlay" id="removalModal" onclick="if(event.target === this) closeRemovalModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Request Removal</h2>
            <button class="modal-close" onclick="closeRemovalModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="removalForm" method="POST">
                @csrf
                <p class="modal-help-text">
                    Please provide a reason for requesting removal from this time slot.
                </p>
                <p style="color: #666; margin-bottom: 15px;">
                    This sends a request to admin for review and does not remove you instantly.
                </p>
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">
                    Reason: <span style="color: #dc3545;">*</span>
                </label>
                <textarea name="reason" required maxlength="500" class="modal-reason-input" placeholder="E.g., conflicting appointment, personal emergency..."></textarea>
                <div class="modal-actions">
                    <button type="button" onclick="closeRemovalModal()" class="modal-cancel-btn">Cancel</button>
                    <button type="submit" class="btn-request">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>


    // Show pending toast from previous page action
    (function() {
        var msg = sessionStorage.getItem('scheduleToast');
        if (msg) {
            var data = JSON.parse(msg);
            sessionStorage.removeItem('scheduleToast');
            showToast(data.message, data.type);
        }
    })();

    function markLessonStatus(bookingId, sessionStatus, button) {
        var attendanceStatus = sessionStatus === 'no-show' ? 'absent' : 'attended';
        var promptMessage = sessionStatus === 'no-show'
            ? 'Mark this student as no-show for the selected lesson?'
            : 'Mark this lesson as done and submit it for admin verification?';

        showConfirm({
            title: 'Update Lesson Status',
            message: promptMessage,
            type: sessionStatus === 'no-show' ? 'warning' : 'success',
            confirmText: 'Yes, Continue',
            onConfirm: function() {
                submitLessonStatusUpdate(bookingId, sessionStatus, attendanceStatus, button);
            }
        });
    }

    function submitLessonStatusUpdate(bookingId, sessionStatus, attendanceStatus, button) {
        var actionRow = button.closest('.lesson-action-row');
        var rowButtons = actionRow ? actionRow.querySelectorAll('button') : [];
        var originalLabel = button.textContent;
        button.textContent = sessionStatus === 'no-show' ? 'Marking...' : 'Submitting...';
        rowButtons.forEach(function(rowButton) { rowButton.disabled = true; });

        fetch('{{ url($school->slug) }}/instructor/lessons/' + bookingId + '/update', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                session_status: sessionStatus,
                attendance_status: attendanceStatus
            })
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                showToast(data.message || 'Lesson status updated successfully.', 'success');
                setTimeout(function() {
                    window.location.reload();
                }, 500);
                return;
            }

            showToast(data.message || 'Failed to update lesson status.', 'error');
            button.textContent = originalLabel;
            rowButtons.forEach(function(rowButton) { rowButton.disabled = false; });
        })
        .catch(function(error) {
            console.error('Error:', error);
            showToast('An error occurred while updating lesson status.', 'error');
            button.textContent = originalLabel;
            rowButtons.forEach(function(rowButton) { rowButton.disabled = false; });
        });
    }

    // Tab switching
    function switchMainView(viewName) {
        document.querySelectorAll('.main-toggle-btn').forEach(function(btn) {
            var isTarget = btn.getAttribute('data-view') === viewName;
            btn.classList.toggle('active', isTarget);
        });

        document.querySelectorAll('.main-view-section').forEach(function(section) {
            section.classList.remove('active');
        });

        var targetView = document.getElementById(viewName + '-view');
        if (targetView) {
            targetView.classList.add('active');
        } else {
            document.getElementById('my-slots-view').classList.add('active');
        }
    }
    
    // Toggle date collapse
    function toggleDate(header) {
        var slots = header.nextElementSibling;
        header.classList.toggle('collapsed');
        slots.classList.toggle('collapsed');
    }
    
    // Show/hide past slots - My Slots view
    function toggleShowPastMy(checkbox) {
        var pastItems = document.querySelectorAll('#my-slots-view .schedule-item[data-is-past="true"]');
        pastItems.forEach(function(item) {
            item.style.display = checkbox.checked ? '' : 'none';
        });
    }
    
    // Show/hide past slots - Available view
    function toggleShowPastAvailable(checkbox) {
        var pastItems = document.querySelectorAll('#available-view .schedule-item[data-is-past="true"]');
        pastItems.forEach(function(item) {
            if (checkbox.checked) {
                // Check if it has visible slots when showing past
                var hasVisible = item.getAttribute('data-has-visible') === 'true';
                var showAllCourses = document.getElementById('show-all-courses').checked;
                item.style.display = (hasVisible || showAllCourses) ? '' : 'none';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    // Collapse all - My Slots view
    function toggleCollapseAllMy(checkbox) {
        var headers = document.querySelectorAll('#my-slots-view .schedule-date-header');
        var slots = document.querySelectorAll('#my-slots-view .schedule-slots');
        
        if (checkbox.checked) {
            headers.forEach(function(h) { h.classList.add('collapsed'); });
            slots.forEach(function(s) { s.classList.add('collapsed'); });
        } else {
            headers.forEach(function(h) { h.classList.remove('collapsed'); });
            slots.forEach(function(s) { s.classList.remove('collapsed'); });
        }
    }
    
    // Collapse all - Available view
    function toggleCollapseAllAvailable(checkbox) {
        var headers = document.querySelectorAll('#available-view .schedule-date-header');
        var slots = document.querySelectorAll('#available-view .schedule-slots');
        
        if (checkbox.checked) {
            headers.forEach(function(h) { h.classList.add('collapsed'); });
            slots.forEach(function(s) { s.classList.add('collapsed'); });
        } else {
            headers.forEach(function(h) { h.classList.remove('collapsed'); });
            slots.forEach(function(s) { s.classList.remove('collapsed'); });
        }
    }
    
    // Show all courses (including non-qualified)
    function toggleShowAllCourses(checkbox) {
        var items = document.querySelectorAll('#available-view .slot-item[data-qualified="false"]');
        items.forEach(function(item) {
            item.style.display = checkbox.checked ? '' : 'none';
        });
        
        // Also show/hide date groups that only have non-qualified slots
        var dateGroups = document.querySelectorAll('#available-view .schedule-item');
        dateGroups.forEach(function(group) {
            var hasVisible = group.getAttribute('data-has-visible') === 'true';
            var isPast = group.getAttribute('data-is-past') === 'true';
            var showPast = document.getElementById('show-past-available').checked;
            
            if (!isPast || showPast) {
                group.style.display = (hasVisible || checkbox.checked) ? '' : 'none';
            }
        });
    }
    
    // Leave slot
    function leaveSlot(slotId, btn) {
        showConfirm({
            title: 'Leave Slot',
            message: 'Are you sure you want to leave this slot?',
            type: 'warning',
            onConfirm: () => {
                btn.textContent = 'Leaving...';
                btn.disabled = true;
                
                leaveSlotExecute(slotId, btn);
            }
        });
    }

    function leaveSlotExecute(slotId, btn) {
        fetch('{{ url($school->slug) }}/instructor/timeslots/' + slotId + '/toggle', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                sessionStorage.setItem('scheduleToast', JSON.stringify({message: data.message || 'Successfully left the slot!', type: 'success'}));
                window.location.reload();
            } else {
                showToast(data.message || 'Failed to leave slot', 'error');
                btn.textContent = 'Leave Slot';
                btn.disabled = false;
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
            btn.textContent = 'Leave Slot';
            btn.disabled = false;
        });
    }
    
    // Select slot
    function selectSlot(slotId, btn) {
        btn.textContent = 'Selecting...';
        btn.disabled = true;
        
        fetch('{{ url($school->slug) }}/instructor/timeslots/' + slotId + '/toggle', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                sessionStorage.setItem('scheduleToast', JSON.stringify({message: data.message || 'Successfully selected the slot!', type: 'success'}));
                window.location.reload();
            } else {
                showToast(data.message || 'Failed to select slot', 'error');
                btn.textContent = 'Select Slot';
                btn.disabled = false;
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
            btn.textContent = 'Select Slot';
            btn.disabled = false;
        });
    }
    
    // Removal request modal
    function showRemovalModal(slotId) {
        var modal = document.getElementById('removalModal');
        var form = document.getElementById('removalForm');
        form.action = '{{ url($school->slug) }}/instructor/timeslots/' + slotId + '/request-removal';
        modal.classList.add('active');
        
        // Focus the textarea
        setTimeout(() => {
            modal.querySelector('textarea[name="reason"]').focus();
        }, 300);
    }
    
    function closeRemovalModal() {
        document.getElementById('removalModal').classList.remove('active');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var removalModal = document.getElementById('removalModal');
            if (removalModal && removalModal.classList.contains('active')) {
                closeRemovalModal();
            }
        }
    });
    
    // Handle removal form submission
    document.getElementById('removalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;

        showConfirm({
            title: 'Submit Removal Request',
            message: 'Send this request to admin for review?',
            type: 'warning',
            onConfirm: function() {
                submitRemovalRequest(form);
            }
        });
    });

    function submitRemovalRequest(form) {
        var formData = new FormData(form);
        var submitBtn = form.querySelector('button[type="submit"]');

        submitBtn.textContent = 'Submitting...';
        submitBtn.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                sessionStorage.setItem('scheduleToast', JSON.stringify({message: data.message || 'Removal request submitted!', type: 'success'}));
                window.location.reload();
            } else {
                showToast(data.message || 'Failed to submit request', 'error');
                submitBtn.textContent = 'Submit Request';
                submitBtn.disabled = false;
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
            submitBtn.textContent = 'Submit Request';
            submitBtn.disabled = false;
        });
    }

    // --- Localized Confirmation System ---
    var confirmCallback = null;

    function showConfirm(options) {
        const modal = document.getElementById('confirmModal');
        const icon = document.getElementById('confirmIcon');
        const iconText = document.getElementById('confirmIconText');
        const title = document.getElementById('confirmTitle');
        const message = document.getElementById('confirmMessage');
        const btn = document.getElementById('confirmBtn');
        
        title.textContent = options.title || 'Are you sure?';
        message.textContent = options.message || 'This action cannot be undone.';
        btn.textContent = options.confirmText || 'Confirm';
        
        const type = options.type || 'warning';
        icon.className = `confirm-icon ${type}`;
        btn.className = `confirm-btn confirm-btn-confirm ${type}`;
        
        const icons = { warning: '!', danger: '✕', info: 'i', success: '✓' };
        iconText.textContent = icons[type] || '!';
        confirmCallback = options.onConfirm;
        modal.classList.add('active');
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.remove('active');
        confirmCallback = null;
    }

    function executeConfirm() {
        if (confirmCallback) { try { confirmCallback(); } catch (e) { console.error(e); } }
        closeConfirmModal();
    }
    
    document.getElementById('confirmModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeConfirmModal();
    });
</script>
@endsection
