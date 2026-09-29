@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Manage Schedule')

@section('content')
    @php
        $school = $school ?? $currentSchool ?? null;
        $settings = $school?->schoolSetting;
        $schoolName = $school->name ?? 'Driving School';
        $instructors = $instructors ?? collect();
        $currentFilter = request('type', 'all');
        $primaryColor = $settings->primary_color ?? '#3b82f6';
        $secondaryColor = $settings->secondary_color ?? '#60a5fa';
    @endphp

    @include('school.admin.partials.admin-styles')

    <style>
        /* Custom Button Styles (replacing Bootstrap) */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            display: inline-block;
            text-align: center;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        /* Alert Styles (replacing Bootstrap alerts) */
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            position: relative;
        }

        .alert-success {
            background: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
        }

        .alert-danger {
            background: #fee2e2;
            border: 1px solid #ef4444;
            color: #991b1b;
        }

        .alert .close-btn {
            position: absolute;
            right: 15px;
            top: 15px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: inherit;
            opacity: 0.6;
        }

        .alert .close-btn:hover {
            opacity: 1;
        }

        /* Badge Styles (replacing Bootstrap badges) */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-light {
            background: #f3f4f6;
            color: #374151;
        }

        .badge-success {
            background: #10b981;
            color: white;
        }

        .badge-primary {
            background: #3b82f6;
            color: white;
        }

        .badge-secondary {
            background: #6b7280;
            color: white;
        }

        /* Form Styles (replacing Bootstrap forms) */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color:
                {{ $primaryColor }}
            ;
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        /* Checkbox Styles */
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-label {
            cursor: pointer;
            user-select: none;
        }

        /* Grid System (replacing Bootstrap grid) */
        .row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .col {
            flex: 1;
        }

        .col-half {
            flex: 0 0 calc(50% - 7.5px);
        }

        .col-third {
            flex: 0 0 calc(33.333% - 10px);
        }

        .col-two-thirds {
            flex: 0 0 calc(66.666% - 5px);
        }

        /* Margin/Padding Utilities */
        .mb-3 {
            margin-bottom: 20px;
        }

        .me-2 {
            margin-right: 10px;
        }

        /* Text Utilities */
        .text-muted {
            color: #6b7280;
        }

        .text-center {
            text-align: center;
        }

        .text-danger {
            color: #ef4444;
        }

        .filter-wrap {
            margin-bottom: 20px;
            padding: 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
        }

        .filter-field {
            min-width: 180px;
            flex: 1 1 180px;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
        }

        .filter-note {
            margin-top: 10px;
            color: #6b7280;
            font-size: 0.85rem;
        }

        /* Container Styles */
        .timeslots-container {
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
            border-bottom: 3px solid var(--primary-color);
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .page-subtitle {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .icon-20 {
            width: 20px;
            height: 20px;
        }

        .center-toggle-wrap {
            display: flex;
            justify-content: center;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .chevron-icon {
            margin-left: 10px;
            transition: transform 0.3s;
        }

        .course-badge {
            margin-bottom: 12px;
            padding: 8px 12px;
            background: var(--header-gradient);
            color: white;
            border-radius: 8px;
            display: inline-block;
            font-weight: 500;
            box-shadow: var(--brand-shadow);
        }

        .course-type {
            opacity: 0.9;
            font-size: 0.85em;
            margin-left: 5px;
        }

        .count-admin {
            color: #3b82f6;
        }

        .count-self {
            color: #10b981;
        }

        .mt-12 {
            margin-top: 12px;
        }

        .timeslot-notes {
            margin-top: 12px;
            padding: 10px;
            background: #f9fafb;
            border-radius: 6px;
            font-size: 0.9rem;
            color: #666;
        }

        .mr-8 {
            margin-right: 8px;
        }

        .hidden-form {
            display: none;
        }

        .slot-badge-more {
            background: #6b7280;
            color: white;
        }

        .calendar-legend {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #666;
        }

        .legend-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 3px;
        }

        .legend-indicator-open {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            margin: 0 5px;
        }

        .legend-indicator-assigned {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            margin: 0 5px 0 15px;
        }

        .legend-help {
            margin-top: 8px;
            display: block;
        }

        .info-panel {
            padding: 15px;
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .info-panel-list {
            margin: 10px 0 0 0;
            padding-left: 20px;
            font-size: 0.9rem;
        }

        .info-panel-toggle {
            display: none;
            width: 100%;
            border: 1px solid #bfdbfe;
            background: #ffffff;
            color: #1d4ed8;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 8px 10px;
            margin-top: 10px;
            cursor: pointer;
            text-align: left;
        }

        .no-courses-msg {
            padding: 20px;
            background: #fef2f2;
            border: 1px solid #fca5a5;
            border-radius: 8px;
        }

        .instructors-count {
            color: #888;
        }

        .no-instructors-msg {
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
        }

        .loading-center {
            text-align: center;
            padding: 40px;
        }

        .day-modal-content {
            max-width: 800px;
        }

        .day-modal-title {
            margin: 0;
        }

        .day-modal-date {
            color: rgba(255, 255, 255, 0.9);
            display: block;
            margin-top: 5px;
        }

        .details-wrap {
            padding: 10px;
        }

        .details-section {
            margin-bottom: 20px;
        }

        .details-label {
            color: #666;
            display: block;
            margin-bottom: 8px;
        }

        .details-time {
            font-size: 1.1rem;
        }

        .details-notes {
            background: #f9fafb;
            padding: 12px;
            border-radius: 6px;
            min-height: 40px;
        }

        .day-empty {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .day-empty-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .day-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .day-card {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            background: #f9fafb;
            transition: all 0.2s;
        }

        .day-card-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .day-card-time {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .day-card-meta {
            margin-top: 5px;
            font-size: 0.9rem;
            color: #666;
        }

        .day-counts {
            font-size: 0.85rem;
            color: #999;
        }

        .day-instructors {
            margin-bottom: 8px;
        }

        .day-instructors-label {
            color: #666;
        }

        .day-notes {
            margin-top: 10px;
            padding: 10px;
            background: white;
            border-radius: 4px;
            font-size: 0.9rem;
            color: #666;
        }

        .multi-select-auto {
            height: auto;
        }

        /* View Toggle Styles */
        .view-toggle {
            display: flex;
            gap: 0;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            width: fit-content;
        }

        .view-btn {
            padding: 8px 20px;
            background: transparent;
            color: #4b5563;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .view-btn.active {
            background:
                {{ $primaryColor }}
            ;
            color: white;
        }

        .view-btn:hover:not(.active) {
            background: #e5e7eb;
        }

        /* Standardized Calendar Nav Buttons */
        .calendar-nav .nav-btn {
            padding: 10px 20px;
            background: white;
            color: #374151;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .calendar-nav .nav-btn:hover {
            border-color:
                {{ $primaryColor }}
            ;
            color:
                {{ $primaryColor }}
            ;
            background: rgba(var(--primary-rgb), 0.05);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .view-content {
            display: none;
        }

        .view-content.active {
            display: block;
        }

        /* Filter Dropdown Styles */
        .filter-dropdown {
            position: relative;
            display: inline-block;
            margin-bottom: 30px;
        }

        .filter-dropdown select {
            padding: 12px 40px 12px 16px;
            border: 2px solid
                {{ $primaryColor }}
            ;
            border-radius: 8px;
            background: white;
            color: #333;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            appearance: none;
            min-width: 200px;
            transition: all 0.3s ease;
        }

        .filter-dropdown select:hover {
            border-color:
                {{ $secondaryColor }}
            ;
            box-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.2);
        }

        .filter-dropdown select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        .filter-dropdown::after {
            content: '▼';
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color:
                {{ $primaryColor }}
            ;
            font-size: 12px;
        }

        .filter-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .info-panel {
            padding: 15px;
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .info-panel-list {
            margin: 10px 0 0 0;
            padding-left: 20px;
            font-size: 0.9rem;
        }

        .info-panel-toggle {
            display: none;
            width: 100%;
            border: 1px solid #bfdbfe;
            background: #ffffff;
            color: #1d4ed8;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 8px 10px;
            margin-top: 10px;
            cursor: pointer;
            text-align: left;
        }

        .btn-create {
            padding: 10px 20px;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .nav-tabs {
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 30px;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #666;
            font-weight: 500;
            padding: 12px 24px;
            transition: all 0.3s;
        }

        .nav-tabs .nav-link:hover {
            color:
                {{ $primaryColor }}
            ;
            background: #f9fafb;
        }

        .nav-tabs .nav-link.active {
            color:
                {{ $primaryColor }}
            ;
            border-bottom: 3px solid
                {{ $primaryColor }}
            ;
            background: transparent;
        }

        .timeslot-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .timeslot-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .date-header {
            background: linear-gradient(135deg,
                    {{ $primaryColor }}
                    0%,
                    {{ $secondaryColor }}
                    100%);
            color: white;
            padding: 15px 20px;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s;
        }

        .date-header:hover {
            filter: brightness(0.95);
        }

        .date-header.collapsed .bi-chevron-down {
            transform: rotate(-90deg);
        }

        .timeslot-card .card-body {
            padding: 20px;
            transition: all 0.3s ease;
            max-height: 5000px;
            overflow: hidden;
        }

        .timeslot-card .card-body.collapsed {
            max-height: 0;
            padding: 0 20px;
            overflow: hidden;
        }

        .timeslot-item {
            background: #f9fafb;
            border-left: 4px solid transparent;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 12px;
            transition: all 0.3s;
        }

        .timeslot-item.type-open {
            border-left-color: #10b981;
        }

        .timeslot-item.type-assigned {
            border-left-color: #3b82f6;
        }

        .timeslot-row {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .timeslot-time {
            flex: 0 0 250px;
        }

        .timeslot-details {
            flex: 1;
        }

        .timeslot-actions {
            flex: 0 0 100px;
            text-align: right;
        }

        .time-badge {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            display: inline-block;
            margin-bottom: 8px;
        }

        .type-badge {
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .instructor-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 8px;
        }

        .instructor-info .info-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .instructor-info .info-item i {
            color: #9ca3af;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .empty-state i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 20px;
        }

        .empty-state p {
            color: #9ca3af;
            font-size: 1.1rem;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .btn-sm-custom {
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 5px;
            transition: all 0.2s;
        }

        .modal-header.modal-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .modal-header.modal-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .modal-header h5 {
            margin: 0;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 10px 12px;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        /* Validation Feedback Styles */
        .is-invalid {
            border-color: #ef4444 !important;
            background-color: #fffafb !important;
        }

        .field-error {
            color: #ef4444;
            font-size: 0.8rem;
            margin-top: 4px;
            font-weight: 500;
            display: block;
        }

        .modal-error-summary {
            background: #fef2f2;
            border: 1px solid #fee2e2;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            color: #991b1b;
        }

        /* Multi-select styling */
        select[multiple] {
            padding: 8px !important;
            background: #f9fafb !important;
            min-height: 180px !important;
            display: block !important;
            width: 100% !important;
            border: 2px solid #d1d5db !important;
        }

        select[multiple] option {
            padding: 8px 10px;
            margin: 2px 0;
            border-radius: 4px;
            cursor: pointer;
        }

        select[multiple] option:hover {
            background: #e0e7ff;
        }

        select[multiple] option:checked {
            background: linear-gradient(135deg,
                    {{ $primaryColor }}
                    0%,
                    {{ $secondaryColor }}
                    100%);
            color: white;
            font-weight: 500;
        }

        .instructor-list {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            max-height: 250px;
            overflow-y: auto;
            background: #f9fafb;
            margin-bottom: 8px;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .instructor-list::-webkit-scrollbar {
            display: none;
        }

        .instructor-checkbox {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 8px;
            transition: all 0.2s;
        }

        .instructor-checkbox:hover {
            background: white;
            border-color:
                {{ $primaryColor }}
            ;
        }

        .instructor-checkbox:has(.checkbox:checked) {
            background: #f0f4ff;
            border-color:
                {{ $primaryColor }}
            ;
        }

        .instructor-checkbox:has(.checkbox:checked) .checkbox-label {
            color:
                {{ $primaryColor }}
            ;
            font-weight: 500;
        }

        /* Custom Modal Styles - Optimized for Performance */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            width: 600px;
            max-width: 92%;
            border-radius: 19px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            will-change: auto;
        }

        .modal-header {
            position: relative;
            background: linear-gradient(135deg,
                    {{ $primaryColor }}
                    0%,
                    {{ $secondaryColor }}
                    100%);
            color: white;
            padding: 32px;
            border-radius: 16px 16px 0 0;
        }

        .modal-header.modal-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .modal-header.modal-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .modal-header h5 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 600;
        }

        .modal-header .btn-close {
            position: absolute;
            right: 24px;
            top: 24px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 24px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .modal-header .btn-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .modal-body {
            padding: 32px;
            max-height: 70vh;
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .modal-body::-webkit-scrollbar {
            display: none;
        }

        .modal-footer {
            padding: 20px 32px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            border-radius: 0 0 16px 16px;
        }

        /* Calendar View Styles */
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding: 15px 20px;
            background: linear-gradient(135deg,
                    {{ $primaryColor }}
                    0%,
                    {{ $secondaryColor }}
                    100%);
            border-radius: 10px;
            color: white;
        }

        .calendar-nav {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .nav-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        /* Calendar Grid Layout (Pixel-Perfect Match) */
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            background: transparent;
        }

        .calendar-day-name {
            padding: 10px;
            text-align: center;
            font-weight: 600;
            color: #64748b;
            font-size: 13px;
            text-transform: capitalize;
        }

        .calendar-day {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            min-height: 140px;
            padding: 12px;
            transition: all 0.2s ease;
            position: relative;
            cursor: pointer;
            display: flex;
            flex-direction: column;
        }

        .calendar-day:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transform: translateY(-2px);
        }

        .calendar-day.other-month {
            background: #f8fafc;
            color: #94a3b8;
        }

        .calendar-day.has-slots {
            border: 2px solid {{ $primaryColor }};
            background: {{ $primaryColor }}08; /* Dynamic primary color with 3% opacity */
        }

        .day-number {
            font-weight: 700;
            font-size: 15px;
            color: #1e293b;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .today-label {
            background: {{ $primaryColor }};
            color: white;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 20px;
            text-transform: uppercase;
        }

        /* Full-Width Slot Bars */
        .day-slots-wrap {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-top: auto;
        }

        .slot-bar {
            width: 100%;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            color: white;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Standard Capacity Colors (Matches Legend Image) */
        .slot-bar.available {
            background: #10b981; /* Green: Has Available Spots */
        }

        .slot-bar.full {
            background: #3b82f6; /* Blue: Fully Assigned */
        }

        .slot-bar.empty {
            background: #94a3b8; /* Muted Slate: No Instructor */
            opacity: 0.8;
            font-style: italic;
        }

        .slot-bar.more-indicator {
            background: #64748b; /* Slate: Standard indicator */
        }

        /* Header & Navigation (Branded Gradient) */
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding: 15px 24px;
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
            border-radius: 12px;
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .month-nav {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .nav-arrow {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            transition: all 0.2s;
        }

        .nav-arrow:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
            border-color: white;
        }

        #currentMonth {
            margin: 0 !important;
            font-weight: 800 !important;
            color: white !important;
            min-width: 200px;
            text-align: center;
            font-size: 1.4rem !important;
            letter-spacing: -0.02em;
        }

        /* Export Buttons */
        .export-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .btn-export {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-export-pdf {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-export-pdf:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .timeslots-container {
                padding: 15px;
                margin: 10px auto;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .page-title {
                font-size: 1.4rem;
            }

            .page-subtitle {
                font-size: 0.85rem;
            }

            .view-toggle {
                width: 100%;
            }

            .view-btn {
                flex: 1;
                padding: 10px 15px;
                font-size: 0.9rem;
                text-align: center;
            }

            .filter-dropdown {
                width: 100%;
            }

            .filter-dropdown select {
                width: 100%;
                min-width: auto;
            }

            .row {
                flex-direction: column;
                gap: 12px;
            }

            .col-half,
            .col-third,
            .col-two-thirds {
                flex: 1 1 100%;
            }

            .timeslot-card {
                margin-bottom: 15px;
            }

            .date-header {
                padding: 12px 15px;
                font-size: 1rem;
            }

            .timeslot-table th,
            .timeslot-table td {
                padding: 10px 8px;
                font-size: 0.85rem;
            }

            .info-panel {
                margin-bottom: 12px;
                padding: 12px;
            }

            .info-panel-list {
                display: none;
                margin-top: 8px;
            }

            .info-panel.expanded .info-panel-list {
                display: block;
            }

            .info-panel-toggle {
                display: block;
            }

            .slot-badge {
                font-size: 0.65rem;
                padding: 3px 5px;
            }

            .info-panel {
                margin-bottom: 12px;
                padding: 12px;
            }

            .info-panel-list {
                display: none;
                margin-top: 8px;
            }

            .info-panel.expanded .info-panel-list {
                display: block;
            }

            .info-panel-toggle {
                display: block;
            }

            .modal-content {
                width: 95%;
                max-width: 95%;
                margin: 10px;
            }

            .modal-header {
                padding: 20px;
            }

            .modal-header h5 {
                font-size: 1.3rem;
            }

            .modal-body {
                padding: 20px;
            }

            .modal-footer {
                flex-direction: column;
                gap: 10px;
            }

            .modal-footer .btn {
                width: 100%;
            }

            /* Calendar View Responsive */
            .calendar-grid {
                gap: 3px;
            }

            .calendar-day {
                min-height: 60px;
                padding: 5px;
            }

            .day-number {
                font-size: 0.9rem;
            }

            .day-slots {
                display: none;
            }

            .calendar-day.has-slots::after {
                content: '•';
                display: block;
                color: #3b82f6;
                font-size: 1.5rem;
                line-height: 1;
            }

            .btn-create {
                width: 100%;
                justify-content: center;
            }

            .export-buttons {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .timeslots-container {
                padding: 10px;
                margin: 5px auto;
            }

            .page-title {
                font-size: 1.2rem;
            }

            .view-btn {
                padding: 8px 10px;
                font-size: 0.8rem;
            }

            .timeslot-table th,
            .timeslot-table td {
                padding: 8px 6px;
                font-size: 0.8rem;
            }

            .btn-sm {
                padding: 4px 8px;
                font-size: 0.75rem;
            }

            .calendar-day {
                min-height: 50px;
                padding: 3px;
            }

            .day-number {
                font-size: 0.8rem;
            }

            .form-control {
                padding: 8px 12px;
                font-size: 0.9rem;
            }

            .form-label {
                font-size: 0.85rem;
            }

            .btn-export {
                padding: 8px 12px;
                font-size: 0.8rem;
            }
        }

        /* View Switcher Logic */
        .view-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .view-content.active {
            display: block !important;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <div class="timeslots-container">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid var(--primary-color);">
            <div>
                <h1 class="page-title">Manage Schedule</h1>
                <p class="page-subtitle">Manage TDC and PDC sessions with verified student capacity.</p>
            </div>
            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 15px;">
                {{-- Primary Actions (Above Tabs) --}}
                <div style="display: flex; gap: 12px;">
                    <button type="button" id="createScheduleBtn" class="btn btn-success" onclick="openCreateModal(); return false;" style="display: flex; align-items: center; gap: 8px; padding: 10px 20px; font-weight: 600; border-radius: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
                        <i class="bi bi-calendar-plus"></i> Create Schedule
                    </button>
                    <a href="{{ school_route('admin.exports.schedules.pdf') }}" class="btn btn-info" style="display: flex; align-items: center; gap: 8px; padding: 10px 20px; font-weight: 600; border-radius: 8px; background: {{ $primaryColor }}; color: white; text-decoration: none; box-shadow: 0 4px 12px {{ $primaryColor }}40;">
                        <i class="bi bi-file-earmark-pdf-fill" style="color: #ff4d4d;"></i> Export PDF
                    </a>
                </div>

                <div class="view-toggle-wrap" style="display: flex; background: #f1f5f9; padding: 4px; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <button type="button" class="view-btn active" data-view-toggle="list" style="padding: 6px 16px; border: none; border-radius: 7px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;">
                        <i class="bi bi-list-ul"></i> List
                    </button>
                    <button type="button" class="view-btn" data-view-toggle="calendar" style="padding: 6px 16px; border: none; border-radius: 7px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; margin-left: 4px;">
                        <i class="bi bi-calendar3"></i> Calendar
                    </button>
                </div>
            </div>
        </div>

        <style>
            .view-btn { background: transparent; color: #64748b; }
            .view-btn.active { background: #ffffff; color: {{ $primaryColor }}; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
        </style>

        <div class="filter-wrap">
            <form method="GET" action="{{ route('schools.admin.schedules', $school) }}" class="filter-form" style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end;">
                <div class="filter-field">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date', $startDate) }}" required>
                </div>
                <div class="filter-field">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date', $endDate) }}" required>
                </div>
                
                {{-- Course Filter --}}
                <div class="filter-field" style="min-width: 200px;">
                    <label for="course_id" class="form-label">Course</label>
                    <select name="course_id" id="course_id" class="form-control">
                        <option value="">-- All Courses --</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Type Filter --}}
                <div class="filter-field" style="min-width: 150px;">
                    <label for="session_type" class="form-label">Session Type</label>
                    <select name="session_type" id="session_type" class="form-control">
                        <option value="">-- All Types --</option>
                        <option value="theoretical" {{ request('session_type') == 'theoretical' ? 'selected' : '' }}>Theoretical (TDC)</option>
                        <option value="practical" {{ request('session_type') == 'practical' ? 'selected' : '' }}>Practical (PDC)</option>
                    </select>
                </div>

                <div class="filter-actions" style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="background: {{ $primaryColor }}; border: none; padding: 10px 25px; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 12px {{ $primaryColor }}40;">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('schools.admin.schedules', $school) }}" class="btn btn-secondary" style="background: #e2e8f0; color: #475569; border: none; padding: 10px 25px; border-radius: 8px; font-weight: 600; text-decoration: none;">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            </form>
            @if($errors->has('start_date') || $errors->has('end_date') || $errors->has('month'))
                <div class="text-danger" style="margin-top: 10px;">
                    {{ $errors->first('start_date') ?: ($errors->first('end_date') ?: $errors->first('month')) }}
                </div>
            @endif
            <div class="filter-note">
                Month navigation applies a full-month date range and overrides custom start/end dates.
            </div>
        </div>

        <!-- Calendar View (Pixel-Perfect Match) -->
        <div id="calendar-view" class="view-content">
            <div class="calendar-header">
                <div class="month-nav">
                    <div class="nav-arrow" onclick="changeMonth(-1)"><i class="bi bi-chevron-left"></i></div>
                    <h3 id="currentMonth">{{ \Carbon\Carbon::parse($startDate)->format('F Y') }}</h3>
                    <div class="nav-arrow" onclick="changeMonth(1)"><i class="bi bi-chevron-right"></i></div>
                </div>
            </div>

            <div class="calendar-grid">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                    <div class="calendar-day-name">{{ $dayName }}</div>
                @endforeach

                @php
                    $startDateObj = \Carbon\Carbon::parse($startDate)->startOfMonth();
                    $endDateObj = $startDateObj->copy()->endOfMonth();
                    $startOfGrid = $startDateObj->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                    $endOfGrid = $endDateObj->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
                    $currentDate = $startOfGrid->copy();
                    $todayDate = \Carbon\Carbon::today()->format('Y-m-d');
                @endphp

                @while($currentDate <= $endOfGrid)
                    @php
                        $dateStr = $currentDate->format('Y-m-d');
                        $isToday = $dateStr === $todayDate;
                        $isOtherMonth = $currentDate->format('m') !== $startDateObj->format('m');
                        // Flexible lookup for different date formats (Y-m-d or Y-m-d 00:00:00)
                        $daySlots = $timeslots->get($dateStr) ?? $timeslots->get($dateStr . ' 00:00:00') ?? collect();
                        $hasSchedule = $daySlots->isNotEmpty();
                    @endphp

                    <div class="calendar-day {{ $isToday ? 'is-today' : '' }} {{ $isOtherMonth ? 'other-month' : '' }} {{ $hasSchedule ? 'has-slots' : '' }}"
                         @if(!$isOtherMonth) onclick="showDayModal('{{ $dateStr }}', '{{ $currentDate->format('F j, Y, l') }}')" @endif>
                        
                        <div class="day-number">
                            {{ $currentDate->format('j') }}
                            @if($isToday)
                                <span class="today-label">Today</span>
                            @endif
                        </div>

                        @if(!$isOtherMonth && $hasSchedule)
                            <div class="day-slots-wrap">
                                @foreach($daySlots->take(3) as $slot)
                                    @php
                                        $instructorCount = $slot->instructors->count();
                                        $availableSpots = $slot->getAvailableStudentSpots();
                                        $isFull = $availableSpots <= 0;
                                        $adminCount = $slot->getAdminAssignedCount();
                                        $selfCount = $slot->getSelfSelectedCount();
                                        $totalAssigned = $adminCount + $selfCount;
                                        $maxCapacity = ($slot->session_type ?? '') === 'theoretical' ? $slot->max_students : $slot->max_instructors;
                                        
                                        // Determine status class
                                        $statusClass = 'available';
                                        if ($instructorCount === 0) {
                                            $statusClass = 'empty';
                                        } elseif ($isFull) {
                                            $statusClass = 'full';
                                        }
                                    @endphp
                                    <div class="slot-bar {{ $statusClass }}">
                                        {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} 
                                        @if($instructorCount > 0)
                                            ({{ $totalAssigned }}/{{ $maxCapacity }})
                                        @endif
                                    </div>
                                @endforeach
                                @if($daySlots->count() > 3)
                                    <div class="slot-bar more-indicator">
                                        +{{ $daySlots->count() - 3 }} more
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                    @php $currentDate->addDay(); @endphp
                @endwhile
            </div>

            <!-- Legend (Standard Colors) -->
            <div class="calendar-legend-box" style="margin-top: 20px; padding: 20px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 8px;">
                    <span style="font-weight: 800; color: #1e293b; font-size: 1.1rem;">Legend:</span>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 16px; height: 16px; background: #10b981; border-radius: 4px;"></span>
                        <span style="font-weight: 500; color: #64748b;">Has Available Spots</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 16px; height: 16px; background: #3b82f6; border-radius: 4px;"></span>
                        <span style="font-weight: 500; color: #64748b;">Fully Assigned</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 16px; height: 16px; background: #94a3b8; border-radius: 4px;"></span>
                        <span style="font-weight: 500; color: #64748b;">No Instructor (No Room)</span>
                    </div>
                </div>
                <p style="margin: 0; font-size: 0.9rem; color: #94a3b8; font-weight: 500;">Click on a day to view details</p>
            </div>
        </div>
        <div id="list-view" class="view-content active" style="background: #ffffff; padding: 0; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; margin-top: 20px;">
            @if($timeslots->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-calendar-x"></i>
                    <p>No schedules found. Create your first schedule above!</p>
                </div>
            @else
                @foreach($timeslots as $date => $dateTimeslots)
                    @php
                        $isToday = \Carbon\Carbon::parse($date)->isToday();
                        $tdcCount = $dateTimeslots->filter(fn($t) => $t->resolved_session_type === 'theoretical')->count();
                        $pdcCount = $dateTimeslots->count() - $tdcCount;
                    @endphp

                    <div class="agenda-day-group {{ $isToday ? 'is-today' : 'collapsed' }}" id="day-{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}">
                        {{-- Branded Day Header --}}
                        <div class="agenda-header" onclick="toggleAgendaDay(this)">
                            <div class="header-main" style="display: flex; align-items: center;">
                                <span class="full-date" style="font-weight: 700; font-size: 1.05rem;">
                                    {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}
                                </span>
                                <span class="day-badge" style="background: {{ $isToday ? 'rgba(255,255,255,0.2)' : '#ffffff' }}; 
                                                            color: {{ $isToday ? '#ffffff' : '#64748b' }}; 
                                                            padding: 2px 14px; border-radius: 15px; border: 1px solid {{ $isToday ? 'rgba(255,255,255,0.4)' : '#e2e8f0' }}; 
                                                            font-size: 0.8rem; font-weight: 700; margin-left: 15px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    {{ \Carbon\Carbon::parse($date)->format('l') }}
                                </span>
                            </div>

                            <div class="header-summary" style="display: flex; align-items: center; gap: 20px;">
                                <div class="summary-pills" style="font-weight: 600; font-size: 0.85rem; opacity: 0.9;">
                                    <span>{{ $tdcCount }} TDC</span>
                                    <span style="margin-left: 12px;">{{ $pdcCount }} PDC</span>
                                </div>
                                <i class="bi bi-chevron-down chevron" style="font-size: 1.1rem; opacity: 0.7;"></i>
                            </div>
                        </div>

                        {{-- Day Content (Table) --}}
                        <div class="agenda-content">
                            <div class="table-responsive">
                                <table class="schedule-table">
                                    <thead>
                                        <tr>
                                            <th style="font-size: 0.8rem;">Time</th>
                                            <th style="font-size: 0.8rem;">Course / Session</th>
                                            <th style="font-size: 0.8rem;">Instructors</th>
                                            <th style="font-size: 0.8rem;">Capacity</th>
                                            <th style="text-align: center; font-size: 0.8rem;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dateTimeslots as $timeslot)
                                            @php
                                                $totalCount = $timeslot->instructors->count();
                                                $availableSpots = $timeslot->getAvailableStudentSpots();
                                                $sessionType = $timeslot->resolved_session_type;
                                                $isFull = $availableSpots <= 0;
                                            @endphp
                                            <tr class="timeslot-table-row timeslot-item" 
                                                data-slot-id="{{ $timeslot->id }}"
                                                data-course-id="{{ $timeslot->course_id }}"
                                                data-start-time="{{ $timeslot->start_time }}"
                                                data-end-time="{{ $timeslot->end_time }}"
                                                data-session-type="{{ $sessionType }}"
                                                data-max-instructors="{{ $timeslot->max_instructors }}"
                                                data-max-students="{{ $timeslot->max_students }}"
                                                data-notes="{{ $timeslot->notes ?? '' }}">

                                                <td style="width: 170px;">
                                                    <div style="font-size: 1rem; font-weight: 700; color: #1e293b;">
                                                        {{ \Carbon\Carbon::parse($timeslot->start_time)->format('g:i A') }}
                                                        <small style="display: block; font-weight: 500; color: #64748b; font-size: 0.75rem;">to {{ \Carbon\Carbon::parse($timeslot->end_time)->format('g:i A') }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div style="font-weight: 600; color: #334155; font-size: 0.95rem;">{{ $timeslot->course->title ?? 'General' }}</div>
                                                    <div style="margin-top: 4px;">
                                                        <span style="font-size: 0.7rem; font-weight: 800; padding: 2px 8px; border-radius: 4px; background: {{ $sessionType === 'practical' ? '#eff6ff' : '#f0fdf4' }}; color: {{ $sessionType === 'practical' ? '#2563eb' : '#166534' }}; border: 1px solid {{ $sessionType === 'practical' ? '#bfdbfe' : '#bbf7d0' }}; text-transform: uppercase;">
                                                            {{ $sessionType === 'practical' ? 'PDC' : 'TDC' }}
                                                        </span>
                                                        @php
                                                            $statusColors = [
                                                                'open' => ['bg' => '#f0f9ff', 'color' => '#0369a1', 'border' => '#bae6fd'],
                                                                'closed' => ['bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#e2e8f0'],
                                                                'cancelled' => ['bg' => '#fee2e2', 'color' => '#ef4444', 'border' => '#fecaca'],
                                                                'completed' => ['bg' => '#f0fdf4', 'color' => '#166534', 'border' => '#bbf7d0'],
                                                            ];
                                                            $currStatus = strtolower($timeslot->status);
                                                            $colors = $statusColors[$currStatus] ?? ['bg' => '#f8fafc', 'color' => '#64748b', 'border' => '#e2e8f0'];
                                                        @endphp
                                                        <span style="font-size: 0.7rem; color: {{ $colors['color'] }}; background: {{ $colors['bg'] }}; border: 1px solid {{ $colors['border'] }}; padding: 2px 8px; border-radius: 4px; font-weight: 800; margin-left: 8px; text-transform: uppercase;">
                                                            {{ $timeslot->status }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                                        @php
                                                            $instructorCount = $timeslot->instructors->count();
                                                            $firstInstructor = $timeslot->instructors->first();
                                                        @endphp
                                                        
                                                        @if($instructorCount > 0)
                                                            @php $isSelf = ($firstInstructor->pivot->assignment_type ?? '') === 'self_selected'; @endphp
                                                            <span class="badge {{ $isSelf ? 'badge-success' : 'badge-primary' }}" style="font-size: 0.75rem; padding: 2px 8px; border-radius: 5px; font-weight: 500;">
                                                                {{ $isSelf ? '[S] ' : '[A] ' }}{{ $firstInstructor->name }}
                                                            </span>
                                                            @if($instructorCount > 1)
                                                                <span style="font-size: 0.75rem; color: #64748b; font-weight: 600; align-self: center; margin-left: 4px;">
                                                                    +{{ $instructorCount - 1 }} more
                                                                </span>
                                                            @endif
                                                        @else
                                                            <span style="color: #cbd5e1; font-style: italic; font-size: 0.85rem;">Unassigned</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($timeslot->instructors->isEmpty())
                                                        <div style="font-size: 0.85rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">
                                                            <i class="bi bi-exclamation-circle" style="font-size: 0.8rem;"></i> No Room
                                                        </div>
                                                        <div style="font-size: 0.7rem; color: #cbd5e1; font-weight: 500;">Assign instructor to open</div>
                                                    @else
                                                        <div style="font-size: 0.85rem; font-weight: 700; color: {{ $isFull ? '#64748b' : '#059669' }};">
                                                            {{ $isFull ? 'FULL' : $availableSpots . ' SPOTS' }}
                                                        </div>
                                                        <div style="font-size: 0.75rem; color: #94a3b8;">Max: {{ $timeslot->max_students }} Students</div>
                                                    @endif
                                                </td>
                                                <td style="text-align: center; width: 180px;">
                                                    <div style="display: flex; gap: 8px; justify-content: center;">
                                                        <button type="button" onclick="showSlotDetails({{ $timeslot->id }})" class="btn btn-sm btn-outline-primary" style="display: flex; align-items: center; gap: 6px; padding: 6px 12px; font-weight: 600; font-size: 0.8rem; border-radius: 6px;">
                                                            <i class="bi bi-eye"></i> View
                                                        </button>
                                                        <button type="button" onclick="confirmDeleteSchedule({{ $timeslot->id }})" class="btn btn-sm btn-outline-danger" style="display: flex; align-items: center; gap: 6px; padding: 6px 12px; font-weight: 600; font-size: 0.8rem; border-radius: 6px;">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                    </div>
                                                    <form id="deleteScheduleForm{{ $timeslot->id }}" method="POST" action="{{ route('schools.admin.schedules.delete', [$school, $timeslot->id]) }}" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <style>
            .agenda-day-group { transition: all 0.3s ease; }
            .agenda-header { 
                display: flex; 
                justify-content: space-between; 
                align-items: center; 
                cursor: pointer; 
                transition: all 0.2s; 
                padding: 14px 24px;
                border-bottom: 1px solid #e2e8f0;
                border-left: 6px solid transparent;
            }
            .agenda-header:hover { filter: brightness(0.98); }
            
            /* Not Today - Collapsed */
            .agenda-day-group:not(.is-today).collapsed .agenda-header {
                background: #f1f5f9;
                color: #475569;
                border-left-color: #cbd5e1;
            }
            
            /* Not Today - Expanded */
            .agenda-day-group:not(.is-today):not(.collapsed) .agenda-header {
                background: #f8fafc;
                color: #1e293b;
                border-left-color: {{ $primaryColor }};
            }
            
            /* Today - Always Branded */
            .agenda-day-group.is-today .agenda-header {
                background: {{ $primaryColor }};
                color: #ffffff;
                border-left-color: rgba(255,255,255,0.4);
                border-bottom-color: rgba(255,255,255,0.1);
            }
            
            .agenda-day-group.collapsed .agenda-content { display: none; }
            .chevron { transition: transform 0.3s; }
            .collapsed .chevron { transform: rotate(-90deg); }
            .agenda-content { background: #ffffff; }
            .schedule-table { width: 100%; border-collapse: collapse; }
            .schedule-table th { background: #f8fafc; padding: 8px 20px; text-align: left; font-size: 0.65rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0; }
            .schedule-table td { padding: 8px 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
            .timeslot-table-row:hover { background: #fdfdfd; }
        </style>

        <script>
            function toggleAgendaDay(header) {
                const group = header.parentElement;
                group.classList.toggle('collapsed');
            }
        </script>
        <!-- End Agenda View -->
        <!-- End Calendar View -->
    </div>

    <!-- Create Schedule Modal (Unified) -->
    <div class="modal" id="createModal">
        <div class="modal-content">
            <div class="modal-header modal-success">
                <h5><i class="bi bi-calendar-plus"></i> Create Schedule</h5>
                <button type="button" class="btn-close" onclick="closeCreateModal()">&times;</button>
            </div>
            <form id="createScheduleForm" method="POST" action="{{ route('schools.admin.schedules.create', $school) }}">
                @csrf
                <div class="modal-body">
                    <div id="createScheduleErrors" class="alert alert-danger" style="display: none; margin-bottom: 20px;"></div>
                    @if($errors->any() && (old('course_id') || old('date') || old('start_time')))
                        <div class="modal-error-summary">
                            <strong>Please fix the errors below:</strong>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="info-panel">
                        <strong>How it works:</strong>
                        <button type="button" class="info-panel-toggle" onclick="toggleInfoPanel(this)">Show details</button>
                        <ul class="info-panel-list">
                            <li>Select the course for this schedule</li>
                            <li>Set the max capacity for this schedule</li>
                            <li>Optionally pre-assign instructors (they'll be marked as "Admin Assigned")</li>
                            <li>Remaining spots will be available for instructor self-selection</li>
                        </ul>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-book"></i> Course <span class="text-danger">*</span>
                        </label>
                        @if(isset($courses) && $courses->isNotEmpty())
                            <select name="course_id" id="createCourseSelect" class="form-control {{ $errors->has('course_id') ? 'is-invalid' : '' }}" required>
                                <option value="">-- Select a Course --</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" data-type="{{ $course->course_type }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->title }} ({{ strtoupper($course->course_type) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id') <p class="field-error">{{ $message }}</p> @enderror
                        @else
                            <p class="text-muted text-center no-courses-msg">
                                <i class="bi bi-exclamation-triangle"></i> No active courses available. Please create a course first.
                            </p>
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="createSessionType">
                            <i class="bi bi-diagram-3"></i> Session Type
                            <span class="text-danger">*</span>
                        </label>
                        <select name="session_type" id="createSessionType" class="form-control">
                            <option value="theoretical">Theoretical (TDC)</option>
                            <option value="practical">Practical (PDC)</option>
                        </select>
                        <input type="hidden" name="session_type" id="createSessionTypeHidden">
                        <small class="text-muted">Determines seating vs 1-on-1 logic.</small>
                    </div>

                    <div class="form-group" id="batchSchedulingWrapper" style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <label class="checkbox-wrapper" style="margin-bottom: 6px; cursor: pointer;">
                            <input type="checkbox" id="toggleBatchScheduling" class="checkbox" onchange="toggleBatchMode(this.checked)">
                            <span class="checkbox-label" style="font-weight: 600; color: #1e293b;">
                                <i class="bi bi-calendar2-range"></i> TDC Batch Scheduling (Sequential multi-day lessons)
                            </span>
                        </label>
                        <small class="text-muted" id="batchSchedulingHint" style="display: block;">
                            Creates sequential schedule days (e.g. Day 1, Day 2) under one linked batch so students take the full sequence.
                        </small>
                    </div>

                    <div id="singleDateGroup" class="form-group">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="createScheduleDate" class="form-control {{ $errors->has('date') ? 'is-invalid' : '' }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" value="{{ old('date') }}">
                        <small class="text-muted" style="display:block;margin-top:4px;">Schedules must be for a future date (tomorrow onwards) — today is not allowed.</small>
                        <p class="field-error" id="createScheduleDateFeedback" style="display:none;"></p>
                        @error('date') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div id="batchDatesGroup" class="form-group" style="display: none; background: #eff6ff; padding: 14px; border-radius: 8px; border: 1px solid #bfdbfe;">
                        <label class="form-label" style="font-weight: 600; color: #1e40af;">
                            <i class="bi bi-calendar-week"></i> Batch Lesson Prefix
                        </label>
                        <input type="text" name="batch_lesson_prefix" class="form-control" placeholder="e.g. TDC Batch A - Day" style="margin-bottom: 12px;">

                        <label class="form-label" style="font-weight: 600; color: #1e40af;">
                            <i class="bi bi-calendar-plus"></i> Sequential Batch Dates
                        </label>
                        <div id="batchDatesContainer" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 10px;">
                            <div class="batch-date-row" style="display: flex; gap: 8px; align-items: center;">
                                <span style="font-weight: 600; font-size: 0.85rem; color: #1e40af; min-width: 55px;">Day 1:</span>
                                <input type="date" name="batch_dates[]" class="form-control batch-date-input" min="{{ date('Y-m-d', strtotime('+1 day')) }}" style="flex: 1;">
                            </div>
                            <div class="batch-date-row" style="display: flex; gap: 8px; align-items: center;">
                                <span style="font-weight: 600; font-size: 0.85rem; color: #1e40af; min-width: 55px;">Day 2:</span>
                                <input type="date" name="batch_dates[]" class="form-control batch-date-input" min="{{ date('Y-m-d', strtotime('+1 day')) }}" style="flex: 1;">
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addBatchDateField()" style="padding: 5px 12px; font-size: 0.8rem;">
                            + Add Another Day
                        </button>
                    </div>

                    <script>
                        var CREATE_TOMORROW_MIN = "{{ date('Y-m-d', strtotime('+1 day')) }}";
                        function isTheoreticalCourseSelected() {
                            var sel = document.getElementById('createCourseSelect');
                            if (!sel || !sel.value) return false;
                            var opt = sel.options[sel.selectedIndex];
                            var t = (opt.getAttribute('data-type') || '').toLowerCase();
                            return t === 'theoretical';
                        }
                        function updateBatchAvailability() {
                            var checkbox = document.getElementById('toggleBatchScheduling');
                            var hint = document.getElementById('batchSchedulingHint');
                            if (!checkbox) return;
                            var allowed = isTheoreticalCourseSelected();
                            if (!allowed) {
                                checkbox.checked = false;
                                toggleBatchMode(false);
                                checkbox.disabled = true;
                                if (hint) hint.textContent = 'TDC Batch Scheduling is only available for Theoretical (TDC) courses. Select a Theoretical course to enable it.';
                            } else {
                                checkbox.disabled = false;
                                if (hint) hint.textContent = 'Creates sequential schedule days (e.g. Day 1, Day 2) under one linked batch so students take the full sequence.';
                            }
                        }
                        function validateScheduleDate(input) {
                            var fb = document.getElementById('createScheduleDateFeedback');
                            if (!input || !input.value) { if (fb) fb.style.display = 'none'; return true; }
                            var today = new Date(); today.setHours(0,0,0,0);
                            var picked = new Date(input.value + 'T00:00:00');
                            if (isNaN(picked.getTime()) || picked <= today) {
                                if (fb) {
                                    fb.textContent = 'Invalid date: schedules cannot be created for today or past dates. Please pick a future date (tomorrow onwards).';
                                    fb.style.display = 'block';
                                }
                                input.setCustomValidity('Schedules cannot be created for today or past dates.');
                                return false;
                            }
                            if (fb) fb.style.display = 'none';
                            input.setCustomValidity('');
                            return true;
                        }
                        document.addEventListener('DOMContentLoaded', function() {
                            var sel = document.getElementById('createCourseSelect');
                            if (sel) sel.addEventListener('change', updateBatchAvailability);
                            var dateInput = document.getElementById('createScheduleDate');
                            if (dateInput) {
                                dateInput.setAttribute('min', CREATE_TOMORROW_MIN);
                                dateInput.addEventListener('change', function() { validateScheduleDate(dateInput); });
                            }
                            updateBatchAvailability();
                        });
                        function toggleBatchMode(enabled) {
                            const singleGroup = document.getElementById('singleDateGroup');
                            const batchGroup = document.getElementById('batchDatesGroup');
                            const dateInput = document.getElementById('createScheduleDate');
                            const batchDateInputs = document.querySelectorAll('input[name="batch_dates[]"]');

                            if (enabled) {
                                singleGroup.style.display = 'none';
                                batchGroup.style.display = 'block';
                                if (dateInput) dateInput.required = false;
                                batchDateInputs.forEach(i => i.required = true);
                            } else {
                                singleGroup.style.display = 'block';
                                batchGroup.style.display = 'none';
                                if (dateInput) dateInput.required = true;
                                batchDateInputs.forEach(i => i.required = false);
                            }
                        }

                        function addBatchDateField() {
                            const container = document.getElementById('batchDatesContainer');
                            const count = container.querySelectorAll('.batch-date-row').length + 1;
                            const row = document.createElement('div');
                            row.className = 'batch-date-row';
                            row.style.cssText = 'display: flex; gap: 8px; align-items: center;';
                            row.innerHTML = `
                                <span style="font-weight: 600; font-size: 0.85rem; color: #1e40af; min-width: 55px;">Day ${count}:</span>
                                <input type="date" name="batch_dates[]" class="form-control batch-date-input" min="${CREATE_TOMORROW_MIN}" style="flex: 1;" required>
                                <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: #ef4444; font-size: 1.1rem; cursor: pointer;">&times;</button>
                            `;
                            container.appendChild(row);
                        }
                    </script>

                    @php
                        $timeOptions = [];
                        $startTime = \Carbon\Carbon::createFromTime(6, 0);
                        $endTime = \Carbon\Carbon::createFromTime(18, 0);
                        while ($startTime <= $endTime) {
                            $timeOptions[$startTime->format('H:i')] = $startTime->format('g:i A');
                            $startTime->addMinutes(30);
                        }
                    @endphp

                    <div class="row">
                        <div class="col-half">
                            <label class="form-label">Start Time</label>
                            <div class="custom-select-container" id="startTimeContainer">
                                <div class="custom-select-trigger" onclick="toggleCustomSelect('startTimeList')">
                                    <span id="startTimeLabel">-- Start --</span>
                                    <i class="bi bi-chevron-down"></i>
                                </div>
                                <div class="custom-select-list" id="startTimeList">
                                    @foreach($timeOptions as $val => $label)
                                        <div class="custom-select-option" data-value="{{ $val }}" onclick="selectCustomTime('start_time', '{{ $val }}', '{{ $label }}', 'startTimeList', 'startTimeLabel')">
                                            {{ $label }}
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="start_time" id="start_time_hidden" required value="{{ old('start_time') }}">
                            </div>
                        </div>
                        <div class="col-half">
                            <label class="form-label">End Time</label>
                            <div class="custom-select-container" id="endTimeContainer">
                                <div class="custom-select-trigger" onclick="toggleCustomSelect('endTimeList')">
                                    <span id="endTimeLabel">-- End --</span>
                                    <i class="bi bi-chevron-down"></i>
                                </div>
                                <div class="custom-select-list" id="endTimeList">
                                    @foreach($timeOptions as $val => $label)
                                        <div class="custom-select-option" data-value="{{ $val }}" onclick="selectCustomTime('end_time', '{{ $val }}', '{{ $label }}', 'endTimeList', 'endTimeLabel')">
                                            {{ $label }}
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="end_time" id="end_time_hidden" required value="{{ old('end_time') }}">
                            </div>
                        </div>
                    </div>

                    <style>
                        .custom-select-container {
                            position: relative;
                            width: 100%;
                        }
                        .custom-select-trigger {
                            padding: 10px 15px;
                            background: white;
                            border: 1px solid #e2e8f0;
                            border-radius: 8px;
                            cursor: pointer;
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            font-size: 0.9rem;
                            color: #1e293b;
                            transition: all 0.2s;
                        }
                        .custom-select-trigger:hover {
                            border-color: {{ $primaryColor }};
                        }
                        .custom-select-list {
                            position: absolute;
                            top: 100%;
                            left: 0;
                            right: 0;
                            background: white;
                            border: 1px solid #e2e8f0;
                            border-radius: 8px;
                            margin-top: 5px;
                            max-height: 180px; /* Limits shown items to ~5-6 */
                            overflow-y: auto;
                            z-index: 1000;
                            display: none;
                            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                        }
                        .custom-select-option {
                            padding: 10px 15px;
                            cursor: pointer;
                            font-size: 0.85rem;
                            color: #475569;
                            transition: all 0.2s;
                        }
                        .custom-select-option:hover {
                            background: #f8fafc;
                            color: {{ $primaryColor }};
                            padding-left: 20px;
                        }
                        .custom-select-list.active {
                            display: block;
                        }
                    </style>

                    <script>
                        function toggleCustomSelect(id) {
                            const list = document.getElementById(id);
                            const allLists = document.querySelectorAll('.custom-select-list');
                            allLists.forEach(l => { if(l.id !== id) l.classList.remove('active'); });
                            list.classList.toggle('active');
                            
                            // Close when clicking outside
                            document.addEventListener('click', function closeSelect(e) {
                                if (!e.target.closest('.custom-select-container')) {
                                    list.classList.remove('active');
                                    document.removeEventListener('click', closeSelect);
                                }
                            });
                        }

                        function selectCustomTime(inputName, val, label, listId, labelId) {
                            document.getElementById(inputName + '_hidden').value = val;
                            document.getElementById(labelId).textContent = label;
                            document.getElementById(listId).classList.remove('active');
                            
                            validateTimeRange();
                        }

                        function validateTimeRange() {
                            const start = document.getElementById('start_time_hidden').value;
                            const end = document.getElementById('end_time_hidden').value;
                            const errorMsg = document.getElementById('timeRangeError');
                            const submitBtn = document.getElementById('submitCreateBtn');
                            
                            if (start && end) {
                                const startTimeParts = start.split(':');
                                const endTimeParts = end.split(':');
                                
                                const startMinutes = parseInt(startTimeParts[0], 10) * 60 + parseInt(startTimeParts[1], 10);
                                const endMinutes = parseInt(endTimeParts[0], 10) * 60 + parseInt(endTimeParts[1], 10);
                                const duration = endMinutes - startMinutes;

                                console.log(`Validating Time Range: ${start} to ${end} | Duration: ${duration} mins`);
                                
                                if (start >= end) {
                                    errorMsg.textContent = 'End time must be after start time.';
                                    errorMsg.style.display = 'block';
                                    document.getElementById('startTimeContainer').querySelector('.custom-select-trigger').style.borderColor = '#ef4444';
                                    document.getElementById('endTimeContainer').querySelector('.custom-select-trigger').style.borderColor = '#ef4444';
                                    if(submitBtn) submitBtn.disabled = true;
                                } else if (duration < 60) {
                                    errorMsg.textContent = 'The session must be at least 1 hour long. (Current: ' + duration + ' mins)';
                                    errorMsg.style.display = 'block';
                                    document.getElementById('startTimeContainer').querySelector('.custom-select-trigger').style.borderColor = '#ef4444';
                                    document.getElementById('endTimeContainer').querySelector('.custom-select-trigger').style.borderColor = '#ef4444';
                                    if(submitBtn) submitBtn.disabled = true;
                                } else if (duration > 480) {
                                    errorMsg.textContent = 'A single session cannot exceed 8 hours per day (' + (duration / 60).toFixed(1) + ' hrs selected). Please split into multiple days using batch scheduling.';
                                    errorMsg.style.display = 'block';
                                    document.getElementById('startTimeContainer').querySelector('.custom-select-trigger').style.borderColor = '#ef4444';
                                    document.getElementById('endTimeContainer').querySelector('.custom-select-trigger').style.borderColor = '#ef4444';
                                    if(submitBtn) submitBtn.disabled = true;
                                } else {
                                    errorMsg.style.display = 'none';
                                    document.getElementById('startTimeContainer').querySelector('.custom-select-trigger').style.borderColor = '#e2e8f0';
                                    document.getElementById('endTimeContainer').querySelector('.custom-select-trigger').style.borderColor = '#e2e8f0';
                                    if(submitBtn) submitBtn.disabled = false;
                                }
                            }
                        }
                    </script>

                    <p id="timeRangeError" style="color: #ef4444; font-size: 0.75rem; margin-top: 8px; display: none; font-weight: 600;"></p>

                    <div class="form-group" id="maxStudentsGroup">
                        <label class="form-label" id="maxStudentsLabel">Student Capacity (Seats)</label>
                        <input type="number" name="max_students" id="createMaxStudents" class="form-control {{ $errors->has('max_students') ? 'is-invalid' : '' }}" min="1" value="{{ old('max_students', 30) }}">
                        <small class="text-muted" id="maxStudentsHelp">Total number of students allowed to book this classroom session.</small>
                        @error('max_students') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group" id="maxInstructorsGroup">
                        <label class="form-label">Max Instructors (Capacity)</label>
                        <input type="number" name="max_instructors" id="createMaxInstructors" class="form-control {{ $errors->has('max_instructors') ? 'is-invalid' : '' }}" min="1" max="10" value="{{ old('max_instructors', 3) }}" required>
                        <small class="text-muted">Maximum number of instructors allowed to join this slot.</small>
                        @error('max_instructors') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-people"></i> Pre-Assign Instructors (Optional)
                            <small class="instructors-count">({{ $instructors->count() }} available)</small>
                        </label>
                        @if($instructors->isEmpty())
                            <p class="text-muted text-center no-instructors-msg">
                                <i class="bi bi-info-circle"></i> No active instructors available.
                            </p>
                        @else
                            <select name="instructor_ids[]" class="form-control" multiple size="6">
                                @foreach($instructors as $instructor)
                                    @php
                                        $accreditedCourseIds = [];
                                        foreach($courses as $course) {
                                            if ($instructor->canTeach($course)) {
                                                $accreditedCourseIds[] = $course->id;
                                            }
                                        }
                                    @endphp
                                    <option value="{{ $instructor->id }}" data-accredited-courses="{{ json_encode($accreditedCourseIds) }}">
                                        {{ $instructor->name }} ({{ $instructor->email }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Hold Ctrl (Windows) or Cmd (Mac) to select multiple instructors. 
                                Leave unselected to make all spots available for self-selection. 
                                Selected instructors will be marked as "Admin Assigned" [A].
                            </small>
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="E.g., Saturday morning rush - need experienced instructors"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <div id="batchModeAlert" style="display: none; width: 100%; text-align: left; padding: 10px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 6px; margin-bottom: 10px; font-size: 0.9rem; color: #856404;">
                        <i class="bi bi-info-circle-fill"></i> <strong>Batch Mode Active:</strong> Since you have selected multiple instructors for a PDC session, the system will create <strong>one separate slot for each instructor</strong>.
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Cancel</button>
                    <button type="submit" id="submitCreateBtn" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Create Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Schedule Details Modal -->
    <div class="modal" id="detailsModal">
        <div class="modal-content">
            <div class="modal-header modal-primary">
                <h5><i class="bi bi-info-circle"></i> Schedule Details</h5>
                <button type="button" class="btn-close" onclick="closeDetailsModal()">&times;</button>
            </div>
            <div class="modal-body" id="detailsModalContent">
                <!-- Content will be populated by JavaScript -->
                <div class="loading-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDetailsModal()">Close</button>
                <button type="button" class="btn btn-primary" onclick="openEditModal()">
                    <i class="bi bi-pencil"></i> Edit Schedule
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Schedule Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header modal-primary">
                <h5><i class="bi bi-pencil-square"></i> Edit Schedule</h5>
                <button type="button" class="btn-close" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST" id="editScheduleForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div id="editScheduleErrors" class="alert alert-danger" style="display: none; margin-bottom: 20px;"></div>
                    <div id="editModalContent">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Day Schedule Modal (shows all schedules for clicked day) -->
    <div class="modal" id="dayModal">
        <div class="modal-content day-modal-content">
            <div class="modal-header modal-success">
                <div>
                    <h5 class="day-modal-title"><i class="bi bi-calendar-day"></i> <span id="modalDayTitle">Schedule Details</span></h5>
                    <small id="modalDayDate" class="day-modal-date"></small>
                </div>
                <button type="button" class="btn-close" onclick="closeDayModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalDayBody">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDayModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        var currentSlotId = null;
        var currentSlotData = null;

        // Define global functions first for reliable access
        window.openCreateModal = function() {
            var modal = document.getElementById('createModal');
            if (modal) {
                modal.style.visibility = 'visible';
                modal.style.display = 'flex';
                // Trigger smart form logic on open
                if (typeof updateFormMode === 'function') {
                    updateFormMode();
                }
            }
        };

        window.closeCreateModal = function() {
            var createModal = document.getElementById('createModal');
            if(createModal) createModal.style.display = 'none';
        };

        // Course-Driven Smart Form Logic
        document.addEventListener('DOMContentLoaded', function() {
            const courseSelect = document.getElementById('createCourseSelect');
            const sessionTypeSelect = document.getElementById('createSessionType');
            const maxStudentsGroup = document.getElementById('maxStudentsGroup');
            const maxInstructorsGroup = document.getElementById('maxInstructorsGroup');
            const batchModeAlert = document.getElementById('batchModeAlert');
            const instructorSelect = document.querySelector('select[name="instructor_ids[]"]');
            const maxInstructorsInput = document.getElementById('createMaxInstructors');

            function updateFormMode() {
                if (!courseSelect) return;
                const selectedOption = courseSelect.options[courseSelect.selectedIndex];
                if (!selectedOption || !selectedOption.value) return;

                const courseId = selectedOption.value;
                const type = selectedOption.getAttribute('data-type');
                
                if (instructorSelect) {
                    Array.from(instructorSelect.options).forEach(opt => {
                        let accreditedCourses = [];
                        try { accreditedCourses = JSON.parse(opt.dataset.accreditedCourses || '[]'); } catch(e) {}
                        
                        if (accreditedCourses.includes(parseInt(courseId))) {
                            opt.disabled = false;
                            opt.text = opt.dataset.originalText || opt.text;
                        } else {
                            if (!opt.dataset.originalText) {
                                opt.dataset.originalText = opt.text;
                            }
                            opt.disabled = true;
                            opt.selected = false;
                            opt.text = opt.dataset.originalText + ' (Not Accredited)';
                        }
                    });
                }

                const selectedInstructors = instructorSelect ? Array.from(instructorSelect.selectedOptions).length : 0;
                const sessionTypeHidden = document.getElementById('createSessionTypeHidden');

                if (sessionTypeSelect) {
                    if (type === 'theoretical') {
                        sessionTypeSelect.value = 'theoretical';
                        sessionTypeSelect.disabled = true;
                        if (sessionTypeHidden) {
                            sessionTypeHidden.value = 'theoretical';
                            sessionTypeHidden.disabled = false;
                        }
                    } else if (type === 'practical') {
                        sessionTypeSelect.value = 'practical';
                        sessionTypeSelect.disabled = true;
                        if (sessionTypeHidden) {
                            sessionTypeHidden.value = 'practical';
                            sessionTypeHidden.disabled = false;
                        }
                    } else {
                        // Combo or other types: Allow manual selection
                        sessionTypeSelect.disabled = false;
                        if (sessionTypeHidden) {
                            sessionTypeHidden.disabled = true; // Use the dropdown's value
                        }
                    }
                }

                const effectiveSessionType = sessionTypeSelect
                    ? sessionTypeSelect.value
                    : (type === 'practical' ? 'practical' : 'theoretical');

                if (effectiveSessionType === 'theoretical') {
                    // TDC Mode: Focus on Seating
                    maxStudentsGroup.style.display = 'block';
                    const maxStudentsInput = document.getElementById('createMaxStudents');
                    if (maxStudentsInput) maxStudentsInput.disabled = false;

                    // For TDC, we hide "Max Instructors" (one-to-one focus) 
                    // and default internal instructors to 1 in the background logic.
                    maxInstructorsGroup.style.display = 'none';
                    maxInstructorsInput.disabled = true;

                    batchModeAlert.style.display = 'none';
                } else {
                    // PDC Mode: Focus on 1-on-1
                    maxStudentsGroup.style.display = 'none';
                    const maxStudentsInput = document.getElementById('createMaxStudents');
                    if (maxStudentsInput) maxStudentsInput.disabled = true;

                    if (selectedInstructors > 1) {
                        // Batch Multi-Assign Mode active
                        batchModeAlert.style.display = 'block';
                        maxInstructorsGroup.style.display = 'none';
                        maxInstructorsInput.disabled = true;
                    } else {
                        batchModeAlert.style.display = 'none';
                        maxInstructorsGroup.style.display = 'block';
                        maxInstructorsInput.value = 1;
                        maxInstructorsInput.disabled = true;
                    }
                }
            }

            if (courseSelect) courseSelect.addEventListener('change', updateFormMode);
            if (instructorSelect) instructorSelect.addEventListener('change', updateFormMode);
            if (sessionTypeSelect) sessionTypeSelect.addEventListener('change', updateFormMode);
            updateFormMode();
        });

        window.openDetailsModal = function() {
            var detailsModal = document.getElementById('detailsModal');
            if(detailsModal) {
                detailsModal.style.visibility = 'visible';
                detailsModal.style.display = 'flex';
            }
        };

        window.closeDetailsModal = function() {
            var detailsModal = document.getElementById('detailsModal');
            if(detailsModal) detailsModal.style.display = 'none';
        };

        window.openEditModal = function() {
            window.closeDetailsModal();
            if (window.currentSlotData) {
                populateEditModal(window.currentSlotData);
                var editModal = document.getElementById('editModal');
                if(editModal) {
                    editModal.style.visibility = 'visible';
                    editModal.style.display = 'flex';
                }
            }
        };

        window.closeEditModal = function() {
            var editModal = document.getElementById('editModal');
            if(editModal) editModal.style.display = 'none';
        };

        window.switchView = function(view) {
            const listView = document.getElementById('list-view');
            const calendarView = document.getElementById('calendar-view');
            const filterWrap = document.querySelector('.filter-wrap');
            const viewBtns = document.querySelectorAll('.view-btn');

            if (!listView || !calendarView || viewBtns.length === 0) return;

            viewBtns.forEach(btn => btn.classList.remove('active'));

            if (view === 'list') {
                listView.classList.add('active');
                calendarView.classList.remove('active');
                if (filterWrap) filterWrap.style.display = 'block';
                const listBtn = document.querySelector('.view-btn[data-view-toggle="list"]');
                if (listBtn) listBtn.classList.add('active');
                localStorage.setItem('adminTimeslotsView', 'list');
            } else {
                listView.classList.remove('active');
                calendarView.classList.add('active');
                if (filterWrap) filterWrap.style.display = 'none';
                const calendarBtn = document.querySelector('.view-btn[data-view-toggle="calendar"]');
                if (calendarBtn) calendarBtn.classList.add('active');
                localStorage.setItem('adminTimeslotsView', 'calendar');
            }
        };

        window.initSchedules = function() {
            console.log('Initializing schedules...');
            const createBtn = document.getElementById('createScheduleBtn');
            if (createBtn) {
                // Remove existing listener if any to avoid double triggers
                const newBtn = createBtn.cloneNode(true);
                createBtn.parentNode.replaceChild(newBtn, createBtn);
                newBtn.addEventListener('click', function(event) {
                    event.preventDefault();
                    window.openCreateModal();
                });
            }

            document.querySelectorAll('.view-btn[data-view-toggle]').forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const view = this.getAttribute('data-view-toggle');
                    if (view) window.switchView(view);
                });
            });

            document.querySelectorAll('.btn-view-edit[data-slot-id]').forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const slotId = this.getAttribute('data-slot-id');
                    if (slotId) window.showSlotDetails(slotId);
                });
            });

            const savedView = localStorage.getItem('adminTimeslotsView');
            if (savedView === 'calendar') window.switchView('calendar');
        };

        console.log('Schedules script starting execution...');

        // Store all schedule data for calendar modal access
        var allSchedulesData = {
            @foreach($timeslots as $date => $dateTimeslots)
                @php
                    $formattedDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
                @endphp
                {!! json_encode($formattedDate) !!}: [
                    @foreach($dateTimeslots as $timeslot)
                        @php
                            $availableSpots = $timeslot->getAvailableSpots();
                            $adminCount = $timeslot->getAdminAssignedCount();
                            $selfCount = $timeslot->getSelfSelectedCount();
                            $totalCount = $timeslot->instructors->count();
                        @endphp
                        {
                            id: {!! json_encode($timeslot->id) !!},
                            sessionType: {!! json_encode($timeslot->session_type ?? (($timeslot->course && $timeslot->course->course_type === 'practical') ? 'practical' : 'theoretical')) !!},
                            time: {!! json_encode(\Carbon\Carbon::parse($timeslot->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($timeslot->end_time)->format('h:i A')) !!},
                            startTime: {!! json_encode($timeslot->start_time) !!},
                            endTime: {!! json_encode($timeslot->end_time) !!},
                            instructors: [
                                @foreach($timeslot->instructors as $instructor)
                                    { 
                                        name: {!! json_encode($instructor->name) !!}, 
                                        type: {!! json_encode($instructor->pivot->assignment_type ?? "admin_assigned") !!} 
                                    }@if(!$loop->last),@endif
                                @endforeach
                            ],
                            notes: {!! json_encode($timeslot->notes ?? "No notes") !!},
                            status: {!! json_encode($timeslot->status) !!},
                            availableSpots: {!! json_encode($availableSpots) !!},
                            adminCount: {!! json_encode($adminCount) !!},
                            selfCount: {!! json_encode($selfCount) !!},
                            totalCount: {!! json_encode($totalCount) !!},
                            start_time: {!! json_encode($timeslot->start_time) !!},
                            end_time: {!! json_encode($timeslot->end_time) !!},
                            maxInstructors: {!! json_encode($timeslot->max_instructors) !!},
                            maxStudents: {!! json_encode($timeslot->max_students) !!},
                            bookings: [
                                @foreach($timeslot->bookings as $booking)
                                    {
                                        student_name: {!! json_encode($booking->student->name ?? 'Unknown Student') !!},
                                        instructor_name: {!! json_encode($booking->instructor->name ?? 'Unassigned') !!},
                                        status: {!! json_encode($booking->status) !!}
                                    }@if(!$loop->last),@endif
                                @endforeach
                            ]
                        }@if(!$loop->last),@endif
                    @endforeach
                ]@if(!$loop->last),@endif
            @endforeach
        };

        console.log('Schedules data loaded. Count:', Object.keys(allSchedulesData).length || 0);

        // Initialize on load
        window.initSchedules();

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const createModal = document.getElementById('createModal');
            const detailsModal = document.getElementById('detailsModal');
            const editModal = document.getElementById('editModal');
            const dayModal = document.getElementById('dayModal');

            if (event.target == createModal) {
                window.closeCreateModal();
            } else if (event.target == detailsModal) {
                window.closeDetailsModal();
            } else if (event.target == editModal) {
                window.closeEditModal();
            } else if (event.target == dayModal) {
                window.closeDayModal();
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key !== 'Escape') {
                return;
            }

            const createModal = document.getElementById('createModal');
            const detailsModal = document.getElementById('detailsModal');
            const editModal = document.getElementById('editModal');
            const dayModal = document.getElementById('dayModal');

            if (createModal && createModal.style.display === 'flex') {
                window.closeCreateModal();
                return;
            }

            if (editModal && editModal.style.display === 'flex') {
                window.closeEditModal();
                return;
            }

            if (detailsModal && detailsModal.style.display === 'flex') {
                window.closeDetailsModal();
                return;
            }

            if (dayModal && dayModal.style.display === 'flex') {
                window.closeDayModal();
            }
        });

        // AJAX Form Handling
        async function handleScheduleSubmit(formId, errorContainerId, successMessage) {
            const form = document.getElementById(formId);
            const errorContainer = document.getElementById(errorContainerId);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn.innerHTML;

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Reset errors
                errorContainer.style.display = 'none';
                errorContainer.innerHTML = '';
                
                // Loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        if (typeof Toast !== 'undefined' && Toast.success) {
                            Toast.success(data.message || successMessage);
                        } else if (typeof showToast !== 'undefined') {
                            showToast('success', data.message || successMessage);
                        }
                        
                        // Wait a bit and reload to show changes
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else if (response.status === 422) {
                        // Validation errors
                        errorContainer.style.display = 'block';
                        let errorHtml = '<strong>Please fix the following errors:</strong><ul style="margin-top: 10px; padding-left: 20px;">';
                        for (const field in data.errors) {
                            data.errors[field].forEach(err => {
                                errorHtml += `<li>${err}</li>`;
                            });
                        }
                        errorHtml += '</ul>';
                        errorContainer.innerHTML = errorHtml;
                        
                        // Scroll modal to top to show errors
                        const modalContent = form.closest('.modal-content');
                        if (modalContent) modalContent.scrollTop = 0;
                    } else {
                        throw new Error(data.message || 'An unexpected error occurred.');
                    }
                } catch (error) {
                    console.error('Submission error:', error);
                    errorContainer.style.display = 'block';
                    errorContainer.innerHTML = `<strong>Error:</strong> ${error.message}`;
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            });
        }

        // Initialize AJAX handlers
        document.addEventListener('DOMContentLoaded', function() {
            handleScheduleSubmit('createScheduleForm', 'createScheduleErrors', 'Schedule created successfully!');
            handleScheduleSubmit('editScheduleForm', 'editScheduleErrors', 'Schedule updated successfully!');
        });

        // Collapsible Date Sections
        window.toggleDate = function(dateHeader) {
            const cardBody = dateHeader.closest('.timeslot-card').querySelector('.card-body');
            cardBody.classList.toggle('collapsed');
            dateHeader.classList.toggle('collapsed');
        };

        window.toggleInfoPanel = function(button) {
            const panel = button.closest('.info-panel');
            if (!panel) return;

            const expanded = panel.classList.toggle('expanded');
            button.textContent = expanded ? 'Hide details' : 'Show details';
        }

        // Calendar Navigation - Preserves view state
        window.changeMonth = function(delta) {
            const currentMonthEl = document.getElementById('currentMonth');
            const monthText = currentMonthEl.textContent.trim();
            const currentDate = new Date(monthText + ' 1');

            currentDate.setMonth(currentDate.getMonth() + delta);

            const year = currentDate.getFullYear();
            const month = String(currentDate.getMonth() + 1).padStart(2, '0');
            const monthStart = `${year}-${month}-01`;

            const endDateObj = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
            const endMonth = String(endDateObj.getMonth() + 1).padStart(2, '0');
            const endDay = String(endDateObj.getDate()).padStart(2, '0');
            const monthEnd = `${endDateObj.getFullYear()}-${endMonth}-${endDay}`;

            const url = new URL(window.location);
            url.searchParams.set('month', `${year}-${month}`);
            url.searchParams.set('start_date', monthStart);
            url.searchParams.set('end_date', monthEnd);
            window.location.href = url.toString();
        }

        // Show Slot Details Modal
        window.showSlotDetails = function(slotId) {
            console.log('showSlotDetails called with ID:', slotId);
            currentSlotId = slotId;

            // Find the slot data from the page DOM using data attributes
            const slotItem = document.querySelector(`.timeslot-item[data-slot-id="${slotId}"]`);

            if (!slotItem) {
                console.error('Could not find slot item with ID:', slotId);
                if (typeof Toast !== 'undefined' && Toast.error) {
                    Toast.error('Could not find schedule details. Please refresh the page and try again.', 'Not Found');
                } else if (typeof showToast !== 'undefined') {
                    showToast('error', 'Could not find schedule details. Please refresh the page and try again.');
                }
                return;
            }

            console.log('Found slot item:', slotItem);

            // Extract data from data attributes and DOM
            const startTime = slotItem.dataset.startTime || '';
            const endTime = slotItem.dataset.endTime || '';
            const courseId = slotItem.dataset.courseId || '';
            const sessionType = slotItem.dataset.sessionType || 'theoretical';
            let maxInstructors = slotItem.dataset.maxInstructors || '';
            const notes = slotItem.dataset.notes || 'No notes';

            // Format time
            const timeText = startTime && endTime ? 
                `${window.formatTime(startTime)} - ${window.formatTime(endTime)}` : 
                'Time not available';

            // Get instructors from the DOM
            const instructorBadges = slotItem.querySelectorAll('.badge-primary, .badge-success');
            let instructorsList = [];
            instructorBadges.forEach(badge => {
                const nameText = badge.textContent.trim().replace(/^\[A\]\s*|^\[S\]\s*/, ''); // Remove prefix
                instructorsList.push({
                    name: nameText,
                    type: badge.classList.contains('badge-primary') ? 'admin' : 'self'
                });
            });

            console.log('Instructors found:', instructorsList);

            // Get availability
            const availableBadge = slotItem.querySelector('.badge-success:not(.badge-primary), .badge-secondary');
            const availableText = availableBadge ? availableBadge.textContent.trim() : 'Full';
            const maxStudents = slotItem.dataset.maxStudents || '1';
            maxInstructors = slotItem.dataset.maxInstructors || '1';

            // Store data for edit modal
            window.currentSlotData = {
                id: slotId,
                courseId: courseId,
                time: timeText,
                sessionType: sessionType,
                instructors: instructorsList,
                notes: notes,
                available: availableText,
                maxInstructors: maxInstructors,
                maxStudents: maxStudents
            };

                // Populate details modal
                let instructorsHtml = '';
                if (instructorsList.length > 0) {
                    instructorsHtml = instructorsList.map(inst => {
                        const icon = inst.type === 'admin' ? '[A]' : '[S]';
                        const label = inst.type === 'admin' ? 'Admin Assigned' : 'Self Selected';
                        return `<span class="badge badge-${inst.type === 'admin' ? 'primary' : 'success'}" title="${label}">${icon} ${inst.name}</span>`;
                    }).join(' ');
                } else {
                    instructorsHtml = '<span class="text-muted">No instructors assigned yet</span>';
                }

                // Helper to get dynamic status
                window.getDynamicStatus = function(status, date, startTime, endTime) {
                    if (status === 'cancelled') return { text: 'CANCELLED', color: '#ef4444', bg: '#fee2e2' };
                    if (status === 'completed' || status === 'done') return { text: 'COMPLETED', color: '#10b981', bg: '#d1fae5' };
                    if (status === 'no-show') return { text: 'NO-SHOW', color: '#ef4444', bg: '#fee2e2' };

                    const now = new Date();
                    const slotStart = new Date(`${date} ${startTime}`);
                    const slotEnd = new Date(`${date} ${endTime}`);

                    if (now >= slotStart && now <= slotEnd) {
                        return { text: 'IN SESSION', color: '#0ea5e9', bg: '#e0f2fe' };
                    } else if (now > slotEnd) {
                        return { text: 'ABSENT', color: '#f59e0b', bg: '#fef3c7' };
                    } else {
                        return { text: 'SCHEDULED', color: '#64748b', bg: '#f1f5f9' };
                    }
                };

                // Add Bookings/Pairings Logic
                let bookingsHtml = '';
                // Get bookings from our pre-loaded data
                let slotData = null;
                let slotDate = '';
                for (let date in window.allSchedulesData) {
                    let found = window.allSchedulesData[date].find(s => s.id == slotId);
                    if (found) {
                        slotData = found;
                        slotDate = date;
                        break;
                    }
                }
                
                const bookings = slotData ? slotData.bookings || [] : [];
                
                if (bookings.length > 0) {
                    bookingsHtml = `
                        <div class="details-section" style="margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 15px;">
                            <strong class="details-label" style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; color: #1e293b;">
                                <i class="bi bi-people"></i> ${sessionType === 'practical' ? 'Student-Instructor Pairings' : 'Attendance List'}
                            </strong>
                            <div style="background: #f8fafc; border-radius: 8px; padding: 12px; border: 1px solid #e2e8f0;">
                                <table style="width: 100%; font-size: 0.85rem; border-collapse: collapse;">
                                    <thead>
                                        <tr style="text-align: left; border-bottom: 1px solid #e2e8f0;">
                                            <th style="padding: 8px; color: #64748b;">Student</th>
                                            ${sessionType === 'practical' ? '<th style="padding: 8px; color: #64748b;">Instructor</th>' : ''}
                                            <th style="padding: 8px; color: #64748b;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${bookings.map(b => {
                                            const dyn = window.getDynamicStatus(b.status, slotDate, slotData.start_time, slotData.end_time);
                                            return `
                                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                                    <td style="padding: 8px; font-weight: 600; color: #334155;">${b.student_name}</td>
                                                    ${sessionType === 'practical' ? `<td style="padding: 8px; color: #334155;">${b.instructor_name}</td>` : ''}
                                                    <td style="padding: 8px;">
                                                        <span style="font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 4px; background: ${dyn.bg}; color: ${dyn.color}; text-transform: uppercase; border: 1px solid ${dyn.color}40;">
                                                            ${dyn.text}
                                                        </span>
                                                    </td>
                                                </tr>
                                            `;
                                        }).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                } else {
                    bookingsHtml = `
                        <div class="details-section" style="margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 15px;">
                            <strong class="details-label"><i class="bi bi-people"></i> Students</strong>
                            <p style="font-size: 0.85rem; color: #94a3b8; font-style: italic; margin-left: 5px;">No students booked yet.</p>
                        </div>
                    `;
                }

                document.getElementById('detailsModalContent').innerHTML = `
                    <div class="details-wrap">
                        <div class="details-section">
                            <strong class="details-label">Time:</strong>
                            <div class="details-time">${timeText}</div>
                        </div>

                        <div class="details-section">
                            <strong class="details-label">Instructors:</strong>
                            <div>${instructorsHtml}</div>
                        </div>

                        <div class="details-section">
                            <strong class="details-label">Availability:</strong>
                            <div>${availableText}</div>
                        </div>

                        <div class="details-section">
                            <strong class="details-label">Session Type:</strong>
                            <div>${sessionType === 'practical' ? 'Practical' : 'Theoretical'}</div>
                        </div>

                        ${bookingsHtml}

                        <div class="details-section">
                            <strong class="details-label">Notes:</strong>
                            <div class="details-notes">
                                ${notes}
                            </div>
                        </div>
                    </div>
                `;

                window.openDetailsModal();

            console.log('Modal opened successfully');
        }

        // Helper function to format time (HH:MM:SS to HH:MM AM/PM)
        window.formatTime = function(timeStr) {
            if (!timeStr) return '';
            const [hours, minutes] = timeStr.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const hour12 = hour % 12 || 12;
            return `${hour12}:${minutes} ${ampm}`;
        }

        // Show all schedules for a specific day in modal
        window.showDayModal = function(dateStr, formattedDate) {
            console.log('Showing day modal for:', dateStr);
            console.log('All available dates in allSchedulesData:', Object.keys(allSchedulesData));
            console.log('Full allSchedulesData object:', allSchedulesData);

            document.getElementById('modalDayTitle').textContent = 'Schedules for ' + formattedDate.split(',')[0];
            document.getElementById('modalDayDate').textContent = formattedDate;

            // Get schedules for this date from the stored data
            const daySchedules = allSchedulesData[dateStr] || [];

            console.log('Looking for date:', dateStr);
            console.log('Found schedules:', daySchedules);

            if (daySchedules.length === 0) {
                document.getElementById('modalDayBody').innerHTML = `
                    <div class="day-empty">
                        <i class="bi bi-calendar-x day-empty-icon"></i>
                        <p>No schedules found for this date.</p>
                    </div>
                `;
            } else {
                let modalContent = '<div class="day-list">';

                daySchedules.forEach(schedule => {
                    // Build instructors HTML
                    let instructorsHtml = '';
                    if (schedule.instructors.length > 0) {
                        schedule.instructors.forEach(instructor => {
                            const icon = instructor.type === 'admin_assigned' ? '[A]' : '[S]';
                            const badgeClass = instructor.type === 'admin_assigned' ? 'badge-primary' : 'badge-success';
                            const title = instructor.type === 'admin_assigned' ? 'Admin Assigned' : 'Self Selected';
                            instructorsHtml += `<span class="badge ${badgeClass}" title="${title}">${icon} ${instructor.name}</span> `;
                        });
                    } else {
                        instructorsHtml = '<span class="text-muted">No instructors assigned</span>';
                    }

                    // Build availability badge
                    let availableHtml = '';
                    if (schedule.availableSpots > 0) {
                        availableHtml = `<span class="badge badge-success">${schedule.availableSpots} Spot${schedule.availableSpots > 1 ? 's' : ''} Available</span>`;
                    } else {
                        availableHtml = '<span class="badge badge-secondary">✓ Full</span>';
                    }

                    modalContent += `
                        <div class="day-card">
                            <div class="day-card-head">
                                <div>
                                    <div class="day-card-time">
                                        <i class="bi bi-clock"></i> ${window.formatTime(schedule.startTime)} - ${window.formatTime(schedule.endTime)}
                                    </div>
                                    ${availableHtml}
                                    <div class="day-card-meta">
                                        <i class="bi bi-people-fill"></i> ${schedule.totalCount}/${schedule.maxInstructors} Instructors
                                        ${schedule.adminCount > 0 || schedule.selfCount > 0 ? 
                                            `<span class="day-counts">
                                                (${schedule.adminCount > 0 ? `<span class="count-admin">${schedule.adminCount} admin</span>` : ''}${schedule.adminCount > 0 && schedule.selfCount > 0 ? ', ' : ''}${schedule.selfCount > 0 ? `<span class="count-self">${schedule.selfCount} self</span>` : ''})
                                            </span>` 
                                            : ''}
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary" onclick="window.closeDayModal(); window.showSlotDetails(${schedule.id})">
                                    <i class="bi bi-eye"></i> View/Edit
                                </button>
                            </div>
                            <div class="day-instructors">
                                <strong class="day-instructors-label">Instructors:</strong><br>
                                ${instructorsHtml}
                            </div>
                            ${schedule.notes ? `
                            <div class="day-notes">
                                <i class="bi bi-sticky"></i> ${schedule.notes}
                            </div>
                            ` : ''}
                        </div>
                    `;
                });

                modalContent += '</div>';
                document.getElementById('modalDayBody').innerHTML = modalContent;
            }

            var dayModal = document.getElementById('dayModal');
            if (dayModal) {
                dayModal.style.visibility = 'visible';
                dayModal.style.display = 'flex';
            }
        }

        window.openDayModal = function() {
            var dayModal = document.getElementById('dayModal');
            if(dayModal) {
                dayModal.style.visibility = 'visible';
                dayModal.style.display = 'flex';
            }
        };

        window.closeDayModal = function() {
            document.getElementById('dayModal').style.display = 'none';
        }

        // Populate Edit Modal
        window.populateEditModal = function(slotData) {
            const schoolSlug = '{{ $school->slug }}';
            const actionUrl = `/${schoolSlug}/admin/schedules/${slotData.id}`;
            document.getElementById('editScheduleForm').action = actionUrl;

            // Get available instructors from the create modal
            const createModalSelect = document.querySelector('#createModal select[name="instructor_ids[]"]');
            const allInstructors = createModalSelect ? Array.from(createModalSelect.options).map(opt => ({
                id: opt.value,
                name: opt.dataset.originalText || opt.text,
                accreditedCourses: opt.dataset.accreditedCourses || '[]'
            })) : [];

            // Build instructor multi-select
            let instructorOptions = allInstructors.map(inst => {
                // Match by checking if instructor name is in the assigned list
                const instructorName = inst.name.split(' (')[0].trim(); // Remove email part
                const isSelected = slotData.instructors.some(assigned => {
                    const assignedName = assigned.name.replace(/[🔵🟢]\s*/, '').trim();
                    return assignedName === instructorName;
                });
                
                let isAccredited = false;
                try {
                    const courses = JSON.parse(inst.accreditedCourses);
                    isAccredited = courses.includes(parseInt(slotData.courseId));
                } catch(e) {}
                
                if (isAccredited) {
                    return `<option value="${inst.id}" ${isSelected ? 'selected' : ''}>${inst.name}</option>`;
                } else {
                    return `<option value="${inst.id}" disabled>${inst.name} (Not Accredited)</option>`;
                }
            }).join('');

            document.getElementById('editModalContent').innerHTML = `
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-diagram-3"></i> Session Type
                    </label>
                    <select name="session_type" id="editSessionType" class="form-control" onchange="window.updateEditCapacityVisibility()">
                        <option value="theoretical" ${slotData.sessionType === 'theoretical' ? 'selected' : ''}>Theoretical</option>
                        <option value="practical" ${slotData.sessionType === 'practical' ? 'selected' : ''}>Practical</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6" id="editMaxInstructorsGroup" style="${slotData.sessionType === 'theoretical' ? 'display:block' : 'display:block'}">
                        <div class="form-group">
                            <label class="form-label">Max Instructors</label>
                            <input type="number" name="max_instructors" class="form-control" value="${slotData.maxInstructors}" min="1" max="10">
                        </div>
                    </div>
                    <div class="col-md-6" id="editMaxStudentsGroup" style="${slotData.sessionType === 'practical' ? 'display:none' : 'display:block'}">
                        <div class="form-group">
                            <label class="form-label">Max Students (Seats)</label>
                            <input type="number" name="max_students" class="form-control" value="${slotData.maxStudents}" min="1">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-people"></i> Assigned Instructors
                    </label>
                    <select name="instructor_ids[]" class="form-control multi-select-auto" multiple size="6">
                        ${instructorOptions}
                    </select>
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> Hold Ctrl (Windows) or Cmd (Mac) to select multiple. 
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3">${slotData.notes !== 'No notes' ? slotData.notes : ''}</textarea>
                </div>
            `;
        }

        window.updateEditCapacityVisibility = function() {
            const type = document.getElementById('editSessionType').value;
            const instructorsGroup = document.getElementById('editMaxInstructorsGroup');
            const studentsGroup = document.getElementById('editMaxStudentsGroup');

            if (type === 'practical') {
                // PDC: No Seats, but show Instructors
                if (instructorsGroup) instructorsGroup.style.display = 'block';
                if (studentsGroup) studentsGroup.style.display = 'none';
            } else {
                // TDC: Show Both
                if (instructorsGroup) instructorsGroup.style.display = 'block';
                if (studentsGroup) studentsGroup.style.display = 'block';
            }
        }


        // Confirm Delete Schedule
        window.confirmDeleteSchedule = function(scheduleId) {
            showConfirm({
                type: 'danger',
                title: 'Delete Schedule',
                message: 'Are you sure you want to delete this schedule? This action cannot be undone.',
                confirmText: 'Yes, Delete',
                onConfirm: async () => {
                    const form = document.getElementById('deleteScheduleForm' + scheduleId);
                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: new FormData(form),
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            if (typeof Toast !== 'undefined' && Toast.success) {
                                Toast.success(data.message || 'Schedule deleted successfully.');
                            } else if (typeof showToast !== 'undefined') {
                                showToast('success', data.message || 'Schedule deleted successfully.');
                            }
                            
                            // Reload to refresh the list
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            throw new Error(data.message || 'Failed to delete schedule.');
                        }
                    } catch (error) {
                        console.error('Delete error:', error);
                        if (typeof Toast !== 'undefined' && Toast.error) {
                            Toast.error(error.message);
                        } else if (typeof showToast !== 'undefined') {
                            showToast('error', error.message);
                        }
                    }
                }
            });
        };

    </script>

@endsection