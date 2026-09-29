@extends('layouts.system-admin')
@section('title', 'Users')
@section('page-title', 'User Management')

@section('styles')
<style>
    /* Statistics Cards */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    @media (max-width: 1024px) {
        .stats-row { grid-template-columns: repeat(2, 1fr); }
    }
    
    @media (max-width: 600px) {
        .stats-row { grid-template-columns: 1fr; }
    }
    
    .stat-box {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border-left: 4px solid;
    }
    
    .stat-box.students { border-color: #053d86; }
    .stat-box.instructors { border-color: #10b981; }
    .stat-box.total { border-color: #8b5cf6; }
    .stat-box.active { border-color: #f59e0b; }
    
    .stat-box .number {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.25rem;
    }
    
    .stat-box.students .number { color: #053d86; }
    .stat-box.instructors .number { color: #10b981; }
    .stat-box.total .number { color: #8b5cf6; }
    .stat-box.active .number { color: #f59e0b; }
    
    .stat-box .label {
        font-size: 0.85rem;
        color: #6b7280;
        font-weight: 500;
    }
    
    .stat-box .sub {
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 0.25rem;
    }

    /* Tabs */
    .tabs-container {
        margin-bottom: 1.5rem;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .tabs {
        display: flex;
        gap: 0.5rem;
    }
    
    .tab {
        padding: 12px 24px;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        color: #6b7280;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .tab:hover {
        color: #053d86;
        background: rgba(5, 61, 134, 0.05);
    }
    
    .tab.active {
        color: #053d86;
        border-bottom-color: #053d86;
        font-weight: 600;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Filter Bar */
    .filter-bar {
        background: white;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .filter-form-inline {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: flex-end;
        width: 100%;
    }
    
    .filter-group {
        flex: 1;
        min-width: 180px;
    }
    
    .filter-group label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 0.4rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 0.6rem 0.75rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: border-color 0.2s;
    }
    
    .filter-group select:focus,
    .filter-group input:focus {
        outline: none;
        border-color: #053d86;
    }
    
    .filter-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn-filter {
        padding: 0.6rem 1.25rem;
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-filter.primary {
        background: #053d86;
        color: white;
    }
    
    .btn-filter.primary:hover {
        background: #0a4a9e;
    }
    
    .btn-filter.secondary {
        background: #f3f4f6;
        color: #4b5563;
    }
    
    .btn-filter.secondary:hover {
        background: #e5e7eb;
    }

    /* User Avatar */
    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .user-avatar.student { background: #053d86; }
    .user-avatar.instructor { background: #10b981; }

    .school-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #e0e7ff;
        color: #3730a3;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
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
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #6b7280;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
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

    .actions-cell {
        display: flex;
        gap: 6px;
        align-items: center;
    }

    .inline-form {
        display: inline;
    }

    .user-name-wrap {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .modal-title-icon {
        margin-right: 8px;
    }

    .users-pagination {
        margin-top: 1rem;
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

    .modal-footer {
        padding: 20px 25px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .modal-danger .modal-header {
        background: #fef2f2;
        border-bottom: 1px solid #fecaca;
    }

    .modal-danger .modal-header h3 {
        color: #dc2626;
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
    
    /* Responsive */
    @media (max-width: 768px) {
        .modal-content { max-width: 95%; margin: 10px; }
        .modal-body { padding: 15px; }
        .modal-footer { flex-direction: column; }
        .modal-footer .btn, .modal-footer button { width: 100%; min-height: 44px; }
        .btn-sm { padding: 8px 12px; min-height: 44px; font-size: 0.82rem; }
        th, td { padding: 10px 8px; font-size: 0.82rem; }
        .actions-cell { flex-direction: column; gap: 6px; }
    }
</style>
@endsection

@section('content')
@php
    $totalStudents = $totalStudents ?? (isset($students) ? $students->count() : 0);
    $activeStudents = $activeStudents ?? (isset($students) ? $students->where('status', 'active')->count() : 0);
    $totalInstructors = $totalInstructors ?? (isset($instructors) ? $instructors->count() : 0);
    $activeInstructors = $activeInstructors ?? (isset($instructors) ? $instructors->where('status', 'active')->count() : 0);
    $totalUsers = $totalStudents + $totalInstructors;
    $totalActive = $activeStudents + $activeInstructors;
@endphp

<!-- Statistics Cards -->
<div class="stats-row">
    <div class="stat-box students">
        <div class="number">{{ $totalStudents }}</div>
        <div class="label">Students</div>
        <div class="sub">{{ $activeStudents }} active</div>
    </div>
    <div class="stat-box instructors">
        <div class="number">{{ $totalInstructors }}</div>
        <div class="label">Instructors</div>
        <div class="sub">{{ $activeInstructors }} active</div>
    </div>
    <div class="stat-box total">
        <div class="number">{{ $totalUsers }}</div>
        <div class="label">Total Users</div>
        <div class="sub">Across all schools</div>
    </div>
    <div class="stat-box active">
        <div class="number">{{ $totalActive }}</div>
        <div class="label">Active Users</div>
        <div class="sub">Currently active</div>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <form method="GET" class="filter-form-inline">
        <div class="filter-group">
            <label>School</label>
            <select name="school_id">
                <option value="">All Schools</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email...">
        </div>
        <div class="filter-buttons">
            <button type="submit" class="btn-filter primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('system-admin.users') }}" class="btn-filter secondary"><i class="fas fa-times"></i> Clear</a>
        </div>
    </form>
</div>

<!-- Tabs -->
<div class="tabs-container">
    <div class="tabs" role="tablist" aria-label="User type tabs">
        <button class="tab active" type="button" data-tab="students" id="students-tab-btn" role="tab" aria-selected="true" aria-controls="students-tab" onclick="switchTab('students', this)">
            <i class="fas fa-user-graduate"></i> Students ({{ $totalStudents }})
        </button>
        <button class="tab" type="button" data-tab="instructors" id="instructors-tab-btn" role="tab" aria-selected="false" aria-controls="instructors-tab" tabindex="-1" onclick="switchTab('instructors', this)">
            <i class="fas fa-chalkboard-teacher"></i> Instructors ({{ $totalInstructors }})
        </button>
    </div>
</div>

<!-- Students Tab Content -->
<div id="students-tab" class="tab-content active" role="tabpanel" aria-labelledby="students-tab-btn" tabindex="0">
    <div class="card">
        <div class="card-body">
            @if(isset($students) && $students->count() > 0)
            <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="hide-mobile">ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th class="hide-mobile">Contact</th>
                        <th>School</th>
                        <th>Status</th>
                        <th class="hide-mobile">Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr>
                        <td class="hide-mobile">{{ $student->id }}</td>
                        <td>
                            <div class="user-name-wrap">
                                <div class="user-avatar student">
                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                </div>
                                <strong>{{ $student->name }}</strong>
                            </div>
                        </td>
                        <td>{{ $student->email }}</td>
                        <td class="hide-mobile">{{ $student->contact ?: '—' }}</td>
                        <td>
                            <span class="school-badge">
                                <i class="fas fa-school"></i>
                                {{ $student->school->name }}
                            </span>
                        </td>
                        <td>
                            <span class="status-badge {{ $student->status === 'active' ? 'status-active' : 'status-inactive' }}">
                                {{ ucfirst($student->status) }}
                            </span>
                        </td>
                        <td class="hide-mobile">{{ $student->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="actions-cell">
                                <form action="{{ route('system-admin.users.toggle-status', ['type' => 'student', 'id' => $student->id]) }}" method="POST" class="inline-form">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-sm btn-toggle {{ $student->status === 'active' ? 'btn-active' : 'btn-inactive' }}" 
                                            title="{{ $student->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas {{ $student->status === 'active' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                        {{ $student->status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <button type="button" class="btn-sm btn-danger" 
                                        data-action="delete-user" data-type="student" data-id="{{ $student->id }}" data-name="{{ $student->name }}"
                                        title="Delete Student">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <div class="users-pagination">
                {{ $students->appends(array_merge(request()->query(), ['tab' => 'students']))->links() }}
            </div>
            @else
            <div class="empty-state">
                <i class="fas fa-user-graduate"></i>
                <p>No students found.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Instructors Tab Content -->
<div id="instructors-tab" class="tab-content" role="tabpanel" aria-labelledby="instructors-tab-btn" tabindex="0">
    <div class="card">
        <div class="card-body">
            @if(isset($instructors) && $instructors->count() > 0)
            <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="hide-mobile">ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th class="hide-mobile">Contact</th>
                        <th>School</th>
                        <th>Status</th>
                        <th class="hide-mobile">Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($instructors as $instructor)
                    <tr>
                        <td class="hide-mobile">{{ $instructor->id }}</td>
                        <td>
                            <div class="user-name-wrap">
                                <div class="user-avatar instructor">
                                    {{ strtoupper(substr($instructor->name, 0, 1)) }}
                                </div>
                                <strong>{{ $instructor->name }}</strong>
                            </div>
                        </td>
                        <td>{{ $instructor->email }}</td>
                        <td class="hide-mobile">{{ $instructor->contact ?: '—' }}</td>
                        <td>
                            <span class="school-badge">
                                <i class="fas fa-school"></i>
                                {{ $instructor->school->name }}
                            </span>
                        </td>
                        <td>
                            <span class="status-badge {{ $instructor->status === 'active' ? 'status-active' : 'status-inactive' }}">
                                {{ ucfirst($instructor->status) }}
                            </span>
                        </td>
                        <td class="hide-mobile">{{ $instructor->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="actions-cell">
                                <form action="{{ route('system-admin.users.toggle-status', ['type' => 'instructor', 'id' => $instructor->id]) }}" method="POST" class="inline-form">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-sm btn-toggle {{ $instructor->status === 'active' ? 'btn-active' : 'btn-inactive' }}" 
                                            title="{{ $instructor->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas {{ $instructor->status === 'active' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                        {{ $instructor->status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <button type="button" class="btn-sm btn-danger" 
                                        data-action="delete-user" data-type="instructor" data-id="{{ $instructor->id }}" data-name="{{ $instructor->name }}"
                                        title="Delete Instructor">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <div class="users-pagination">
                {{ $instructors->appends(array_merge(request()->query(), ['tab' => 'instructors']))->links() }}
            </div>
            @else
            <div class="empty-state">
                <i class="fas fa-chalkboard-teacher"></i>
                <p>No instructors found.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete User Confirmation Modal -->
<div class="modal-overlay" id="deleteUserModal" role="dialog" aria-modal="true" aria-labelledby="deleteUserModalTitle" aria-hidden="true">
    <div class="modal-content modal-danger">
        <div class="modal-header">
            <h3 id="deleteUserModalTitle"><i class="fas fa-exclamation-triangle modal-title-icon"></i>Delete User</h3>
            <button type="button" class="modal-close" onclick="closeModal('deleteUserModal')" aria-label="Close delete user modal">&times;</button>
        </div>
        <form id="deleteUserForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteUserName"></strong>?</p>
                <div class="warning-text">
                    <i class="fas fa-exclamation-triangle"></i>
                    This action cannot be undone. All associated data will be permanently removed.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('deleteUserModal')">Cancel</button>
                <button type="submit" class="btn-delete"><i class="fas fa-trash"></i> Delete User</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(tabName, tabButton = null) {
    const tabs = Array.from(document.querySelectorAll('.tab'));

    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Deactivate all tabs
    tabs.forEach(tab => {
        tab.classList.remove('active');
        tab.setAttribute('aria-selected', 'false');
        tab.setAttribute('tabindex', '-1');
    });
    
    // Show selected tab content
    const selectedPanel = document.getElementById(tabName + '-tab');
    selectedPanel.classList.add('active');
    
    // Activate clicked tab
    if (tabButton) {
        tabButton.classList.add('active');
        tabButton.setAttribute('aria-selected', 'true');
        tabButton.setAttribute('tabindex', '0');
        tabButton.focus();
    }

    const newUrl = new URL(window.location.href);
    newUrl.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', newUrl.toString());
}

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
    const btn = e.target.closest('[data-action="delete-user"]');
    if (!btn) return;
    document.getElementById('deleteUserName').textContent = btn.dataset.name;
    document.getElementById('deleteUserForm').action = '{{ url("system-admin/users") }}/' + btn.dataset.type + '/' + btn.dataset.id;
    openModal('deleteUserModal');
});

// Close modal when clicking outside
document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.setAttribute('aria-hidden', 'true');
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
            this.setAttribute('aria-hidden', 'true');
            activeModal = null;
            if (modalTrigger) {
                modalTrigger.focus();
            }
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

document.querySelectorAll('.tab').forEach((tab, index, tabs) => {
    tab.addEventListener('keydown', function(e) {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(e.key)) {
            return;
        }

        e.preventDefault();
        let nextIndex = index;

        if (e.key === 'ArrowRight') nextIndex = (index + 1) % tabs.length;
        if (e.key === 'ArrowLeft') nextIndex = (index - 1 + tabs.length) % tabs.length;
        if (e.key === 'Home') nextIndex = 0;
        if (e.key === 'End') nextIndex = tabs.length - 1;

        const nextTab = tabs[nextIndex];
        switchTab(nextTab.dataset.tab, nextTab);
    });
});

const initialTab = new URLSearchParams(window.location.search).get('tab');
if (initialTab === 'instructors') {
    const instructorsTabButton = document.querySelector('.tab[data-tab="instructors"]');
    if (instructorsTabButton) {
        switchTab('instructors', instructorsTabButton);
    }
}
</script>
@endsection
