@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Schedule')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;

    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
    $borderRadius = $settings?->border_radius ?? 8;
@endphp

<style>
    .schedule-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .schedule-header {
        margin-bottom: 20px;
        border-bottom: 4px solid {{ $primaryColor }};
        padding-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .schedule-header h1 {
        font-size: 2rem;
        font-weight: 400;
        margin: 0;
        color: #1a202c;
    }
    
    .main-toggle {
        display: flex;
        gap: 0;
        background: white;
        border: 2px solid {{ $primaryColor }};
        border-radius: 8px;
        overflow: hidden;
    }
    
    .main-toggle-btn {
        padding: 12px 28px;
        background: white;
        color: #495057;
        border: none;
        cursor: pointer;
        font-size: 15px;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .main-toggle-btn.active {
        background: {{ $primaryColor }};
        color: white;
    }
    
    .main-toggle-btn:hover:not(.active) {
        background: #f8f9fa;
    }
    
    .main-view-section {
        display: none;
    }
    
    .main-view-section.active {
        display: block;
    }
    
    .schedule-grid {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 20px;
        margin-top: 20px;
        align-items: start;
    }
    
    @media (max-width: 992px) {
        .schedule-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .schedule-main {
        background: transparent;
        border: none;
        border-radius: 0;
        padding: 0;
        width: 100%;
    }
    
    .collapse-btn {
        background: #212529;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 20px;
    }
    
    .collapse-btn:hover {
        background: #000;
    }
    
    .schedule-item {
        margin-bottom: 12px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .schedule-date-header {
        @if($settings?->use_gradient_header)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $settings?->secondary_color ?? '#0a58ca' }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
        padding: 12px 16px;
        border-radius: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .schedule-date-header:hover {
        opacity: 0.9;
    }
    
    .schedule-date-header.cancelled-header {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }
    
    .schedule-date-header .icon {
        font-size: 18px;
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
    
    .schedule-bookings {
        padding-left: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        background: white;
    }
    
    .schedule-bookings.collapsed {
        max-height: 0 !important;
    }
    
    .booking-item {
        border-left: none;
        border-bottom: 1px solid #dee2e6;
        background: white;
        padding: 16px;
        margin: 0;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    
    .booking-item:last-child {
        border-bottom: none;
    }
    
    .booking-icon {
        width: 40px;
        height: 40px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    
    .booking-details {
        flex: 1;
    }
    
    .booking-instructor {
        font-weight: 600;
        color: #000;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .course-badge {
        background: #17a2b8;
        color: white;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .booking-time {
        color: #6c757d;
        font-size: 14px;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .booking-status {
        color: #6c757d;
        font-size: 13px;
    }
    
    /* Sidebar */
    .requests-sidebar {
        align-self: start;
        display: flex;
        flex-direction: column;
        gap: 20px;
        position: sticky;
        top: 20px;
        margin-top: 68px;
    }
    
    .sidebar-title {
        font-size: 1.5rem;
        font-weight: 400;
        margin: 0 0 20px 0;
        color: #000;
    }
    
    .request-card {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
    }
    
    .request-card:last-child {
        margin-bottom: 0;
    }
    
    .request-date {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        color: #000;
    }
    
    .request-date .icon {
        font-size: 18px;
        color: #6c757d;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status-badge.approved {
        background: #d4edda;
        color: #155724;
    }
    
    .status-badge.pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .request-time {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 8px 0;
        color: #000;
        font-size: 14px;
    }
    
    .request-instructor {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 8px 0;
        color: #000;
        font-size: 14px;
    }
    
    .request-status {
        margin-top: 12px;
        padding: 10px;
        border-radius: 4px;
        font-size: 13px;
    }
    
    .request-status.confirmed {
        background: #d4edda;
        color: #155724;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .request-status.waiting {
        background: #fff3cd;
        color: #856404;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    /* Available Schedules */
    .available-schedule-card {
        border-left: none;
        border-bottom: 1px solid #dee2e6;
        background: white;
        padding: 16px;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .available-schedule-card:last-child {
        border-bottom: none;
    }
    
    .available-icon {
        display: none;
    }
    
    .available-details {
        flex: 1;
        width: 100%;
    }
    
    .book-now-btn {
        background: {{ $primaryColor }};
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 14px;
        margin-top: 8px;
    }
    
    .book-now-btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    
    .instructor-select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        font-size: 14px;
        margin-top: 8px;
        background: white;
        cursor: pointer;
    }
    
    .instructor-select:focus {
        outline: none;
        border-color: {{ $primaryColor }};
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }
    
    .select-label {
        font-size: 13px;
        color: #6c757d;
        margin-top: 8px;
        margin-bottom: 4px;
        font-weight: 500;
    }
    
    /* Collapsible Sidebar */
    .sidebar-collapsible {
        margin-bottom: 16px;
    }
    
    .sidebar-section-header {
        background: #f8f9fa;
        padding: 12px 16px;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s;
        border: 1px solid #dee2e6;
    }
    
    .sidebar-section-header:hover {
        background: #e9ecef;
    }
    
    .sidebar-section-title {
        font-weight: 600;
        color: #000;
        font-size: 1rem;
        margin: 0;
    }
    
    .sidebar-toggle-icon {
        transition: transform 0.3s;
        font-size: 14px;
    }
    
    .sidebar-section-header.collapsed .sidebar-toggle-icon {
        transform: rotate(-90deg);
    }
    
    .sidebar-section-content {
        max-height: 600px;
        overflow: hidden;
        transition: max-height 0.3s ease;
        margin-top: 8px;
    }
    
    .sidebar-section-content.collapsed {
        max-height: 0 !important;
        margin-top: 0;
    }
    
    .mini-booking-card {
        background: white;
        border: 1px solid #dee2e6;
        border-left: 3px solid {{ $primaryColor }};
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 10px;
        font-size: 13px;
    }
    
    .mini-booking-date {
        font-weight: 600;
        color: #000;
        margin-bottom: 6px;
        font-size: 14px;
    }
    
    .mini-booking-info {
        color: #6c757d;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .calendar-placeholder {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 40px 20px;
        text-align: center;
        color: #6c757d;
    }
    
    .sidebar-section {
        margin-bottom: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 16px;
    }
    
    .sidebar-section:last-child {
        margin-bottom: 0;
    }
    
    .sidebar-section-title-simple {
        font-size: 1.1rem;
        font-weight: 600;
        color: #000;
        margin: 0 0 12px 0;
    }
    
    /* Tablet Responsiveness */
    @media (max-width: 992px) {
        .schedule-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .schedule-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .schedule-header h1 {
            font-size: 1.75rem;
        }
        
        .main-toggle {
            width: 100%;
        }
        
        .main-toggle-btn {
            flex: 1;
            padding: 12px 20px;
            font-size: 14px;
        }
        
        .requests-sidebar {
            width: 100%;
            max-width: none;
        }
        
        .sidebar-section {
            margin-bottom: 15px;
        }
    }
    
    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .schedule-container {
            padding: 10px;
        }
        
        .schedule-header h1 {
            font-size: 1.2rem;
        }
        
        .main-toggle-btn {
            padding: 8px 14px;
            font-size: 0.85rem;
        }
        
        .schedule-main,
        .requests-sidebar {
            padding: 8px;
        }
        
        .schedule-date-header {
            padding: 10px 12px;
            font-size: 0.75rem;
        }
        
        .schedule-date-header .date-text {
            font-size: 0.75rem;
        }
        
        .toggle-icon {
            font-size: 0.9rem;
        }
        
        .booking-item {
            padding: 10px;
        }
        
        .booking-card {
            padding: 8px;
            margin-bottom: 6px;
        }
        
        .booking-info p {
            font-size: 0.7rem;
            margin: 2px 0;
            line-height: 1.2;
        }
        
        .booking-instructor {
            font-size: 0.75rem;
            margin-bottom: 4px;
        }
        
        .booking-time {
            font-size: 0.7rem;
            margin-left: -6px;
        }
        
        .booking-status {
            font-size: 0.65rem;
            margin-top: 4px;
        }
        
        .course-badge {
            font-size: 0.6rem;
            padding: 2px 6px;
        }
        
        .mini-booking-card {
            padding: 6px 8px;
            margin-bottom: 6px;
        }
        
        .mini-booking-card .mini-date {
            font-size: 0.65rem;
        }
        
        .mini-booking-card .mini-course {
            font-size: 0.7rem;
        }
        
        .collapse-btn {
            padding: 8px 14px;
            font-size: 0.85rem;
            margin-bottom: 8px;
        }
        
        .available-schedule-card {
            padding: 12px;
            margin: 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .available-schedule-card:last-child {
            border-bottom: none;
        }
        
        .course-name {
            font-size: 0.75rem;
        }
        
        .time-info {
            font-size: 0.7rem;
        }
        
        .booking-details .booking-status {
            font-size: 0.65rem;
        }
        
        .instructor-select {
            font-size: 0.85rem;
            padding: 8px 10px;
        }
        
        .book-now-btn {
            padding: 10px 14px;
            font-size: 0.85rem;
            min-height: 38px;
        }
        
        .sidebar-section {
            padding: 8px;
            margin-bottom: 8px;
        }
        
        .sidebar-section h3 {
            font-size: 0.85rem;
            margin-bottom: 8px;
        }
    }
    
    /* Small Mobile Responsiveness */
    @media (max-width: 480px) {
        .schedule-container {
            padding: 8px;
        }
        
        .schedule-header {
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .schedule-header h1 {
            font-size: 1.1rem;
            margin: 0;
        }
        
        .main-toggle {
            gap: 6px;
        }
        
        .main-toggle-btn {
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .schedule-main,
        .requests-sidebar {
            padding: 8px;
            border-radius: 6px;
        }
        
        .schedule-date-header {
            padding: 8px 10px;
            font-size: 0.8rem;
        }
        
        .schedule-date-header .icon {
            font-size: 0.9rem;
            display: none; /* Hide emoji on small screens */
        }
        
        .toggle-icon {
            font-size: 0.7rem;
        }
        
        .booking-card {
            padding: 10px;
            margin-bottom: 8px;
        }
        
        .booking-info p {
            font-size: 0.75rem;
            margin: 3px 0;
            line-height: 1.3;
        }
        
        .booking-time {
            font-size: 0.75rem;
            padding: 4px 8px;
            margin-top: 6px;
        }
        
        .mini-booking-card {
            padding: 6px 8px;
            margin-bottom: 6px;
        }
        
        .mini-date {
            font-size: 0.7rem;
        }
        
        .mini-course {
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-badge {
            font-size: 0.65rem;
            padding: 2px 6px;
        }
        
        .sidebar-section {
            padding: 10px;
            margin-bottom: 10px;
        }
        
        .sidebar-section h3 {
            font-size: 0.9rem;
            margin: 0 0 8px 0;
        }
        
        .collapse-btn {
            padding: 8px 14px;
            font-size: 0.85rem;
            margin-bottom: 10px;
        }
        
        .available-schedule-card {
            padding: 10px;
            margin-bottom: 10px;
        }
        
        .course-name {
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 6px;
        }
        
        .time-info,
        .spots-info {
            font-size: 0.75rem;
            margin-bottom: 4px;
        }
        
        .select-label {
            font-size: 0.75rem;
            margin-bottom: 4px;
        }
        
        .instructor-select {
            font-size: 0.85rem;
            padding: 8px 10px;
            margin-bottom: 6px;
        }
        
        .book-now-btn {
            padding: 8px 14px;
            font-size: 0.85rem;
            font-weight: 600;
            min-height: 38px;
        }
        
        /* Make cards more compact */
        .schedule-grid {
            gap: 10px;
        }
        
        .sidebar-section {
            padding: 8px;
            margin-bottom: 8px;
        }
        
        /* Hide "Manual" badge on very small screens */
        .available-schedule-card .status-badge {
            display: none;
        }
        
        /* Simplify available schedule cards */
        .available-schedule-card {
            padding: 10px;
        }
        
        /* Show mobile queue button */
        .mobile-queue-btn {
            display: inline-block !important;
        }
        
        /* Hide sidebar on mobile */
        .requests-sidebar {
            display: none;
        }
    }
    
    /* Queue Popup Styles */
    .queue-popup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        overflow-y: auto;
    }
    
    .queue-popup.active {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .queue-popup-content {
        background: white;
        border-radius: 12px;
        max-width: 500px;
        width: 100%;
        max-height: 80vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .queue-popup-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .queue-popup-header h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .queue-close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #6c757d;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .queue-close-btn:hover {
        color: #000;
    }

    .queue-close-btn:focus-visible {
        outline: 2px solid {{ $primaryColor }};
        outline-offset: 2px;
        border-radius: 6px;
    }
    
    .queue-popup-body {
        padding: 16px 20px;
        overflow-y: auto;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .queue-popup-body::-webkit-scrollbar {
        display: none;
    }
    
    /* Filter/Checkbox Container for Mobile */
    .schedule-filters {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }
    
    .filter-checkbox {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-size: 14px;
        color: #6c757d;
        white-space: nowrap;
    }
    
    .filter-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: {{ $primaryColor }};
    }

    .mobile-queue-btn {
        display: none;
    }

    .queue-meta {
        font-size: 0.78rem;
        color: #6c757d;
    }

    .queue-action-row {
        display: flex;
        gap: 4px;
        margin-top: 8px;
    }

    .queue-action-form {
        flex: 1;
    }

    .queue-action-btn {
        width: 100%;
        color: white;
        border: none;
        padding: 7px 10px;
        border-radius: 4px;
        font-size: 0.82rem;
        cursor: pointer;
        min-height: 34px;
    }

    .queue-action-confirm {
        background: #28a745;
    }

    .queue-action-remove {
        background: #dc3545;
    }

    .schedule-alert {
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 16px;
        position: relative;
    }

    .schedule-alert-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .schedule-alert-error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }

    .dismiss-alert-btn {
        background: none;
        border: none;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0 4px;
        line-height: 1;
    }

    .dismiss-alert-btn-success {
        color: #155724;
    }

    .dismiss-alert-btn-error {
        position: absolute;
        top: 8px;
        right: 8px;
        color: #721c24;
    }

    .error-list-compact {
        margin: 0;
        padding-left: 20px;
    }

    .schedule-section-title {
        margin: 0;
        color: #000;
        font-size: 1.2rem;
        font-weight: 600;
    }

    .schedule-empty-state {
        text-align: center;
        color: #6c757d;
        padding: 40px;
    }

    .schedule-empty-state-compact {
        text-align: center;
        color: #6c757d;
        padding: 20px;
        font-size: 14px;
    }

    .schedule-cancelled-title {
        margin: 30px 0 12px 0;
        color: #dc3545;
        font-size: 1.1rem;
    }

    .is-hidden {
        display: none;
    }

    .schedule-bookings-expanded {
        max-height: 800px;
    }

    .booking-side-strip {
        width: 4px;
        border-radius: 2px;
        flex-shrink: 0;
        align-self: stretch;
    }

    .booking-side-strip-completed {
        background: #28a745;
    }

    .booking-side-strip-primary {
        background: {{ $secondaryColor }};
    }

    .booking-side-strip-cancelled {
        background: #dc3545;
    }

    .booking-status-pill {
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
    }

    .booking-status-completed {
        background: #28a745;
        color: #fff;
    }

    .booking-status-cancelled-you {
        background: #ffc107;
        color: #000;
    }

    .booking-status-cancelled-instructor {
        background: #17a2b8;
        color: #fff;
    }

    .booking-status-cancelled-school {
        background: #6c757d;
        color: #fff;
    }

    .booking-status-cancelled-default {
        background: #dc3545;
        color: #fff;
    }

    .booking-cancel-reason {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 4px;
        font-style: italic;
    }

    .queue-sidebar-highlight {
        background: #fff3cd;
        border: 2px solid #ffc107;
    }

    .queue-sidebar-highlight-my {
        margin-top: 35px;
    }

    .queue-sidebar-title {
        color: #856404;
    }

    .queue-sidebar-help {
        font-size: 0.85rem;
        color: #856404;
        margin-bottom: 12px;
    }

    .mini-booking-card-queued {
        border-left-color: #ffc107;
        background: white;
    }

    .mini-booking-date-queued {
        color: #856404;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .mini-booking-info-strong {
        font-weight: 600;
        color: #000;
    }

    .queue-vehicle-chip {
        background: #17a2b8;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.65rem;
        margin-left: 4px;
    }

    .available-filter-select {
        padding: 5px 10px;
        border: 1.5px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.85rem;
        background: white;
        cursor: pointer;
    }

    .booking-time-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .course-badge-primary {
        background: {{ $primaryColor }};
        color: white;
    }

    .course-badge-enrolled {
        background: #d4edda;
        color: #155724;
    }

    .course-badge-branch {
        background: #e0e7ff;
        color: #3730a3;
        font-size: 0.78rem;
    }

    .available-actions-row {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-top: 8px;
        flex-wrap: wrap;
    }

    .booking-status-inline {
        margin: 0;
        flex: 1;
    }

    .book-now-btn-inline {
        margin: 0;
    }

    .book-now-btn-disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .queue-popup-queued-section {
        background: #fff3cd;
        border: 2px solid #ffc107;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 16px;
    }

    .queue-popup-queued-title {
        margin: 0 0 8px 0;
        color: #856404;
        font-size: 0.9rem;
    }

    .queue-popup-queued-help {
        margin: 0 0 10px 0;
        color: #856404;
        font-size: 0.75rem;
    }

    .queue-popup-queued-card {
        background: white;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 8px;
        border: 1px solid #ffc107;
    }

    .queue-popup-queued-date {
        font-weight: 600;
        color: #000;
        font-size: 0.85rem;
        margin-bottom: 4px;
    }

    .queue-popup-queued-info {
        color: #6c757d;
        font-size: 0.75rem;
    }

    .queue-popup-queued-meta {
        color: #6c757d;
        font-size: 0.75rem;
        margin-top: 2px;
    }

    .queue-popup-upcoming-title {
        margin: 0 0 10px 0;
        color: #000;
        font-size: 0.9rem;
    }
    
    @media (max-width: 768px) {
        .schedule-filters {
            gap: 8px;
            margin-top: 8px;
        }
        
        .filter-checkbox {
            font-size: 11px;
            gap: 4px;
            background: #f8f9fa;
            padding: 6px 10px;
            border-radius: 16px;
            border: 1px solid #dee2e6;
        }
        
        .filter-checkbox input[type="checkbox"] {
            width: 14px;
            height: 14px;
        }
        
        .filter-checkbox span {
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }
    
    @media (max-width: 480px) {
        .schedule-header-controls {
            flex-direction: column;
            align-items: stretch !important;
            gap: 8px !important;
        }
        
        .schedule-filters {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
        }
        
        .filter-checkbox {
            font-size: 10px;
            padding: 5px 8px;
            justify-content: center;
        }
        
        .filter-checkbox input[type="checkbox"] {
            width: 12px;
            height: 12px;
        }
        
        .filter-checkbox span {
            max-width: none;
        }
    }
</style>

<div class="schedule-container">
    <div class="schedule-header">
        <h1>Schedule</h1>
        <div class="main-toggle">
            <button class="main-toggle-btn active" onclick="switchMainView('my-schedule')">My Schedule</button>
            <button class="main-toggle-btn" onclick="switchMainView('available-schedules')">Available Schedules</button>
        </div>
    </div>
    

    
    <!-- My Schedule View -->
    <div id="my-schedule-view" class="main-view-section active">
    <div class="schedule-grid">
        <!-- Left: Schedule List -->
        <div class="schedule-main">
            <div class="schedule-header-controls">
                <h3 class="schedule-section-title">My Confirmed Schedule</h3>
                <div class="schedule-filters">
                    <label class="filter-checkbox">
                        <input type="checkbox" id="collapse-all-my" onchange="toggleCollapseAllMySchedule(this)">
                        <span>Collapse All</span>
                    </label>
{{-- 
                    <label class="filter-checkbox">
                        <input type="checkbox" id="show-past-my" onchange="toggleShowPastMySchedule(this)">
                        <span>Show Past</span>
                    </label>
--}}
                    <button class="collapse-btn mobile-queue-btn" onclick="toggleQueuePopup()">My Schedule</button>
                </div>
            </div>
            
            @forelse($groupedBookings as $date => $dateBookings)
                @php
                    $isPast = \Carbon\Carbon::parse($date)->lt(now()->startOfDay());
                @endphp
                <div class="schedule-item {{ $isPast ? 'is-hidden' : '' }}" data-is-past="{{ $isPast ? 'true' : 'false' }}">
                    <div class="schedule-date-header" onclick="toggleDate(this)">
                        <span class="date-text">{{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</span>
                        <span class="toggle-icon">&#x25BC;</span>
                    </div>
                    <div class="schedule-bookings schedule-bookings-expanded">
                        @foreach($dateBookings as $booking)
                            <div class="booking-item">
                                <div class="booking-side-strip {{ $booking->status === 'completed' ? 'booking-side-strip-completed' : 'booking-side-strip-primary' }}"></div>
                                <div class="booking-details">
                                    <div class="booking-instructor">
                                        {{ $booking->instructor->name ?? 'Instructor\'s Name' }}
                                        @if($booking->course)
                                            <span class="course-badge">{{ $booking->course->title ?? 'Course' }}</span>
                                            <span class="course-badge">{{ $booking->course->vehicle_type ?? 'Manual' }}</span>
                                        @endif
                                    </div>
                                    <div class="booking-time">
                                        @if($booking->timeSlot)
                                            {{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($booking->timeSlot->end_time)->format('g:i A') }}
                                        @elseif($booking->scheduled_at)
                                            {{ \Carbon\Carbon::parse($booking->scheduled_at)->format('g:i A') }}
                                        @else
                                            Time TBD
                                        @endif
                                    </div>
                                    <div class="booking-status" style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px;">
                                        @if($booking->status === 'completed')
                                            <span class="booking-status-pill booking-status-completed">
                                                &#10003; Completed
                                            </span>
                                        @else
                                            <span>Status: {{ ucfirst($booking->status ?? 'Scheduled') }}</span>
                                            <form method="POST" action="{{ route('schools.student.bookings.cancel', [$school->slug, $booking->id]) }}" style="margin: 0;">
                                                @csrf
                                                <button type="button" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 3px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'" onclick="showConfirmDialog('Are you sure you want to cancel this scheduled lesson? Your instructor will be notified and your reserved spot will be released.', () => this.closest('form').submit())">
                                                    Cancel Schedule
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="schedule-empty-state">No confirmed schedule.</p>
            @endforelse
            
            <!-- Cancelled Schedules Section -->
            @if($groupedCancelledBookings->count() > 0)
            <h3 class="schedule-cancelled-title">Cancelled Schedules</h3>
            @foreach($groupedCancelledBookings as $date => $dateCancelledBookings)
                @php
                    $isPast = \Carbon\Carbon::parse($date)->lt(now()->startOfDay());
                @endphp
                <div class="schedule-item {{ $isPast ? 'is-hidden' : '' }}" data-is-past="{{ $isPast ? 'true' : 'false' }}">
                    <div class="schedule-date-header cancelled-header" onclick="toggleDate(this)">
                        <span class="date-text">{{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</span>
                        <span class="toggle-icon">&#x25BC;</span>
                    </div>
                    <div class="schedule-bookings schedule-bookings-expanded">
                        @foreach($dateCancelledBookings as $booking)
                            <div class="booking-item">
                                <div class="booking-side-strip booking-side-strip-cancelled"></div>
                                <div class="booking-details">
                                    <div class="booking-instructor">
                                        {{ $booking->instructor->name ?? 'Instructor\'s Name' }}
                                        @if($booking->course)
                                            <span class="course-badge">{{ $booking->course->title ?? 'Course' }}</span>
                                            <span class="course-badge">{{ $booking->course->vehicle_type ?? 'Manual' }}</span>
                                        @endif
                                    </div>
                                    <div class="booking-time">
                                        @if($booking->timeSlot)
                                            {{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($booking->timeSlot->end_time)->format('g:i A') }}
                                        @elseif($booking->scheduled_at)
                                            {{ \Carbon\Carbon::parse($booking->scheduled_at)->format('g:i A') }}
                                        @else
                                            Time TBD
                                        @endif
                                    </div>
                                    <div class="booking-status">
                                        @if($booking->cancelled_by === 'student')
                                            <span class="booking-status-pill booking-status-cancelled-you">
                                                Cancelled by You
                                            </span>
                                        @elseif($booking->cancelled_by === 'instructor')
                                            <span class="booking-status-pill booking-status-cancelled-instructor">
                                                Cancelled by Instructor
                                            </span>
                                        @elseif($booking->cancelled_by === 'admin')
                                            <span class="booking-status-pill booking-status-cancelled-school">
                                                Cancelled by School
                                            </span>
                                        @else
                                            <span class="booking-status-pill booking-status-cancelled-default">
                                                Cancelled
                                            </span>
                                        @endif
                                    </div>
                                    @if($booking->cancellation_reason)
                                        <div class="booking-cancel-reason">
                                            Reason: {{ $booking->cancellation_reason }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
            @endif
        </div>
        
        <!-- Right: Requests Sidebar -->
        <div class="requests-sidebar">
            @if($queueEnabled && $queuedBookings->count() > 0)
            <div class="sidebar-section queue-sidebar-highlight queue-sidebar-highlight-my">
                <h3 class="sidebar-section-title-simple queue-sidebar-title">
                    Queued Schedules ({{ $queuedBookings->count() }})
                </h3>
                <p class="queue-sidebar-help">
                    Pending schedules are auto-confirmed after {{ $queueDays }} days
                </p>
                @foreach($queuedBookings as $booking)
                    <div class="mini-booking-card mini-booking-card-queued">
                        <div class="mini-booking-date mini-booking-date-queued">
                            <span>{{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}</span>
                        </div>
                        <div class="mini-booking-info mini-booking-info-strong">
                            {{ $booking->course->title ?? 'Course' }}
                            @if($booking->course)
                                <span class="queue-vehicle-chip">
                                    {{ $booking->course->vehicle_type ?? 'Manual' }}
                                </span>
                            @endif
                        </div>
                        <div class="mini-booking-info">
                            @if($booking->timeSlot)
                                {{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($booking->timeSlot->end_time)->format('g:i A') }}
                            @elseif($booking->scheduled_at)
                                {{ \Carbon\Carbon::parse($booking->scheduled_at)->format('g:i A') }}
                            @endif
                        </div>
                        <div class="mini-booking-info queue-meta">
                            Added {{ \Carbon\Carbon::parse($booking->created_at)->diffForHumans() }}
                        </div>
                        <div class="queue-action-row">
                            <form method="POST" action="{{ route('schools.student.bookings.confirm', [$school->slug, $booking->id]) }}" class="queue-action-form">
                                @csrf
                                <button type="button" style="width: 100%; background: #28a745; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; cursor: pointer;" onclick="showConfirmDialog('Confirm this booking now?',()=>this.closest('form').submit())">
                                    Confirm
                                </button>
                            </form>
                            <form method="POST" action="{{ route('schools.student.bookings.removeQueue', [$school->slug, $booking->id]) }}" class="queue-action-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" style="width: 100%; background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; cursor: pointer;" onclick="showConfirmDialog('Cancel this booking?',()=>this.closest('form').submit())">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
            
            <div class="sidebar-section">
                <h3 class="sidebar-section-title-simple">Upcoming This Week</h3>
                
                @forelse($upcomingBookings->take(5) as $booking)
                    <div class="mini-booking-card">
                        <div class="mini-booking-date">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}
                        </div>
                        <div class="mini-booking-info">
                            @if($booking->timeSlot)
                                {{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A') }}
                            @else
                                Time TBD
                            @endif
                        </div>
                        <div class="mini-booking-info">
                            {{ $booking->instructor->name ?? 'Instructor' }}
                        </div>
                        @if($booking->course)
                            <div class="mini-booking-info">
                                {{ $booking->course->title }}
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="schedule-empty-state-compact">
                        No upcoming schedules yet.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
    </div>
    <!-- End My Schedule View -->
    
    <!-- Available Schedules View -->
    <div id="available-schedules-view" class="main-view-section">
    <div class="schedule-grid">
        <!-- Left: Available Time Slots -->
        <div class="schedule-main">
            <div class="schedule-header-controls">
                <h3 class="schedule-section-title">Available Schedules</h3>
                <div class="schedule-filters">
                    <label class="filter-checkbox">
                        <input type="checkbox" id="collapse-all-available" onchange="toggleCollapseAllAvailable(this)">
                        <span>Collapse All</span>
                    </label>
{{-- 
                    <label class="filter-checkbox">
                        <input type="checkbox" id="show-past-available" onchange="toggleShowPastAvailable(this)">
                        <span>Show Past</span>
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox" id="show-all-courses" onchange="toggleShowAllCourses()">
                        <span>All Courses</span>
                    </label>
                    @php
                        $branchesForFilter = \App\Models\Branch::where('school_id', $school->id)->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
                    @endphp
                    @if($branchesForFilter->count() > 0)
                    <select id="branch-filter-available" onchange="filterByBranchSchedule()" class="available-filter-select">
                        <option value="">All Branches</option>
                        @foreach($branchesForFilter as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                    @endif
--}}
                    <button class="collapse-btn mobile-queue-btn" onclick="toggleQueuePopup()">My Schedule</button>
                </div>
            </div>

            @php
                $approvedEnrollmentByCourse = collect($enrollmentRequests ?? [])
                    ->where('status', 'approved')
                    ->keyBy('course_id')
                    ->map(fn($enrollment) => $enrollment->id);
            @endphp

            @if(!($hasApprovedEnrollment ?? true))
                <div class="alert alert-info" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;border-radius:10px;padding:14px 16px;margin-bottom:12px;">
                    <strong>Enrollment not processed yet.</strong>
                    @if(!empty($pendingEnrollmentExists))
                        Your enrollment request is still pending admin review. Available schedules will appear here once your enrollment is approved and your payment is verified.
                    @else
                        You don't have an approved enrollment yet. Enroll in a course first — schedules will appear here once approved.
                    @endif
                </div>
            @endif

            @forelse($groupedAvailableSchedules as $date => $dateSchedules)
                @php
                    $isPast = \Carbon\Carbon::parse($date)->lt(now()->startOfDay());
                    // Check if any slots are visible (enrolled) for this date
                    $hasVisibleSlots = $dateSchedules->filter(function($slot) use ($enrolledCourseIds) {
                        return empty($enrolledCourseIds) || in_array($slot->course_id, $enrolledCourseIds);
                    })->count() > 0;
                @endphp
                <div class="schedule-item {{ $isPast || !$hasVisibleSlots ? 'is-hidden' : '' }}" data-is-past="{{ $isPast ? 'true' : 'false' }}" data-has-visible="{{ $hasVisibleSlots ? 'true' : 'false' }}">
                    <div class="schedule-date-header" onclick="toggleDate(this)">
                        <span class="date-text">{{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</span>
                        <span class="toggle-icon">&#x25BC;</span>
                    </div>
                    <div class="schedule-bookings schedule-bookings-expanded">
                        @foreach($dateSchedules as $timeSlot)
                            @php
                                $isEnrolledInCourse = empty($enrolledCourseIds) || in_array($timeSlot->course_id, $enrolledCourseIds);
                                $courseName = $timeSlot->course->title ?? 'Driving Lesson';
                                $linkedEnrollmentRequestId = $approvedEnrollmentByCourse->get($timeSlot->course_id);
                                                                $availableStudentSpots = $timeSlot->getAvailableStudentSpots();
                                                                $slotIsFull = $availableStudentSpots <= 0;
                            @endphp
                               <div class="available-schedule-card {{ !$isEnrolledInCourse ? 'is-hidden' : '' }}" 
                                 data-course-id="{{ $timeSlot->course_id ?? '' }}" 
                                 data-course-name="{{ $courseName }}"
                                 data-enrollment-request-id="{{ $linkedEnrollmentRequestId ?? '' }}"
                                 data-branch-id="{{ $timeSlot->branch_id ?? '' }}"
                                 data-date="{{ $date }}"
                                 data-start-time="{{ \Carbon\Carbon::parse($timeSlot->start_time)->format('H:i') }}"
                                 data-end-time="{{ \Carbon\Carbon::parse($timeSlot->end_time)->format('H:i') }}"
                                                                 data-available-spots="{{ $availableStudentSpots }}"
                                   data-enrolled="{{ $isEnrolledInCourse ? 'true' : 'false' }}">
                                <div class="available-details">
                                    <div class="booking-time booking-time-wrap">
                                        {{ \Carbon\Carbon::parse($timeSlot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($timeSlot->end_time)->format('g:i A') }}
                                        @if($timeSlot->course)
                                            <span class="course-badge course-badge-primary">{{ $timeSlot->course->title ?? 'Course' }}</span>
                                            @if($isEnrolledInCourse)
                                                <span class="course-badge course-badge-enrolled">&#10003; Enrolled</span>
                                            @endif
                                        @endif
                                        @if($timeSlot->branch_id && $timeSlot->branch)
                                            <span class="course-badge course-badge-branch">{{ $timeSlot->branch->name }}</span>
                                        @endif
                                    </div>
                                    
                                    @php
                                        $selectionMode = $settings?->instructor_selection_mode ?? 'auto_assign';
                                    @endphp
                                    
                                    @if($selectionMode === 'student_chooses')
                                        <!-- Student selects instructor -->
                                        <select class="instructor-select" id="instructor-{{ $timeSlot->id }}" onchange="updateBookButton({{ $timeSlot->id }})" {{ $slotIsFull ? 'disabled' : '' }}>
                                            <option value="">Select an instructor</option>
                                            @if($timeSlot->instructors->count() > 0)
                                                @foreach($timeSlot->instructors as $instructor)
                                                    <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        
                                        <div class="available-actions-row">
                                            @if($timeSlot->course)
                                                <span class="course-badge">{{ $timeSlot->course->vehicle_type ?? 'Manual' }}</span>
                                            @endif
                                            <div class="booking-status booking-status-inline">
                                                {{ $availableStudentSpots }} {{ $availableStudentSpots === 1 ? 'spot' : 'spots' }} available
                                            </div>
                                            <button class="book-now-btn book-now-btn-inline book-now-btn-disabled" id="book-btn-{{ $timeSlot->id }}" onclick="bookTimeSlot({{ $timeSlot->id }})" disabled>
                                                {{ $slotIsFull ? 'Full' : 'Schedule Lesson' }}
                                            </button>
                                        </div>
                                    @elseif($selectionMode === 'auto_assign')
                                        <!-- System auto-assigns -->
                                        <div class="available-actions-row">
                                            @if($timeSlot->course)
                                                <span class="course-badge">{{ $timeSlot->course->vehicle_type ?? 'Manual' }}</span>
                                            @endif
                                            <div class="booking-status booking-status-inline">
                                                {{ $availableStudentSpots }} {{ $availableStudentSpots === 1 ? 'spot' : 'spots' }} available
                                            </div>
                                            <button class="book-now-btn book-now-btn-inline {{ $slotIsFull ? 'book-now-btn-disabled' : '' }}" onclick="bookTimeSlotAuto({{ $timeSlot->id }})" {{ $slotIsFull ? 'disabled' : '' }}>
                                                {{ $slotIsFull ? 'Full' : 'Schedule Now' }}
                                            </button>
                                        </div>
                                    @else
                                        <!-- Admin assigns (wait for admin) -->
                                        <div class="available-actions-row">
                                            @if($timeSlot->course)
                                                <span class="course-badge">{{ $timeSlot->course->vehicle_type ?? 'Manual' }}</span>
                                            @endif
                                            <div class="booking-status booking-status-inline">
                                                {{ $availableStudentSpots }} {{ $availableStudentSpots === 1 ? 'spot' : 'spots' }} available
                                            </div>
                                            <button class="book-now-btn book-now-btn-inline {{ $slotIsFull ? 'book-now-btn-disabled' : '' }}" onclick="bookTimeSlotAdmin({{ $timeSlot->id }})" {{ $slotIsFull ? 'disabled' : '' }}>
                                                {{ $slotIsFull ? 'Full' : 'Request Schedule' }}
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="schedule-empty-state">No schedules are currently available.</p>
            @endforelse
        </div>
        
        <!-- Right: My Upcoming Lessons & Calendar -->
        <div class="requests-sidebar">
            <!-- Schedule Queued Section -->
            @if($queueEnabled && $queuedBookings->count() > 0)
            <div class="sidebar-section queue-sidebar-highlight">
                <h3 class="sidebar-section-title-simple queue-sidebar-title">
                    Queued Schedules ({{ $queuedBookings->count() }})
                </h3>
                <p class="queue-sidebar-help">
                    Pending schedules are auto-confirmed after {{ $queueDays }} days
                </p>
                @foreach($queuedBookings as $booking)
                    <div class="mini-booking-card mini-booking-card-queued">
                        <div class="mini-booking-date mini-booking-date-queued">
                            <span>{{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}</span>
                        </div>
                        <div class="mini-booking-info mini-booking-info-strong">
                            {{ $booking->course->title ?? 'Course' }}
                            @if($booking->course)
                                <span class="queue-vehicle-chip">
                                    {{ $booking->course->vehicle_type ?? 'Manual' }}
                                </span>
                            @endif
                        </div>
                        <div class="mini-booking-info">
                            @if($booking->timeSlot)
                                {{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($booking->timeSlot->end_time)->format('g:i A') }}
                            @elseif($booking->scheduled_at)
                                {{ \Carbon\Carbon::parse($booking->scheduled_at)->format('g:i A') }}
                            @endif
                        </div>
                        <div class="mini-booking-info queue-meta">
                            Added {{ \Carbon\Carbon::parse($booking->created_at)->diffForHumans() }}
                        </div>
                        <div class="queue-action-row">
                            <form method="POST" action="{{ route('schools.student.bookings.confirm', [$school->slug, $booking->id]) }}" class="queue-action-form">
                                @csrf
                                <button type="button" style="width: 100%; background: #28a745; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; cursor: pointer;" onclick="showConfirmDialog('Confirm this booking now?',()=>this.closest('form').submit())">
                                    Confirm
                                </button>
                            </form>
                            <form method="POST" action="{{ route('schools.student.bookings.removeQueue', [$school->slug, $booking->id]) }}" class="queue-action-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" style="width: 100%; background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; cursor: pointer;" onclick="showConfirmDialog('Cancel this booking?',()=>this.closest('form').submit())">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
            
            <div class="sidebar-section">
                <h3 class="sidebar-section-title-simple">Upcoming This Week</h3>
                
                @forelse($upcomingBookings->take(5) as $booking)
                    <div class="mini-booking-card">
                        <div class="mini-booking-date">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}
                        </div>
                        <div class="mini-booking-info">
                            @if($booking->timeSlot)
                                {{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A') }}
                            @else
                                Time TBD
                            @endif
                        </div>
                        <div class="mini-booking-info">
                            {{ $booking->instructor->name ?? 'Instructor' }}
                        </div>
                        @if($booking->course)
                            <div class="mini-booking-info">
                                {{ $booking->course->title }}
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="schedule-empty-state-compact">
                        No upcoming schedules yet.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
    </div>
    <!-- End Available Schedules View -->
</div>

<!-- Mobile Queue Popup -->
<div id="queuePopup" class="queue-popup" onclick="closeQueuePopupOnBackdrop(event)">
    <div class="queue-popup-content">
        <div class="queue-popup-header">
            <h3>My Schedule</h3>
            <button type="button" class="queue-close-btn" aria-label="Close schedule queue" onclick="toggleQueuePopup()">&times;</button>
        </div>
        <div class="queue-popup-body">
            @if($queueEnabled && $queuedBookings->count() > 0)
            <div class="queue-popup-queued-section">
                <h4 class="queue-popup-queued-title">Queued Schedules ({{ $queuedBookings->count() }})</h4>
                <p class="queue-popup-queued-help">
                    Auto-confirms after {{ $queueDays }} days
                </p>
                @foreach($queuedBookings as $booking)
                    <div class="queue-popup-queued-card">
                        <div class="queue-popup-queued-date">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}
                        </div>
                        <div class="queue-popup-queued-info">
                            {{ $booking->course->title ?? 'Course' }}
                            @if($booking->course)
                                <span class="queue-vehicle-chip">
                                    {{ $booking->course->vehicle_type ?? 'Manual' }}
                                </span>
                            @endif
                        </div>
                        <div class="queue-popup-queued-meta">
                            @if($booking->timeSlot)
                                {{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($booking->timeSlot->end_time)->format('g:i A') }}
                            @elseif($booking->scheduled_at)
                                {{ \Carbon\Carbon::parse($booking->scheduled_at)->format('g:i A') }}
                            @endif
                        </div>
                        <div class="queue-action-row">
                            <form method="POST" action="{{ route('schools.student.bookings.confirm', [$school->slug, $booking->id]) }}" class="queue-action-form">
                                @csrf
                                <button type="button" style="width: 100%; background: #28a745; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; cursor: pointer;" onclick="showConfirmDialog('Confirm this booking now?',()=>this.closest('form').submit())">
                                    Confirm
                                </button>
                            </form>
                            <form method="POST" action="{{ route('schools.student.bookings.removeQueue', [$school->slug, $booking->id]) }}" class="queue-action-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" style="width: 100%; background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; cursor: pointer;" onclick="showConfirmDialog('Cancel this booking?',()=>this.closest('form').submit())">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
            
            <h4 class="queue-popup-upcoming-title">Upcoming This Week</h4>
            
            @forelse($upcomingBookings->take(10) as $booking)
                <div class="mini-booking-card">
                    <div class="mini-booking-date">
                        {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}
                    </div>
                    <div class="mini-booking-info">
                        @if($booking->timeSlot)
                            {{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A') }}
                        @else
                            Time TBD
                        @endif
                    </div>
                    <div class="mini-booking-info">
                        {{ $booking->instructor->name ?? 'Instructor' }}
                    </div>
                    @if($booking->course)
                        <div class="mini-booking-info">
                            {{ $booking->course->title }}
                        </div>
                    @endif
                </div>
            @empty
                <p class="schedule-empty-state-compact">
                    No upcoming schedules yet.
                </p>
            @endforelse
        </div>
    </div>
</div>

<script>
    function switchMainView(viewName) {
        // Toggle buttons
        document.querySelectorAll('.main-toggle-btn').forEach(btn => btn.classList.remove('active'));
        
        // Toggle views
        document.querySelectorAll('.main-view-section').forEach(section => section.classList.remove('active'));
        
        if (viewName === 'my-schedule') {
            document.querySelectorAll('.main-toggle-btn')[0].classList.add('active');
            document.getElementById('my-schedule-view').classList.add('active');
        } else {
            document.querySelectorAll('.main-toggle-btn')[1].classList.add('active');
            document.getElementById('available-schedules-view').classList.add('active');
        }
    }
    
    function toggleDate(header) {
        const bookings = header.nextElementSibling;
        header.classList.toggle('collapsed');
        bookings.classList.toggle('collapsed');
    }
    
    function collapseAll() {
        const headers = document.querySelectorAll('.schedule-date-header');
        const bookings = document.querySelectorAll('.schedule-bookings');
        
        headers.forEach(header => header.classList.add('collapsed'));
        bookings.forEach(booking => booking.classList.add('collapsed'));
    }
    
    function toggleCollapseAll(button) {
        const headers = document.querySelectorAll('.schedule-date-header');
        const bookings = document.querySelectorAll('.schedule-bookings');
        const isCollapsed = button.textContent === 'Expand All';
        
        if (isCollapsed) {
            // Expand all
            headers.forEach(header => header.classList.remove('collapsed'));
            bookings.forEach(booking => booking.classList.remove('collapsed'));
            document.getElementById('collapseBtn1').textContent = 'Collapse All';
            document.getElementById('collapseBtn2').textContent = 'Collapse All';
        } else {
            // Collapse all
            headers.forEach(header => header.classList.add('collapsed'));
            bookings.forEach(booking => booking.classList.add('collapsed'));
            document.getElementById('collapseBtn1').textContent = 'Expand All';
            document.getElementById('collapseBtn2').textContent = 'Expand All';
        }
    }
    
    function toggleCollapseAllMySchedule(checkbox) {
        const headers = document.querySelectorAll('#my-schedule-view .schedule-date-header');
        const bookings = document.querySelectorAll('#my-schedule-view .schedule-bookings');
        
        if (checkbox.checked) {
            headers.forEach(header => header.classList.add('collapsed'));
            bookings.forEach(booking => booking.classList.add('collapsed'));
        } else {
            headers.forEach(header => header.classList.remove('collapsed'));
            bookings.forEach(booking => booking.classList.remove('collapsed'));
        }
    }
    
    function toggleCollapseAllAvailable(checkbox) {
        const headers = document.querySelectorAll('#available-schedules-view .schedule-date-header');
        const bookings = document.querySelectorAll('#available-schedules-view .schedule-bookings');
        
        if (checkbox.checked) {
            headers.forEach(header => header.classList.add('collapsed'));
            bookings.forEach(booking => booking.classList.add('collapsed'));
        } else {
            headers.forEach(header => header.classList.remove('collapsed'));
            bookings.forEach(booking => booking.classList.remove('collapsed'));
        }
    }
    
    function toggleShowPastSchedules(button) {
        const isShowingPast = button.textContent === 'Hide Past Schedules';
        const pastItems = document.querySelectorAll('.schedule-item[data-is-past="true"]');
        
        if (isShowingPast) {
            // Hide past schedules
            pastItems.forEach(item => item.style.display = 'none');
            document.getElementById('showPastBtn1').textContent = 'Show Past Schedules';
            document.getElementById('showPastBtn2').textContent = 'Show Past Schedules';
        } else {
            // Show past schedules
            pastItems.forEach(item => item.style.display = '');
            document.getElementById('showPastBtn1').textContent = 'Hide Past Schedules';
            document.getElementById('showPastBtn2').textContent = 'Hide Past Schedules';
        }
    }
    
    function toggleShowAllDates() {
        // Legacy function - kept for compatibility
        toggleShowPastSchedules();
    }
    
    function toggleShowPastMySchedule(checkbox) {
        const pastItems = document.querySelectorAll('#my-schedule-view .schedule-item[data-is-past="true"]');
        
        if (checkbox.checked) {
            pastItems.forEach(item => item.style.display = '');
        } else {
            pastItems.forEach(item => item.style.display = 'none');
        }
    }
    
    function toggleShowPastAvailable(checkbox) {
        const pastItems = document.querySelectorAll('#available-schedules-view .schedule-item[data-is-past="true"]');
        const showAllCourses = document.getElementById('show-all-courses')?.checked || false;
        
        if (checkbox.checked) {
            pastItems.forEach(item => {
                // Only show past items that have visible slots (or if showing all courses)
                const hasVisibleSlots = item.getAttribute('data-has-visible') === 'true';
                item.style.display = (hasVisibleSlots || showAllCourses) ? '' : 'none';
            });
        } else {
            pastItems.forEach(item => item.style.display = 'none');
        }
    }
    
    function toggleShowAllCourses() {
        const checkbox = document.getElementById('show-all-courses');
        const showAll = checkbox.checked;
        const nonEnrolledSlots = document.querySelectorAll('.available-schedule-card[data-enrolled="false"]');
        
        nonEnrolledSlots.forEach(slot => {
            slot.style.display = showAll ? '' : 'none';
        });
        
        // Show/hide date groups based on whether they have visible slots
        document.querySelectorAll('#available-schedules-view .schedule-item').forEach(dateGroup => {
            const isPast = dateGroup.getAttribute('data-is-past') === 'true';
            const showPast = document.getElementById('show-past-available')?.checked || false;
            
            // If it's a past date and we're not showing past, keep it hidden
            if (isPast && !showPast) {
                dateGroup.style.display = 'none';
                return;
            }
            
            if (showAll) {
                // When showing all courses, show the date group if it's not past (or past is enabled)
                dateGroup.style.display = '';
            } else {
                // When filtering courses, check if any enrolled slots are visible
                const hasVisibleSlots = dateGroup.getAttribute('data-has-visible') === 'true';
                dateGroup.style.display = hasVisibleSlots ? '' : 'none';
            }
        });
    }
    
    function filterByBranchSchedule() {
        const branchVal = document.getElementById('branch-filter-available')?.value || '';
        const cards = document.querySelectorAll('.available-schedule-card');
        cards.forEach(card => {
            if (!branchVal) {
                // Reset to enrolled filter
                const enrolled = card.getAttribute('data-enrolled');
                const showAll = document.getElementById('show-all-courses')?.checked || false;
                card.style.display = (showAll || enrolled === 'true') ? '' : 'none';
            } else {
                const cardBranch = card.getAttribute('data-branch-id') || '';
                const enrolled = card.getAttribute('data-enrolled');
                const showAll = document.getElementById('show-all-courses')?.checked || false;
                const matchesBranch = cardBranch === branchVal;
                const matchesCourse = showAll || enrolled === 'true';
                card.style.display = (matchesBranch && matchesCourse) ? '' : 'none';
            }
        });
    }

    function toggleQueuePopup() {
        const popup = document.getElementById('queuePopup');
        popup.classList.toggle('active');
        if (popup.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
    
    function closeQueuePopupOnBackdrop(event) {
        if (event.target.id === 'queuePopup') {
            toggleQueuePopup();
        }
    }
    
    function bookTimeSlot(timeSlotId) {
        const select = document.getElementById('instructor-' + timeSlotId);
        const selectedValue = select.value;
        
        if (!selectedValue) {
            alert('Please select an instructor option');
            return;
        }
        
        // Get time slot data from data attributes
        const timeSlotCard = select.closest('.available-schedule-card');
        const courseId = timeSlotCard.getAttribute('data-course-id');
        const courseName = timeSlotCard.getAttribute('data-course-name');
        const date = timeSlotCard.getAttribute('data-date');
        const startTime = timeSlotCard.getAttribute('data-start-time');
        const endTime = timeSlotCard.getAttribute('data-end-time');
        const enrollmentRequestId = timeSlotCard.getAttribute('data-enrollment-request-id');
        const availableSpots = parseInt(timeSlotCard.getAttribute('data-available-spots') || '0', 10);
        const instructorName = select.options[select.selectedIndex].text;

        if (availableSpots <= 0) {
            showToast('This slot is already full.', 'error');
            return;
        }

        if (!enrollmentRequestId) {
            showToast('This schedule cannot be booked because no active enrollment is linked to this course.', 'error');
            return;
        }
        
        openBookingModal(timeSlotId, courseId, courseName, selectedValue, instructorName, date, startTime, endTime, enrollmentRequestId);
    }
    
    function bookTimeSlotAuto(timeSlotId) {
        const button = event.target;
        const timeSlotCard = button.closest('.available-schedule-card');
        const courseId = timeSlotCard.getAttribute('data-course-id');
        const courseName = timeSlotCard.getAttribute('data-course-name');
        const date = timeSlotCard.getAttribute('data-date');
        const startTime = timeSlotCard.getAttribute('data-start-time');
        const endTime = timeSlotCard.getAttribute('data-end-time');
        const enrollmentRequestId = timeSlotCard.getAttribute('data-enrollment-request-id');
        const availableSpots = parseInt(timeSlotCard.getAttribute('data-available-spots') || '0', 10);

        if (availableSpots <= 0) {
            showToast('This slot is already full.', 'error');
            return;
        }

        if (!enrollmentRequestId) {
            showToast('This schedule cannot be booked because no active enrollment is linked to this course.', 'error');
            return;
        }
        
        openBookingModal(timeSlotId, courseId, courseName, null, 'Auto-assigned by system', date, startTime, endTime, enrollmentRequestId);
    }
    
    function bookTimeSlotAdmin(timeSlotId) {
        const button = event.target;
        const timeSlotCard = button.closest('.available-schedule-card');
        const courseId = timeSlotCard.getAttribute('data-course-id');
        const courseName = timeSlotCard.getAttribute('data-course-name');
        const date = timeSlotCard.getAttribute('data-date');
        const startTime = timeSlotCard.getAttribute('data-start-time');
        const endTime = timeSlotCard.getAttribute('data-end-time');
        const enrollmentRequestId = timeSlotCard.getAttribute('data-enrollment-request-id');
        const availableSpots = parseInt(timeSlotCard.getAttribute('data-available-spots') || '0', 10);

        if (availableSpots <= 0) {
            showToast('This slot is already full.', 'error');
            return;
        }

        if (!enrollmentRequestId) {
            showToast('This schedule cannot be booked because no active enrollment is linked to this course.', 'error');
            return;
        }
        
        openBookingModal(timeSlotId, courseId, courseName, null, 'Will be assigned by admin', date, startTime, endTime, enrollmentRequestId);
    }
    
    function updateBookButton(timeSlotId) {
        const select = document.getElementById('instructor-' + timeSlotId);
        const button = document.getElementById('book-btn-' + timeSlotId);
        const timeSlotCard = select.closest('.available-schedule-card');
        const availableSpots = parseInt(timeSlotCard?.getAttribute('data-available-spots') || '0', 10);
        
        if (select.value && availableSpots > 0) {
            button.disabled = false;
            button.classList.remove('book-now-btn-disabled');
        } else {
            button.disabled = true;
            button.classList.add('book-now-btn-disabled');
        }
    }
</script>

<!-- Booking Modal -->
<div id="bookingModal" class="booking-modal">
    <div class="booking-modal-overlay" onclick="closeBookingModal()"></div>
    <div class="booking-modal-content">
        <div class="booking-modal-header">
            <h3>Add a Schedule Request</h3>
            <button type="button" class="modal-close-btn" aria-label="Close schedule request modal" onclick="closeBookingModal()">&times;</button>
        </div>
        <div class="booking-modal-body">
            <form id="bookingForm" method="POST" action="{{ route('schools.student.bookings.store', $school->slug) }}">
                @csrf
                <input type="hidden" name="student_id" value="{{ Auth::guard('student')->id() }}">
                <input type="hidden" name="time_slot_id" id="modal_time_slot_id">
                <input type="hidden" name="course_id" id="modal_course_id">
                <input type="hidden" name="enrollment_request_id" id="modal_enrollment_request_id">
                <input type="hidden" name="instructor_id" id="modal_instructor_id">

                <div class="form-group">
                    <label class="form-label">Course</label>
                    <input type="text" class="form-control" id="modal_course_name" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Instructor</label>
                    <input type="text" class="form-control" id="modal_instructor_name" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Date & Time</label>
                    <input type="text" class="form-control" id="modal_datetime" readonly>
                    <input type="hidden" id="scheduled_at" name="scheduled_at">
                </div>

                <div class="form-group">
                    <label class="form-label" for="notes">Additional Notes (Optional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                              placeholder="Add optional notes for your instructor or scheduler..."></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeBookingModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Add Schedule Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.booking-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
}

.booking-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.booking-modal-content {
    position: relative;
    background: white;
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.booking-modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: {{ $primaryColor }};
    color: white;
}

.booking-modal-header h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    line-height: 1.2;
}

.modal-close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 2rem;
    cursor: pointer;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: background 0.2s;
    line-height: 1;
    padding: 0;
    flex-shrink: 0;
}

.modal-close-btn:hover {
    background: rgba(255, 255, 255, 0.1);
}

.modal-close-btn:focus-visible {
    outline: 2px solid #ffffff;
    outline-offset: 2px;
    border-radius: 6px;
}

.dismiss-alert-btn:focus-visible {
    outline: 2px solid currentColor;
    outline-offset: 2px;
    border-radius: 6px;
}

.notification-dismiss-btn {
    margin-left: 12px;
    color: inherit;
}

.confirm-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 10001;
    align-items: center;
    justify-content: center;
}

.confirm-modal-card {
    background: white;
    padding: 24px;
    border-radius: 8px;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.confirm-modal-title {
    margin: 0 0 16px 0;
    font-size: 1.1rem;
    color: #333;
}

.confirm-modal-message {
    margin: 0 0 20px 0;
    color: #666;
}

.confirm-modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.confirm-modal-btn {
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
}

.confirm-modal-btn-cancel {
    border: 1px solid #ddd;
    background: white;
}

.confirm-modal-btn-confirm {
    border: none;
    background: #0d6efd;
    color: white;
}

.booking-modal-body {
    padding: 24px;
    max-height: calc(90vh - 80px);
    overflow-y: auto;
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.booking-modal-body::-webkit-scrollbar {
    display: none;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #374151;
    font-size: 1rem;
    line-height: 1.5;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.2s;
    line-height: 1.5;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: {{ $primaryColor }};
    box-shadow: 0 0 0 3px {{ $primaryColor }}20;
}

.form-control:read-only {
    background: #f9fafb;
    cursor: not-allowed;
}

.required {
    color: #ef4444;
}

.modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.modal-actions .btn {
    flex: 1;
    padding: 12px 20px;
    font-size: 1rem;
    font-weight: 500;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    line-height: 1.5;
}

.btn-primary {
    background: {{ $primaryColor }};
    color: white;
}

.btn-primary:hover {
    background: {{ $primaryColor }}dd;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px {{ $primaryColor }}40;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

@media (max-width: 768px) {
    .booking-modal-content {
        width: 90%;
        max-width: 400px;
        max-height: 85vh;
    }
    
    .booking-modal-header {
        padding: 10px 12px;
    }
    
    .booking-modal-header h3 {
        font-size: 0.95rem;
        line-height: 1.3;
    }
    
    .modal-close-btn {
        width: 24px;
        height: 24px;
        font-size: 1.3rem;
    }
    
    .booking-modal-body {
        padding: 12px;
        max-height: calc(85vh - 60px);
    }
    
    .form-group {
        margin-bottom: 10px;
    }
    
    .form-label {
        font-size: 0.75rem;
        margin-bottom: 4px;
        line-height: 1.3;
    }
    
    .form-control {
        padding: 6px 8px;
        font-size: 0.8rem;
        line-height: 1.4;
    }
    
    textarea.form-control {
        padding: 6px 8px;
        font-size: 0.8rem;
        min-height: 50px;
        line-height: 1.4;
    }
    
    .modal-actions {
        margin-top: 12px;
        padding-top: 10px;
        gap: 6px;
        flex-direction: column;
    }
    
    .modal-actions .btn {
        padding: 8px 12px;
        font-size: 0.8rem;
        width: 100%;
        line-height: 1.4;
    }
    
    .modal-actions .btn i {
        font-size: 0.75rem;
    }
}

@media (max-width: 480px) {
    .booking-modal-content {
        width: 95%;
        max-width: 340px;
        max-height: 90vh;
        border-radius: 8px;
    }
    
    .booking-modal-header {
        padding: 8px 10px;
    }
    
    .booking-modal-header h3 {
        font-size: 0.85rem;
        line-height: 1.2;
    }
    
    .modal-close-btn {
        width: 22px;
        height: 22px;
        font-size: 1.2rem;
    }
    
    .booking-modal-body {
        padding: 10px;
        max-height: calc(90vh - 50px);
    }
    
    .form-group {
        margin-bottom: 8px;
    }
    
    .form-label {
        font-size: 0.7rem;
        margin-bottom: 3px;
        line-height: 1.2;
    }
    
    .form-control {
        padding: 5px 7px;
        font-size: 0.75rem;
        line-height: 1.3;
    }
    
    textarea.form-control {
        padding: 5px 7px;
        font-size: 0.75rem;
        min-height: 45px;
        line-height: 1.3;
    }
    
    .modal-actions {
        margin-top: 10px;
        padding-top: 8px;
        gap: 5px;
    }
    
    .modal-actions .btn {
        padding: 7px 10px;
        font-size: 0.75rem;
        line-height: 1.3;
    }
    
    .modal-actions .btn i {
        font-size: 0.7rem;
    }
}
</style>

<script>
function openBookingModal(timeSlotId, courseId, courseName, instructorId, instructorName, date, startTime, endTime, enrollmentRequestId) {
    if (!enrollmentRequestId) {
        showToast('Missing enrollment linkage for this schedule.', 'error');
        return;
    }

    // Set hidden fields
    document.getElementById('modal_time_slot_id').value = timeSlotId;
    document.getElementById('modal_course_id').value = courseId;
    document.getElementById('modal_enrollment_request_id').value = enrollmentRequestId;
    document.getElementById('modal_instructor_id').value = instructorId || '';
    
    // Set display fields
    document.getElementById('modal_course_name').value = courseName;
    document.getElementById('modal_instructor_name').value = instructorName || 'To be assigned';
    document.getElementById('modal_datetime').value = `${date} at ${startTime} - ${endTime}`;
    
    // Format scheduled_at correctly as Y-m-d H:i:s format
    // startTime is in HH:mm format, so we add :00 for seconds
    const scheduledDateTime = `${date} ${startTime}:00`;
    document.getElementById('scheduled_at').value = scheduledDateTime;
    
    // Show modal
    document.getElementById('bookingModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeBookingModal() {
    document.getElementById('bookingModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('bookingForm').reset();
}

// Handle form submission
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        return response.json().then(data => ({ ok: response.ok, data }));
    })
    .then(({ ok, data }) => {
        if (ok && data.success) {
            closeBookingModal();
            showToast(data.message || 'Schedule request added to your queue.', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            let errorMsg = data.message || 'Could not create your schedule request. Please try again.';
            if (data.errors) {
                const firstError = Object.values(data.errors)[0];
                if (Array.isArray(firstError) && firstError.length) errorMsg = firstError[0];
            }
            showToast(errorMsg, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Add Schedule Request';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Something went wrong while submitting your schedule request. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Add Schedule Request';
    });
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('bookingModal').style.display === 'flex') {
        closeBookingModal();
    }
    if (e.key === 'Escape' && document.getElementById('confirmModal')) {
        closeConfirmModal();
    }
});

// Auto-close alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const successAlert = document.getElementById('success-alert');
    const errorAlert = document.getElementById('error-alert');
    
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.opacity = '0';
            successAlert.style.transition = 'opacity 0.5s';
            setTimeout(() => successAlert.remove(), 500);
        }, 5000);
    }
    
    if (errorAlert) {
        setTimeout(() => {
            errorAlert.style.opacity = '0';
            errorAlert.style.transition = 'opacity 0.5s';
            setTimeout(() => errorAlert.remove(), 500);
        }, 5000);
    }
});



// Custom confirmation modal
var confirmCallback = null;

function showConfirmDialog(message, onConfirm, onCancel = null) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('confirmModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'confirmModal';
        modal.className = 'confirm-modal';
        
        modal.innerHTML = `
            <div class="confirm-modal-card">
                <h3 class="confirm-modal-title">Confirm Action</h3>
                <p id="confirmMessage" class="confirm-modal-message"></p>
                <div class="confirm-modal-actions">
                    <button onclick="closeConfirmModal(false)" class="confirm-modal-btn confirm-modal-btn-cancel">Cancel</button>
                    <button onclick="closeConfirmModal(true)" class="confirm-modal-btn confirm-modal-btn-confirm">Confirm</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }
    
    document.getElementById('confirmMessage').textContent = message;
    modal.style.display = 'flex';
    confirmCallback = { onConfirm, onCancel };
}

function closeConfirmModal(confirmed = false) {
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.style.display = 'none';
        if (confirmed && confirmCallback && confirmCallback.onConfirm) {
            confirmCallback.onConfirm();
        } else if (!confirmed && confirmCallback && confirmCallback.onCancel) {
            confirmCallback.onCancel();
        }
        confirmCallback = null;
    }
}
</script>

@endsection

