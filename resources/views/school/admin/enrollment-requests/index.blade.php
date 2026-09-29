@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Manage Enrollments')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $schoolName = $school->name ?? 'Driving School';
    $primaryColor = $settings->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
    use Illuminate\Support\Facades\Storage;
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .enrollment-requests-container {
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
        border-bottom: 3px solid {{ $primaryColor }};
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

    .icon-24 {
        width: 24px;
        height: 24px;
    }

    .icon-14 {
        width: 14px;
        height: 14px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    /* Active selection state for stat cards */
    .stat-card.active {
        border-left: 4px solid {{ $primaryColor }} !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        transform: translateY(-3px);
    }
    
    /* Override stat card cursor for clickable cards */
    .stat-card[onclick] {
        cursor: pointer;
    }
    
    .requests-table {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
        margin-top: 0;
    }
    
    .requests-table thead {
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        color: #ffffff;
    }

    .requests-table thead th {
        background: transparent;
        color: inherit;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        text-transform: none;
        font-size: 0.95rem;
        letter-spacing: 0;
    }

    .requests-table thead th:hover {
        background: transparent;
        color: inherit;
    }

    .requests-table thead th:first-child {
        border-top-left-radius: 12px;
    }

    .requests-table thead th:last-child {
        border-top-right-radius: 12px;
    }
    
    .requests-table tbody tr {
        background: transparent;
        transition: background-color 0.2s ease;
    }
    
    .requests-table tbody tr:hover {
        background-color: #f9fafb;
    }

    .requests-table tbody tr.has-cancellation-request {
        background: #f9fafb;
    }

    .requests-table tbody tr.has-cancellation-request:hover {
        background: #f3f4f6;
    }

    .requests-table tbody tr.has-cancellation-request td {
        color: #6b7280;
    }

    .requests-table tbody tr.has-cancellation-request .learner-info strong,
    .requests-table tbody tr.has-cancellation-request .course-name {
        color: #4b5563;
    }

    .requests-table tbody tr.has-cancellation-request .learner-email,
    .requests-table tbody tr.has-cancellation-request .course-type,
    .requests-table tbody tr.has-cancellation-request .date-text,
    .requests-table tbody tr.has-cancellation-request .date-text small {
        color: #9ca3af;
    }
    
    .requests-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .requests-table tbody tr td:first-child {
        border-radius: 0;
    }
    
    .requests-table tbody tr td:last-child {
        border-radius: 0;
    }
    
    .learner-info {
        display: flex;
        flex-direction: column;
    }
    
    .learner-name {
        font-weight: 600;
        color: #111827;
        margin-bottom: 3px;
    }
    
    .learner-email {
        font-size: 0.85rem;
        color: #9ca3af;
    }

    .license-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-top: 4px;
    }

    .license-none {
        background: #f3f4f6;
        color: #6b7280;
    }

    .license-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .license-verified {
        background: #d1fae5;
        color: #065f46;
    }

    .license-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .license-actions {
        display: flex;
        gap: 6px;
        margin-top: 4px;
    }

    .btn-license-verify {
        padding: 2px 8px;
        font-size: 0.7rem;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-license-reject {
        padding: 2px 8px;
        font-size: 0.7rem;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-license-view {
        padding: 2px 8px;
        font-size: 0.7rem;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
    }
    
    .course-info {
        display: flex;
        flex-direction: column;
    }
    
    .course-name {
        font-weight: 600;
        color: #111827;
        margin-bottom: 3px;
    }
    
    .course-type {
        font-size: 0.85rem;
        color: #6b7280;
    }
    
    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-completed {
        background: #dcfce7;
        color: #166534;
    }
    
    .status-cancelled {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    
    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .payment-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .payment-pending {
        background: #fef3c7;
        color: #78350f;
    }
    
    .payment-on_hold {
        background: #dbeafe;
        color: #1e3a8a;
    }
    
    .payment-paid {
        background: #d1fae5;
        color: #065f46;
    }

    .payment-cancelled {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .payment-rejected {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s ease, opacity 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        opacity: 0.9;
    }
    
    .btn-approve {
        background: #10b981;
        color: white;
    }
    
    .btn-reject {
        background: #ef4444;
        color: white;
    }

    .btn-cancel-request {
        background: #b91c1c;
        color: white;
    }
    
    .btn-view {
        background: #3b82f6;
        color: white;
    }
    
    .no-requests {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }
    
    .no-requests-icon {
        font-size: 4rem;
        margin-bottom: 15px;
    }
    
    .no-requests-text {
        font-size: 1.2rem;
    }
    
    .date-text {
        font-size: 0.9rem;
        color: #6b7280;
    }

    .inline-form {
        display: inline;
    }

    .contents-form {
        display: contents;
    }

    .status-muted {
        color: #9ca3af;
        font-size: 0.9rem;
    }

    .cancellation-cell {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .cancellation-pill {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .cancellation-pill-requested {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .cancellation-reason {
        font-size: 0.76rem;
        color: #6b7280;
        line-height: 1.35;
        max-width: 240px;
    }

    .mobile-learner-name {
        font-size: 1rem;
    }

    .mobile-learner-email {
        color: #6b7280;
        font-size: 0.8rem;
    }

    .license-badge-inline {
        margin-top: 4px;
        display: inline-block;
    }

    .branch-scope-banner {
        padding: 12px 18px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .branch-scope-icon {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }

    .branch-scope-text {
        color: #1e40af;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .branch-filter-wrap {
        margin-bottom: 16px;
    }

    .filters-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
    }

    .filters-row .branch-filter-wrap {
        margin-bottom: 0;
    }

    .branch-filter-select {
        max-width: 300px;
        display: inline-block;
        padding: 8px 12px;
        border: 2px solid #e1e5e9;
        border-radius: 8px;
        font-size: 0.9rem;
    }

    .branch-filter-select:focus {
        outline: none;
        border-color: {{ $primaryColor }};
    }

    .filters-row .branch-filter-select {
        max-width: 340px;
        width: 100%;
    }

    .action-bar-wrapper {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        gap: 10px;
    }

    .table-container {
        background: white;
        border-radius: 12px;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .export-actions {
        display: flex;
        gap: 10px;
        margin-left: auto;
    }

    .btn-icon-sm {
        width: 16px;
        height: 16px;
        display: inline-block;
        vertical-align: middle;
    }

    .export-menu-wrap {
        position: relative;
    }

    .export-btn {
        padding: 6px 14px;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        display: flex;
        align-items: center;
        gap: 8px;
        height: 40px;
        font-size: 0.9rem;
    }

    .export-menu {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 5px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        min-width: 180px;
        z-index: 10;
    }

    .export-menu-link {
        display: block;
        padding: 10px 15px;
        text-decoration: none;
        color: #374151;
        transition: background 0.2s;
    }

    .export-menu-link:hover {
        background: #f3f4f6;
    }

    .export-menu-link.with-border {
        border-top: 1px solid #e5e7eb;
    }


    .empty-state-icon {
        width: 48px;
        height: 48px;
        color: #9ca3af;
    }

    .action-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .action-modal-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        max-width: 500px;
        width: 90%;
    }

    .action-modal-title {
        margin: 0 0 20px 0;
        color: #333;
    }

    .action-modal-title-tight {
        margin: 0 0 8px 0;
        color: #333;
    }

    .action-modal-subtitle {
        margin: 0 0 20px 0;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .action-modal-field {
        margin-bottom: 20px;
    }

    .action-modal-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }

    .action-modal-input {
        width: 100%;
        padding: 12px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        resize: none;
    }

    .action-modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .action-modal-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
    }

    .action-modal-btn-secondary {
        background: #e5e7eb;
        color: #333;
    }

    .action-modal-btn-danger {
        background: #ef4444;
        color: white;
    }

    .action-modal-btn-neutral {
        background: #6b7280;
        color: white;
    }

    /* Action Menu Dropdown */
    .action-menu-dropdown {
        position: relative;
        display: inline-block;
    }

    .action-menu-btn {
        background: none;
        border: none;
        padding: 4px 8px;
        cursor: pointer;
        color: #6b7280;
        border-radius: 4px;
        transition: background 0.2s;
    }

    .action-menu-btn:hover {
        background: #f3f4f6;
        color: #111827;
    }

    .action-menu-content {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        background-color: white;
        min-width: 160px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-radius: 8px;
        z-index: 50;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    .action-menu-dropdown.show .action-menu-content {
        display: block;
    }

    .action-menu-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        color: #374151;
        text-decoration: none;
        font-size: 0.85rem;
        transition: background 0.2s;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
    }

    .action-menu-link:hover {
        background-color: #f9fafb;
        color: #111827;
    }

    .action-menu-link.text-danger {
        color: #dc2626;
    }

    .action-menu-link.text-danger:hover {
        background-color: #fef2f2;
    }

    .action-menu-link i, .action-menu-link svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }

    /* Mobile card layout */
    .mobile-card {
        display: none;
        background: white;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-left: 4px solid {{ $primaryColor }};
    }

    .mobile-card.has-cancellation-request {
        background: #f8fafc;
        border-left-color: #9ca3af;
    }

    .mobile-card.has-cancellation-request .mobile-learner-name,
    .mobile-card.has-cancellation-request .mobile-card-value {
        color: #4b5563 !important;
    }

    .mobile-card.has-cancellation-request .mobile-learner-email,
    .mobile-card.has-cancellation-request .mobile-card-label {
        color: #9ca3af;
    }

    .mobile-cancellation-reason {
        margin-top: 8px;
        font-size: 0.78rem;
        color: #6b7280;
        line-height: 1.35;
    }
    
    .mobile-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }
    
    .mobile-card-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .mobile-card-row:last-child {
        border-bottom: none;
    }
    
    .mobile-card-label {
        color: #6b7280;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .mobile-card-value {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.85rem;
        text-align: right;
    }
    
    .mobile-card-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
        flex-wrap: wrap;
    }
    
    .mobile-card-actions .btn {
        flex: 1;
        min-width: 100px;
        padding: 10px 12px;
        font-size: 0.85rem;
        text-align: center;
        min-height: 44px;
    }
    
    /* Mobile responsive */
    @media (max-width: 768px) {
        .enrollment-requests-container {
            padding: 12px;
            margin: 10px auto;
        }
        
        .page-header {
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }
        
        .page-title {
            font-size: 1.3rem;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .stat-value {
            font-size: 1.3rem;
        }
        
        .stat-label {
            font-size: 0.7rem;
        }
        
        /* Hide table, show cards */
        .table-container {
            display: none;
        }
        
        .mobile-card {
            display: block;
        }
        
        /* Action bar responsive */
        .action-bar-wrapper {
            flex-direction: column;
            gap: 10px;
        }
        
        .btn {
            min-height: 44px;
            padding: 10px 14px;
        }

        .filters-row {
            flex-direction: column;
            align-items: stretch;
        }

        .filters-row .branch-filter-select {
            max-width: 100%;
            display: block;
        }
    }
    
    @media (max-width: 480px) {
        .enrollment-requests-container {
            padding: 8px;
        }
        
        .page-title {
            font-size: 1.1rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        
        .mobile-card {
            padding: 12px;
        }
        
        .mobile-card-actions {
            flex-direction: column;
        }
        
        .mobile-card-actions .btn {
            width: 100%;
        }
    }

    /* Unified Verification Modal Layout */
    .verification-modal-card {
        background: white;
        border-radius: 12px;
        width: min(1200px, 98vw);
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    
    .v-modal-header {
        padding: 16px 24px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .v-modal-body {
        display: flex;
        overflow-y: auto;
        padding: 0;
        flex: 1;
    }
    
    .v-modal-sidebar {
        width: 300px;
        background: #fff;
        border-right: 1px solid #e2e8f0;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .v-modal-content {
        flex: 1;
        background: #f1f5f9;
        display: flex;
        gap: 2px;
    }
    
    .v-image-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #1e293b;
        position: relative;
    }
    
    .v-panel-title {
        background: rgba(0,0,0,0.5);
        color: white;
        padding: 8px 16px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        z-index: 10;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .v-image-viewer {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        padding: 40px 20px;
    }
    
    .v-image-viewer img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        box-shadow: 0 4px 12px rgba(0,0,0,0.5);
        border: 2px solid #475569;
    }

    .v-info-label {
        font-size: 0.75rem;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 700;
        margin-bottom: 4px;
    }
    
    .v-info-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
        word-break: break-all;
    }
    
    .v-panel-status {
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 700;
    }

    .v-btn-group {
        display: flex;
        gap: 10px;
        margin-top: auto;
        padding: 20px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .v-empty-image {
        color: #64748b;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .v-panel-actions {
        background: rgba(15, 23, 42, 0.9);
        padding: 12px;
        display: flex;
        justify-content: center;
        gap: 10px;
        border-top: 1px solid #334155;
    }

    .v-btn-panel {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .v-btn-panel-approve {
        background: #10b981;
        color: white;
    }

    .v-btn-panel-reject {
        background: #ef4444;
        color: white;
    }

    .v-btn-panel:hover {
        transform: translateY(-1px);
        opacity: 0.9;
    }

    .v-btn-panel:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .btn-locked {
        opacity: 0.5 !important;
        filter: grayscale(1) !important;
        cursor: not-allowed !important;
        pointer-events: auto !important; /* Keep it clickable for JS feedback */
    }
</style>

<div class="enrollment-requests-container">
    <!-- Page Header -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h1 class="page-title">Manage Enrollments</h1>
            <p class="page-subtitle">View and manage all enrollment requests and active student enrollments</p>
        </div>
        <div class="export-dropdown" id="enrollmentExport">
            <button type="button" class="btn-export-trigger" onclick="this.parentElement.classList.toggle('open')">
                <i class="bi bi-download"></i>
                Export Report
                <i class="bi bi-chevron-down chevron"></i>
            </button>
            <div class="export-dropdown-menu">
                <div class="dropdown-header">Export Options</div>
                <a href="{{ route('schools.admin.exports.enrollments.pdf', ['school' => $school->slug]) }}">
                    <i class="bi bi-file-earmark-pdf-fill" style="color: #ff4d4d;"></i> All Enrollments (PDF)
                </a>
                <a href="{{ route('schools.admin.exports.enrollments.pdf', ['school' => $school->slug, 'status' => 'pending']) }}" class="dropdown-divider-top">
                    <i class="bi bi-clock-history" style="color: #f59e0b;"></i> Pending Only (PDF)
                </a>
                <a href="{{ route('schools.admin.exports.enrollments.pdf', ['school' => $school->slug, 'status' => 'approved']) }}">
                    <i class="bi bi-check-circle-fill" style="color: #10b981;"></i> Active Only (PDF)
                </a>
                <a href="{{ route('schools.admin.exports.enrollments.pdf', ['school' => $school->slug, 'status' => 'completed']) }}">
                    <i class="bi bi-flag-fill" style="color: #3b82f6;"></i> Completed Only (PDF)
                </a>
                <div class="dropdown-header">Spreadsheet</div>
                <a href="{{ route('schools.admin.exports.enrollments.excel', ['school' => $school->slug]) }}">
                    <i class="bi bi-file-earmark-excel-fill" style="color: #10b981;"></i> Full Export (Excel)
                </a>
            </div>
        </div>
    </div>

    @if($admin->isBranchSecretary() && $admin->branch)
    <div class="branch-scope-banner">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#2563eb" class="branch-scope-icon"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" /></svg>
        <span class="branch-scope-text">Showing enrollments for your branch: <strong>{{ $admin->branch->name }}</strong></span>
    </div>
    @endif

    <div class="stats-grid">
        <div class="stat-card info {{ request('status', 'all') === 'all' ? 'active' : '' }}" onclick="applyEnrollmentStatusFilter('all')">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">All Enrollments</div>
                        <div class="stat-value">{{ $allRequestsCount }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card pending {{ request('status') === 'pending' ? 'active' : '' }}" onclick="applyEnrollmentStatusFilter('pending')">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Pending Approval</div>
                        <div class="stat-value">{{ $pendingRequestsCount }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card growth {{ request('status') === 'approved' ? 'active' : '' }}" onclick="applyEnrollmentStatusFilter('approved')">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Active</div>
                        <div class="stat-value">{{ $approvedRequestsCount }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card danger {{ request('status') === 'rejected' ? 'active' : '' }}" onclick="applyEnrollmentStatusFilter('rejected')">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Rejected</div>
                        <div class="stat-value">{{ $rejectedRequestsCount }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="filters-row">
        @if($branches->count() > 0 && !$admin->isBranchSecretary())
        <div class="branch-filter-wrap">
            <select id="branchFilter" class="form-select branch-filter-select" onchange="applyEnrollmentBranchFilter()">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->name }}" {{ request('branch') === $branch->name ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="branch-filter-wrap">
            <select id="sortFilter" class="form-select branch-filter-select" onchange="applyEnrollmentSortFilter()">
                <option value="priority" {{ ($currentSort ?? request('sort', 'priority')) === 'priority' ? 'selected' : '' }}>Sort: Priority (Approvals -> Withdrawals)</option>
                <option value="withdrawal_first" {{ ($currentSort ?? request('sort', 'priority')) === 'withdrawal_first' ? 'selected' : '' }}>Sort: Withdrawal Requested First</option>
                <option value="newest" {{ ($currentSort ?? request('sort', 'priority')) === 'newest' ? 'selected' : '' }}>Sort: Newest First</option>
                <option value="oldest" {{ ($currentSort ?? request('sort', 'priority')) === 'oldest' ? 'selected' : '' }}>Sort: Oldest First</option>
                <option value="learner_az" {{ ($currentSort ?? request('sort', 'priority')) === 'learner_az' ? 'selected' : '' }}>Sort: Learner A-Z</option>
                <option value="learner_za" {{ ($currentSort ?? request('sort', 'priority')) === 'learner_za' ? 'selected' : '' }}>Sort: Learner Z-A</option>
                <option value="status_az" {{ ($currentSort ?? request('sort', 'priority')) === 'status_az' ? 'selected' : '' }}>Sort: Status A-Z</option>
                <option value="status_za" {{ ($currentSort ?? request('sort', 'priority')) === 'status_za' ? 'selected' : '' }}>Sort: Status Z-A</option>
            </select>
        </div>
    </div>
    
    
    @if($allRequests->count() > 0)
        <div class="table-container">
        <table class="requests-table">
            <thead>
                <tr>
                    <th>Learner</th>
                    <th>Experience</th>
                    <th>Course</th>
                    <th>DL Code</th>
                    <th>Branch</th>
                    <th>Fee</th>
                    <th>Status</th>
                    <th>Cancellation Request</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allRequests as $request)
                    @php
                        $displayFee = (float) ($request->price ?? 0);
                        if ($displayFee <= 0) {
                            $displayFee = (float) ($request->package->price ?? ($request->course->price ?? 0));
                        }
                    @endphp
                    <tr class="{{ $request->cancellation_requested ? 'has-cancellation-request' : '' }}" data-status="{{ $request->status }}" data-request-id="{{ $request->id }}" data-branch="{{ $request->branchRelation?->name ?? '' }}">
                        <td>
                            <div class="learner-info">
                                <strong>{{ $request->learner->name }}</strong>
                            </div>
                            <div class="learner-email">{{ $request->learner->email }}</div>
                        </td>
                        <td>
                            @if($request->experience_level === 'experienced')
                                <span class="badge bg-primary" title="Experienced Driver">Experienced</span>
                            @else
                                <span class="badge bg-secondary" title="New Driver">New Driver</span>
                            @endif
                        </td>
                        <td>
                            <div class="course-info">
                                <div class="course-name">{{ $request->course->title ?: 'N/A' }}</div>
                                <div class="course-type">{{ ucfirst($request->course->type ?? 'standard') }}</div>
                            </div>
                        </td>
                        <td>
                            @if($request->requested_dl_code)
                                <span class="badge bg-info text-dark" title="Requested License Code">
                                    {{ $request->requested_dl_code }}
                                </span>
                            @else
                                <span class="text-muted small">N/A</span>
                            @endif
                        </td>
                        <td>{{ $request->branchRelation?->name ?: '—' }}</td>
                        <td>
                            <strong>&#8369;{{ number_format($displayFee, 2) }}</strong>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <span class="status-badge status-{{ $request->status }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </div>
                        </td>
                        <td>
                            @if($request->cancellation_requested)
                                <div class="cancellation-cell">
                                    <span class="cancellation-pill cancellation-pill-requested">Requested</span>
                                    <span class="cancellation-reason" title="{{ $request->cancellation_reason }}">
                                        {{ \Illuminate\Support\Str::limit($request->cancellation_reason, 90) }}
                                    </span>
                                </div>
                            @else
                                <span class="status-muted">No request</span>
                            @endif
                        </td>
                        <td>
                            <span class="payment-badge payment-{{ $request->payment_status }}">
                                {{ ucfirst(str_replace('_', ' ', $request->payment_status)) }}
                            </span>
                        </td>
                        <td>
                            <div class="date-text">
                                {{ $request->created_at->timezone($school->timezone ?? 'Asia/Manila')->format('M d, Y') }}<br>
                                <small>{{ $request->created_at->timezone($school->timezone ?? 'Asia/Manila')->format('h:i A') }}</small>
                            </div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                @if($request->status === 'pending' && $request->cancellation_requested)
                                    <form id="withdrawForm{{ $request->id }}" method="POST" action="{{ route('schools.admin.enrollments.cancel', ['school' => $school, 'enrollmentRequest' => $request->id]) }}" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="remarks" value="Cancelled upon guest withdrawal request.">
                                        <button type="button" class="btn btn-cancel-request" title="Mark this enrollment as withdrawn" onclick="approveWithdrawal('withdrawForm{{ $request->id }}')">
                                            Approve Withdrawal
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-view" onclick="openVerificationModal({{ $request->id }})" title="Review request details">
                                        Review Details
                                    </button>
                                @elseif(in_array($request->status, ['pending', 'revision_required']))
                                    <button type="button" class="btn btn-view" onclick="openVerificationModal({{ $request->id }})" title="Review Enrollment Verification">
                                        Review & Verify Documents
                                    </button>
                                    
                                    @php
                                        $isFullyVerified = ($request->license_status === 'verified' || $request->license_status === 'none') && 
                                                           ($request->payment_status === 'paid' || $request->payment_status === 'none');
                                        $atLeastOneProcessed = ($request->license_status !== 'pending') || ($request->payment_status !== 'pending');
                                    @endphp

                                    <form id="approveForm{{ $request->id }}" method="POST" action="{{ route('schools.admin.enrollments.api.unified-approve', ['school' => $school, 'enrollmentRequest' => $request->id]) }}" style="display:inline;">
                                        @csrf
                                        <button type="button" id="outerApprove{{ $request->id }}" 
                                                class="btn btn-approve {{ !$isFullyVerified ? 'btn-locked' : '' }}" 
                                                onclick="approveRequest({{ $request->id }})" 
                                                title="{{ !$isFullyVerified ? 'Verify documents first' : 'Final Approve' }}">
                                            &#10003; Final Approve
                                        </button>
                                    </form>

                                    <button type="button" id="outerReject{{ $request->id }}" 
                                            class="btn btn-reject {{ !$atLeastOneProcessed ? 'btn-locked' : '' }}" 
                                            onclick="showRejectModal({{ $request->id }})" 
                                            title="{{ !$atLeastOneProcessed ? 'Review documents first' : 'Reject' }}">
                                        &#10005; Reject
                                    </button>
                                @elseif($request->status === 'approved')
                                    <button type="button" class="btn btn-view" onclick="openVerificationModal({{ $request->id }})" title="View Enrollment Record">
                                        View Record
                                    </button>
                                    <span class="badge bg-success" style="font-size: 0.8rem; padding: 6px 10px;">Active Student</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        
        {{-- Mobile card view --}}
        @foreach($allRequests as $request)
        @php
            $displayFee = (float) ($request->price ?? 0);
            if ($displayFee <= 0) {
                $displayFee = (float) ($request->package->price ?? ($request->course->price ?? 0));
            }
        @endphp
        <div class="mobile-card {{ $request->cancellation_requested ? 'has-cancellation-request' : '' }}" data-status="{{ $request->status }}" data-request-id="{{ $request->id }}" data-branch="{{ $request->branchRelation?->name ?? '' }}">
            <div class="mobile-card-header">
                <div>
                    <strong class="mobile-learner-name" style="color: #374151;">
                        {{ $request->learner->name }}
                    </strong>
                    <div class="mobile-learner-email">{{ $request->learner->email }}</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="status-badge status-{{ $request->status }}">{{ ucfirst($request->status) }}</span>
                </div>
            </div>
            <div class="mobile-card-row">
                <span class="mobile-card-label">Course</span>
                <span class="mobile-card-value">{{ $request->course->title ?: 'N/A' }}</span>
            </div>
            <div class="mobile-card-row">
                <span class="mobile-card-label">DL Code</span>
                <span class="mobile-card-value">
                    @if($request->requested_dl_code)
                        <span class="badge bg-info text-dark" style="font-size: 0.75rem;">{{ $request->requested_dl_code }}</span>
                    @else
                        N/A
                    @endif
                </span>
            </div>
            <div class="mobile-card-row">
                <span class="mobile-card-label">Branch</span>
                <span class="mobile-card-value">{{ $request->branchRelation?->name ?: '—' }}</span>
            </div>
            <div class="mobile-card-row">
                <span class="mobile-card-label">Fee</span>
                <span class="mobile-card-value">&#8369;{{ number_format($displayFee, 2) }}</span>
            </div>
            <div class="mobile-card-row">
                <span class="mobile-card-label">Cancellation</span>
                <span class="mobile-card-value">
                    @if($request->cancellation_requested)
                        <span class="cancellation-pill cancellation-pill-requested">Requested</span>
                    @else
                        <span class="status-muted">No request</span>
                    @endif
                </span>
            </div>
            <div class="mobile-card-row">
                <span class="mobile-card-label">Payment</span>
                <span class="payment-badge payment-{{ $request->payment_status }}">
                    {{ ucfirst(str_replace('_', ' ', $request->payment_status)) }}
                </span>
            </div>
            <div class="mobile-card-row">
                <span class="mobile-card-label">Date</span>
                <span class="mobile-card-value">{{ $request->created_at->timezone($school->timezone ?? 'Asia/Manila')->format('M d, Y h:i A') }}</span>
            </div>
            @if($request->cancellation_requested)
                <div class="mobile-cancellation-reason" title="{{ $request->cancellation_reason }}">
                    <strong>Reason:</strong> {{ \Illuminate\Support\Str::limit($request->cancellation_reason, 100) }}
                </div>
            @endif
            @if(in_array($request->status, ['pending', 'approved']))
                <div class="mobile-card-actions">
                    @if($request->status === 'pending' && $request->cancellation_requested)
                        <form id="mobileWithdrawForm{{ $request->id }}" method="POST" action="{{ route('schools.admin.enrollments.cancel', ['school' => $school, 'enrollmentRequest' => $request->id]) }}" style="display:inline;">
                            @csrf
                            <input type="hidden" name="remarks" value="Cancelled upon guest withdrawal request.">
                            <button type="button" class="btn btn-cancel-request" onclick="approveWithdrawal('mobileWithdrawForm{{ $request->id }}')">Approve Withdrawal</button>
                        </form>
                        <button type="button" class="btn btn-view" onclick="openVerificationModal({{ $request->id }})">
                            Review Details
                        </button>
                    @elseif(in_array($request->status, ['pending', 'revision_required']))
                        <button type="button" class="btn btn-view" onclick="openVerificationModal({{ $request->id }})">
                            Review & Verify Documents
                        </button>
                        <form id="mobileApproveForm{{ $request->id }}" method="POST" action="{{ route('schools.admin.enrollments.api.unified-approve', ['school' => $school, 'enrollmentRequest' => $request->id]) }}" style="display:inline;">
                            @csrf
                            <button type="button" id="mobileOuterApprove{{ $request->id }}" class="btn btn-approve opacity-50" disabled onclick="approveRequest({{ $request->id }})">&#10003; Final Approve</button>
                        </form>
                        <button id="mobileOuterReject{{ $request->id }}" class="btn btn-reject" onclick="showRejectModal({{ $request->id }})">&#10005; Reject</button>
                    @elseif($request->status === 'approved')
                        <button type="button" class="btn btn-view" onclick="openVerificationModal({{ $request->id }})">
                            View Record
                        </button>
                        <span class="badge bg-success" style="padding: 8px 12px; border-radius: 6px; font-size: 0.8rem;">Active Student</span>
                    @endif
                </div>
            @endif
        </div>
        @endforeach

        @if($allRequests->hasPages())
            <div class="admin-pagination-wrapper">
                <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <div style="color: #6b7280; font-size: 0.9rem;">
                        Showing {{ $allRequests->firstItem() ?? 0 }} to {{ $allRequests->lastItem() ?? 0 }} of {{ $allRequests->total() }} results
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        @if($allRequests->onFirstPage())
                            <span style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; color: #9ca3af; background: #f9fafb;">Previous</span>
                        @else
                            <a href="{{ $allRequests->previousPageUrl() }}" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; color: #374151; text-decoration: none; background: white;">Previous</a>
                        @endif

                        <span style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; color: #374151; background: #f9fafb;">
                            Page {{ $allRequests->currentPage() }} of {{ $allRequests->lastPage() }}
                        </span>

                        @if($allRequests->hasMorePages())
                            <a href="{{ $allRequests->nextPageUrl() }}" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; color: #374151; text-decoration: none; background: white;">Next</a>
                        @else
                            <span style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; color: #9ca3af; background: #f9fafb;">Next</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
        
    @else
        <div class="no-requests">
            <div class="no-requests-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="empty-state-icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div class="no-requests-text">No enrollment requests yet</div>
        </div>
    @endif
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="action-modal modal">
    <div class="action-modal-card">
        <h3 class="action-modal-title">Reject Enrollment Request</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="action-modal-field">
                <label class="action-modal-label">Selective Rejection (Checklist)</label>
                <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 10px; padding: 12px; background: #fff5f5; border-radius: 8px; border: 1px solid #fed7d7;">
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: 0.95rem; color: #c53030; font-weight: 500;">
                        <input type="checkbox" name="reject_license" value="1" style="width: 20px; height: 20px; accent-color: #e53e3e;">
                        Reject Identity Document (License)
                    </label>
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: 0.95rem; color: #c53030; font-weight: 500;">
                        <input type="checkbox" name="reject_payment" value="1" style="width: 20px; height: 20px; accent-color: #e53e3e;">
                        Reject Payment Proof
                    </label>
                    <p style="font-size: 0.8rem; color: #742a2a; margin: 4px 0 0 0; line-height: 1.4;">
                        <i class="fas fa-info-circle"></i> If checked, the enrollment stays <strong>Pending</strong> and the student is asked to re-upload. If both are unchecked, the enrollment is <strong>Fully Rejected</strong>.
                    </p>
                </div>
            </div>
            <div class="action-modal-field">
                <label for="remarks" class="action-modal-label">
                    Feedback Message to Student *
                </label>
                <textarea id="remarks" name="remarks" rows="4" required
                    class="action-modal-input"
                    placeholder="Provide a message explaining what needs to be fixed..."></textarea>
            </div>
            <div class="action-modal-actions">
                <button type="button" onclick="closeRejectModal()" class="action-modal-btn action-modal-btn-secondary">
                    Cancel
                </button>
                <button type="submit" id="rejectSubmitBtn" class="action-modal-btn action-modal-btn-danger">
                    Reject Request
                </button>
            </div>
        </form>
    </div>
</div>


<!-- License Reject Modal -->
<div id="licenseRejectModal" class="action-modal modal">
    <div class="action-modal-card">
        <h3 class="action-modal-title-tight">Reject Student License</h3>
        <p id="licenseRejectStudentName" class="action-modal-subtitle"></p>
        <form id="licenseRejectForm" method="POST">
            @csrf
            <div class="action-modal-field">
                <label for="rejection_reason" class="action-modal-label">
                    Reason for Rejection *
                </label>
                <textarea id="rejection_reason" name="rejection_reason" rows="4" required
                    class="action-modal-input"
                    placeholder="Explain why this license is being rejected (e.g., expired, unreadable, wrong document)..."></textarea>
            </div>
            <div class="action-modal-actions">
                <button type="button" onclick="closeLicenseRejectModal()" class="action-modal-btn action-modal-btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="action-modal-btn action-modal-btn-danger">
                    Reject License
                </button>
            </div>
        </form>
    </div>
</div>

<!-- License Preview Modal -->
<div id="licensePreviewModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 1001; justify-content: center; align-items: center; padding: 16px;">
    <div style="background: white; border-radius: 12px; width: min(1100px, 96vw); height: min(88vh, 900px); display: flex; flex-direction: column; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid #e5e7eb;">
            <div>
                <h3 style="margin: 0; color: #111827; font-size: 1rem;">Student License Preview</h3>
                <p id="licensePreviewStudent" style="margin: 2px 0 0 0; color: #6b7280; font-size: 0.85rem;"></p>
            </div>
            <button type="button" onclick="closeLicensePreviewModal()" style="border: none; background: transparent; font-size: 1.5rem; line-height: 1; color: #6b7280; cursor: pointer;">&times;</button>
        </div>
        <div style="flex: 1; background: #f3f4f6;">
            <iframe id="licensePreviewFrame" src="" title="License Preview" style="width: 100%; height: 100%; border: 0;"></iframe>
        </div>
    </div>
</div>

<!-- Custom Info Modal -->
<div id="infoModal" class="action-modal modal">
    <div class="action-modal-card" style="max-width: 400px; text-align: center;">
        <div style="width: 60px; height: 60px; background: #fffbeb; color: #f59e0b; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 30px; font-weight: bold; border: 2px solid #fef3c7;">
            !
        </div>
        <h3 class="action-modal-title" id="infoModalTitle">Attention Required</h3>
        <p class="action-modal-subtitle" id="infoModalMessage" style="margin-bottom: 25px; line-height: 1.5;"></p>
        <div class="action-modal-actions" style="justify-content: center;">
            <button type="button" onclick="closeInfoModal()" class="action-modal-btn" style="background: #1e293b; color: white; border: none; padding: 10px 30px;">
                Got it
            </button>
        </div>
    </div>
</div>

<script>
// Server-side filtering is now used.
// JS applyFilters and filterRequests functions removed.

var enrollmentBaseUrl = @json(school_route('admin.enrollments.index'));

function getEnrollmentFilterState() {
    const params = new URLSearchParams(window.location.search);
    const branchFilter = document.getElementById('branchFilter');
    const sortFilter = document.getElementById('sortFilter');
    const currentStatus = params.get('status') || 'all';
    const currentBranch = params.get('branch') || '';
    const currentSort = params.get('sort') || 'priority';

    return {
        status: currentStatus,
        branch: branchFilter ? (branchFilter.value || '') : currentBranch,
        sort: sortFilter ? (sortFilter.value || currentSort) : currentSort,
    };
}

function buildEnrollmentUrl(filters = {}) {
    const merged = Object.assign({}, getEnrollmentFilterState(), filters || {});
    const url = new URL(enrollmentBaseUrl || window.location.pathname, window.location.origin);

    url.searchParams.set('status', merged.status || 'all');
    url.searchParams.set('sort', merged.sort || 'priority');

    if (merged.branch) {
        url.searchParams.set('branch', merged.branch);
    }

    return url;
}

function navigateWithEnrollmentFilters(nextFilters, resetPage = true) {
    const url = buildEnrollmentUrl(nextFilters);
    if (resetPage) {
        url.searchParams.delete('page');
    }

    const target = url.pathname + url.search;

    if (typeof loadContent === 'function') {
        loadContent(target);
        return;
    }

    window.location.href = target;
}

function applyEnrollmentStatusFilter(status) {
    navigateWithEnrollmentFilters({ status: status || 'all' }, true);
}

function applyEnrollmentBranchFilter() {
    const branchFilter = document.getElementById('branchFilter');
    if (!branchFilter) {
        return;
    }

    navigateWithEnrollmentFilters({ branch: branchFilter.value || '' }, true);
}

function applyEnrollmentSortFilter() {
    const sortFilter = document.getElementById('sortFilter');
    if (!sortFilter) {
        return;
    }

    navigateWithEnrollmentFilters({ sort: sortFilter.value || 'priority' }, true);
}

function showRejectModal(requestId) {
    const btn = document.getElementById(`outerReject${requestId}`);
    if (btn && btn.classList.contains('btn-locked')) {
        showInfoModal('Verification Required', 'Please open the Verification Dashboard and review at least one document (License or Payment) before rejecting the entire enrollment.');
        return;
    }

    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = `{{ route('schools.admin.enrollments.reject', ['school' => $school, 'enrollmentRequest' => ':id']) }}`.replace(':id', requestId);
    modal.style.display = 'flex';
}


function approveRequest(requestId) {
    const btn = document.getElementById(`outerApprove${requestId}`);
    if (btn && btn.classList.contains('btn-locked')) {
        showInfoModal('Documents Pending', 'Please verify all documents (Identity & Payment) in the Verification Dashboard before finalizing the enrollment.');
        return;
    }

    showConfirm({
        type: 'success',
        title: 'Approve Enrollment',
        message: 'This will verify the license, confirm the payment, and officially activate the student account in one step. Proceed?',
        confirmText: 'Fully Approve Student',
        onConfirm: function() {
            // Submit the approve form (regular POST)
            var form = document.getElementById('approveForm' + requestId) || document.getElementById('mobileApproveForm' + requestId);
            if (form) form.submit();
        }
    });
}

function approveWithdrawal(formId) {
    const submitForm = function() {
        const form = document.getElementById(formId);
        if (form) {
            form.submit();
        }
    };

    if (typeof showConfirm !== 'function') {
        return;
    }

    showConfirm({
        type: 'warning',
        title: 'Approve Withdrawal',
        message: 'This will withdraw and cancel the enrollment request. Continue?',
        confirmText: 'Approve Withdrawal',
        onConfirm: submitForm,
    });
}


function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.style.display = 'none';
    document.getElementById('remarks').value = '';
}

function showInfoModal(title, message) {
    document.getElementById('infoModalTitle').textContent = title;
    document.getElementById('infoModalMessage').textContent = message;
    document.getElementById('infoModal').style.display = 'flex';
}

function closeInfoModal() {
    document.getElementById('infoModal').style.display = 'none';
}


// Close modals when clicking outside
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});

document.getElementById('infoModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeInfoModal();
    }
});


// License Reject Modal
function showLicenseRejectModal(studentId, studentName) {
    const modal = document.getElementById('licenseRejectModal');
    const form = document.getElementById('licenseRejectForm');
    document.getElementById('licenseRejectStudentName').textContent = 'Student: ' + studentName;
    form.action = '{{ route('schools.admin.enrollments.rejectLicense', ['school' => $school, 'student' => '__STUDENT_ID__']) }}'.replace('__STUDENT_ID__', studentId);
    modal.style.display = 'flex';
}

function closeLicenseRejectModal() {
    const modal = document.getElementById('licenseRejectModal');
    modal.style.display = 'none';
    document.getElementById('rejection_reason').value = '';
}

function showLicensePreviewModal(url, studentName) {
    const modal = document.getElementById('licensePreviewModal');
    const frame = document.getElementById('licensePreviewFrame');
    const student = document.getElementById('licensePreviewStudent');
    student.textContent = 'Student: ' + studentName;
    frame.src = url;
    modal.style.display = 'flex';
}

function closeLicensePreviewModal() {
    const modal = document.getElementById('licensePreviewModal');
    const frame = document.getElementById('licensePreviewFrame');
    modal.style.display = 'none';
    frame.src = '';
}

document.getElementById('licenseRejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLicenseRejectModal();
    }
});

document.getElementById('licensePreviewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLicensePreviewModal();
    }
});


// Prevent double-submit on modal forms
['licenseRejectForm'].forEach(function(formId) {
    var formEl = document.getElementById(formId);
    if (formEl) {
        formEl.addEventListener('submit', function(e) {
            var submitBtn = formEl.querySelector('button[type="submit"]');
            if (submitBtn.disabled) { e.preventDefault(); return; }
            submitBtn.disabled = true;
        });
    }
});

// Reject form submits as a normal POST (no AJAX needed)
document.getElementById('rejectForm')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('rejectSubmitBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
    }
});

// Export Menu Toggle
function toggleExportMenu() {
    const menu = document.getElementById('exportMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

// Close export menu when clicking outside
document.addEventListener('click', function(e) {
    const menu = document.getElementById('exportMenu');
    const button = e.target.closest('button');
    if (menu && menu.style.display === 'block' && (!button || !button.onclick || button.onclick.toString().indexOf('toggleExportMenu') === -1)) {
        menu.style.display = 'none';
    }
});

/**
 * Unified Verification Modal Logic
 */
let currentEnrollmentId = null;

function openVerificationModal(enrollmentId) {
    currentEnrollmentId = enrollmentId;
    const modal = document.getElementById('unifiedVerificationModal');
    modal.style.display = 'flex';
    
    // Show loading state
    document.getElementById('v-modal-loading').style.display = 'flex';
    document.getElementById('v-modal-content').style.visibility = 'hidden';
    
    fetch(`{{ school_route('admin.enrollments.index') }}/api/${enrollmentId}`)
        .then(res => res.json())
        .then(data => {
            // Update Info Sidebar
            document.getElementById('v-student-name').textContent = data.student_name;
            document.getElementById('v-course-title').textContent = data.course_title;
            document.getElementById('v-dl-code').textContent = data.requested_dl_code || 'N/A';
            document.getElementById('v-price').textContent = '₱' + data.total_price;
            const paymentMethod = data.payment_method === 'on_site' ? 'on_site' : 'gcash';
            document.getElementById('v-reference-label').textContent = paymentMethod === 'on_site' ? 'OR Number' : 'GCash Reference No.';
            document.getElementById('v-reference').textContent = data.reference_number || 'N/A';
            
            // Update Statuses in Panel Titles
            updatePanelStatus('license', data.license_status);
            updatePanelStatus('payment', data.payment_status);
            
            // Update Images
            updatePanelImage('license', data.license_url, 'Student License');
            updatePanelImage('payment', data.receipt_url, paymentMethod === 'on_site' ? 'On-site Receipt' : 'GCash Receipt');
            
            // Update Action Buttons Visibility
            const isFinalized = ['approved', 'rejected', 'cancelled', 'completed'].includes(data.status);
            
            // Show license actions if document exists, is pending, and request is not finalized
            const licenseActions = document.getElementById('v-license-actions');
            if (licenseActions) licenseActions.style.display = (data.license_url && (data.license_status === 'pending' || data.license_status === 'none') && !isFinalized) ? 'flex' : 'none';
            // Show payment actions if document exists, is pending, and request is not finalized
            const paymentActions = document.getElementById('v-payment-actions');
            if (paymentActions) paymentActions.style.display = (data.receipt_url && data.payment_status === 'pending' && !isFinalized) ? 'flex' : 'none';
            
            // Show/Hide cancellation warning in sidebar
            const cancellationWarning = document.getElementById('v-cancellation-warning');
            if (cancellationWarning) {
                cancellationWarning.style.display = data.cancellation_requested ? 'block' : 'none';
                if (data.cancellation_requested) {
                    document.getElementById('v-cancellation-reason').textContent = data.cancellation_reason || 'No reason provided.';
                }
            }

            // Initial check for final approval
            checkIfFullyVerified(enrollmentId, data.license_status, data.payment_status, data.cancellation_requested, data.status);
            
            // Show content
            document.getElementById('v-modal-loading').style.display = 'none';
            document.getElementById('v-modal-content').style.visibility = 'visible';
            document.getElementById('v-modal-content').style.display = 'flex';
        })
        .catch(err => {
            document.getElementById('v-modal-loading').innerHTML = `
                <div class="text-danger text-center p-4">
                    <svg class="icon-24 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="font-weight-bold">Error Loading Data</p>
                    <p class="small">The record might be missing or there was a connection error.</p>
                </div>
            `;
            setTimeout(closeVerificationModal, 3000);
        });
}

function updatePanelStatus(type, status) {
    const badge = document.getElementById(`v-${type}-status`);
    if (!badge) return;
    status = status || 'none';
    badge.textContent = status.replace('_', ' ').toUpperCase();
    
    // Reset classes
    badge.className = 'v-panel-status';
    if (status === 'verified' || status === 'paid') badge.classList.add('bg-success', 'text-white');
    else if (status === 'pending') badge.classList.add('bg-warning', 'text-dark');
    else if (status === 'rejected') badge.classList.add('bg-danger', 'text-white');
    else badge.classList.add('bg-secondary', 'text-white');
}

function updatePanelImage(type, url, label) {
    const viewer = document.getElementById(`v-${type}-viewer`);
    if (url) {
        viewer.innerHTML = `<img src="${url}" alt="${label}" class="img-fluid" onclick="window.open('${url}', '_blank')">`;
    } else {
        viewer.innerHTML = `<div class="v-empty-image"><svg class="icon-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg><p>No Document Found</p></div>`;
    }
}

function closeVerificationModal() {
    document.getElementById('unifiedVerificationModal').style.display = 'none';
    currentEnrollmentId = null;
}

function verifyPaymentAjax() {
    if (!currentEnrollmentId) return;
    
    const btn = document.getElementById('v-btn-verify-payment-panel');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Wait...';
    
    const url = `{{ school_route('admin.enrollments.index') }}/api/${currentEnrollmentId}/verify-payment`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Toast.success(data.message);
            updateTableRowStatus(currentEnrollmentId, 'payment', 'paid');
            // Don't close modal, just update the status inside it
            updatePanelStatus('payment', 'paid');
        } else {
            Toast.error(data.message);
        }
    })
    .catch(err => Toast.error('An error occurred during verification.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function verifyLicenseAjax() {
    if (!currentEnrollmentId) return;
    
    const btn = document.getElementById('v-btn-verify-license-panel');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Wait...';
    
    const url = `{{ school_route('admin.enrollments.index') }}/api/${currentEnrollmentId}/verify-license`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Toast.success(data.message);
            updateTableRowStatus(currentEnrollmentId, 'license', 'verified');
            // Don't close modal, just update status inside it
            updatePanelStatus('license', 'verified');
        } else {
            Toast.error(data.message);
        }
    })
    .catch(err => Toast.error('An error occurred during verification.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function updateTableRowStatus(id, type, status) {
    const row = document.querySelector(`tr[data-request-id="${id}"]`);
    if (!row) return;
    
    if (type === 'payment') {
        const badge = row.querySelector('.payment-badge');
        if (badge) {
            badge.className = `payment-badge payment-${status}`;
            badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        }
    } else if (type === 'license') {
        const badge = row.querySelector('.license-badge');
        if (badge) {
            badge.className = `license-badge license-${status}`;
            badge.textContent = 'License: ' + status.charAt(0).toUpperCase() + status.slice(1);
        }
        // Also hide the separate license verify/reject buttons in the table
        const actions = row.querySelector('.license-actions');
        if (actions) actions.style.display = 'none';
    }

    // Hide panel actions in modal after verification
    const actionsDiv = document.getElementById(`v-${type}-actions`);
    if (actionsDiv) actionsDiv.style.display = 'none';

    // Check if fully verified to show final button
    checkIfFullyVerified(id);
}

function checkIfFullyVerified(id, licenseStatus, paymentStatus, cancellationRequested, enrollmentStatus) {
    const row = document.querySelector(`tr[data-request-id="${id}"]`);
    if (!row) return;

    // Determine current statuses if not provided
    if (licenseStatus === undefined || licenseStatus === null) {
        const badge = row.querySelector('.license-badge');
        if (badge) {
            if (badge.classList.contains('license-verified')) licenseStatus = 'verified';
            else if (badge.classList.contains('license-none')) licenseStatus = 'none';
            else licenseStatus = 'pending';
        } else {
            licenseStatus = 'verified'; // Default if no badge
        }
    }
    if (paymentStatus === undefined || paymentStatus === null) {
        const badge = row.querySelector('.payment-badge');
        if (badge) {
            if (badge.classList.contains('payment-paid')) paymentStatus = 'paid';
            else if (badge.classList.contains('payment-none')) paymentStatus = 'none';
            else paymentStatus = 'pending';
        } else {
            paymentStatus = 'paid'; // Default if no badge
        }
    }
    
    // If not provided from API, check the row class
    if (cancellationRequested === undefined || cancellationRequested === null) {
        cancellationRequested = row.classList.contains('has-cancellation-request');
    }

    // Determine enrollment status if not provided
    if (enrollmentStatus === undefined || enrollmentStatus === null) {
        enrollmentStatus = row.getAttribute('data-status');
    }

    const isFinalized = ['approved', 'rejected', 'cancelled', 'completed'].includes(enrollmentStatus);

    const isFullyVerified = (licenseStatus === 'verified' || licenseStatus === 'none') && 
                            (paymentStatus === 'paid' || paymentStatus === 'none') &&
                            !cancellationRequested &&
                            !isFinalized;

    const atLeastOneProcessed = (licenseStatus !== 'pending') || (paymentStatus !== 'pending');

    // Toggle Final Approve Button in Modal
    const finalApproveBtn = document.getElementById('v-btn-final-approve');
    if (finalApproveBtn) {
        finalApproveBtn.style.display = isFullyVerified ? 'block' : 'none';
    }

    // Update outer table buttons
    const approveBtn = document.getElementById(`outerApprove${id}`);
    const rejectBtn = document.getElementById(`outerReject${id}`);
    const mobileApproveBtn = document.getElementById(`mobileApproveForm${id}`);
    const mobileRejectBtn = document.getElementById(`mobileRejectForm${id}`);

    if (approveBtn) {
        approveBtn.title = isFullyVerified ? "Approve enrollment" : "Verify documents first";
        isFullyVerified ? approveBtn.classList.remove('btn-locked') : approveBtn.classList.add('btn-locked');
    }

    if (rejectBtn) {
        rejectBtn.title = atLeastOneProcessed ? "Reject enrollment" : "Review documents first";
        atLeastOneProcessed ? rejectBtn.classList.remove('btn-locked') : rejectBtn.classList.add('btn-locked');
    }

    // Repeat for mobile
    if (mobileApproveBtn) {
        isFullyVerified ? mobileApproveBtn.classList.remove('btn-locked') : mobileApproveBtn.classList.add('btn-locked');
    }
    if (mobileRejectBtn) {
        atLeastOneProcessed ? mobileRejectBtn.classList.remove('btn-locked') : mobileRejectBtn.classList.add('btn-locked');
    }
}

function rejectLicenseFromModal() {
    if (!currentEnrollmentId) return;
    const idToReject = currentEnrollmentId;
    closeVerificationModal();
    
    setTimeout(() => {
        showRejectModal(idToReject);
        document.querySelector('input[name="reject_license"]').checked = true;
        document.querySelector('input[name="reject_payment"]').checked = false;
    }, 100);
}

function rejectPaymentFromModal() {
    if (!currentEnrollmentId) return;
    const idToReject = currentEnrollmentId;
    closeVerificationModal();
    
    setTimeout(() => {
        showRejectModal(idToReject);
        document.querySelector('input[name="reject_license"]').checked = false;
        document.querySelector('input[name="reject_payment"]').checked = true;
    }, 100);
}

function finalApproveFromModal() {
    if (!currentEnrollmentId) return;
    const form = document.getElementById(`approveForm${currentEnrollmentId}`) || 
                 document.getElementById(`mobileApproveForm${currentEnrollmentId}`);
    if (form) {
        form.submit();
    }
}

</script>

<!-- Unified Verification Modal -->
<div id="unifiedVerificationModal" class="action-modal modal" style="display: none;">
    <div class="verification-modal-card">
        <div class="v-modal-header">
            <h3 class="m-0 font-weight-bold" style="font-size: 1.1rem; color: #1e293b;">Verification Dashboard</h3>
            <button type="button" onclick="closeVerificationModal()" class="btn-close" style="border:none;background:none;font-size:1.5rem;">&times;</button>
        </div>
        
        <div id="v-modal-loading" style="display:none; flex:1; justify-content:center; align-items:center; flex-direction:column; gap:15px; background: #fff;">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-muted">Loading enrollment data...</p>
        </div>

        <div id="v-modal-content" class="v-modal-body">
            <!-- Sidebar with Info -->
            <div class="v-modal-sidebar">
                <div class="mb-3">
                    <div class="v-info-label">Learner Name</div>
                    <div id="v-student-name" class="v-info-value">-</div>
                </div>
                <div class="mb-3">
                    <div class="v-info-label">Enrolled Course</div>
                    <div id="v-course-title" class="v-info-value">-</div>
                </div>
                <div class="mb-3">
                    <div class="v-info-label">Requested DL Code</div>
                    <div id="v-dl-code" class="v-info-value">-</div>
                </div>
                <div class="mb-3">
                    <div class="v-info-label">Total Amount</div>
                    <div id="v-price" class="v-info-value">-</div>
                </div>
                <hr class="my-3">
                <div class="mb-3">
                    <div id="v-reference-label" class="v-info-label">GCash Reference No.</div>
                    <div id="v-reference" class="v-info-value" style="font-family: monospace; font-size: 1.1rem; color: #2563eb;">-</div>
                
                <!-- Cancellation Warning -->
                <div id="v-cancellation-warning" style="display: none; margin-top: 20px; padding: 12px; background: #fff5f5; border-radius: 8px; border: 1px solid #fed7d7;">
                    <div class="v-info-label" style="color: #c53030; display: flex; align-items: center; gap: 4px;">
                        <svg class="icon-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Withdrawal Requested
                    </div>
                    <div id="v-cancellation-reason" style="font-size: 0.85rem; color: #742a2a; margin-top: 5px; line-height: 1.4;">-</div>
                    <div style="margin-top: 8px; font-size: 0.75rem; color: #9b1c1c; font-style: italic;">
                        Approval is blocked until this request is resolved.
                    </div>
                </div>
                </div>
                <div class="mt-auto">
                    <p class="text-muted small" style="line-height: 1.4;">
                        <svg class="icon-14 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"></path></svg>
                        Tip: Zoom into images to verify expiration dates or transaction timestamps.
                    </p>
                </div>
            </div>

            <!-- Main Content: Images Side-by-Side -->
            <div class="v-modal-content">
                <!-- Panel 1: Student License -->
                <div class="v-image-panel">
                    <div class="v-panel-title">
                        <span>Student License</span>
                        <span id="v-license-status" class="v-panel-status">PENDING</span>
                    </div>
                    <div id="v-license-viewer" class="v-image-viewer">
                        <!-- Image injected here -->
                    </div>
                    <div class="v-panel-actions" id="v-license-actions" style="display: none;">
                        <button type="button" class="v-btn-panel v-btn-panel-reject" onclick="rejectLicenseFromModal()">
                            <svg class="icon-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Reject License
                        </button>
                        <button type="button" id="v-btn-verify-license-panel" class="v-btn-panel v-btn-panel-approve" onclick="verifyLicenseAjax()">
                            <svg class="icon-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Approve License
                        </button>
                    </div>
                </div>

                <!-- Panel 2: GCash Receipt -->
                <div class="v-image-panel">
                    <div class="v-panel-title">
                        <span>Payment Receipt</span>
                        <span id="v-payment-status" class="v-panel-status">PENDING</span>
                    </div>
                    <div id="v-payment-viewer" class="v-image-viewer">
                        <!-- Image injected here -->
                    </div>
                    <div class="v-panel-actions" id="v-payment-actions" style="display: none;">
                        <button type="button" class="v-btn-panel v-btn-panel-reject" onclick="rejectPaymentFromModal()">
                            <svg class="icon-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Reject Payment
                        </button>
                        <button type="button" id="v-btn-verify-payment-panel" class="v-btn-panel v-btn-panel-approve" onclick="verifyPaymentAjax()">
                            <svg class="icon-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Approve Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Footer for Actions -->
        <div class="v-btn-group">
            <button type="button" onclick="closeVerificationModal()" class="btn btn-secondary" style="background:#e2e8f0; color:#475569;">Close</button>
            <div class="ms-auto">
                <button type="button" id="v-btn-final-approve" class="btn btn-success" style="background: #10b981; border:none; display:none;" onclick="finalApproveFromModal()">
                    <svg class="icon-14 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Final Complete Enrollment
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Close unified modal when clicking outside
document.getElementById('unifiedVerificationModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeVerificationModal();
});
</script>
@endsection
