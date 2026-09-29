@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'User Management')

@section('content')
    @php
        $school = $school ?? $currentSchool ?? null;
        $settings = $school?->schoolSetting;
        $schoolName = $school->name ?? 'Driving School';

        $primaryColor = $settings->primary_color ?? '#3b82f6';
        $secondaryColor = $settings->secondary_color ?? '#60a5fa';

        // Statistics are now passed from the controller to ensure accuracy with pagination
        $totalUsers = $totalStudents + $totalInstructors;
        $totalActive = $activeStudents + $activeInstructors;
        $totalInactive = $inactiveStudents + $inactiveInstructors;

        $oldInviteRole = old('role');
        $showStudentInviteErrors = $errors->any() && $oldInviteRole === 'student';
        $showInstructorInviteErrors = $errors->any() && $oldInviteRole === 'instructor';
        $requireInstructorLicense = $settings?->require_instructor_license ?? true;
    @endphp

    @include('school.admin.partials.admin-styles')

    <style>
        :root {
            --primary-color:
                {{ $primaryColor }}
            ;
            --secondary-color:
                {{ $secondaryColor }}
            ;
            --primary-rgb:
                {{ implode(',', sscanf($primaryColor, '#%02x%02x%02x')) }}
            ;
            --header-gradient: linear-gradient(135deg,
                    {{ $primaryColor }}
                    0%,
                    {{ $secondaryColor }}
                    100%);
            --border-radius: 20px;
            --button-border-radius: 12px;

            /* Branded Button Variables */
            --btn-primary-bg:
                {{ $primaryColor }}
            ;
            --btn-primary-text: #ffffff;
            --btn-secondary-bg:
                {{ $secondaryColor }}
            ;
            --btn-secondary-text: #ffffff;
            --btn-gradient: var(--header-gradient);
        }

        .admin-mgmt-container {
            padding: 20px;
            margin: 0 auto;
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

        .page-header-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-review-requests {
            position: relative;
        }

        .request-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            border-radius: 999px;
            background: #ef4444;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
        }

        /* Statistics Cards - Using shared styles from admin-styles.blade.php */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        /* Additional stat card color variants for user management */
        .stat-card.total {
            border-left-color: var(--primary-color);
        }

        .stat-card.total::before {
            background: var(--primary-color);
        }

        .stat-card.total .stat-icon {
            background: rgba(var(--primary-rgb), 0.1);
            color: var(--primary-color);
        }

        .stat-card.inactive {
            border-left-color: #6b7280;
        }

        .stat-card.inactive::before {
            background: #6b7280;
        }

        .stat-card.inactive .stat-icon {
            background: #6b728015;
            color: #6b7280;
        }

        .stat-card.filter-selected {
            box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.18), 0 8px 20px rgba(0, 0, 0, 0.14);
            transform: translateY(-1px);
        }

        /* Action Buttons */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .control-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 6px;
        }

        .contact-input-group {
            display: flex;
            align-items: stretch;
        }

        .contact-prefix {
            display: flex;
            align-items: center;
            background: #f3f4f6;
            border: 2px solid #e1e5e9;
            border-right: none;
            padding: 0 12px;
            border-radius: 8px 0 0 8px;
            color: #4b5563;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .contact-input-group input {
            border-radius: 0 8px 8px 0 !important;
            flex: 1;
        }

        .action-controls {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search-box input {
            width: 100%;
            padding: 10px 40px 10px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .search-box::after {
            content: "";
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
        }

        .btn-create {
            padding: 10px 20px;
            @if(($settings->button_style ?? 'solid') === 'gradient')
                background: var(--btn-gradient);
            @else background: var(--btn-primary-bg);
            @endif color: var(--btn-primary-text);
            border: none;
            border-radius: var(--button-border-radius);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.10);
        }

        .btn-create:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18);
        }

        .icon-24 {
            width: 24px;
            height: 24px;
        }

        .icon-18 {
            width: 18px;
            height: 18px;
        }

        .icon-14 {
            width: 14px;
            height: 14px;
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

        .modal-error-summary ul {
            margin: 8px 0 0 0;
            padding-left: 20px;
            font-size: 0.85rem;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 12px;
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
            white-space: nowrap;
        }

        thead {
            background: var(--header-gradient);
            color: white;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.95rem;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f1f3f5;
        }

        tbody tr {
            transition: background-color 0.2s;
        }

        tbody tr:hover {
            background-color: #f8f9fa;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .role-student,
        .role-guest {
            background: var(--role-student-bg);
            color: var(--role-student-text);
        }

        .role-guest {
            border: 1px dashed var(--role-student-text);
        }

        .role-instructor {
            background: var(--role-instructor-bg);
            color: var(--role-instructor-text);
        }

        .branch-label {
            font-size: 0.85rem;
        }

        .branch-assigned {
            color: #374151;
        }

        .branch-unassigned {
            color: #9ca3af;
        }

        .btn-action {
            padding: 6px 12px;
            margin: 0;
            border: none;
            border: 1px solid transparent;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            line-height: 1.2;
            white-space: nowrap;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-start;
            align-items: center;
            flex-wrap: wrap;
        }

        .action-buttons form {
            margin: 0;
            display: inline-flex;
        }

        .action-buttons .btn-action {
            padding: 8px 12px;
            border-radius: 10px;
        }

        .action-buttons .btn-info {
            background: #e8efff;
            color: #2563eb;
            border-color: #c7d2fe;
        }

        .action-buttons .btn-warning {
            background: #fef3c7;
            color: #b45309;
            border-color: #fde68a;
        }

        .action-buttons .btn-success {
            background: #dcfce7;
            color: #166534;
            border-color: #bbf7d0;
        }

        .action-buttons .btn-secondary {
            background: #ffe4e6;
            color: #be123c;
            border-color: #fecdd3;
        }

        .action-buttons .btn-primary {
            background: #dbeafe;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        .action-buttons .btn-action:hover {
            transform: translateY(-1px);
            filter: brightness(0.98);
        }

        .actions-cell {
            min-width: 220px;
        }

        .actions-group {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }

        .btn-edit {
            background: #ffc107;
            color: #000;
        }

        .btn-edit:hover {
            background: #ffb300;
            transform: scale(1.05);
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
            transform: scale(1.05);
        }

        .btn-view {
            background: #17a2b8;
            color: white;
        }

        .btn-view:hover {
            background: #138496;
            transform: scale(1.05);
        }

        .btn-toggle {
            background: #f0ad4e;
            color: white;
        }

        .btn-toggle:hover {
            background: #ec971f;
            transform: scale(1.05);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            justify-content: center;
            align-items: center;
        }

        .icon-14 {
            width: 14px;
            height: 14px;
        }

        .icon-18 {
            width: 18px;
            height: 18px;
        }

        .icon-20 {
            width: 20px;
            height: 20px;
        }

        .icon-24 {
            width: 24px;
            height: 24px;
        }

        .icon-32 {
            width: 32px;
            height: 32px;
        }

        .ms-auto {
            margin-left: auto !important;
        }

        .me-1 {
            margin-right: 0.25rem !important;
        }

        .me-2 {
            margin-right: 0.5rem !important;
        }

        .me-3 {
            margin-right: 1rem !important;
        }

        /* Consolidate Modal Content styles */
        .modal-content {
            padding: 0 !important;
            border: none !important;
            border-radius: 20px !important;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            background: white;
            transition: transform 0.2s ease-out;
            width: min(600px, 92%);
            margin: auto;
            flex-shrink: 0;
        }

        #verifyLicenseModal .modal-content {
            width: 1100px;
            max-width: 95%;
            flex-shrink: 0;
        }

        /* Override shared admin transform scaling for this page's .modal pattern to prevent 'shrinking' effect. */
        .modal .modal-content {
            transform: none !important;
            transition: none !important;
            animation: fadeIn 0.2s ease-out !important;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }



        .modal-header {
            background: var(--header-gradient);
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-header h3 {
            color: white !important;
            margin: 0 !important;
            font-size: 1.25rem !important;
            font-weight: 600 !important;
            background: none !important;
            padding: 0 !important;
            border: none !important;
        }

        .btn-close-modal {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 1.5rem;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            line-height: 1;
        }

        .btn-close-modal:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            padding: 24px 32px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .modal-content form {
            padding: 0 !important;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            margin-top: 0;
            width: 100%;
            justify-content: flex-end;
        }

        .modal-buttons button,
        .modal-buttons .btn-submit,
        .modal-buttons .btn-cancel,
        .modal-footer .btn {
            padding: 10px 20px;
            border-radius: var(--button-border-radius, 8px);
            font-weight: 600;
            transition: all 0.2s;
            font-size: 0.95rem;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--header-gradient);
            color: white;
        }

        .btn-secondary {
            background: #64748b;
            color: white;
        }

        .btn-primary:hover,
        .btn-secondary:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Verification Modal Specifics */
        .verify-modal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .restriction-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 12px;
            margin-top: 15px;
        }

        .restriction-chip {
            position: relative;
            cursor: pointer;
            user-select: none;
        }

        .restriction-chip input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .chip-content {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 8px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: var(--button-border-radius, 12px);
            font-weight: 600;
            color: #64748b;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
        }

        .restriction-chip:hover .chip-content {
            border-color: var(--primary-color);
            background: #f1f5f9;
            transform: translateY(-2px);
        }

        .restriction-chip input:checked+.chip-content {
            background: var(--header-gradient);
            border-color: transparent;
            color: white;
            box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3);
        }

        .verification-preview-container {
            position: relative;
            width: 100%;
            height: 100%;
            border-radius: var(--border-radius, 12px);
            overflow: hidden;
            background: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .verification-preview-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .verification-preview-container:hover img {
            transform: scale(1.05);
        }

        .verification-preview-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 8px;
            text-align: center;
            font-size: 0.75rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .verification-preview-container:hover .verification-preview-overlay {
            opacity: 1;
        }

        .status-badge-selector {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }

        .status-option {
            flex: 1;
            cursor: pointer;
        }

        .status-option input {
            position: absolute;
            opacity: 0;
        }

        .status-btn {
            display: block;
            padding: 12px;
            text-align: center;
            border-radius: var(--button-border-radius, 10px);
            border: 2px solid #e2e8f0;
            font-weight: 600;
            transition: all 0.2s;
            color: #64748b;
        }

        .status-option:nth-child(1) input:checked+.status-btn {
            background: #dcfce7;
            border-color: #22c55e;
            color: #166534;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
        }

        .status-option:nth-child(2) input:checked+.status-btn {
            background: #fee2e2;
            border-color: #ef4444;
            color: #991b1b;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.98);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .unlock-requests-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 60vh;
            overflow-y: auto;
            padding-right: 2px;
            margin: 0 24px 0;
        }

        .unlock-request-item {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px;
            background: #f9fafb;
        }

        .unlock-request-header {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }

        .unlock-request-name {
            font-weight: 600;
            color: #1f2937;
        }

        .unlock-request-role {
            font-size: 0.8rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .unlock-request-meta {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .unlock-request-reason {
            margin: 10px 0;
            font-size: 0.88rem;
            color: #374151;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .unlock-request-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }

        .unlock-request-actions form {
            margin: 0;
        }

        .btn-approve-request,
        .btn-deny-request {
            border: none;
            border-radius: 8px;
            padding: 8px 14px;
            color: #fff;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-approve-request {
            background: #059669;
        }

        .btn-approve-request:hover {
            background: #047857;
        }

        .btn-deny-request {
            background: #dc2626;
        }

        .btn-deny-request:hover {
            background: #b91c1c;
        }

        .modal-content form {
            padding: 32px;
            background: #fff;
            margin: 0;
            border-radius: 0 0 16px 16px;
            max-height: 72vh;
            overflow-y: auto;
            scrollbar-gutter: stable;
        }

        .modal-required-note {
            margin: 0 0 18px;
            font-size: 0.86rem;
            color: #6b7280;
        }

        .required-indicator {
            color: #dc2626;
            margin-left: 2px;
        }

        .field-help {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 4px;
        }

        .field-error {
            font-size: 0.8rem;
            color: #b91c1c;
            margin-top: 6px;
        }

        .modal-error-summary {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 16px;
        }

        .modal-error-summary strong {
            display: block;
            margin-bottom: 8px;
            font-size: 0.88rem;
        }

        .modal-error-summary ul {
            margin: 0;
            padding-left: 18px;
        }

        .modal-error-summary li {
            margin-bottom: 4px;
            font-size: 0.82rem;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #1a202c;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            box-sizing: border-box;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: #fff;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            padding: 24px 32px 32px 32px;
            background: #fff;
        }

        .btn-cancel {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            flex: 1;
            padding: 16px 24px;
            font-size: 1.05rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
        }

        .btn-submit {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            flex: 1;
            padding: 16px 24px;
            font-size: 1.05rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
            border: none;
            cursor: pointer;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35);
        }

        .btn-submit:disabled {
            cursor: not-allowed;
            opacity: 0.75;
            transform: none;
            box-shadow: none;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }

        .empty-state-text {
            font-size: 1.2rem;
            color: #666;
        }

        .pagination-groups {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .pagination-title {
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: #6b7280;
        }

        .pagination-title {
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: #6b7280;
        }

        .export-dropdown-menu a .dot.pdf {
            background: #ef4444;
        }

        .export-dropdown-menu a .dot.excel {
            background: #10b981;
        }

        /* Mobile Responsive Styles */
        /* --- ENHANCED USER PROFILE MODAL STYLES --- */
        .profile-modal-container {
            border-radius: 20px;
            overflow: hidden;
            border: none;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
        }

        .profile-modal-header {
            background: linear-gradient(135deg, var(--primary-color), #4f46e5);
            padding: 35px 30px;
            position: relative;
            color: white;
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .profile-modal-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            border: 4px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .profile-modal-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-header-info h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 0 8px 0;
            letter-spacing: -0.025em;
            color: white !important;
        }

        .profile-role-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            backdrop-filter: blur(4px);
        }

        .profile-modal-body {
            padding: 30px;
            background: white;
            max-height: 70vh;
            overflow-y: auto;
        }

        .profile-section {
            margin-bottom: 30px;
        }

        .profile-section:last-child {
            margin-bottom: 0;
        }

        .profile-section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
        }

        .profile-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .profile-info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .profile-info-label {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 600;
        }

        .profile-info-value {
            font-size: 0.95rem;
            color: #1e293b;
            font-weight: 500;
        }

        .profile-info-value.pii-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .profile-modal-footer {
            padding: 20px 30px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .verification-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
        }

        .license-image-container {
            width: 100%;
            height: 200px;
            background: #1e293b;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: zoom-in;
        }

        .license-image-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        @media (max-width: 640px) {
            .profile-modal-header {
                flex-direction: column;
                text-align: center;
                padding: 30px 20px;
            }
            .profile-info-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .admin-mgmt-container {
                padding: 15px;
                margin: 10px auto;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .page-header-right {
                width: 100%;
            }

            .page-header-right .btn-create {
                width: 100%;
                justify-content: center;
            }

            .page-title {
                font-size: 1.4rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .stat-card {
                padding: 18px;
            }

            .stat-value {
                font-size: 1.8rem;
            }

            .tabs {
                flex-wrap: wrap;
            }

            .tab {
                padding: 10px 16px;
                font-size: 0.9rem;
                flex: 1;
                min-width: 120px;
                text-align: center;
            }

            .action-bar {
                flex-direction: column;
                gap: 15px;
                padding: 12px;
            }

            .action-bar>div:last-child {
                flex-direction: row;
                width: auto;
            }

            .action-controls {
                width: 100%;
                align-items: center;
                justify-content: flex-start;
                gap: 8px;
            }

            .search-box {
                max-width: 100%;
                width: 100%;
            }

            .control-label {
                font-size: 0.8rem;
                margin-bottom: 4px;
            }

            .btn-create {
                width: auto;
                justify-content: center;
            }

            .table-container {
                border-radius: 8px;
            }

            table {
                font-size: 0.85rem;
                min-width: 780px;
            }

            th,
            td {
                padding: 10px 8px;
                white-space: nowrap;
            }

            .btn-action {
                padding: 5px 8px;
                font-size: 0.8rem;
                margin: 0;
            }

            .actions-group {
                gap: 4px;
            }

            .status-badge {
                padding: 4px 8px;
                font-size: 0.75rem;
            }

            /* Hide less important columns on mobile */
            .hide-mobile {
                display: none;
            }

            .modal-content {
                width: 95%;
                max-width: 95%;
                min-width: 0;
                margin: 10px;
            }

            .modal-content h3 {
                padding: 20px;
                font-size: 1.3rem;
            }

            .modal-content form {
                padding: 20px;
            }

            .modal-buttons {
                padding: 15px 20px 20px;
                flex-direction: column;
            }

            .btn-cancel,
            .btn-submit {
                width: 100%;
                padding: 14px;
            }
        }

        @media (max-width: 480px) {
            .admin-mgmt-container {
                padding: 10px;
                margin: 5px auto;
            }

            .page-title {
                font-size: 1.2rem;
            }

            .stat-card {
                padding: 14px;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .stat-label {
                font-size: 0.75rem;
            }

            .stat-detail {
                font-size: 0.75rem;
            }

            .tab {
                padding: 8px 12px;
                font-size: 0.85rem;
                min-width: 100px;
            }

            table {
                font-size: 0.8rem;
                min-width: 720px;
            }

            th,
            td {
                padding: 8px 6px;
                white-space: nowrap;
            }

            .btn-action {
                padding: 4px 6px;
                font-size: 0.75rem;
            }

            .form-group label {
                font-size: 0.9rem;
            }

            .form-group input,
            .form-group select {
                padding: 10px 12px;
                font-size: 0.95rem;
            }
        }

        /* Contact Input Styling */
        .contact-input-group {
            display: flex;
            align-items: stretch;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            background: #f9fafb;
            transition: all 0.2s;
        }

        .contact-input-group:focus-within {
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 4px var(--primary-shadow);
        }

        .contact-prefix {
            background: #f1f5f9;
            padding: 10px 16px;
            color: #475569;
            font-weight: 600;
            border-right: 1px solid #e2e8f0;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }

        .contact-input-group input {
            border: none !important;
            box-shadow: none !important;
            padding: 10px 16px !important;
            background: transparent !important;
            width: 100%;
            font-size: 0.95rem;
        }

        .field-help {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 6px;
        }

        /* Pagination styles are inherited from shared admin-styles */
        .btn-reveal-pii {
            background: none;
            border: none;
            padding: 0;
            color: var(--primary-color);
            cursor: pointer;
            opacity: 0.6;
            transition: all 0.2s;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-reveal-pii:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        .btn-reveal-pii.timer-active {
            color: #94a3b8;
            cursor: wait;
        }

        .masked-pii {
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .pii-wrapper {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 24px;
        }

        .pii-indicator {
            font-size: 0.75rem;
            color: #6b7280;
            margin-left: 4px;
            cursor: help;
        }

        /* Improved Table Spacing */
        #usersTable th,
        #usersTable td {
            vertical-align: middle !important;
            padding: 16px 12px;
        }

        #usersTable thead th {
            white-space: nowrap;
        }

        .availability-indicator {
            position: relative;
            display: inline-block;
        }

        .availability-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            position: absolute;
            top: -4px;
            left: -14px;
            border: 2px solid white;
            box-shadow: 0 1px 4px rgba(0,0,0,0.15);
        }

        .availability-dot.available {
            background-color: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        }

        .availability-dot.unavailable {
            background-color: #94a3b8;
        }

        /* License Badges */
        .license-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .license-badge.pending {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .license-badge.verified {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .license-badge.rejected {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .license-badge.none {
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }
    </style>

    <div class="admin-mgmt-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-left">
                <h1 class="page-title">User Management</h1>
                <p class="page-subtitle">Manage students and instructors in your driving school</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total {{ (($activeRoleFilter ?? 'all') === 'all' && ($activeStatusFilter ?? 'all') === 'all') ? 'filter-selected' : '' }}"
                onclick="applyQuickFilter('all')">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Total Users</div>
                            <div class="stat-value">{{ $totalUsers }}</div>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-detail"><strong>{{ $totalStudents }}</strong> Students &middot;
                        <strong>{{ $totalInstructors }}</strong> Instructors
                    </div>
                </div>
            </div>

            <div class="stat-card active {{ (($activeRoleFilter ?? 'all') === 'all' && ($activeStatusFilter ?? 'all') === 'active') ? 'filter-selected' : '' }}"
                onclick="applyQuickFilter('active')">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Active Users</div>
                            <div class="stat-value">{{ $totalActive }}</div>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-detail"><strong>{{ $activeStudents }}</strong> Students &middot;
                        <strong>{{ $activeInstructors }}</strong> Instructors
                    </div>
                </div>
            </div>

            <div class="stat-card inactive {{ (($activeRoleFilter ?? 'all') === 'all' && ($activeStatusFilter ?? 'all') === 'inactive') ? 'filter-selected' : '' }}"
                onclick="applyQuickFilter('inactive')">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Inactive Users</div>
                            <div class="stat-value">{{ $totalInactive }}</div>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-detail"><strong>{{ $inactiveStudents }}</strong> Students &middot;
                        <strong>{{ $inactiveInstructors }}</strong> Instructors
                    </div>
                </div>
            </div>

            <div class="stat-card students {{ (($activeRoleFilter ?? 'all') === 'student' && ($activeStatusFilter ?? 'all') === 'all') ? 'filter-selected' : '' }}"
                onclick="applyQuickFilter('students')">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Students</div>
                            <div class="stat-value">{{ $totalStudents }}</div>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-detail"><strong>{{ $activeStudents }}</strong> Active &middot;
                        <strong>{{ $inactiveStudents }}</strong> Inactive
                    </div>
                </div>
            </div>

            <div class="stat-card instructors {{ (($activeRoleFilter ?? 'all') === 'instructor' && ($activeStatusFilter ?? 'all') === 'all') ? 'filter-selected' : '' }}"
                onclick="applyQuickFilter('instructors')">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Instructors</div>
                            <div class="stat-value">{{ $totalInstructors }}</div>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-detail"><strong>{{ $activeInstructors }}</strong> Active &middot;
                        <strong>{{ $inactiveInstructors }}</strong> Inactive
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notifications handled at the bottom of the file -->

        <!-- Students Section -->
        <div id="usersSection" class="user-section">
            <div class="action-bar">
                <div class="search-box">
                    <label for="userSearch" class="control-label">Search Users</label>
                    <input type="text" id="userSearch" value="{{ $activeSearch ?? '' }}"
                        placeholder="Search users by name, email, or role...">
                </div>
                <div class="action-controls">
                    @if(isset($branches) && $branches->count() > 0)
                        <label for="branchFilter" class="control-label">Branch Filter</label>
                        <select id="branchFilter" onchange="applyBranchFilter()"
                            style="padding: 10px 14px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem; background: white; cursor: pointer;">
                            <option value="all" {{ ($activeBranchFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Branches
                            </option>
                            <option value="unassigned" {{ ($activeBranchFilter ?? 'all') === 'unassigned' ? 'selected' : '' }}>
                                Unassigned</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) ($activeBranchFilter ?? 'all') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <div class="export-dropdown" id="exportDropdown">
                        <button class="btn-export-trigger" onclick="toggleExportDropdown()">
                            <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export
                            <svg class="chevron icon-14" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="export-dropdown-menu">
                            <div class="dropdown-header">Students</div>
                            <a href="{{ school_route('admin.exports.students.pdf', ['branch' => request('branch', 'all')]) }}">
                                <span class="dot pdf"></span> Students (PDF)
                            </a>
                            <a href="{{ school_route('admin.exports.students.excel', ['branch' => request('branch', 'all')]) }}">
                                <span class="dot excel"></span> Students (Excel)
                            </a>
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-header">Instructors</div>
                            <a href="{{ school_route('admin.exports.instructors.pdf') }}">
                                <span class="dot pdf"></span> Instructors (PDF)
                            </a>
                            <a href="{{ school_route('admin.exports.instructors.excel') }}">
                                <span class="dot excel"></span> Instructors (Excel)
                            </a>
                        </div>
                    </div>
                    <button class="btn-create" onclick="openCreateStudentModal()">
                        <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Add Student
                    </button>
                    <button class="btn-create" onclick="openCreateInstructorModal()">
                        <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Add Instructor
                    </button>
                </div>
            </div>

            <div class="table-container">
                @if($users->count() > 0)
                    <table id="usersTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>
                                    Email
                                    @if($settings->enable_pii_masking ?? false)
                                        <i class="bi bi-shield-lock pii-indicator"
                                            title="Privacy Protected (PII Masking Active)"></i>
                                    @endif
                                </th>
                                <th>
                                    Contact
                                    @if($settings->enable_pii_masking ?? false)
                                        <i class="bi bi-shield-lock pii-indicator"
                                            title="Privacy Protected (PII Masking Active)"></i>
                                    @endif
                                </th>
                                <th>Role</th>
                                <th>Branch</th>
                                <th>License</th>
                                <th>Status</th>
                                <th>Portal Activity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr data-role="{{ $user->role }}" data-status="{{ $user->status }}"
                                    data-branch="{{ $user->branch_id ?? 'unassigned' }}">
                                    <td>
                                        <div class="availability-indicator" style="{{ $user->role === 'instructor' ? 'margin-left: 16px;' : '' }}">
                                            @if($user->role === 'instructor')
                                                <span class="availability-dot {{ ($user->availability ?? 'available') === 'available' ? 'available' : 'unavailable' }}" 
                                                      title="{{ ($user->availability ?? 'available') === 'available' ? 'Available' : 'Unavailable' }}"></span>
                                            @endif
                                            <strong>{{ $user->name }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        @if(($settings->enable_pii_masking ?? false) && $user->email)
                                            @php
                                                $emailParts = explode('@', $user->email);
                                                $maskedEmail = substr($emailParts[0], 0, 2) . str_repeat('*', max(0, strlen($emailParts[0]) - 2)) . '@' . $emailParts[1];
                                            @endphp
                                            <div class="pii-wrapper">
                                                <span class="masked-pii" data-full="{{ $user->email }}">{{ $maskedEmail }}</span>
                                                <button type="button" class="btn-reveal-pii" onclick="revealPII(this)"
                                                    title="Click to reveal (5s)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        @else
                                            {{ $user->email }}
                                        @endif
                                    </td>
                                    <td>
                                        @if(($settings->enable_pii_masking ?? false) && $user->contact)
                                            @php
                                                $contactValue = $user->contact;
                                                $maskedContact = substr($contactValue, 0, 4) . '****' . substr($contactValue, -2);
                                            @endphp
                                            <div class="pii-wrapper">
                                                <span class="masked-pii" data-full="{{ $contactValue }}">{{ $maskedContact }}</span>
                                                <button type="button" class="btn-reveal-pii" onclick="revealPII(this)"
                                                    title="Click to reveal (5s)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        @else
                                            {{ $user->contact ?: '—' }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="role-badge role-{{ $user->role }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $branchName = $user->branch_id ? ($branches->firstWhere('id', $user->branch_id)?->name ?? null) : null;
                                        @endphp
                                        <span
                                            class="branch-label {{ $branchName ? 'branch-assigned' : 'branch-unassigned' }}">{{ $branchName ?? '—' }}</span>
                                    </td>
                                    <td>
                                        @if($user->role === 'instructor')
                                            @php
                                                $lStatus = $user->license_status ?? 'none';
                                                $lColors = [
                                                    'none' => 'bg-secondary',
                                                    'pending' => 'bg-warning text-dark',
                                                    'verified' => 'bg-success',
                                                    'rejected' => 'bg-danger'
                                                ];
                                                $lLabels = [
                                                    'none' => 'None',
                                                    'pending' => 'Pending',
                                                    'verified' => 'Verified',
                                                    'rejected' => 'Rejected'
                                                ];
                                            @endphp
                                            <div class="d-flex flex-column gap-1">
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    <span class="license-badge {{ $lStatus }}">
                                                        {{ $lLabels[$lStatus] }}
                                                    </span>
                                                    @if(($lStatus === 'pending' || $lStatus === 'none' || $lStatus === 'rejected') && $user->license_image)
                                                        <button type="button" class="btn-action btn-success js-verify-license"
                                                           style="padding: 4px 10px; font-size: 0.75rem;"
                                                           data-id="{{ $user->id }}" 
                                                           data-role="instructor"
                                                           data-name="{{ $user->name }}"
                                                           data-email="{{ $user->email }}"
                                                           data-contact="{{ $user->contact }}"
                                                           data-address="{{ $user->address }}"
                                                           data-branch="{{ $branches->find($user->branch_id)->name ?? 'Not Assigned' }}"
                                                           data-status="{{ $user->status }}"
                                                           data-license-number="{{ $user->license_number }}"
                                                           data-license-image="{{ asset('storage/' . $user->license_image) }}"
                                                           data-license-status="{{ $user->license_status }}"
                                                           data-restrictions="{{ json_encode($user->restriction_codes ?? []) }}"
                                                           data-rejection-reason="{{ $user->license_rejection_reason }}"
                                                           data-last-login="{{ ($user->last_login_at ?? null) ? \Illuminate\Support\Carbon::parse($user->last_login_at)->diffForHumans() : 'Never' }}"
                                                           data-last-logout="{{ ($user->last_logout_at ?? null) ? \Illuminate\Support\Carbon::parse($user->last_logout_at)->diffForHumans() : 'Never' }}">
                                                           <i class="bi bi-shield-check me-1"></i> Verify
                                                        </button>
                                                    @endif
                                                </div>
                                                @if($lStatus === 'verified' && !empty($user->restriction_codes))
                                                    <div class="d-flex flex-wrap gap-1 mt-1" style="max-width: 120px;">
                                                        @foreach($user->restriction_codes as $code)
                                                            <span class="badge bg-primary" style="font-size: 0.65rem;">{{ $code }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $user->status }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.82rem; line-height: 1.4; color: #4b5563;">
                                            <div>
                                                <span style="font-weight: 600; color: #1f2937;">In:</span>
                                                {{ ($user->last_login_at ?? null) ? \Illuminate\Support\Carbon::parse($user->last_login_at)->diffForHumans() : 'Never' }}
                                            </div>
                                            <div>
                                                <span style="font-weight: 600; color: #1f2937;">Out:</span>
                                                {{ ($user->last_logout_at ?? null) ? \Illuminate\Support\Carbon::parse($user->last_logout_at)->diffForHumans() : 'Never' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="actions-cell">
                                        <div class="action-buttons">
                                            {{-- Global View Button --}}
                                            @if($user->role === 'student' || $user->role === 'guest')
                                                <button type="button" class="btn-action btn-primary js-view-user" 
                                                    data-id="{{ $user->id }}" 
                                                    data-role="student"
                                                    data-name="{{ $user->name }}"
                                                    data-email="{{ $user->email }}"
                                                    data-contact="{{ $user->contact }}"
                                                    data-address="{{ $user->address }}"
                                                    data-branch="{{ $branches->find($user->branch_id)->name ?? 'Not Assigned' }}"
                                                    data-status="{{ $user->status }}"
                                                    data-profile-picture="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : '' }}"
                                                    data-last-login="{{ ($user->last_login_at ?? null) ? \Illuminate\Support\Carbon::parse($user->last_login_at)->diffForHumans() : 'Never' }}"
                                                    data-last-logout="{{ ($user->last_logout_at ?? null) ? \Illuminate\Support\Carbon::parse($user->last_logout_at)->diffForHumans() : 'Never' }}"
                                                    title="View Student">
                                                    <i class="bi bi-eye"></i>
                                                    <span>View</span>
                                                </button>
                                            @elseif($user->role === 'instructor')
                                                <button type="button" class="btn-action btn-primary js-view-user" 
                                                    data-id="{{ $user->id }}" 
                                                    data-role="instructor"
                                                    data-name="{{ $user->name }}"
                                                    data-email="{{ $user->email }}"
                                                    data-contact="{{ $user->contact }}"
                                                    data-address="{{ $user->address }}"
                                                    data-branch="{{ $branches->find($user->branch_id)->name ?? 'Not Assigned' }}"
                                                    data-status="{{ $user->status }}"
                                                    data-license="{{ $user->license_number }}"
                                                    data-license-image="{{ $user->license_image ? asset('storage/' . $user->license_image) : '' }}"
                                                    data-license-status="{{ $user->license_status }}"
                                                    data-restrictions="{{ json_encode($user->restriction_codes ?? []) }}"
                                                    data-specializations="{{ json_encode($user->course_specializations ?? []) }}"
                                                    data-availability="{{ $user->availability ?? 'available' }}"
                                                    data-rejection-reason="{{ $user->license_rejection_reason }}"
                                                    data-profile-picture="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : '' }}"
                                                    data-last-login="{{ ($user->last_login_at ?? null) ? \Illuminate\Support\Carbon::parse($user->last_login_at)->diffForHumans() : 'Never' }}"
                                                    data-last-logout="{{ ($user->last_logout_at ?? null) ? \Illuminate\Support\Carbon::parse($user->last_logout_at)->diffForHumans() : 'Never' }}"
                                                    title="View Instructor">
                                                    <i class="bi bi-eye"></i>
                                                    <span>View</span>
                                                </button>
@endif

                                            {{-- Global Edit Button --}}
                                            @if($user->role === 'student' || $user->role === 'guest')
                                                <button type="button" class="btn-action btn-info js-edit-user" data-role="student"
                                                    data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                                    data-email="{{ $user->email }}" data-contact="{{ $user->contact }}"
                                                    data-address="{{ $user->address }}" data-branch="{{ $user->branch_id }}"
                                                    title="Edit Student">
                                                    <i class="bi bi-pencil-square"></i>
                                                    <span>Edit</span>
                                                </button>
                                            @elseif($user->role === 'instructor')
                                                <button type="button" class="btn-action btn-info js-edit-user" data-role="instructor"
                                                    data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                                    data-email="{{ $user->email }}" data-contact="{{ $user->contact }}"
                                                    data-license="{{ $user->license_number }}" data-address="{{ $user->address }}"
                                                    data-availability="{{ $user->availability }}" data-branch="{{ $user->branch_id }}"
                                                    data-specializations="{{ json_encode($user->course_specializations ?? []) }}"
                                                    title="Edit Instructor">
                                                    <i class="bi bi-pencil-square"></i>
                                                    <span>Edit</span>
                                                </button>

                                                {{-- Verify button moved to License column --}}
                                            @endif

                                            {{-- Global Status Toggle --}}
                                            @php
                                                $toggleRoute = '';
                                                if ($user->role === 'student' || $user->role === 'guest') {
                                                    $toggleRoute = route('schools.admin.students.toggleStatus', [$school, $user->id]);
                                                } elseif ($user->role === 'instructor') {
                                                    $toggleRoute = route('schools.admin.instructors.toggleStatus', [$school, $user->id]);
                                                }
                                            @endphp

                                            @if($toggleRoute)
                                                <button type="button"
                                                    class="btn-action {{ $user->status === 'active' ? 'btn-warning' : 'btn-success' }}"
                                                    onclick="unifiedToggle({{ $user->id }}, 'status', '{{ $user->status }}', '{{ $user->role }}')"
                                                    title="{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                                    <i
                                                        class="bi {{ $user->status === 'active' ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                                    <span>{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}</span>
                                                </button>
                                            @endif

                                            {{-- Instructor Specific Availability --}}
                                            @if($user->role === 'instructor')
                                                <button type="button"
                                                    class="btn-action {{ $user->availability === 'available' ? 'btn-secondary' : 'btn-primary' }}"
                                                    onclick="unifiedToggle({{ $user->id }}, 'availability', '{{ $user->availability }}', '{{ $user->role }}')"
                                                    title="{{ $user->availability === 'available' ? 'Mark Unavailable' : 'Mark Available' }}">
                                                    <i
                                                        class="bi {{ $user->availability === 'available' ? 'bi-person-dash' : 'bi-person-check' }}"></i>
                                                    <span>{{ $user->availability === 'available' ? 'Unavailable' : 'Available' }}</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon"></div>
                        <div class="empty-state-text">No users found. Add your first student or instructor to get started!</div>
                    </div>
                @endif
            </div>

            @if($users->count() > 0)
                <div class="mt-4 pagination-groups">
                    <div>
                        <h4 style="font-size: 0.9rem; margin-bottom: 5px; color: #6b7280;">
                            Showing {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                        </h4>
                        @if($users->hasPages())
                            <div class="admin-pagination-wrapper">
                                {!! $users->links('vendor.pagination.drivingapp') !!}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- INVITE STUDENT MODAL -->
    <div id="createStudentModal" class="modal">
        <div class="modal-content" style="width: min(600px, 95%);">
            <div class="modal-header">
                <h3><i class="bi bi-person-plus me-2"></i>Invite New Student</h3>
                <button type="button" class="btn-close-modal" onclick="closeCreateStudentModal()">×</button>
            </div>
            <form id="createStudentInviteForm" method="POST" action="{{ school_route('admin.storeAccount') }}"
                data-no-ajax="1">
                @csrf
                <div class="modal-body">
                    <p class="modal-required-note">Fields marked with <span class="required-indicator">*</span> are
                        required.</p>

                    @if($showStudentInviteErrors)
                        <div class="modal-error-summary" role="alert">
                            <strong>Please fix the following:</strong>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Name <span class="required-indicator">*</span></label>
                        <input type="text" name="name" value="{{ $showStudentInviteErrors ? old('name') : '' }}"
                            placeholder="Enter student's full name" required
                            class="{{ $showStudentInviteErrors && $errors->has('name') ? 'is-invalid' : '' }}">
                    </div>
                    <div class="form-group">
                        <label>Email <span class="required-indicator">*</span></label>
                        <input type="email" name="email" value="{{ $showStudentInviteErrors ? old('email') : '' }}"
                            placeholder="student@example.com" required
                            class="{{ $showStudentInviteErrors && $errors->has('email') ? 'is-invalid' : '' }}">
                        <p class="field-help">An account setup link will be sent to this email address.</p>
                    </div>
                    <div class="form-group">
                        <label>Contact <span class="required-indicator">*</span></label>
                        <div class="contact-input-group">
                            <span class="contact-prefix">+63</span>
                            <input type="text" name="contact" value="{{ $showStudentInviteErrors ? old('contact') : '' }}"
                                placeholder="9123456789" required maxlength="10"
                                class="{{ $showStudentInviteErrors && $errors->has('contact') ? 'is-invalid' : '' }}">
                        </div>
                        <p class="field-help">Enter the 10-digit number after +63 (e.g., 9123456789).</p>
                    </div>
                    <div class="form-group">
                        <label>Address <span class="required-indicator">*</span></label>
                        <input type="text" name="address" value="{{ $showStudentInviteErrors ? old('address') : '' }}"
                            placeholder="Enter full address" required
                            class="{{ $showStudentInviteErrors && $errors->has('address') ? 'is-invalid' : '' }}">
                    </div>
                    @if(isset($branches) && $branches->count() > 0)
                        <div class="form-group">
                            <label>Branch <span class="required-indicator">*</span></label>
                            <select name="branch_id" class="branch-modal-select" required>
                                <option value="" disabled selected>Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $showStudentInviteErrors && (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="alert alert-warning" style="margin-bottom: 20px; font-size: 0.9rem;">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>No branches available.</strong> Please <a href="{{ school_route('admin.branches.index') }}"
                                class="fw-bold">create a branch</a> first to add users.
                        </div>
                    @endif

                    @if(isset($courses) && $courses->count() > 0)
                        <div class="form-group">
                            <label>Assigned Course <span class="required-indicator">*</span></label>
                            <select name="course_id" class="branch-modal-select" required>
                                <option value="" disabled selected>Select Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ $showStudentInviteErrors && (string) old('course_id') === (string) $course->id ? 'selected' : '' }}>
                                        {{ $course->title }} ({{ ucfirst($course->course_type) }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="field-help">Student will be automatically enrolled upon registration.</p>
                        </div>
                    @else
                        <div class="alert alert-warning" style="margin-bottom: 20px; font-size: 0.9rem;">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>No active courses available.</strong> Please <a
                                href="{{ school_route('admin.courses.index') }}" class="fw-bold">create a course</a> first to
                            add students.
                        </div>
                    @endif
                    <input type="hidden" name="role" value="student">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateStudentModal()"
                        style="background: #64748b; color: white; border: none; height: 42px; padding: 0 20px; border-radius: 10px; font-weight: 600;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary"
                        style="background: var(--btn-gradient); color: var(--btn-primary-text); border: none; height: 42px; padding: 0 20px; border-radius: 10px; font-weight: 600;"
                        {{ (!isset($branches) || $branches->count() == 0 || !isset($courses) || $courses->count() == 0) ? 'disabled' : '' }}>
                        Add Student
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT STUDENT MODAL -->
    <div id="editStudentModal" class="modal">
        <div class="modal-content" style="width: min(600px, 95%);">
            <div class="modal-header">
                <h3><i class="bi bi-pencil-square me-2"></i>Edit Student</h3>
                <button type="button" class="btn-close-modal" onclick="closeEditStudentModal()">×</button>
            </div>
            <form id="editStudentForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name <span class="required-indicator">*</span></label>
                        <input type="text" id="edit_student_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Email <span class="required-indicator">*</span></label>
                        <input type="email" id="edit_student_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Contact <span class="required-indicator">*</span></label>
                        <div class="contact-input-group">
                            <span class="contact-prefix">+63</span>
                            <input type="text" id="edit_student_contact" name="contact" required maxlength="10"
                                placeholder="9123456789">
                        </div>
                        <p class="field-help">Enter the 10-digit number after +63 (e.g., 9123456789).</p>
                    </div>
                    <div class="form-group">
                        <label>Address <span class="required-indicator">*</span></label>
                        <input type="text" id="edit_student_address" name="address" required>
                    </div>
                    @if(isset($branches) && $branches->count() > 0)
                        <div class="form-group">
                            <label>Branch <span class="required-indicator">*</span></label>
                            <select id="edit_student_branch" name="branch_id" class="branch-modal-select" required>
                                <option value="" disabled>Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditStudentModal()"
                        style="background: #64748b; color: white; border: none; height: 42px; padding: 0 20px; border-radius: 10px; font-weight: 600;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary"
                        style="background: var(--header-gradient); color: white; border: none; height: 42px; padding: 0 20px; border-radius: 10px; font-weight: 600;">
                        Update Student
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ADD INSTRUCTOR MODAL -->
    <div id="createInstructorModal" class="modal">
        <div class="modal-content" style="width: min(600px, 95%);">
            <div class="modal-header">
                <h3><i class="bi bi-person-plus me-2"></i>Add New Instructor</h3>
                <button type="button" class="btn-close-modal" onclick="closeCreateInstructorModal()">×</button>
            </div>
            <form id="createInstructorInviteForm" method="POST" action="{{ school_route('admin.storeAccount') }}"
                data-no-ajax="1" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="modal-required-note">Fields marked with <span class="required-indicator">*</span> are
                        required.</p>

                    @if($showInstructorInviteErrors)
                        <div class="modal-error-summary" role="alert">
                            <strong>Please fix the following:</strong>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Name <span class="required-indicator">*</span></label>
                        <input type="text" name="name" value="{{ $showInstructorInviteErrors ? old('name') : '' }}"
                            placeholder="Enter instructor's full name" required
                            class="{{ $showInstructorInviteErrors && $errors->has('name') ? 'is-invalid' : '' }}">
                    </div>
                    <div class="form-group">
                        <label>Email <span class="required-indicator">*</span></label>
                        <input type="email" name="email" value="{{ $showInstructorInviteErrors ? old('email') : '' }}"
                            placeholder="instructor@example.com" required
                            class="{{ $showInstructorInviteErrors && $errors->has('email') ? 'is-invalid' : '' }}">
                        <p class="field-help">An account setup link will be sent to this email address.</p>
                    </div>
                    <div class="form-group">
                        <label>Contact <span class="required-indicator">*</span></label>
                        <div class="contact-input-group">
                            <span class="contact-prefix">+63</span>
                            <input type="text" name="contact"
                                value="{{ $showInstructorInviteErrors ? old('contact') : '' }}" placeholder="9123456789"
                                required maxlength="10"
                                class="{{ $showInstructorInviteErrors && $errors->has('contact') ? 'is-invalid' : '' }}">
                        </div>
                        <p class="field-help">Enter the 10-digit number after +63 (e.g., 9123456789).</p>
                    </div>
                    <div class="form-group">
                        <label>License Number <span class="required-indicator">*</span></label>
                        <input type="text" name="license_number"
                            value="{{ $showInstructorInviteErrors ? old('license_number') : '' }}"
                            placeholder="Enter license number" required
                            class="{{ $showInstructorInviteErrors && $errors->has('license_number') ? 'is-invalid' : '' }}">
                    </div>
                    <div class="form-group">
                        <label>Address <span class="required-indicator">*</span></label>
                        <input type="text" name="address" value="{{ $showInstructorInviteErrors ? old('address') : '' }}"
                            placeholder="Enter full address" required
                            class="{{ $showInstructorInviteErrors && $errors->has('address') ? 'is-invalid' : '' }}">
                    </div>
                    <div class="form-group">
                        <label>License Image <span class="required-indicator">*</span></label>
                        <input type="file" name="license_image" accept="image/*" required
                            class="{{ $showInstructorInviteErrors && $errors->has('license_image') ? 'is-invalid' : '' }}">
                        <p class="field-help">Upload a photo of the instructor's license.</p>
                    </div>
                    @if(isset($branches) && $branches->count() > 0)
                        <div class="form-group">
                            <label>Branch <span class="required-indicator">*</span></label>
                            <select name="branch_id" class="branch-modal-select" required>
                                <option value="" disabled selected>Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $showInstructorInviteErrors && (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="alert alert-warning" style="margin-bottom: 20px; font-size: 0.9rem;">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>No branches available.</strong> Please <a href="{{ school_route('admin.branches.index') }}"
                                class="fw-bold">create a branch</a> first to add instructors.
                        </div>
                    @endif
                    {{-- Course Specializations hidden: does not affect scheduling (LTO restriction codes are the hard gate) --}}
                    <div style="display: none;">
                    @if(isset($courses) && $courses->count() > 0)
                        <div class="form-group">
                            <label>Course Specializations <span class="required-indicator">*</span></label>
                            <div
                                style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; background: #f8fafc;">
                                @foreach($courses as $course)
                                    <label
                                        style="display: flex; align-items: center; gap: 8px; margin-bottom: 0; cursor: pointer; font-weight: normal; font-size: 0.9rem;">
                                        <input type="checkbox" name="course_specializations[]" value="{{ $course->id }}"
                                            style="width: auto;">
                                        {{ $course->title }}
                                    </label>
                                @endforeach
                            </div>
                            <p class="field-help">Select courses this instructor is qualified to teach.</p>
                        </div>
                    @else
                        <div class="alert alert-warning" style="margin-bottom: 20px; font-size: 0.9rem;">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>No active courses available.</strong> Please <a
                                href="{{ school_route('admin.courses.index') }}" class="fw-bold">create a course</a> first to
                            assign specializations.
                        </div>
                    @endif
                    </div>
                    <input type="hidden" name="role" value="instructor">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateInstructorModal()"
                        style="background: #64748b; color: white; border: none; height: 42px; padding: 0 20px; border-radius: 10px; font-weight: 600;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary"
                        style="background: var(--btn-gradient); color: var(--btn-primary-text); border: none; height: 42px; padding: 0 20px; border-radius: 10px; font-weight: 600;"
                        {{ (!isset($branches) || $branches->count() == 0 || !isset($courses) || $courses->count() == 0) ? 'disabled' : '' }}>
                        Add Instructor
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT INSTRUCTOR MODAL -->
    <div id="editInstructorModal" class="modal">
        <div class="modal-content" style="width: min(600px, 95%);">
            <div class="modal-header">
                <h3><i class="bi bi-pencil-square me-2"></i>Edit Instructor</h3>
                <button type="button" class="btn-close-modal" onclick="closeEditInstructorModal()">×</button>
            </div>
            <form id="editInstructorForm" method="POST" action="" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name <span class="required-indicator">*</span></label>
                        <input type="text" id="edit_instructor_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Email <span class="required-indicator">*</span></label>
                        <input type="email" id="edit_instructor_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Contact <span class="required-indicator">*</span></label>
                        <div class="contact-input-group">
                            <span class="contact-prefix">+63</span>
                            <input type="text" id="edit_instructor_contact" name="contact" required maxlength="10"
                                placeholder="9123456789">
                        </div>
                        <p class="field-help">Enter the 10-digit number after +63 (e.g., 9123456789).</p>
                    </div>
                    <div class="form-group">
                        <label>License Number <span class="required-indicator">*</span></label>
                        <input type="text" id="edit_instructor_license" name="license_number" required>
                    </div>
                    <div class="form-group">
                        <label>Address <span class="required-indicator">*</span></label>
                        <input type="text" id="edit_instructor_address" name="address" required>
                    </div>
                    <div class="form-group">
                        <label>Availability <span class="required-indicator">*</span></label>
                        <select id="edit_instructor_availability" name="availability" required>
                            <option value="available">Available (Visible for Bookings)</option>
                            <option value="unavailable">Unavailable (Hidden from Bookings)</option>
                        </select>
                        <p class="field-help">Set if this instructor is currently available for session assignments.</p>
                    </div>
                    <div class="form-group">
                        <label>License Image <span class="required-indicator">*</span></label>
                        <input type="file" name="license_image" accept="image/*">
                        <p class="field-help">Upload a photo of the instructor's license.</p>
                    </div>
                    {{-- Course Specializations hidden: does not affect scheduling (LTO restriction codes are the hard gate) --}}
                    <div style="display: none;">
                    @if(isset($courses) && $courses->count() > 0)
                        <div class="form-group">
                            <label>Course Specializations <span class="required-indicator">*</span></label>
                            <div id="edit_instructor_specializations"
                                style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; background: #f8fafc;">
                                @foreach($courses as $course)
                                    <label
                                        style="display: flex; align-items: center; gap: 8px; margin-bottom: 0; cursor: pointer; font-weight: normal; font-size: 0.9rem;">
                                        <input type="checkbox" name="course_specializations[]" value="{{ $course->id }}"
                                            style="width: auto;">
                                        {{ $course->title }}
                                    </label>
                                @endforeach
                            </div>
                            <p class="field-help">Select courses this instructor is qualified to teach.</p>
                        </div>
                    @endif
                    </div>
                    @if(isset($branches) && $branches->count() > 0)
                        <div class="form-group">
                            <label>Branch <span class="required-indicator">*</span></label>
                            <select id="edit_instructor_branch" name="branch_id" class="branch-modal-select" required>
                                <option value="" disabled>Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditInstructorModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Instructor</button>
                </div>
            </form>
        </div>
    </div>


    <script>
        window.piiMaskingEnabled = {{ ($settings->enable_pii_masking ?? false) ? 'true' : 'false' }};
        window.schoolSlug = '{{ $school->slug }}';
        window.studentBaseUrl = '/{{ $school->slug }}/admin/students';
        window.instructorBaseUrl = '/{{ $school->slug }}/admin/instructors';
        window.courseTitles = {!! isset($courses) ? json_encode($courses->pluck('title', 'id')) : '{}' !!};
        window.__userMgmtInitialFilterState = {
            search: @json($activeSearch ?? ''),
            status: @json($activeStatusFilter ?? 'all'),
            role: @json($activeRoleFilter ?? 'all'),
            branch: @json((string) ($activeBranchFilter ?? 'all'))
        };

        function hardenUserManagementActionForms() {
            const actionForms = document.querySelectorAll('form[data-no-ajax][action*="/toggle-status"], form[data-no-ajax][action*="/availability"]');
            if (!actionForms.length) {
                return;
            }

            let pageToken = '';

            actionForms.forEach(form => {
                form.classList.add('native-form');
                form.setAttribute('data-no-ajax', '1');
                form.setAttribute('data-no-submit-guard', '1');

                const tokenInput = form.querySelector('input[name="_token"]');
                if (!pageToken && tokenInput && tokenInput.value) {
                    pageToken = tokenInput.value;
                }
            });

            if (pageToken) {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                if (csrfMeta && csrfMeta.getAttribute('content') !== pageToken) {
                    csrfMeta.setAttribute('content', pageToken);
                }
            }
        }

        // Initialize user management page
        function initializeUserManagementPage() {
            console.log('User Management: Initializing...');
            try {
                hardenUserManagementActionForms();
                
                // Prioritize search binding
                bindUserSearchEvents();
                
                const searchInput = document.getElementById('userSearch');
                if (searchInput && searchInput.value) {
                    applyLocalUserTableSearch(searchInput.value);
                }

                // Auto-strip leading zero from contact inputs in modals
                // (Consolidated with global delegation at the end of the script)
                console.log('User Management: Initialized successfully');
            } catch (err) {
                console.error('User Management: Initialization error:', err);
            }
        }

        // Robust initialization that works for both full-page loads and AJAX partials
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeUserManagementPage);
        } else {
            // If already loaded (AJAX), give a tiny breather for the DOM to be ready
            setTimeout(initializeUserManagementPage, 10);
        }

        function getUserFilterState() {
            const params = new URLSearchParams(window.location.search);
            const initialUserFilterState = window.__userMgmtInitialFilterState || {};
            const searchInput = document.getElementById('userSearch');
            const liveSearch = searchInput ? searchInput.value : null;
            return {
                search: (liveSearch !== null ? liveSearch : (params.get('search') || initialUserFilterState.search || '')),
                status: params.get('status') || initialUserFilterState.status || 'all',
                role: params.get('role') || initialUserFilterState.role || 'all',
                branch: params.get('branch') || initialUserFilterState.branch || 'all'
            };
        }

        function buildUserManagementUrl(filters, resetPage = true) {
            const merged = Object.assign({}, getUserFilterState(), filters || {});
            const url = new URL(window.location.pathname, window.location.origin);

            const search = (merged.search || '').trim();
            if (search) {
                url.searchParams.set('search', search);
            }

            if (merged.status && merged.status !== 'all') {
                url.searchParams.set('status', merged.status);
            }

            if (merged.role && merged.role !== 'all') {
                url.searchParams.set('role', merged.role);
            }

            if (merged.branch && merged.branch !== 'all') {
                url.searchParams.set('branch', merged.branch);
            }

            if (!resetPage) {
                const currentPage = new URLSearchParams(window.location.search).get('page');
                if (currentPage) {
                    url.searchParams.set('page', currentPage);
                }
            }

            return url;
        }

        function navigateWithUserFilters(nextFilters, resetPage = true) {
            const targetUrl = buildUserManagementUrl(nextFilters, resetPage);
            const target = targetUrl.pathname + targetUrl.search;
            if (typeof loadContent === 'function') {
                loadContent(target);
                return;
            }

            window.location.href = target;
        }

        function applyQuickFilter(type) {
            let role = 'all';
            let status = 'all';

            if (type === 'students') {
                role = 'student';
            } else if (type === 'instructors') {
                role = 'instructor';
            } else if (type === 'active') {
                status = 'active';
            } else if (type === 'inactive') {
                status = 'inactive';
            }

            navigateWithUserFilters({ role, status }, true);
        }

        function applyBranchFilter() {
            const select = document.getElementById('branchFilter');
            if (!select) {
                return;
            }

            navigateWithUserFilters({ branch: select.value || 'all' }, true);
        }

        function bindUserSearchEvents() {
            // Use event delegation on the document to ensure search works even if injected via AJAX
            if (window._userSearchBound) return;
            window._userSearchBound = true;

            document.addEventListener('keydown', function(event) {
                if (event.target && event.target.id === 'userSearch') {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        const val = event.target.value.trim();
                        // Trigger server-side search on Enter
                        navigateWithUserFilters({ search: val });
                    }
                }
            });

            document.addEventListener('input', function(event) {
                if (event.target && event.target.id === 'userSearch') {
                    applyLocalUserTableSearch(event.target.value || '');
                }
            });
            
            console.log('User search events bound (Delegated)');
        }

        function applyLocalUserTableSearch(rawValue) {
            const table = document.getElementById('usersTable');
            if (!table) return;

            const tbody = table.querySelector('tbody');
            if (!tbody) return;

            const query = (rawValue || '').trim().toLowerCase();
            const rows = Array.from(tbody.querySelectorAll('tr')).filter(function (row) {
                return row.id !== 'userSearchNoResultRow';
            });
            const columnCount = table.querySelectorAll('thead th').length || 7;

            let visibleCount = 0;
            rows.forEach(function (row) {
                const cells = row.querySelectorAll('td');
                if (cells.length < 4) return;

                // For Name, search the text content
                const name = (cells[0].textContent || '').toLowerCase();
                
                // For Email and Contact, check for masked-pii spans first to get the full value
                const emailSpan = cells[1].querySelector('.masked-pii');
                const email = (emailSpan ? emailSpan.dataset.full : cells[1].textContent || '').toLowerCase();
                
                const contactSpan = cells[2].querySelector('.masked-pii');
                const contact = (contactSpan ? contactSpan.dataset.full : cells[2].textContent || '').toLowerCase();
                
                const role = (cells[3].textContent || '').toLowerCase();

                const visible = query === '' ||
                    name.indexOf(query) !== -1 ||
                    email.indexOf(query) !== -1 ||
                    contact.indexOf(query) !== -1 ||
                    role.indexOf(query) !== -1;

                row.style.display = visible ? '' : 'none';
                if (visible) {
                    visibleCount++;
                }
            });

            let noResultRow = document.getElementById('userSearchNoResultRow');
            if (visibleCount === 0 && rows.length > 0) {
                if (!noResultRow) {
                    noResultRow = document.createElement('tr');
                    noResultRow.id = 'userSearchNoResultRow';
                    noResultRow.innerHTML = '<td colspan="' + columnCount + '" style="text-align:center;padding:18px;color:#6b7280;">No users match your search on this page.</td>';
                    tbody.appendChild(noResultRow);
                }
            } else if (noResultRow) {
                noResultRow.remove();
            }
        }

        function setInviteFormLoadingState(form, isLoading) {
            if (!form) return;

            const submitButton = form.querySelector('.btn-submit');
            if (!submitButton) return;

            const defaultText = submitButton.dataset.defaultText || submitButton.textContent.trim();
            submitButton.dataset.defaultText = defaultText;
            submitButton.disabled = isLoading;
            submitButton.textContent = isLoading ? 'Adding...' : defaultText;
        }

        function bindInviteFormSubmit(formId) {
            const form = document.getElementById(formId);
            if (!form || form.dataset.loadingBound === '1') {
                return;
            }

            form.dataset.loadingBound = '1';
            form.addEventListener('submit', function () {
                setInviteFormLoadingState(form, true);
            });
        }


        // Student Modal Functions
        function openCreateStudentModal() {
            document.getElementById('createStudentModal').style.display = 'flex';
            setInviteFormLoadingState(document.getElementById('createStudentInviteForm'), false);
        }

        function closeCreateStudentModal() {
            document.getElementById('createStudentModal').style.display = 'none';
            setInviteFormLoadingState(document.getElementById('createStudentInviteForm'), false);
        }

        function editStudent(id, name, email, contact, address, branchId) {
            const form = document.getElementById('editStudentForm');
            form.action = `${window.studentBaseUrl}/${id}`;
            document.getElementById('edit_student_name').value = name || '';
            document.getElementById('edit_student_email').value = email || '';

            let displayContact = contact || '';
            if (displayContact.startsWith('+63')) {
                displayContact = displayContact.substring(3);
            } else if (displayContact.startsWith('0')) {
                displayContact = displayContact.substring(1);
            }
            document.getElementById('edit_student_contact').value = displayContact;
            document.getElementById('edit_student_address').value = address || '';
            const branchSelect = document.getElementById('edit_student_branch');
            if (branchSelect) branchSelect.value = branchId || '';
            document.getElementById('editStudentModal').style.display = 'flex';
        }

        function closeEditStudentModal() {
            document.getElementById('editStudentModal').style.display = 'none';
        }

        // Export Dropdown
        function toggleExportDropdown() {
            document.getElementById('exportDropdown').classList.toggle('open');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.js-edit-user');
            if (btn) {
                const role = btn.dataset.role;
                if (role === 'student') {
                    editStudent(
                        btn.dataset.id,
                        btn.dataset.name,
                        btn.dataset.email,
                        btn.dataset.contact,
                        btn.dataset.address,
                        btn.dataset.branch
                    );
                } else if (role === 'instructor') {
                    editInstructor(
                        btn.dataset.id,
                        btn.dataset.name,
                        btn.dataset.email,
                        btn.dataset.contact,
                        btn.dataset.license,
                        btn.dataset.address,
                        btn.dataset.branch,
                        btn.dataset.specializations,
                        btn.dataset.availability
                    );
                }
            }

            const dropdown = document.getElementById('exportDropdown');
            if (dropdown && !dropdown.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });

        function viewStudent(id) {
            Toast.info('Student details view coming soon!', 'Feature Info');
        }

        function unifiedToggle(id, type, currentValue, role) {
            const action = (type === 'status')
                ? (currentValue === 'active' ? 'Deactivate' : 'Activate')
                : (currentValue === 'available' ? 'Mark Unavailable' : 'Mark Available');

            const typeLabel = (type === 'status') ? 'Account Status' : 'Availability';

            showConfirm({
                type: (currentValue === 'active' || currentValue === 'available') ? 'warning' : 'success',
                title: `${action} ${role.charAt(0).toUpperCase() + role.slice(1)}`,
                message: `Are you sure you want to ${action.toLowerCase()} this ${role}'s ${typeLabel.toLowerCase()}?`,
                confirmText: `Yes, ${action}`,
                onConfirm: () => {
                    // Get fresh token from meta tag
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    // Determine correct route (Relative paths for Hostinger/CORS safety)
                    let url = '';
                    if (type === 'availability') {
                        url = '/' + window.schoolSlug + '/admin/instructors/' + id + '/availability';
                    } else {
                        const basePart = (role === 'instructor') ? 'instructors' : 'students';
                        url = '/' + window.schoolSlug + '/admin/' + basePart + '/' + id + '/toggle-status';
                    }

                    fetch(url, {
                        method: 'PATCH',
                        redirect: 'manual', // <--- PREVENT 405 error by stopping automatic PATCH follow
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(async response => {
                            // Check for session timeouts (419/401)
                            if (response.status === 419 || response.status === 401) {
                                showConfirm({
                                    type: 'error',
                                    title: 'Session Expired',
                                    message: 'Your session has timed out. Please refresh and log in again.',
                                    confirmText: 'Log In Again',
                                    onConfirm: () => window.location.reload()
                                });
                                return;
                            }

                            // SUCCESS: In 'manual' mode, status 0 or 302 means the PATCH worked and Laravel is redirecting "Back"
                            // Status 0 (opaque redirect) or ok (200) both mean SUCCESS here.
                            if (response.ok || response.status === 0 || response.status === 302) {
                                Toast.success(`${typeLabel} updated successfully!`);

                                try {
                                    // CLEAN REFRESH: Manually trigger a fresh GET request to reload the table
                                    if (typeof loadContent === 'function') {
                                        loadContent(window.location.pathname + window.location.search);
                                    } else {
                                        window.location.reload();
                                    }
                                } catch (refreshErr) {
                                    console.warn('Silent refresh error:', refreshErr);
                                    window.location.reload();
                                }
                            } else {
                                const error = new Error(`Server error`);
                                error.status = response.status;
                                throw error;
                            }
                        })
                        .catch(error => {
                            console.error('Toggle error:', error);
                            // Forensic Reporting: No more "Unknown" if we have a status
                            const detail = error.status ? `(Status ${error.status})` : `(${error.message})`;
                            Toast.error('Failed to update ' + detail + '. Please refresh and try again.');
                        });
                }
            });
        }

        // Instructor Modal Functions
        function openCreateInstructorModal() {
            document.getElementById('createInstructorModal').style.display = 'flex';
            setInviteFormLoadingState(document.getElementById('createInstructorInviteForm'), false);
        }

        function closeCreateInstructorModal() {
            document.getElementById('createInstructorModal').style.display = 'none';
            setInviteFormLoadingState(document.getElementById('createInstructorInviteForm'), false);
        }

        function editInstructor(id, name, email, contact, license, address, branchId, specializations, availability) {
            const form = document.getElementById('editInstructorForm');
            form.action = `${window.instructorBaseUrl}/${id}`;
            document.getElementById('edit_instructor_name').value = name || '';
            document.getElementById('edit_instructor_email').value = email || '';

            let displayContact = contact || '';
            // Remove any non-digit characters (like dashes or spaces from seeders)
            displayContact = displayContact.replace(/\D/g, '');
            
            if (displayContact.startsWith('63')) {
                displayContact = displayContact.substring(2);
            } else if (displayContact.startsWith('0')) {
                displayContact = displayContact.substring(1);
            }
            document.getElementById('edit_instructor_contact').value = displayContact;
            document.getElementById('edit_instructor_license').value = license || '';
            document.getElementById('edit_instructor_address').value = address || '';
            const availabilitySelect = document.getElementById('edit_instructor_availability');
            if (availabilitySelect) availabilitySelect.value = availability || 'available';
            const branchSelect = document.getElementById('edit_instructor_branch');
            if (branchSelect) branchSelect.value = branchId || '';

            // Reset and populate specializations
            const specContainer = document.getElementById('edit_instructor_specializations');
            if (specContainer) {
                const checkboxes = specContainer.querySelectorAll('input[type="checkbox"]');
                let specArray = [];
                try {
                    specArray = JSON.parse(specializations || '[]');
                } catch (e) {
                    console.error('Error parsing specializations:', e);
                }

                checkboxes.forEach(cb => {
                    cb.checked = specArray.includes(parseInt(cb.value)) || specArray.includes(cb.value);
                });
            }

            document.getElementById('editInstructorModal').style.display = 'flex';
        }

        function closeEditInstructorModal() {
            document.getElementById('editInstructorModal').style.display = 'none';
        }

        function closeEditInstructorModal() {
            document.getElementById('editInstructorModal').style.display = 'none';
        }

        // --- UNIFIED USER VIEW MODAL LOGIC ---
        let currentEditingData = null;

        function openUserViewModal(data) {
            currentEditingData = data;
            const modal = document.getElementById('userViewModal');
            if (modal) {
                modal.style.display = 'flex';
                renderUserViewState();
            }
        }

        function closeUserViewModal() {
            const modal = document.getElementById('userViewModal');
            if (modal) modal.style.display = 'none';
            currentEditingData = null;
        }

        function renderUserViewState() {
            const content = document.getElementById('userViewContent');
            const data = currentEditingData;
            if (!content || !data) return;

            const isInstructor = data.role === 'instructor';
            
            const maskPII = (val, type) => {
                if (!window.piiMaskingEnabled || !val) return val;
                if (type === 'email') {
                    const parts = val.split('@');
                    if (parts.length < 2) return val;
                    return parts[0].substring(0, 2) + '*'.repeat(Math.max(0, parts[0].length - 2)) + '@' + parts[1];
                }
                if (type === 'contact') {
                    return val.substring(0, 4) + '****' + val.substring(Math.max(0, val.length - 2));
                }
                return val;
            };

            const emailDisplay = maskPII(data.email, 'email');
            const contactDisplay = maskPII(data.contact, 'contact');

            const parseJson = (val) => {
                if (!val) return [];
                if (Array.isArray(val)) return val;
                try { return JSON.parse(val); } catch (e) { return []; }
            };

            const restrictions = parseJson(data.restrictions);
            const specializations = parseJson(data.specializations);

            content.innerHTML = `
                <div class="profile-modal-header">
                    <button type="button" class="btn-close-modal" onclick="closeUserViewModal()" style="position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.2); border: none; border-radius: 8px; color: white; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 20px; cursor: pointer;">×</button>
                    <div class="profile-modal-avatar">
                        ${data.profilePicture ? `<img src="${data.profilePicture}">` : data.name.charAt(0)}
                    </div>
                    <div class="profile-header-info">
                        <h3>${data.name}</h3>
                        <span class="profile-role-badge">${data.role.toUpperCase()}</span>
                    </div>
                </div>
                
                <div class="profile-modal-body">
                    <div class="profile-section">
                        <div class="profile-section-title"><i class="bi bi-person-lines-fill"></i>Personal Information</div>
                        <div class="profile-info-grid">
                            <div class="profile-info-item">
                                <div class="profile-info-label">Email Address</div>
                                <div class="profile-info-value pii-wrapper">
                                    <span class="masked-pii" data-full="${data.email}">${emailDisplay}</span>
                                    ${window.piiMaskingEnabled ? `<button type="button" class="btn-reveal-pii" onclick="revealPII(this)" title="Reveal (5s)"><i class="bi bi-eye"></i></button>` : ''}
                                </div>
                            </div>
                            <div class="profile-info-item">
                                <div class="profile-info-label">Contact Number</div>
                                <div class="profile-info-value pii-wrapper">
                                    <span class="masked-pii" data-full="${data.contact}">${contactDisplay}</span>
                                    ${window.piiMaskingEnabled ? `<button type="button" class="btn-reveal-pii" onclick="revealPII(this)" title="Reveal (5s)"><i class="bi bi-eye"></i></button>` : ''}
                                </div>
                            </div>
                            <div class="profile-info-item" style="grid-column: span 2;">
                                <div class="profile-info-label">Current Address</div>
                                <div class="profile-info-value">${data.address || 'Not Provided'}</div>
                            </div>
                            <div class="profile-info-item">
                                <div class="profile-info-label">Branch Assignment</div>
                                <div class="profile-info-value">${data.branch || 'Unassigned'}</div>
                            </div>
                            <div class="profile-info-item">
                                <div class="profile-info-label">Account Status</div>
                                <div class="profile-info-value">
                                    <span class="status-badge ${data.status}">
                                        ${data.status.toUpperCase()}
                                    </span>
                                </div>
                            </div>
                            <div class="profile-info-item">
                                <div class="profile-info-label">Last Activity</div>
                                <div class="profile-info-value" style="font-size: 0.8rem;">
                                    <div>In: ${data.lastLogin || 'Never'}</div>
                                    <div>Out: ${data.lastLogout || 'Never'}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    ${isInstructor ? `
                        <div class="profile-section">
                            <div class="profile-section-title"><i class="bi bi-card-heading"></i>Instructor Credentials</div>
                            <div class="profile-info-grid">
                                <div class="profile-info-item">
                                    <div class="profile-info-label">License Number</div>
                                    <div class="profile-info-value" style="font-family: monospace; font-weight: 700; color: #4f46e5;">${data.license || 'N/A'}</div>
                                </div>
                                <div class="profile-info-item">
                                    <div class="profile-info-label">License Status</div>
                                    <div class="profile-info-value">
                                        <span class="license-badge ${data.licenseStatus || 'none'}">
                                            ${(data.licenseStatus || 'NONE').toUpperCase()}
                                        </span>
                                    </div>
                                </div>
                                <div class="profile-info-item" style="grid-column: span 2;">
                                    <div class="profile-info-label">Restriction Codes</div>
                                    <div class="profile-info-value">
                                        ${restrictions.length > 0 ? restrictions.map(r => `<span class="badge bg-primary text-white me-1">${r}</span>`).join('') : '<span class="text-muted">No restrictions</span>'}
                                    </div>
                                </div>
                                <div class="profile-info-item" style="grid-column: span 2;">
                                    <div class="profile-info-label">Course Specializations</div>
                                    <div class="profile-info-value">
                                        ${specializations.length > 0 ? specializations.map(s => `<span class="badge bg-info text-white me-1">${window.courseTitles[s] || s}</span>`).join('') : '<span class="text-muted">None listed</span>'}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="verification-card mt-3">
                                <div class="profile-info-label">License Image Preview</div>
                                ${data.licenseImage ? `
                                    <div class="license-image-container" onclick="openLightbox('${data.licenseImage}')">
                                        <img src="${data.licenseImage}">
                                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.6); color: white; padding: 4px; text-align: center; font-size: 0.7rem;">Click to enlarge</div>
                                    </div>
                                ` : '<div class="text-center py-4 text-muted">No license image uploaded</div>'}
                            </div>
                        </div>
                    ` : ''}
                </div>
                
                <div class="profile-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeUserViewModal()" style="border-radius: 10px; font-weight: 600;">Close</button>
                    ${isInstructor && (data.licenseStatus === 'pending' || data.licenseStatus === 'rejected' || data.licenseStatus === 'none') ? `
                        <button type="button" class="btn btn-success" onclick="toggleLicenseVerificationMode(true)" style="background: #10b981; border: none; border-radius: 10px; font-weight: 600; color: white;">
                            <i class="bi bi-shield-check me-2"></i>Verify License
                        </button>
                    ` : ''}
                </div>
            `;
        }

        function toggleLicenseVerificationMode(show) {
            if (!show) {
                renderUserViewState();
                return;
            }

            const content = document.getElementById('userViewContent');
            const data = currentEditingData;
            if (!content || !data) return;

            const restrictionCodes = ['A', 'A1', 'B', 'B1', 'B2', 'C', 'D', 'BE', 'CE'];
            let currentRestrictions = [];
            try {
                currentRestrictions = JSON.parse(data.restrictions || '[]');
            } catch (e) { console.error(e); }

            content.innerHTML = `
                <div class="profile-modal-header" style="padding: 20px 25px; background: linear-gradient(135deg, #6366f1, #a855f7);">
                    <h3 style="margin: 0; color: white !important;"><i class="bi bi-shield-check me-2"></i>Verify License</h3>
                    <button type="button" class="btn-close-modal" onclick="toggleLicenseVerificationMode(false)" style="position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.2); border: none; border-radius: 8px; color: white; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 20px; cursor: pointer;">×</button>
                </div>
                
                <form id="integratedVerifyForm" method="POST" action="${window.instructorBaseUrl}/${data.id}/verify" style="margin: 0;">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="profile-modal-body" style="background: #f8fafc;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Verification Decision</label>
                                    <div class="status-badge-selector">
                                        <label class="status-option">
                                            <input type="radio" name="status" value="verified" ${data.licenseStatus !== 'rejected' ? 'checked' : ''} onchange="document.getElementById('integrated_rejection_group').style.display='none'; document.getElementById('integrated_restrictions_group').style.display='block';">
                                            <span class="status-btn"><i class="bi bi-check-circle me-1"></i> Approve</span>
                                        </label>
                                        <label class="status-option">
                                            <input type="radio" name="status" value="rejected" ${data.licenseStatus === 'rejected' ? 'checked' : ''} onchange="document.getElementById('integrated_rejection_group').style.display='block'; document.getElementById('integrated_restrictions_group').style.display='none';">
                                            <span class="status-btn"><i class="bi bi-x-circle me-1"></i> Reject</span>
                                        </label>
                                    </div>
                                </div>

                                <div id="integrated_restrictions_group" class="form-group" style="display: ${data.licenseStatus === 'rejected' ? 'none' : 'block'};">
                                    <label>Restriction Codes</label>
                                    <div class="restriction-grid">
                                        ${restrictionCodes.map(code => `
                                            <label class="restriction-chip">
                                                <input type="checkbox" name="restriction_codes[]" value="${code}" ${currentRestrictions.includes(code) ? 'checked' : ''}>
                                                <span class="chip-content">${code}</span>
                                            </label>
                                        `).join('')}
                                    </div>
                                </div>

                                <div id="integrated_rejection_group" class="form-group" style="display: ${data.licenseStatus === 'rejected' ? 'block' : 'none'};">
                                    <label>Rejection Reason</label>
                                    <textarea name="rejection_reason" class="form-control" rows="4" placeholder="Explain why the license was rejected...">${data.rejectionReason || ''}</textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label>License Document Preview</label>
                                <div class="license-image-container" style="height: 300px;" onclick="openLightbox('${data.licenseImage || ''}')">
                                    <img src="${data.licenseImage || ''}" onerror="this.src='/images/placeholder-license.png'">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="toggleLicenseVerificationMode(false)" style="border-radius: 10px;">Back to Profile</button>
                        <button type="submit" class="btn btn-primary" style="border-radius: 10px; background: var(--header-gradient); border: none;">Save Verification</button>
                    </div>
                </form>
            `;
        }

        bindInviteFormSubmit('createStudentInviteForm');
        bindInviteFormSubmit('createInstructorInviteForm');

        (function restoreInviteModalAfterValidationError() {
            const hasInviteValidationErrors = @json($errors->any());
            const inviteRole = @json($oldInviteRole);
            const isEdit = @json(old('is_edit'));

            if (!hasInviteValidationErrors) {
                return;
            }

            if (isEdit) {
                // For edit modals, we'd need more data (ID, etc.) to restore perfectly.
                // But usually validation errors happen on 'Create' because 'Edit' uses JS to pre-fill.
                // If an edit fails, it redirects back.
                return;
            }

            if (inviteRole === 'student') {
                openCreateStudentModal();
            } else if (inviteRole === 'instructor') {
                openCreateInstructorModal();
            }
        })();

        // Close modal when clicking outside
        window.onclick = function (e) {
            if (e.target.classList.contains('modal')) {
                e.target.style.display = 'none';
            }
        }

        // Auto-strip leading zero from contact inputs
        document.addEventListener('input', function (e) {
            if (e.target.getAttribute('name') === 'contact' || e.target.id.includes('contact')) {
                let value = e.target.value;
                if (value.startsWith('0')) {
                    e.target.value = value.substring(1);
                }
            }
        });

        // --- GLOBAL EVENT DELEGATION ---
        document.addEventListener('input', function (e) {
            // Auto-strip leading zero from contact inputs
            if (e.target.getAttribute('name') === 'contact' || e.target.id.includes('contact')) {
                let value = e.target.value;
                if (value.startsWith('0')) {
                    e.target.value = value.substring(1);
                }
                // Allow only numbers
                e.target.value = e.target.value.replace(/\D/g, '');
            }
        });

        document.addEventListener('click', function (e) {
            // Edit User Delegation
            const editBtn = e.target.closest('.js-edit-user');
            if (editBtn) {
                const role = editBtn.dataset.role;
                if (role === 'student') {
                    editStudent(
                        editBtn.dataset.id,
                        editBtn.dataset.name,
                        editBtn.dataset.email,
                        editBtn.dataset.contact,
                        editBtn.dataset.address,
                        editBtn.dataset.branch
                    );
                } else if (role === 'instructor') {
                    editInstructor(
                        editBtn.dataset.id,
                        editBtn.dataset.name,
                        editBtn.dataset.email,
                        editBtn.dataset.contact,
                        editBtn.dataset.license,
                        editBtn.dataset.address,
                        editBtn.dataset.branch,
                        editBtn.dataset.specializations,
                        editBtn.dataset.availability
                    );
                }
                return;
            }

            // View User Delegation
            const viewBtn = e.target.closest('.js-view-user');
            if (viewBtn) {
                e.preventDefault();
                openUserViewModal({
                    id: viewBtn.dataset.id,
                    role: viewBtn.dataset.role,
                    name: viewBtn.dataset.name,
                    email: viewBtn.dataset.email,
                    contact: viewBtn.dataset.contact,
                    address: viewBtn.dataset.address,
                    branch: viewBtn.dataset.branch,
                    status: viewBtn.dataset.status,
                    license: viewBtn.dataset.license,
                    licenseImage: viewBtn.dataset.licenseImage,
                    licenseStatus: viewBtn.dataset.licenseStatus,
                    restrictions: viewBtn.dataset.restrictions,
                    specializations: viewBtn.dataset.specializations,
                    availability: viewBtn.dataset.availability,
                    rejectionReason: viewBtn.dataset.rejectionReason,
                    lastLogin: viewBtn.dataset.lastLogin,
                    lastLogout: viewBtn.dataset.lastLogout,
                    profilePicture: viewBtn.dataset.profilePicture
                });
                return;
            }

            // Verify License Delegation (Directly opens modal in verification mode)
            const verifyBtn = e.target.closest('.js-verify-license');
            if (verifyBtn) {
                e.preventDefault();
                openUserViewModal({
                    id: verifyBtn.dataset.id,
                    role: verifyBtn.dataset.role,
                    name: verifyBtn.dataset.name,
                    email: verifyBtn.dataset.email,
                    contact: verifyBtn.dataset.contact,
                    address: verifyBtn.dataset.address,
                    branch: verifyBtn.dataset.branch,
                    status: verifyBtn.dataset.status,
                    license: verifyBtn.dataset.licenseNumber,
                    licenseImage: verifyBtn.dataset.licenseImage,
                    licenseStatus: verifyBtn.dataset.licenseStatus || 'pending',
                    restrictions: verifyBtn.dataset.restrictions || '[]',
                    specializations: verifyBtn.dataset.specializations || '[]',
                    rejectionReason: verifyBtn.dataset.rejectionReason || '',
                    lastLogin: verifyBtn.dataset.lastLogin,
                    lastLogout: verifyBtn.dataset.lastLogout
                });
                toggleLicenseVerificationMode(true);
                return;
            }
        });

        // PII Reveal Logic with 5s Timer
        function revealPII(button) {
            const span = button.previousElementSibling;
            const fullValue = span.getAttribute('data-full');
            const originalMasked = span.textContent;

            if (span.classList.contains('revealed')) return;

            // Show full value
            span.textContent = fullValue;
            span.classList.add('revealed');

            // UI Feedback: Change icon to clock and disable button
            const originalIcon = button.innerHTML;
            button.innerHTML = '<i class="bi bi-clock-history"></i>';
            button.classList.add('timer-active');
            button.disabled = true;

            // Auto-hide after 5 seconds
            setTimeout(() => {
                span.textContent = originalMasked;
                span.classList.remove('revealed');
                button.innerHTML = originalIcon;
                button.classList.remove('timer-active');
                button.disabled = false;
            }, 5000);
        }

        // --- LIGHTBOX SYSTEM ---
        function openLightbox(src) {
            if (!src) return;
            const lightbox = document.getElementById('globalLightbox');
            const img = document.getElementById('lightboxImage');
            if (lightbox && img) {
                img.src = src;
                lightbox.style.display = 'flex';
                // Trigger animation
                setTimeout(() => lightbox.classList.add('active'), 10);
            }
        }

        function closeLightbox() {
            const lightbox = document.getElementById('globalLightbox');
            if (lightbox) {
                lightbox.classList.remove('active');
                setTimeout(() => lightbox.style.display = 'none', 300);
            }
        }
        
        // Ensure validation errors are visible even if modal is closed
        document.addEventListener('DOMContentLoaded', function() {
            const hasErrors = @json($errors->any());
            if (hasErrors) {
                const errorMessages = {!! json_encode($errors->all()) !!};
                let errorHtml = '<ul style="text-align: left; margin: 0; padding-left: 20px;">';
                errorMessages.forEach(msg => {
                    errorHtml += `<li>${msg}</li>`;
                });
                errorHtml += '</ul>';
                
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: `Please fix the following issues:<br><br>${errorHtml}`,
                    confirmButtonColor: 'var(--primary-color)'
                });
            }
        });
    </script>

    <!-- LICENSE VERIFICATION MODAL -->
    <!-- USER VIEW MODAL (Unified) -->
    <div id="userViewModal" class="modal">
        <div class="modal-content profile-modal-container" style="width: min(850px, 95%); padding: 0;">
            <div id="userViewContent">
                <!-- Content will be injected via JS -->
                <div class="modal-body p-5 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading user details...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- GLOBAL LIGHTBOX -->
    <div id="globalLightbox" class="lightbox-overlay" onclick="closeLightbox()">
        <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
        <img id="lightboxImage" src="" onclick="event.stopPropagation()">
    </div>

    <style>
        .lightbox-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            backdrop-filter: blur(5px);
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .lightbox-overlay.active {
            opacity: 1;
        }
        .lightbox-overlay img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 8px;
            box-shadow: 0 0 50px rgba(0,0,0,0.5);
            transform: scale(0.9);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .lightbox-overlay.active img {
            transform: scale(1);
        }
        .lightbox-close {
            position: absolute;
            top: 30px;
            right: 30px;
            background: none;
            border: none;
            color: white;
            font-size: 50px;
            cursor: pointer;
            line-height: 1;
            z-index: 10001;
        }
    </style>

@endsection