@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Branch Management')

@section('content')
<!-- Fix: Load Bootstrap Icons for this view since stat cards rely on them -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $settings = $school?->schoolSetting;
    $primaryColor = $settings->primary_color ?? '#667eea';
    $secondaryColor = $settings->secondary_color ?? '#764ba2';
    $useGradient = $settings?->use_gradient_header;
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .branches-container {
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
        font-size: 0.85rem;
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
    .stat-card.total { border-left-color: #6366f1; }
    .stat-card.total .stat-icon { background: #eef2ff; color: #4338ca; }
    
    .stat-card.active-branches { border-left-color: #10b981; }
    .stat-card.active-branches .stat-icon { background: #ecfdf5; color: #047857; }

    .stat-card.inactive-branches { border-left-color: #ef4444; }
    .stat-card.inactive-branches .stat-icon { background: #fef2f2; color: #b91c1c; }

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

    .search-box {
        position: relative;
        flex: 1;
        max-width: 450px;
    }

    .search-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        flex: 1;
        max-width: 450px;
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
        height: 40px;
    }

    .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px {{ $primaryColor }}30;
        filter: brightness(1.05);
    }
    
    .btn-create svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
    }

    /* Branch Table card */
    .contact-email {
        font-size: 0.85rem;
        color: {{ $primaryColor }}80;
    }

    .required-mark {
        color: #dc3545;
    }

    /* Branch Table */
    .branch-table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px {{ $primaryColor }}10;
        overflow: hidden;
        border: 1px solid {{ $primaryColor }}08;
        margin-top: 25px;
    }

    .branch-table {
        width: 100%;
        border-collapse: collapse;
    }

    .branch-table thead {
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
    }

    .branch-table thead th {
        background: transparent;
        color: inherit;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        font-size: 0.95rem;
        text-transform: none;
        letter-spacing: 0;
    }

    .branch-table tbody tr {
        border-bottom: 1px solid #e5e7eb;
        transition: background 0.2s;
    }

    .branch-table tbody tr:hover {
        background: #f9fafb;
    }

    .branch-table tbody td {
        padding: 14px 18px;
        font-size: 0.95rem;
        color: #374151;
        vertical-align: middle;
    }

    .branch-name {
        font-weight: 600;
        color: #1f2937;
    }

    .branch-slug {
        font-size: 0.8rem;
        color: #9ca3af;
        margin-top: 2px;
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

    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
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
        background: {{ $primaryColor }}15;
        color: {{ $primaryColor }};
    }

    .btn-edit:hover { background: {{ $primaryColor }}25; }

    .btn-toggle {
        background: #fef3c7;
        color: #92400e;
    }

    .btn-delete {
        background: #fee2e2;
        color: #991b1b;
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
        flex-shrink: 0; /* Extra safety for layout integrity */
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
        line-height: 1;
    }

    .btn-close-modal:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }

    .modal-body {
        padding: 32px !important;
        background: white;
        max-height: 70vh;
        overflow-y: auto;
    }

    .modal-footer {
        padding: 24px 32px 32px;
        display: flex;
        gap: 12px;
        background: white;
        border-top: 1px solid {{ $primaryColor }}08;
        justify-content: flex-end;
    }

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
        flex: 0 0 auto !important;
    }

    .btn-secondary {
        background: #94a3b8 !important; /* Themed slate-400 */
        box-shadow: 0 4px 12px rgba(148, 163, 184, 0.25) !important;
    }

    .btn-primary:hover, .btn-secondary:hover {
        transform: translateY(-1px);
        filter: brightness(1.05);
    }

    .btn-primary svg, .btn-secondary svg {
        width: 18px !important;
        height: 18px !important;
        flex-shrink: 0 !important;
    }

    /* Close modal animation */
    @keyframes dropdownSlide {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .icon-18 { width: 18px; height: 18px; }
    .icon-14 { width: 14px; height: 14px; }
    .icon-24 { width: 24px; height: 24px; }

    .btn-secondary {
        padding: 10px 20px;
        background: #f3f4f6;
        color: #374151;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    .btn-primary {
        padding: 10px 20px;
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-group label {
        display: block;
        font-size: 0.9rem;
        font-weight: 600;
        color: {{ $primaryColor }}cc;
        margin-bottom: 8px;
    }

    .form-group input, 
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 10px 16px;
        border: 2px solid {{ $primaryColor }}15;
        border-radius: 12px;
        font-size: 0.95rem;
        transition: all 0.2s;
        background: {{ $primaryColor }}05;
        color: #1f2937;
    }

    .form-group input:focus,
    .form-group textarea:focus,
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
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .branch-stats {
            flex-direction: column;
        }

        .branch-table-card {
            overflow-x: auto;
        }

        .action-buttons {
            flex-direction: column;
        }
    }

    .contact-input-group {
        display: flex;
        align-items: center;
        border: 2px solid {{ $primaryColor }}15;
        border-radius: 12px;
        overflow: hidden;
        background: {{ $primaryColor }}05;
        transition: all 0.2s;
    }

    .contact-input-group:focus-within {
        border-color: {{ $primaryColor }};
        background: white;
        box-shadow: 0 0 0 4px {{ $primaryColor }}15;
    }

    .contact-prefix {
        background: #f1f5f9;
        padding: 10px 16px;
        color: #475569;
        font-weight: 600;
        border-right: 1px solid #e2e8f0;
        font-size: 0.95rem;
    }

    .contact-input-group input {
        border: none !important;
        box-shadow: none !important;
        padding: 10px 16px !important;
        background: transparent !important;
    }

    .field-help {
        font-size: 0.8rem;
        color: {{ $primaryColor }}70;
        margin-top: 6px;
    }
</style>

<div class="branches-container">
    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Branch Management</h1>
            <p class="page-subtitle">Manage school locations and verify branch-specific operational metrics for {{ $schoolName }}</p>
        </div>
        <div class="header-actions">
            <button class="btn-create" onclick="openBranchModal()">
                <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                Add Branch
            </button>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="stats-grid">
        <div class="stat-card total active" onclick="filterBranches('all', this)">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Branches</div>
                        <div class="stat-value">{{ $totalBranchesCount }}</div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
                <div style="font-size: 0.85rem; color: #6b7280;">Global school locations</div>
            </div>
        </div>

        <div class="stat-card active-branches" onclick="filterBranches('active', this)">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Active</div>
                        <div class="stat-value">{{ $activeBranchesCount }}</div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
                <div style="font-size: 0.85rem; color: #6b7280;">Operating branches</div>
            </div>
        </div>

        <div class="stat-card inactive-branches" onclick="filterBranches('inactive', this)">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Inactive</div>
                        <div class="stat-value">{{ $inactiveBranchesCount ?? max(0, $totalBranchesCount - $activeBranchesCount) }}</div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
                <div style="font-size: 0.85rem; color: #6b7280;">Non-operating branches</div>
            </div>
        </div>
    </div>

    {{-- Action Bar - Moved below cards --}}
    <div class="action-bar-container" style="margin-top: 20px; background: #fff; padding: 20px; border-radius: 16px; border: 1px solid {{ $primaryColor }}15; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <div class="search-box">
            <label class="control-label" style="display: block; font-size: 0.82rem; font-weight: 600; color: #4b5563; margin-bottom: 8px;">Search Branches</label>
            <div class="search-wrapper">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="branchSearch" placeholder="Search by name, slug, or address..." onkeyup="filterBranchTable()">
            </div>
        </div>
    </div>

    {{-- Branch Table --}}
    @if($branches->isEmpty())
        <div class="empty-state">
            <i class="bi bi-building"></i>
            <h3>No Branches Yet</h3>
            <p>Create your first branch to organize your driving school locations.</p>
        </div>
    @else
        <div class="branch-table-card">
            <table class="branch-table">
                <thead>
                    <tr>
                        <th>Branch Name</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Students</th>
                        <th>Instructors</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branches as $branch)
                    <tr>
                        <td>
                            <div class="branch-name">{{ $branch->name }}</div>
                            <div class="branch-slug">{{ $branch->slug }}</div>
                        </td>
                        <td>{{ $branch->address ?? '-' }}</td>
                        <td>
                            @if($branch->contact_number || $branch->email)
                                @if($branch->contact_number)
                                    <div>{{ $branch->contact_number }}</div>
                                @endif
                                @if($branch->email)
                                    <div class="contact-email">{{ $branch->email }}</div>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $branch->students_count }}</td>
                        <td>{{ $branch->instructors_count }}</td>
                        <td>
                            @if($branch->is_active)
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
                            <div class="action-buttons">
                                <button class="btn-action btn-edit" onclick="editBranch({{ json_encode($branch) }})">
                                    <i class="bi bi-pencil-fill"></i> Edit
                                </button>
                                <button class="btn-action btn-toggle" onclick="toggleBranch({{ $branch->id }}, {{ $branch->is_active ? 'true' : 'false' }})">
                                    <i class="bi bi-{{ $branch->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                    {{ $branch->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button class="btn-action btn-delete" onclick="deleteBranch({{ $branch->id }}, '{{ addslashes($branch->name) }}')">
                                    <i class="bi bi-trash-fill"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $branches->links() }}
        </div>
    @endif
</div>

{{-- Create/Edit Branch Modal --}}
<div class="modal-overlay" id="branchModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 id="branchModalTitle">
                <span id="branchModalTitleText">Add New Branch</span>
            </h5>
            <button class="btn-close-modal" onclick="closeBranchModal()">&times;</button>
        </div>
        <form id="branchForm" method="POST" action="{{ route('schools.admin.branches.store', $school) }}">
            @csrf
            <input type="hidden" name="_method" id="branchMethod" value="POST">
            <input type="hidden" name="branch_id" id="branchId" value="{{ old('branch_id') }}">
            <div class="modal-body">
                <div class="form-group">
                    <label for="branchName">Branch Name <span class="required-mark">*</span></label>
                    <input type="text" id="branchName" name="name" required placeholder="e.g. Main Branch, Makati Branch">
                    <div class="form-hint">A unique URL slug will be generated automatically.</div>
                </div>
                <div class="form-group">
                    <label for="branchAddress">Address</label>
                    <textarea id="branchAddress" name="address" rows="2" placeholder="Full branch address"></textarea>
                </div>
                <div class="form-group">
                    <label for="branchContact">Contact Number</label>
                    @if($settings->enforce_ph_contact ?? true)
                        <div class="contact-input-group">
                            <span class="contact-prefix">+63</span>
                            <input type="text" id="branchContact" name="contact_number" placeholder="9123456789" inputmode="numeric" pattern="[0-9]*" autocomplete="tel" maxlength="10">
                        </div>
                        <p class="field-help">Enter the 10-digit number after +63 (e.g., 9123456789).</p>
                    @else
                        <input type="text" id="branchContact" name="contact_number" placeholder="Enter contact number">
                    @endif
                </div>
                <div class="form-group">
                    <label for="branchEmail">Email</label>
                    <input type="email" id="branchEmail" name="email" placeholder="branch@school.com">
                </div>
                <div class="form-group">
                    <label for="branchSortOrder">Sort Order</label>
                    <input type="number" id="branchSortOrder" name="sort_order" value="0" min="0">
                    <div class="form-hint">Lower numbers appear first.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeBranchModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="branchSubmitBtn" style="background: {{ $primaryColor }};">
                    <span id="branchSubmitBtnText">Create Branch</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const branchesBaseUrl = '{{ url($school->slug . "/admin/branches") }}';
    const csrfToken = '{{ csrf_token() }}';

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
        enforceNumericOnly(document.getElementById('branchContact'));

        @if($errors->any())
            @if(old('_method') === 'PUT')
                restoreBranchModalFromValidation({
                    id: @json(old('branch_id')),
                    name: @json(old('name')),
                    address: @json(old('address')),
                    contact_number: @json(old('contact_number')),
                    email: @json(old('email')),
                    sort_order: @json(old('sort_order', 0))
                }, true);
            @else
                restoreBranchModalFromValidation({
                    name: @json(old('name')),
                    address: @json(old('address')),
                    contact_number: @json(old('contact_number')),
                    email: @json(old('email')),
                    sort_order: @json(old('sort_order', 0))
                }, false);
            @endif
        @endif
    });

    function restoreBranchModalFromValidation(data, isEdit) {
        if (isEdit) {
            document.getElementById('branchModalTitleText').textContent = 'Edit Branch';
            document.getElementById('branchSubmitBtnText').textContent = 'Save Changes';
            document.getElementById('branchForm').action = branchesBaseUrl + '/' + data.id;
            document.getElementById('branchMethod').value = 'PUT';
            document.getElementById('branchId').value = data.id || '';
        } else {
            document.getElementById('branchModalTitleText').textContent = 'Add New Branch';
            document.getElementById('branchSubmitBtnText').textContent = 'Create Branch';
            document.getElementById('branchForm').action = branchesBaseUrl;
            document.getElementById('branchMethod').value = 'POST';
            document.getElementById('branchId').value = '';
        }

        document.getElementById('branchName').value = data.name || '';
        document.getElementById('branchAddress').value = data.address || '';
        document.getElementById('branchContact').value = data.contact_number || '';
        document.getElementById('branchEmail').value = data.email || '';
        document.getElementById('branchSortOrder').value = data.sort_order || 0;

        document.getElementById('branchModal').classList.add('active');
    }

    function openBranchModal() {
        document.getElementById('branchModalTitleText').textContent = 'Add New Branch';
        document.getElementById('branchSubmitBtnText').textContent = 'Create Branch';
        document.getElementById('branchForm').action = branchesBaseUrl;
        document.getElementById('branchMethod').value = 'POST';
        document.getElementById('branchId').value = '';

        document.getElementById('branchName').value = '';
        document.getElementById('branchAddress').value = '';
        document.getElementById('branchContact').value = '';
        document.getElementById('branchEmail').value = '';
        document.getElementById('branchSortOrder').value = '0';

        document.getElementById('branchModal').classList.add('active');
    }

    function editBranch(branch) {
        document.getElementById('branchModalTitleText').textContent = 'Edit Branch';
        document.getElementById('branchSubmitBtnText').textContent = 'Save Changes';
        document.getElementById('branchForm').action = branchesBaseUrl + '/' + branch.id;
        document.getElementById('branchMethod').value = 'PUT';
        document.getElementById('branchId').value = branch.id;

        document.getElementById('branchName').value = branch.name || '';
        document.getElementById('branchAddress').value = branch.address || '';
        document.getElementById('branchContact').value = branch.contact_number || '';
        document.getElementById('branchEmail').value = branch.email || '';
        document.getElementById('branchSortOrder').value = branch.sort_order || 0;

        document.getElementById('branchModal').classList.add('active');
    }

    function closeBranchModal() {
        document.getElementById('branchModal').classList.remove('active');
    }

    function toggleBranch(branchId, isActive) {
        const action = isActive ? 'deactivate' : 'activate';
        showConfirm({
            type: isActive ? 'warning' : 'success',
            title: (isActive ? 'Deactivate' : 'Activate') + ' Branch',
            message: `Are you sure you want to ${action} this branch?`,
            confirmText: 'Yes, ' + action.charAt(0).toUpperCase() + action.slice(1),
            onConfirm: () => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = branchesBaseUrl + '/' + branchId + '/toggle';

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = csrfToken;

                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'PATCH';

                form.appendChild(csrf);
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function deleteBranch(branchId, branchName) {
        showConfirm({
            type: 'danger',
            title: 'Delete Branch',
            message: `Are you sure you want to delete "${branchName}"? This action cannot be undone.`,
            confirmText: 'Yes, Delete Branch',
            onConfirm: () => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = branchesBaseUrl + '/' + branchId;

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = csrfToken;

                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';

                form.appendChild(csrf);
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Branch Filtration & Search
    function filterBranches(status, element) {
        // Toggle card active state
        document.querySelectorAll('.stat-card').forEach(card => card.classList.remove('active'));
        if (element) element.classList.add('active');

        const rows = document.querySelectorAll('.branch-table tbody tr');
        rows.forEach(row => {
            if (status === 'all') {
                row.style.display = '';
            } else if (status === 'active' || status === 'inactive') {
                const isActive = row.querySelector('.status-badge').classList.contains('status-active');
                if (status === 'active') {
                    row.style.display = isActive ? '' : 'none';
                } else {
                    row.style.display = !isActive ? '' : 'none';
                }
            }
        });
    }

    function filterBranchTable() {
        const input = document.getElementById('branchSearch');
        const filter = input.value.toLowerCase();
        const rows = document.querySelectorAll('.branch-table tbody tr');

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length < 2) return;

            const nameSlug = cells[0].textContent.toLowerCase();
            const address = cells[1].textContent.toLowerCase();

            const isVisible = nameSlug.includes(filter) || address.includes(filter);
            row.style.display = isVisible ? '' : 'none';
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeBranchModal();
    });

    // Close modal on overlay click
    document.getElementById('branchModal').addEventListener('click', function(e) {
        if (e.target === this) closeBranchModal();
    });
</script>
@endsection
