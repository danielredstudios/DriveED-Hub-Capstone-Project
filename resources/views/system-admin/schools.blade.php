@extends('layouts.system-admin')
@section('title', 'Schools')
@section('page-title', 'Driving Schools')

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
    
    .btn-create {
        padding: 10px 20px;
        background: #053d86;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-create:hover {
        background: #0a4a9e;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(5, 61, 134, 0.3);
    }
    
    .school-icon {
        width: 40px;
        height: 40px;
        background: #053d86;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .slug-badge {
        background: #f3f4f6;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-family: monospace;
        color: #4b5563;
    }
    
    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }
    
    .modal-overlay.active {
        display: flex;
    }
    
    .modal {
        background: white;
        border-radius: 12px;
        width: 100%;
        max-width: 550px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    
    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-header h3 {
        margin: 0;
        font-size: 1.25rem;
        color: #1f2937;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #6b7280;
        padding: 0;
        line-height: 1;
    }
    
    .modal-close:hover {
        color: #1f2937;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .modal-body .form-group {
        margin-bottom: 16px;
    }
    
    .modal-body label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #374151;
    }
    
    .modal-body input,
    .modal-body textarea {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.95rem;
    }
    
    .modal-body input:focus,
    .modal-body textarea:focus {
        outline: none;
        border-color: #053d86;
    }
    
    .modal-body textarea {
        resize: vertical;
        min-height: 80px;
    }
    
    .modal-body small {
        display: block;
        margin-top: 4px;
        color: #6b7280;
        font-size: 0.8rem;
    }
    
    .modal-footer {
        padding: 16px 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    
    .btn-cancel {
        padding: 10px 20px;
        background: #f3f4f6;
        color: #374151;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        cursor: pointer;
    }
    
    .btn-cancel:hover {
        background: #e5e7eb;
    }
    
    .btn-submit {
        padding: 10px 20px;
        background: #053d86;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
    }
    
    .btn-submit:hover {
        background: #0a4a9e;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .password-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-toggle {
        position: absolute;
        right: 12px;
        background: none;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .password-toggle:hover {
        color: #374151;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .status-active { background: #d1fae5; color: #065f46; }
    .status-inactive { background: #fee2e2; color: #991b1b; }

    .action-buttons {
        display: flex;
        gap: 6px;
    }

    .btn-action {
        width: 32px;
        height: 32px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-view { background: #e0f2fe; color: #0369a1; }
    .btn-view:hover { background: #bae6fd; }
    
    .btn-activate { background: #d1fae5; color: #065f46; }
    .btn-activate:hover { background: #a7f3d0; }
    
    .btn-deactivate { background: #fef3c7; color: #92400e; }
    .btn-deactivate:hover { background: #fde68a; }
    
    .btn-delete { background: #fee2e2; color: #991b1b; }
    .btn-delete:hover { background: #fecaca; }

    /* Fix for small circular buttons: Swap the icon for a centered loading spinner */
    .btn-action.btn-submitting i {
        display: none; /* Hide the icon to clear space */
    }

    .btn-action.btn-submitting::after {
        margin-left: 0 !important; /* Remove margin to center the spinner perfectly */
    }

    .btn-action.btn-submitting {
        opacity: 0.8;
        cursor: wait;
    }

    /* Delete Confirmation Modal */
    .delete-modal .modal {
        max-width: 400px;
    }

    .delete-modal .modal-body {
        text-align: center;
        padding: 30px;
    }

    .delete-modal .warning-icon {
        width: 60px;
        height: 60px;
        background: #fee2e2;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: #dc2626;
        font-size: 1.5rem;
    }

    .delete-modal h4 {
        margin: 0 0 10px;
        color: #1f2937;
    }

    .delete-modal p {
        color: #6b7280;
        margin: 0;
    }

    .btn-danger {
        padding: 10px 20px;
        background: #dc2626;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
    }

    .btn-danger:hover {
        background: #b91c1c;
    }

    .section-title-icon {
        margin-right: 0.5rem;
        color: #053d86;
    }

    .school-row-main {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .school-email {
        font-size: 0.8rem;
        color: #6b7280;
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

    .modal-section-divider {
        margin: 20px 0;
        border: none;
        border-top: 1px solid #e5e7eb;
    }

    .modal-section-title {
        margin-bottom: 16px;
        color: #374151;
        font-size: 1rem;
    }

    .danger-note {
        margin-top: 10px;
        color: #dc2626;
        font-size: 0.85rem;
    }

    .modal-footer-centered {
        justify-content: center;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .modal { max-width: 95%; margin: 10px; }
        .modal-body { padding: 15px; }
        .form-row { grid-template-columns: 1fr; gap: 10px; }
        .modal-footer { flex-direction: column; }
        .modal-footer .btn { width: 100%; min-height: 44px; }
        .action-bar { flex-direction: column; gap: 10px; }
        .search-box { max-width: 100%; }
        .btn-action { width: 40px; height: 40px; min-height: 44px; min-width: 44px; }
        .status-badge { padding: 6px 12px; }
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
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search schools...">
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>
    <button type="button" class="btn-create" onclick="openModal('createSchoolModal')">
        <i class="fas fa-plus"></i> Add Driving School
    </button>
</div>

<div class="card">
    <div class="card-header">
        <h3>
            <i class="fas fa-building section-title-icon"></i>
            All Schools ({{ $schools->total() }})
        </h3>
    </div>
    <div class="card-body">
        @if($schools->count() > 0)
        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>School</th>
                    <th>Slug</th>
                    <th>Students</th>
                    <th>Instructors</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($schools as $school)
                <tr>
                    <td>
                        <div class="school-row-main">
                            <div class="school-icon">
                                {{ strtoupper(substr($school->name, 0, 2)) }}
                            </div>
                            <div>
                                <strong>{{ $school->name }}</strong>
                                @if($school->email)
                                <div class="school-email">{{ $school->email }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td><span class="slug-badge">{{ $school->slug }}</span></td>
                    <td>{{ $school->students_count }}</td>
                    <td>{{ $school->instructors_count }}</td>
                    <td>
                        <span class="status-badge {{ ($school->status ?? 'active') === 'active' ? 'status-active' : 'status-inactive' }}">
                            {{ ucfirst($school->status ?? 'active') }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="/{{ $school->slug }}" target="_blank" class="btn-action btn-view" title="View School">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <form action="{{ route('system-admin.schools.toggle-status', $school) }}" method="POST" class="inline-form">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-action {{ ($school->status ?? 'active') === 'active' ? 'btn-deactivate' : 'btn-activate' }}" title="{{ ($school->status ?? 'active') === 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas {{ ($school->status ?? 'active') === 'active' ? 'fa-ban' : 'fa-check' }}"></i>
                                </button>
                            </form>
                            <button type="button" class="btn-action btn-delete" data-action="delete-school" data-slug="{{ $school->slug }}" data-name="{{ $school->name }}" title="Delete School">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        {{ $schools->appends(request()->query())->links() }}
        @else
        <div class="empty-state">
            <i class="fas fa-building empty-state-icon"></i>
            <p>No schools registered yet.</p>
        </div>
        @endif
    </div>
</div>

<!-- Create School Modal -->
<div class="modal-overlay" id="createSchoolModal" role="dialog" aria-modal="true" aria-labelledby="createSchoolModalTitle" aria-hidden="true">
    <div class="modal">
        <div class="modal-header">
            <h3 id="createSchoolModalTitle"><i class="fas fa-building section-title-icon"></i> Add Driving School</h3>
            <button class="modal-close" onclick="closeModal('createSchoolModal')" aria-label="Close create school modal">&times;</button>
        </div>
        <form action="{{ route('system-admin.schools.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="school_name">School Name</label>
                    <input type="text" name="name" id="school_name" required placeholder="Enter school name">
                </div>
                <div class="form-group">
                    <label for="school_slug">Slug (URL)</label>
                    <input type="text" name="slug" id="school_slug" required placeholder="e.g., smart-driving" pattern="[a-z0-9\-]+" title="Lowercase letters, numbers, and hyphens only">
                    <small>This will be used in the URL: yoursite.com/<strong>slug</strong>/login</small>
                </div>
                <div class="form-group">
                    <label for="school_email">School Email</label>
                    <input type="email" name="email" id="school_email" placeholder="school@example.com">
                </div>
                <div class="form-group">
                    <label for="school_address">Address</label>
                    <textarea name="address" id="school_address" placeholder="Enter school address"></textarea>
                </div>
                
                <hr class="modal-section-divider">
                <h4 class="modal-section-title">School Admin Account</h4>
                
                <div class="form-group">
                    <label for="admin_name">Admin Name</label>
                    <input type="text" name="admin_name" id="admin_name" required placeholder="Admin full name">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="admin_email">Admin Email</label>
                        <input type="email" name="admin_email" id="admin_email" required placeholder="admin@school.com">
                    </div>
                    <div class="form-group">
                        <label for="admin_password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="admin_password" id="admin_password" required placeholder="Password" minlength="8">
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('admin_password', this)" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="password-hint">Must be at least 8 characters and include an uppercase letter, a number, and a special character (!@#$%^&*).</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('createSchoolModal')">Cancel</button>
                <button type="submit" class="btn-submit">Create School</button>
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

// Auto-generate slug from name
document.getElementById('school_name').addEventListener('input', function() {
    const slug = this.value
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
    document.getElementById('school_slug').value = slug;
});

// Toggle password visibility
function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Close modal when clicking outside
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.setAttribute('aria-hidden', 'true');
    overlay.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal(this.id);
        }
    });
});

// Delete confirmation via event delegation (XSS-safe)
document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-action="delete-school"]');
    if (!btn) return;
    document.getElementById('deleteSchoolName').textContent = btn.dataset.name;
    document.getElementById('deleteSchoolForm').action = '/system-admin/schools/' + btn.dataset.slug;
    openModal('deleteSchoolModal');
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

<!-- Delete Confirmation Modal -->
<div class="modal-overlay delete-modal" id="deleteSchoolModal" role="dialog" aria-modal="true" aria-labelledby="deleteSchoolModalTitle" aria-hidden="true">
    <div class="modal">
        <div class="modal-body">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h4 id="deleteSchoolModalTitle">Delete School?</h4>
            <p>Are you sure you want to delete <strong id="deleteSchoolName"></strong>?</p>
            <p class="danger-note">This will permanently delete all students, instructors, admins, courses, and schedules.</p>
        </div>
        <div class="modal-footer modal-footer-centered">
            <button type="button" class="btn-cancel" onclick="closeModal('deleteSchoolModal')">Cancel</button>
            <form id="deleteSchoolForm" method="POST" class="inline-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger"><i class="fas fa-trash"></i> Delete Permanently</button>
            </form>
        </div>
    </div>
</div>
@endsection
