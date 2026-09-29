@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Admin & Secretary Management')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $settings = $school?->schoolSetting;
    $primaryColor = $settings->primary_color ?? '#667eea';
    $secondaryColor = $settings->secondary_color ?? '#764ba2';
    $useGradient = $settings?->use_gradient_header;
    $pendingInviteCount = ($pendingInvitations ?? collect())->count();
@endphp

@include('school.admin.partials.admin-styles')

<style>
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
        border-bottom: 3px solid {{ $primaryColor }}40;
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

    /* Statistics Cards - Standardized */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border-left: 5px solid transparent;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .stat-card.active {
        border-left-color: {{ $primaryColor }};
    }

    .stat-card.active::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: {{ $primaryColor }}08;
    }

    .stat-content {
        position: relative;
        z-index: 1;
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .stat-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 800;
        color: #111827;
        line-height: 1;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    /* Card Variants */
    .stat-card.admins { border-left-color: #3b82f6; }
    .stat-card.admins .stat-icon { background: #eff6ff; color: #1d4ed8; }
    
    .stat-card.managers { border-left-color: #10b981; }
    .stat-card.managers .stat-icon { background: #ecfdf5; color: #047857; }
    
    .stat-card.total { border-left-color: #6366f1; }
    .stat-card.total .stat-icon { background: #eef2ff; color: #4338ca; }

    /* Action Bar */
    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding: 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }

    .search-wrapper {
        position: relative;
        flex: 1;
        max-width: 450px;
        display: flex;
        align-items: center;
    }

    .search-wrapper input {
        width: 100% !important;
        padding: 10px 16px 10px 42px !important;
        border: 2px solid {{ $primaryColor }}15 !important;
        border-radius: 12px !important;
        font-size: 0.95rem !important;
        transition: all 0.2s !important;
        background: {{ $primaryColor }}05 !important;
        color: #1f2937 !important;
        height: 40px !important;
        display: block !important;
        outline: none !important;
    }

    .search-wrapper input:focus {
        border-color: {{ $primaryColor }} !important;
        background: white !important;
        box-shadow: 0 0 0 4px {{ $primaryColor }}10 !important;
    }

    .search-wrapper .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: {{ $primaryColor }}80;
        font-size: 1.1rem;
        z-index: 10;
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 18px;
    }
    
    .action-controls {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-shrink: 0;
    }

    .btn-pending-toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 42px;
        padding: 0 14px;
        background: white;
        color: #374151;
        border: 2px solid #d1d5db;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .btn-pending-toggle:hover {
        border-color: {{ $primaryColor }}80;
        color: {{ $primaryColor }};
        transform: translateY(-1px);
    }

    .pending-count-badge {
        min-width: 22px;
        height: 22px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        color: #1f2937;
        background: #e5e7eb;
        padding: 0 6px;
        line-height: 1;
    }

    .pending-count-badge.has-items {
        color: white;
        background: #f59e0b;
    }

    #pendingInvitationsModal .modal-content.pending-modal-content {
        width: min(1440px, 98vw);
        min-width: 900px;
        max-width: 98vw;
    }

    .pending-modal-body {
        padding: 0 !important;
        max-height: 70vh;
        overflow: auto;
    }

    .pending-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid {{ $primaryColor }}15;
        background: {{ $primaryColor }}06;
    }

    .pending-panel-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #1f2937;
    }

    .pending-panel-subtitle {
        margin: 0;
        font-size: 0.82rem;
        color: #6b7280;
    }

    .pending-panel-body {
        padding: 0;
    }

    .pending-table-wrapper {
        overflow-x: auto;
    }

    .pending-table {
        width: 100%;
        border-collapse: collapse;
    }

    .pending-table thead th {
        text-align: left;
        padding: 12px 16px;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #6b7280;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .pending-table tbody td {
        padding: 12px 16px;
        border-bottom: 1px solid #f3f4f6;
        color: #1f2937;
        font-size: 0.9rem;
        vertical-align: middle;
    }

    .pending-table tbody tr:last-child td {
        border-bottom: none;
    }

    .pending-role {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        background: {{ $secondaryColor }}14;
        color: {{ $secondaryColor }};
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .pending-status {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .pending-status.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .pending-status.expired {
        background: #fee2e2;
        color: #991b1b;
    }

    .pending-invite-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-invite-action {
        border: none;
        border-radius: 8px;
        padding: 7px 10px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .btn-invite-resend {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .btn-invite-resend:hover {
        background: #bfdbfe;
    }

    .btn-invite-cancel {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-invite-cancel:hover {
        background: #fecaca;
    }

    .pending-empty {
        padding: 20px;
        font-size: 0.9rem;
        color: #6b7280;
        text-align: center;
    }

    .btn-create {
        height: 40px;
        padding: 0 16px;
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px {{ $primaryColor }}20;
    }

    .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px {{ $primaryColor }}30;
        filter: brightness(1.05);
    }

    .btn-create svg {
        width: 18px !important;
        height: 18px !important;
        flex-shrink: 0;
    }

    /* Export Dropdown - Symmetric with User Management */
    .export-dropdown {
        position: relative;
    }

    .btn-export-trigger {
        display: flex;
        align-items: center;
        gap: 8px;
        height: 40px;
        padding: 0 16px;
        background: white;
        color: {{ $primaryColor }};
        border: 2px solid {{ $primaryColor }}20;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-export-trigger:hover {
        border-color: {{ $primaryColor }};
        background: {{ $primaryColor }}05;
        transform: translateY(-1px);
    }

    .export-dropdown-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border: 1px solid {{ $primaryColor }}15;
        width: 220px;
        z-index: 50;
        display: none;
        padding: 8px;
    }

    .export-dropdown-menu.show {
        display: block;
        animation: dropdownIn 0.2s ease-out;
    }

    @keyframes dropdownIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .export-dropdown-menu a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        color: #4b5563;
        text-decoration: none;
        font-size: 0.9rem;
        border-radius: 8px;
        transition: background 0.2s;
    }

    .export-dropdown-menu a:hover {
        background: #f9fafb;
    }

    .export-dropdown-menu .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .export-dropdown-menu .dot.pdf { background: #ef4444; }
    .export-dropdown-menu .dot.excel { background: #10b981; }

    .dropdown-header {
        font-size: 0.75rem;
        font-weight: 700;
        color: #9ca3af;
        text-transform: uppercase;
        padding: 8px 12px 4px;
        letter-spacing: 0.05em;
    }

    /* Table Styles Refresh */
    .admin-table-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid #f3f4f6;
        overflow: hidden;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
    }

    .admin-table thead {
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
    }

    .admin-table thead th {
        background: transparent;
        color: inherit;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        font-size: 0.95rem;
        text-transform: none;
        letter-spacing: 0;
    }

    /* Badges & Actions - Color Theory */
    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .role-school-admin {
        background: {{ $primaryColor }}15;
        color: {{ $primaryColor }};
    }

    .role-branch-secretary {
        background: {{ $secondaryColor }}15;
        color: {{ $secondaryColor }};
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        gap: 6px;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-action {
        padding: 8px 14px;
        border: none;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .btn-edit {
        background: {{ $primaryColor }}10;
        color: {{ $primaryColor }};
    }

    .btn-edit:hover { background: {{ $primaryColor }}20; }

    .btn-toggle {
        background: #fef3c7;
        color: #92400e;
    }

    .btn-delete {
        background: #fee2e2;
        color: #991b1b;
    }


    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .error-text {
        color: #dc3545;
        font-size: 0.85rem;
        margin-top: 4px;
    }

    /* High-Design Modal Styles - Glassmorphism & Symmetrical Constraints */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        animation: fadeInOverlay 0.3s ease;
    }

    .modal-overlay.active {
        display: flex;
    }

    @keyframes fadeInOverlay {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-content {
        background: white;
        width: 600px;
        min-width: 600px;
        max-width: 92%;
        border-radius: 16px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
        animation: modalScaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    @keyframes modalScaleIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }

    .modal-header {
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white !important;
        padding: 32px !important;
        border-bottom: none !important;
        position: relative;
    }

    .modal-header h5 {
        font-size: 1.75rem !important;
        font-weight: 600 !important;
        color: white !important;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .btn-close-modal {
        position: absolute;
        top: 25px;
        right: 25px;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 1.2rem;
        z-index: 10;
    }

    .modal-body {
        padding: 32px !important;
        background: white;
    }

    .modal-footer {
        padding: 24px 32px 32px;
        display: flex;
        gap: 12px;
        background: white;
        border-top: 1px solid #f3f4f6;
    }

    /* Premium Export - Red Design */
    .btn-export-trigger {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 600;
        transition: all 0.2s;
        cursor: pointer;
        height: 42px;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
    }

    .btn-export-trigger:hover {
        background: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
    }

    .btn-export-trigger svg {
        color: white;
        width: 18px !important;
        height: 18px !important;
    }

    .export-dropdown-menu {
        backdrop-filter: blur(12px) !important;
        background: rgba(255, 255, 255, 0.9) !important;
        border: 1px solid rgba(0, 0, 0, 0.05) !important;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15) !important;
    }

    /* High-Design Form Logic - Missing Sync */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 0.9rem;
        font-weight: 600;
        color: {{ $primaryColor }}cc;
        margin-bottom: 8px;
    }

    .form-group input, 
    .form-group select, 
    .form-group textarea {
        width: 100%;
        padding: 10px 16px;
        border: 2px solid {{ $primaryColor }}15;
        border-radius: 12px;
        font-size: 0.95rem;
        transition: all 0.2s;
        background: {{ $primaryColor }}05;
    }

    .form-group input:focus, 
    .form-group select:focus {
        outline: none;
        border-color: {{ $primaryColor }};
        background: white;
        box-shadow: 0 0 0 4px {{ $primaryColor }}15;
    }

    .form-hint {
        font-size: 0.8rem;
        color: {{ $primaryColor }}70;
        margin-top: 6px;
    }

    .contact-input-group {
        display: flex;
        align-items: center;
        background: {{ $primaryColor }}05;
        border: 2px solid {{ $primaryColor }}15;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.2s;
    }

    .contact-input-group:focus-within {
        border-color: {{ $primaryColor }};
        background: white;
        box-shadow: 0 0 0 4px {{ $primaryColor }}15;
    }

    .contact-prefix {
        padding: 0 15px;
        background: {{ $primaryColor }}10;
        color: {{ $primaryColor }};
        font-weight: 700;
        font-size: 0.95rem;
        height: 44px;
        display: flex;
        align-items: center;
        border-right: 2px solid {{ $primaryColor }}15;
    }

    .contact-input-group input {
        flex: 1;
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        height: 44px !important;
        padding: 0 15px !important;
    }

    /* Restoring Compact Buttons */
    .btn-secondary, .btn-primary {
        height: 40px !important;
        padding: 0 16px !important;
        border-radius: 10px !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        border: none !important;
        cursor: pointer !important;
        transition: all 0.2s !important;
        color: white !important;
        white-space: nowrap !important;
        flex: 0 0 auto !important; /* Prevent expansion */
    }

    .btn-secondary {
        background: #94a3b8 !important; /* Themed slate-400 */
        box-shadow: 0 4px 12px rgba(148, 163, 184, 0.2) !important;
    }

    .modal-header h5 svg {
        width: 48px !important;
        height: 48px !important;
        margin-bottom: 5px;
    }

    .modal-footer {
        padding: 24px 32px 32px;
        display: flex;
        gap: 12px;
        background: white;
        border-top: 1px solid {{ $primaryColor }}08;
        justify-content: flex-end;
        align-items: center;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .action-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }

        .search-wrapper {
            max-width: none;
        }

        .action-controls {
            width: 100%;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        #pendingInvitationsModal .modal-content.pending-modal-content {
            min-width: 0;
            width: 95vw;
        }

        .admin-table-card {
            overflow-x: auto;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
    .btn-reveal-pii {
        background: none;
        border: none;
        padding: 0;
        color: {{ $primaryColor }};
        cursor: pointer;
        opacity: 0.6;
        transition: all 0.2s;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        vertical-align: middle;
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
        vertical-align: middle;
    }

    .pii-wrapper {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        min-height: 24px;
        vertical-align: middle;
    }

    /* Improved Table Spacing */
    .admin-table th, 
    .admin-table td {
        vertical-align: middle !important;
        padding: 18px 15px !important;
    }

    .admin-table thead th {
        white-space: nowrap;
    }
</style>

<div class="admin-mgmt-container">
    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">School Admin & Branch Managers Management</h1>
            <p class="page-subtitle">Manage school administrators and branch managers for {{ $schoolName }}</p>
        </div>
        <div class="header-actions" style="display: flex; gap: 10px; align-items: center;">
            <button class="btn-create" onclick="openCreateModal('school_admin')">
                <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                Add Admin
            </button>
            <button class="btn-create" onclick="openCreateModal('branch_secretary')" style="background: {{ $secondaryColor }}; box-shadow: 0 4px 12px {{ $secondaryColor }}30;">
                <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                Add Manager
            </button>
        </div>
    </div>
    {{-- Admin Count Cards --}}
    <div class="stats-grid">
        <div class="stat-card admins active" onclick="filterAdmins('school_admin', this)">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">School Admins</div>
                        <div class="stat-value">{{ $admins->where('role', 'school_admin')->count() }}</div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
                <div style="font-size: 0.85rem; color: #6b7280;">Full system access</div>
            </div>
        </div>
        
        <div class="stat-card managers" onclick="filterAdmins('branch_secretary', this)">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Branch Managers</div>
                        <div class="stat-value">{{ $admins->where('role', 'branch_secretary')->count() }}</div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-person-workspace"></i>
                    </div>
                </div>
                <div style="font-size: 0.85rem; color: #6b7280;">Location-specific access</div>
            </div>
        </div>

        <div class="stat-card total" onclick="filterAdmins('all', this)">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Staff</div>
                        <div class="stat-value">{{ $admins->count() }}</div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
                <div style="font-size: 0.85rem; color: #6b7280;">All management accounts</div>
            </div>
        </div>
    </div>

    <div class="action-bar" style="margin-top: 20px; background: #fff; padding: 20px; border-radius: 16px; border: 1px solid {{ $primaryColor }}10; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <div class="search-wrapper">
            <i class="bi bi-search search-icon"></i>
            <input type="text" id="adminSearch" placeholder="Search by name, email, or role..." onkeyup="filterAdminTable()">
        </div>
        <div class="action-controls">
            <button type="button" class="btn-pending-toggle" id="pendingInvitationsToggle" onclick="openPendingInvitationsModal()">
                <i class="bi bi-envelope-paper"></i>
                Pending Accounts
                <span class="pending-count-badge {{ $pendingInviteCount > 0 ? 'has-items' : '' }}">{{ $pendingInviteCount }}</span>
            </button>
            <div class="export-dropdown" id="adminExportDropdown">
                <button class="btn-export-trigger" onclick="toggleAdminExportDropdown()">
                    <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export
                    <svg class="chevron icon-14" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="export-dropdown-menu" id="adminExportMenu">
                    <div class="dropdown-header">Administrators</div>
                    <a href="{{ school_route('admin.exports.admins.pdf') }}">
                        <span class="dot pdf" style="background: #ef4444;"></span> Export PDF
                    </a>
                    <a href="{{ school_route('admin.exports.admins.excel') }}">
                        <span class="dot excel" style="background: #10b981;"></span> Export Excel
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}


    {{-- Admin Table --}}
    @if($admins->count() > 0)
    <div class="admin-table-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>
                        Email
                        @if($settings->enable_pii_masking ?? false)
                            <i class="bi bi-shield-lock" style="font-size: 0.75rem; color: rgba(255,255,255,0.8); margin-left: 4px;" title="Privacy Protected (PII Masking Active)"></i>
                        @endif
                    </th>
                    <th>Role</th>
                    <th>Branch</th>
                    <th>Status</th>
                    <th>Portal Activity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($admins as $adminRow)
                <tr>
                    <td>
                        <span class="admin-name">{{ $adminRow->name }}</span>
                    </td>
                    <td>
                        @if(($settings->enable_pii_masking ?? false) && $adminRow->email)
                            @php
                                $emailParts = explode('@', $adminRow->email);
                                $maskedEmail = substr($emailParts[0], 0, 2) . str_repeat('*', max(0, strlen($emailParts[0]) - 2)) . '@' . $emailParts[1];
                            @endphp
                            <div class="pii-wrapper">
                                <span class="masked-pii" data-full="{{ $adminRow->email }}">{{ $maskedEmail }}</span>
                                <button type="button" class="btn-reveal-pii" onclick="revealPII(this)" title="Click to reveal (5s)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        @else
                            {{ $adminRow->email }}
                        @endif
                    </td>
                    <td>
                        @if($adminRow->role === 'school_admin')
                            <span class="role-badge role-school-admin">
                                <i class="bi bi-shield-fill"></i> School Admin
                            </span>
                        @else
                            <span class="role-badge role-branch-secretary">
                                <i class="bi bi-person-badge"></i> Branch Manager
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($adminRow->role === 'branch_secretary' && $adminRow->branch)
                            {{ $adminRow->branch->name }}
                        @else
                            <span class="muted-dash">—</span>
                        @endif
                    </td>
                    <td>
                        @if($adminRow->is_active)
                            <span class="status-badge status-active">
                                <i class="bi bi-check-circle-fill"></i> Active
                            </span>
                        @else
                            <span class="status-badge status-inactive">
                                <i class="bi bi-x-circle-fill"></i> Inactive
                            </span>
                        @endif
                    </td>
                    <td>
                        <div style="font-size: 0.82rem; line-height: 1.4; color: #4b5563;">
                            <div>
                                <span style="font-weight: 600; color: #1f2937;">In:</span> 
                                {{ $adminRow->last_login_at ? $adminRow->last_login_at->diffForHumans() : 'Never' }}
                            </div>
                            <div>
                                <span style="font-weight: 600; color: #1f2937;">Out:</span> 
                                {{ $adminRow->last_logout_at ? $adminRow->last_logout_at->diffForHumans() : 'Never' }}
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($adminRow->id === $admin->id)
                            <span class="you-badge">
                                <i class="bi bi-person-fill"></i> You
                            </span>
                        @else
                            <div class="action-buttons">
                                <button class="btn-action btn-edit" onclick="openEditModal(this)"
                                    data-id="{{ $adminRow->id }}"
                                    data-name="{{ $adminRow->name }}"
                                    data-email="{{ $adminRow->email }}"
                                    data-contact="{{ $adminRow->contact }}"
                                    data-role="{{ $adminRow->role }}"
                                    data-branch-id="{{ $adminRow->branch_id }}"
                                    data-update-url="{{ route('schools.admin.admin-management.update', [$school, $adminRow]) }}">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <form action="{{ route('schools.admin.admin-management.toggleStatus', [$school, $adminRow]) }}" method="POST" class="inline-form">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-action btn-toggle" title="{{ $adminRow->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $adminRow->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                        {{ $adminRow->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form action="{{ route('schools.admin.admin-management.destroy', [$school, $adminRow]) }}" method="POST" class="inline-form" onsubmit="return confirmDelete(event, '{{ $adminRow->name }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete">
                                        <i class="bi bi-trash3"></i> Delete
                                    </button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <i class="bi bi-people"></i>
        <h3>No Administrators Found</h3>
        <p>Get started by adding your first administrator or branch manager.</p>
    </div>
    @endif
</div>

{{-- Pending Invitations Modal --}}
<div class="modal-overlay" id="pendingInvitationsModal">
    <div class="modal-content pending-modal-content">
        <div class="modal-header">
            <h5>Pending Accounts</h5>
            <button class="btn-close-modal" onclick="closePendingInvitationsModal()">&times;</button>
        </div>
        <div class="modal-body pending-modal-body">
            <div class="pending-panel-header">
                <div>
                    <h3 class="pending-panel-title">{{ $pendingInviteCount }} Pending Account{{ $pendingInviteCount === 1 ? '' : 's' }}</h3>
                    <p class="pending-panel-subtitle">Accounts that haven't been set up yet. These count toward branch capacity until activated, expired, or removed.</p>
                </div>
            </div>
            <div class="pending-panel-body">
                @if($pendingInviteCount > 0)
                    <div class="pending-table-wrapper">
                        <table class="pending-table">
                            <thead>
                                <tr>
                                    <th>Recipient</th>
                                    <th>Role</th>
                                    <th>Branch</th>
                                    <th>Status</th>
                                    <th>Sent At</th>
                                    <th>Expires At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingInvitations as $invitation)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 600;">{{ $invitation->payload['name'] ?? $invitation->name ?? 'Unnamed Recipient' }}</div>
                                            <div style="font-size: 0.82rem; color: #6b7280;">{{ $invitation->email }}</div>
                                        </td>
                                        <td>
                                            <span class="pending-role">{{ strtoupper(str_replace('_', ' ', $invitation->role)) }}</span>
                                        </td>
                                        <td>{{ $invitation->branch?->name ?? 'All Branches' }}</td>
                                        <td>
                                            @if($invitation->isExpired())
                                                <span class="pending-status expired">Expired</span>
                                            @else
                                                <span class="pending-status pending">Pending</span>
                                            @endif
                                        </td>
                                        <td>{{ $invitation->created_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
                                        <td>{{ $invitation->expires_at?->format('M d, Y H:i') ?? 'No Expiry' }}</td>
                                        <td>
                                            <div class="pending-invite-actions">
                                                <form action="{{ route('schools.admin.invitations.resend', ['school' => $school, 'invitation' => $invitation]) }}" method="POST" class="inline-form native-form pending-invitation-form" data-no-ajax="1" data-no-submit-guard="1" data-protected="1">
                                                    @csrf
                                                    <button type="button" class="btn-invite-action btn-invite-resend" title="Resend Setup Link" data-pending-submit="1">
                                                        <i class="bi bi-arrow-repeat"></i>
                                                        Resend Link
                                                    </button>
                                                </form>
                                                <form action="{{ route('schools.admin.invitations.cancel', ['school' => $school, 'invitation' => $invitation]) }}" method="POST" class="inline-form native-form pending-invitation-form" data-no-ajax="1" data-no-submit-guard="1" data-protected="1">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn-invite-action btn-invite-cancel" title="Remove Account Setup" data-pending-submit="1" data-confirm-message="Are you sure you want to remove this pending account?">
                                                        <i class="bi bi-x-circle"></i>
                                                        Remove
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="pending-empty">
                        No pending invitations found.
                    </div>
                @endif
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closePendingInvitationsModal()">Close</button>
        </div>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal-overlay" id="createModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5>
                <span id="modalTitle">Add Staff Member</span>
            </h5>
            <button class="btn-close-modal" onclick="closeCreateModal()">&times;</button>
        </div>
        <form action="{{ route('schools.admin.admin-management.store', $school) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_name">Full Name <span class="required-indicator">*</span></label>
                    <input type="text" id="create_name" name="name" required placeholder="Enter full name" value="{{ old('name') }}">
                </div>
                <div class="form-group">
                    <label for="create_email">Email Address <span class="required-indicator">*</span></label>
                    <input type="email" id="create_email" name="email" required placeholder="Enter email address" value="{{ old('email') }}">
                    <p class="form-hint">A setup link will be sent to this email for the user to set their password.</p>
                </div>
                <div class="form-group">
                    <label for="create_contact">Contact Number <span class="required-indicator">*</span></label>
                    @if($settings->enforce_ph_contact ?? true)
                        <div class="contact-input-group">
                            <span class="contact-prefix">+63</span>
                            <input type="text" id="create_contact" name="contact" required placeholder="9123456789" value="{{ old('contact') }}" inputmode="numeric" autocomplete="tel" maxlength="10">
                        </div>
                        <p class="form-hint">Enter the 10-digit number after +63 (e.g., 9123456789).</p>
                    @else
                        <input type="text" id="create_contact" name="contact" required placeholder="Enter contact number" value="{{ old('contact') }}">
                    @endif
                </div>
                <input type="hidden" id="create_role" name="role" value="{{ old('role', 'school_admin') }}">
                
                <div class="form-group branch-group-hidden" id="create_branch_group">
                    <label for="create_branch_id">Assign to Branch <span class="required-indicator">*</span></label>
                    <select id="create_branch_id" name="branch_id">
                        <option value="" disabled selected>— Select Branch —</option>
                        @foreach($branches as $branch)
                            @php
                                $capacity = $branchCapacityMap[$branch->id] ?? [
                                    'used' => 0,
                                    'limit' => ($branchSecretaryLimit ?? 1),
                                    'at_capacity' => false,
                                ];
                                $isAtCapacity = (bool) ($capacity['at_capacity'] ?? false);
                            @endphp
                            <option value="{{ $branch->id }}"
                                {{ old('branch_id') == $branch->id ? 'selected' : '' }}
                                @if($isAtCapacity) disabled @endif>
                                {{ $branch->name }}
                                ({{ $capacity['used'] }}/{{ $capacity['limit'] }} used)
                                @if($isAtCapacity) (full) @endif
                            </option>
                        @endforeach
                    </select>
                    <div class="form-hint">
                        Capacity includes active branch managers and pending, unexpired invitations.
                        Limit per branch: {{ $branchSecretaryLimit ?? 1 }}.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeCreateModal()">Cancel</button>
                <button type="submit" class="btn-primary" style="background: {{ $primaryColor }};">
                    Add Account
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5>
                Edit Administrator
            </h5>
            <button class="btn-close-modal" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_name">Full Name <span class="required-indicator">*</span></label>
                    <input type="text" id="edit_name" name="name" required placeholder="Enter full name">
                </div>
                <div class="form-group">
                    <label for="edit_email">Email Address <span class="required-indicator">*</span></label>
                    <input type="email" id="edit_email" name="email" required placeholder="Enter email address">
                </div>
                <div class="form-group">
                    <label for="edit_password">Password</label>
                    <input type="password" id="edit_password" name="password" placeholder="Leave blank to keep current" minlength="8">
                    <div class="form-hint">Leave blank if you don't want to change the password.</div>
                </div>
                <div class="form-group">
                    <label for="edit_password_confirmation">Confirm Password</label>
                    <input type="password" id="edit_password_confirmation" name="password_confirmation" placeholder="Confirm new password" minlength="8">
                </div>
                <div class="form-group">
                    <label for="edit_contact">Contact Number <span class="required-indicator">*</span></label>
                    @if($settings->enforce_ph_contact ?? true)
                        <div class="contact-input-group">
                            <span class="contact-prefix">+63</span>
                            <input type="text" id="edit_contact" name="contact" required placeholder="9123456789" inputmode="numeric" autocomplete="tel" maxlength="10">
                        </div>
                        <p class="form-hint">Enter the 10-digit number after +63 (e.g., 9123456789).</p>
                    @else
                        <input type="text" id="edit_contact" name="contact" required placeholder="Enter contact number">
                    @endif
                </div>
                <input type="hidden" id="edit_role" name="role">
                
                <div class="form-group branch-group-hidden" id="edit_branch_group">
                    <label for="edit_branch_id">Assign to Branch <span class="required-indicator">*</span></label>
                    <select id="edit_branch_id" name="branch_id">
                        <option value="" disabled>— Select Branch —</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-primary" style="background: {{ $primaryColor }};">
                    Update Administrator
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function enforceNumericOnly(input) {
        if (!input) return;
        const sanitize = function() {
            let val = input.value.replace(/\D+/g, '');
            if (val.startsWith('0')) {
                val = val.substring(1);
            }
            input.value = val;
        };
        input.addEventListener('input', sanitize);
        input.addEventListener('paste', function() { setTimeout(sanitize, 0); });
    }

    document.addEventListener('DOMContentLoaded', function() {
        enforceNumericOnly(document.getElementById('create_contact'));
        enforceNumericOnly(document.getElementById('edit_contact'));
    });

    // Toggle branch field visibility based on role selection
    function toggleBranchField(prefix) {
        const roleSelect = document.getElementById(prefix + '_role');
        const branchGroup = document.getElementById(prefix + '_branch_group');
        const branchSelect = document.getElementById(prefix + '_branch_id');

        if (roleSelect.value === 'branch_secretary') {
            branchGroup.style.display = 'block';
            branchSelect.setAttribute('required', 'required');
        } else {
            branchGroup.style.display = 'none';
            branchSelect.removeAttribute('required');
            branchSelect.value = '';
        }
    }

    // Create Modal
    function openCreateModal(role) {
        if (role) {
            document.getElementById('create_role').value = role;
            const titleElem = document.getElementById('modalTitle');
            if (role === 'school_admin') {
                titleElem.innerText = 'Add School Admin';
            } else if (role === 'branch_secretary') {
                titleElem.innerText = 'Add Branch Manager';
            }
        }
        document.getElementById('createModal').classList.add('active');
        toggleBranchField('create');
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.remove('active');
    }

    // Edit Modal
    function openEditModal(button) {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');

        form.action = button.dataset.updateUrl;

        document.getElementById('edit_name').value = button.dataset.name;
        document.getElementById('edit_email').value = button.dataset.email;
        document.getElementById('edit_contact').value = button.dataset.contact || '';
        document.getElementById('edit_role').value = button.dataset.role;
        document.getElementById('edit_branch_id').value = button.dataset.branchId || '';
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_password_confirmation').value = '';

        toggleBranchField('edit');
        modal.classList.add('active');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }

    // Delete confirmation
    function confirmDelete(event, name) {
        if (!confirm('Are you sure you want to delete "' + name + '"? This action cannot be undone.')) {
            event.preventDefault();
            return false;
        }
        return true;
    }

    // Close modals on overlay click
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });
    });

    // Close modals on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(function(modal) {
                modal.classList.remove('active');
            });
        }
    });

    // Administrative Filtration & Search
    function filterAdmins(role, element) {
        // Toggle card active state
        document.querySelectorAll('.stat-card').forEach(card => card.classList.remove('active'));
        if (element) element.classList.add('active');

        const rows = document.querySelectorAll('.admin-table tbody tr');
        rows.forEach(row => {
            if (role === 'all') {
                row.style.display = '';
            } else {
                const rowRole = row.querySelector('.role-badge').classList.contains('role-school-admin') ? 'school_admin' : 'branch_secretary';
                row.style.display = rowRole === role ? '' : 'none';
            }
        });
    }

    function filterAdminTable() {
        const input = document.getElementById('adminSearch');
        const filter = input.value.toLowerCase();
        const rows = document.querySelectorAll('.admin-table tbody tr');

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length < 3) return;

            const name = cells[0].textContent.toLowerCase();
            const email = cells[1].textContent.toLowerCase();
            const role = cells[2].textContent.toLowerCase();

            const isVisible = name.includes(filter) || email.includes(filter) || role.includes(filter);
            row.style.display = isVisible ? '' : 'none';
        });
    }

    function toggleAdminExportDropdown() {
        document.getElementById('adminExportMenu').classList.toggle('show');
    }

    // Close export dropdown when clicking outside
    window.addEventListener('click', function(e) {
        if (!e.target.closest('#adminExportDropdown')) {
            document.getElementById('adminExportMenu').classList.remove('show');
        }
    });

    window.openPendingInvitationsModal = function() {
        document.getElementById('pendingInvitationsModal').classList.add('active');
    };

    window.closePendingInvitationsModal = function() {
        document.getElementById('pendingInvitationsModal').classList.remove('active');
    };

    function initializePendingInvitationModalActions() {
        const pendingModal = document.getElementById('pendingInvitationsModal');
        if (!pendingModal) return;

        pendingModal.addEventListener('click', function(event) {
            const actionButton = event.target.closest('[data-pending-submit="1"]');
            if (!actionButton) return;

            const actionForm = actionButton.closest('form');
            if (!actionForm) return;

            if (actionButton.dataset.submitting === 'true') {
                return;
            }

            const confirmMessage = actionButton.getAttribute('data-confirm-message');
            if (confirmMessage && !confirm(confirmMessage)) {
                return;
            }

            // Reopen pending modal after redirect so action feedback stays in context.
            sessionStorage.setItem('adminManagementPendingModalReopen', '1');

            actionButton.dataset.submitting = 'true';
            actionButton.disabled = true;
            actionForm.submit();

            setTimeout(function() {
                actionButton.dataset.submitting = 'false';
                actionButton.disabled = false;
            }, 8000);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleBranchField('create');
        initializePendingInvitationModalActions();

        const reopenPendingModal = sessionStorage.getItem('adminManagementPendingModalReopen') === '1';
        if (reopenPendingModal) {
            sessionStorage.removeItem('adminManagementPendingModalReopen');
            window.openPendingInvitationsModal();
        }

        if (@json($errors->any()) && @json(old('role'))) {
            openCreateModal(@json(old('role')));
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
        
        console.log('PII Revealed (Temporary)');
    }
</script>
@endsection
