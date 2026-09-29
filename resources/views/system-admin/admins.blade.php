@extends('layouts.system-admin')
@section('title', 'School Admins')
@section('page-title', 'School Administrators')

@section('styles')
<style>
    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 15px 20px;
        background: #f8f9fa;
        border-radius: 8px;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .search-box {
        position: relative;
        flex: 1;
        max-width: 350px;
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
        border-color: #053d86;
    }
    
    .search-box button {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        background: #053d86;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 6px 12px;
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .search-box button:hover {
        background: #0a4a9e;
    }

    .filter-select {
        padding: 10px 15px;
        border: 2px solid #e1e5e9;
        border-radius: 8px;
        font-size: 0.95rem;
        min-width: 200px;
    }

    .filter-select:focus {
        outline: none;
        border-color: #053d86;
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        background: #053d86;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1rem;
    }

    .school-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #e0e7ff;
        color: #3730a3;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .btn-primary {
        background: #053d86;
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s;
    }

    .btn-primary:hover {
        background: #0a4a9e;
    }

    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        width: 100%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }

    .modal-header {
        padding: 20px 25px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        color: #1f2937;
        font-size: 1.25rem;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #6b7280;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }

    .modal-close:hover {
        color: #1f2937;
    }

    .modal-body {
        padding: 25px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #374151;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: border-color 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #053d86;
    }

    .modal-footer {
        padding: 20px 25px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    /* Action Buttons */
    .btn-sm {
        padding: 6px 12px;
        font-size: 0.8rem;
        border-radius: 6px;
        cursor: pointer;
        border: none;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
    }

    .btn-toggle {
        background: #f3f4f6;
        color: #374151;
    }

    .btn-toggle:hover {
        background: #e5e7eb;
    }

    .btn-toggle.btn-active {
        background: #dcfce7;
        color: #166534;
    }

    .btn-toggle.btn-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .btn-danger:hover {
        background: #fecaca;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .status-badge.active {
        background: #dcfce7;
        color: #166534;
    }

    .status-badge.inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .actions-cell {
        display: flex;
        gap: 6px;
        align-items: center;
    }

    /* Delete Modal */
    .modal-danger .modal-header {
        background: #fef2f2;
        border-bottom: 1px solid #fecaca;
    }

    .modal-danger .modal-header h3 {
        color: #dc2626;
    }

    .btn-delete {
        background: #dc2626;
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
    }

    .btn-delete:hover {
        background: #b91c1c;
    }

    .warning-text {
        background: #fef3c7;
        color: #92400e;
        padding: 12px 15px;
        border-radius: 8px;
        margin-top: 15px;
        font-size: 0.9rem;
    }

    .action-bar-controls {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .section-title-icon {
        margin-right: 0.5rem;
        color: #053d86;
    }

    .admin-name-wrap {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .no-school {
        color: #9ca3af;
    }

    .status-dot {
        font-size: 6px;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #6b7280;
    }

    .empty-state-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .modal-title-icon {
        margin-right: 8px;
    }

    .required-mark {
        color: #dc2626;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .modal-content { max-width: 95%; margin: 10px; }
        .modal-body { padding: 15px; }
        .modal-footer { flex-direction: column; }
        .modal-footer .btn, .modal-footer button { width: 100%; min-height: 44px; }
        .action-bar { flex-direction: column; gap: 10px; }
        .search-box { max-width: 100%; }
        .status-badge { padding: 6px 12px; }
        th, td { padding: 10px 8px; font-size: 0.82rem; }
    }
    
    @media (max-width: 480px) {
        .card-header { flex-direction: column; gap: 8px; }
        .action-buttons { flex-wrap: wrap; }
    }
</style>
@endsection

@section('content')
<!-- Action Bar -->
<div class="action-bar">
    <form method="GET" class="search-box">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email...">
        @if(request('school_id'))
            <input type="hidden" name="school_id" value="{{ request('school_id') }}">
        @endif
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>
    
    <div class="action-bar-controls">
        <form method="GET">
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            <select name="school_id" class="filter-select" onchange="this.form.submit()">
                <option value="">All Schools</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                        {{ $school->name }}
                    </option>
                @endforeach
            </select>
        </form>
        
        <button type="button" class="btn-primary" onclick="openModal('createAdminModal')">
            <i class="fas fa-plus"></i> Add School Admin
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>
            <i class="fas fa-user-tie section-title-icon"></i>
            School Admins ({{ $admins->total() }})
        </h3>
    </div>
    <div class="card-body">
        @if($admins->count() > 0)
        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>School</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($admins as $admin)
                <tr>
                    <td>
                        <div class="admin-name-wrap">
                            <div class="user-avatar">
                                {{ strtoupper(substr($admin->name, 0, 1)) }}
                            </div>
                            <strong>{{ $admin->name }}</strong>
                        </div>
                    </td>
                    <td>{{ $admin->email }}</td>
                    <td>
                        @if($admin->school)
                            <span class="school-badge">
                                <i class="fas fa-school"></i>
                                {{ $admin->school->name }}
                            </span>
                        @else
                            <span class="no-school">No school</span>
                        @endif
                    </td>
                    <td>
                        <span class="status-badge {{ $admin->is_active ? 'active' : 'inactive' }}">
                            <i class="fas fa-circle status-dot"></i>
                            {{ $admin->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>{{ $admin->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="actions-cell">
                            <form action="{{ route('system-admin.admins.toggle-status', $admin->id) }}" method="POST" class="inline-form">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-sm btn-toggle {{ $admin->is_active ? 'btn-active' : 'btn-inactive' }}" 
                                        title="{{ $admin->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas {{ $admin->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                    {{ $admin->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <button type="button" class="btn-sm btn-danger" 
                                    data-action="delete-admin" data-id="{{ $admin->id }}" data-name="{{ $admin->name }}"
                                    title="Delete Admin">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        {{ $admins->appends(request()->query())->links() }}
        @else
        <div class="empty-state">
            <i class="fas fa-user-tie empty-state-icon"></i>
            <p>No school admins found.</p>
        </div>
        @endif
    </div>
</div>

<!-- Create School Admin Modal -->
<div class="modal-overlay" id="createAdminModal" role="dialog" aria-modal="true" aria-labelledby="createAdminModalTitle" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="createAdminModalTitle"><i class="fas fa-user-plus section-title-icon modal-title-icon"></i>Add School Admin</h3>
            <button type="button" class="modal-close" onclick="closeModal('createAdminModal')" aria-label="Close create admin modal">&times;</button>
        </div>
        <form action="{{ route('system-admin.admins.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="school_id">Assign to School <span class="required-mark">*</span></label>
                    <select name="school_id" id="school_id" required>
                        <option value="">Select a school...</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="name">Admin Name <span class="required-mark">*</span></label>
                    <input type="text" name="name" id="name" required placeholder="Enter admin name">
                </div>
                <div class="form-group">
                    <label for="email">Email Address <span class="required-mark">*</span></label>
                    <input type="email" name="email" id="email" required placeholder="admin@example.com">
                </div>
                <div class="form-group">
                    <label for="password">Password <span class="required-mark">*</span></label>
                    <input type="password" name="password" id="password" required placeholder="Minimum 8 characters" minlength="8">
                    <small class="password-hint" style="display: block; margin-top: 4px; color: #6b7280; font-size: 0.8rem;">Must be at least 8 characters and include an uppercase letter, a number, and a special character (!@#$%^&*).</small>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password <span class="required-mark">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="Confirm password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('createAdminModal')">Cancel</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Create Admin</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Admin Confirmation Modal -->
<div class="modal-overlay" id="deleteAdminModal" role="dialog" aria-modal="true" aria-labelledby="deleteAdminModalTitle" aria-hidden="true">
    <div class="modal-content modal-danger">
        <div class="modal-header">
            <h3 id="deleteAdminModalTitle"><i class="fas fa-exclamation-triangle modal-title-icon"></i>Delete Admin</h3>
            <button type="button" class="modal-close" onclick="closeModal('deleteAdminModal')" aria-label="Close delete admin modal">&times;</button>
        </div>
        <form id="deleteAdminForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteAdminName"></strong>?</p>
                <div class="warning-text">
                    <i class="fas fa-exclamation-triangle"></i>
                    This action cannot be undone. The admin will be permanently removed.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('deleteAdminModal')">Cancel</button>
                <button type="submit" class="btn-delete"><i class="fas fa-trash"></i> Delete Admin</button>
            </div>
        </form>
    </div>
</div>

<script>
let activeModal = null;
let modalTrigger = null;

function openModal(id) {
    const modal = document.getElementById(id);
    modalTrigger = document.activeElement;
    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
    activeModal = modal;

    const firstFocusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if (firstFocusable) {
        firstFocusable.focus();
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    modal.classList.remove('active');
    modal.setAttribute('aria-hidden', 'true');
    activeModal = null;
    if (modalTrigger) {
        modalTrigger.focus();
    }
}

// Delete confirmation via event delegation (XSS-safe)
document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-action="delete-admin"]');
    if (!btn) return;
    document.getElementById('deleteAdminName').textContent = btn.dataset.name;
    document.getElementById('deleteAdminForm').action = '{{ url("system-admin/admins") }}/' + btn.dataset.id;
    openModal('deleteAdminModal');
});

// Close modal when clicking outside
document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.setAttribute('aria-hidden', 'true');
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal(this.id);
        }
    });
});

document.addEventListener('keydown', function(e) {
    if (activeModal && e.key === 'Escape') {
        e.preventDefault();
        closeModal(activeModal.id);
        return;
    }

    if (!activeModal || e.key !== 'Tab') {
        return;
    }

    const focusable = Array.from(activeModal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'));
    if (focusable.length === 0) {
        return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
    }
});
</script>
@endsection

