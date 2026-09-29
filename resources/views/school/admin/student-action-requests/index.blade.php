@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Student Action Requests')

@section('content')
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
    .sar-container {
        padding: 20px;
        margin: 20px auto;
        max-width: 1200px;
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
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    .header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-create {
        padding: 12px 24px;
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
    }

    .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-create-remove {
        padding: 12px 24px;
        background: #dc3545;
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
    }

    .btn-create-remove:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(220, 53, 69, 0.4);
    }

    /* Alert Messages */
    .alert {
        padding: 15px 20px;
        margin-bottom: 20px;
        border-radius: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        animation: slideIn 0.3s ease;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    .close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: inherit;
        opacity: 0.6;
    }

    .close-btn:hover {
        opacity: 1;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }

    .empty-state i {
        font-size: 4rem;
        color: {{ $primaryColor }};
        opacity: 0.3;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        color: #374151;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #6b7280;
        font-size: 1rem;
    }

    /* Table */
    .sar-table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .sar-table {
        width: 100%;
        border-collapse: collapse;
    }

    .sar-table thead {
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
    }

    .sar-table thead th {
        padding: 14px 18px;
        text-align: left;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .sar-table tbody tr {
        border-bottom: 1px solid #e5e7eb;
        transition: background 0.2s;
    }

    .sar-table tbody tr:hover {
        background: #f9fafb;
    }

    .sar-table tbody td {
        padding: 14px 18px;
        font-size: 0.95rem;
        color: #374151;
        vertical-align: middle;
    }

    .student-name {
        font-weight: 600;
        color: #1f2937;
    }

    .student-detail {
        font-size: 0.85rem;
        color: #6b7280;
    }

    /* Badges */
    .type-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        gap: 6px;
    }

    .type-add {
        background: #d1fae5;
        color: #065f46;
    }

    .type-remove {
        background: #fee2e2;
        color: #991b1b;
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

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .status-denied {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Action Buttons */
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

    .btn-approve {
        background: #d1fae5;
        color: #065f46;
    }

    .btn-approve:hover {
        background: #a7f3d0;
    }

    .btn-deny {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-deny:hover {
        background: #fecaca;
    }

    .reviewed-info {
        font-size: 0.8rem;
        color: #9ca3af;
    }

    .reviewed-info strong {
        color: #6b7280;
    }

    .text-muted-dash {
        color: #9ca3af;
    }

    .icon-add-green {
        color: #065f46;
    }

    .icon-remove-red {
        color: #991b1b;
    }

    .icon-approve-green {
        color: #065f46;
    }

    .icon-deny-red {
        color: #991b1b;
    }

    .required-asterisk {
        color: #dc3545;
    }

    .modal-confirm-text {
        color: #374151;
        margin-bottom: 20px;
    }

    .text-approve-strong {
        color: #065f46;
    }

    .text-deny-strong {
        color: #991b1b;
    }

    /* Modal */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 16px;
        width: 100%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalSlide 0.3s ease;
    }

    @keyframes modalSlide {
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-header h5 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .btn-close-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #6b7280;
        padding: 5px;
        line-height: 1;
    }

    .btn-close-modal:hover {
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
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
        font-size: 0.9rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: border-color 0.2s;
        box-sizing: border-box;
        font-family: inherit;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: {{ $primaryColor }};
        box-shadow: 0 0 0 3px {{ $primaryColor }}20;
    }

    .form-hint {
        font-size: 0.8rem;
        color: #9ca3af;
        margin-top: 4px;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 15px 25px;
        border-top: 1px solid #e5e7eb;
    }

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

    .btn-danger {
        padding: 10px 20px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-danger:hover {
        background: #c82333;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
    }

    .btn-success {
        padding: 10px 20px;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-success:hover {
        background: #218838;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
    }

    .error-text {
        color: #dc3545;
        font-size: 0.85rem;
        margin-top: 4px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .header-actions {
            width: 100%;
        }

        .header-actions button {
            flex: 1;
        }

        .sar-table-card {
            overflow-x: auto;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<div class="sar-container">
    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="bi bi-clipboard-check"></i>
                Student Action Requests
            </h1>
            <p class="page-subtitle">Manage student add/remove requests for {{ $schoolName }}</p>
        </div>
        @if($admin->isBranchSecretary())
        <div class="header-actions">
            <button class="btn-create" onclick="openAddModal()">
                <i class="bi bi-person-plus-fill"></i> Request Add Student
            </button>
            <button class="btn-create-remove" onclick="openRemoveModal()">
                <i class="bi bi-person-dash-fill"></i> Request Remove Student
            </button>
        </div>
        @endif
    </div>



    {{-- Requests Table --}}
    @if($requests->count() > 0)
    <div class="sar-table-card">
        <table class="sar-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Student Info</th>
                    <th>Branch</th>
                    <th>Requested By</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $request)
                <tr>
                    {{-- Type --}}
                    <td>
                        @if($request->action === 'add')
                            <span class="type-badge type-add">
                                <i class="bi bi-person-plus-fill"></i> Add
                            </span>
                        @else
                            <span class="type-badge type-remove">
                                <i class="bi bi-person-dash-fill"></i> Remove
                            </span>
                        @endif
                    </td>

                    {{-- Student Info --}}
                    <td>
                        <div class="student-name">{{ $request->student_name ?? ($request->student->name ?? '—') }}</div>
                        @if($request->student_email || optional($request->student)->email)
                            <div class="student-detail">
                                <i class="bi bi-envelope"></i> {{ $request->student_email ?? $request->student->email }}
                            </div>
                        @endif
                        @if($request->student_contact || optional($request->student)->contact)
                            <div class="student-detail">
                                <i class="bi bi-telephone"></i> {{ $request->student_contact ?? $request->student->contact }}
                            </div>
                        @endif
                    </td>

                    {{-- Branch --}}
                    <td>
                        @if($request->branch)
                            {{ $request->branch->name }}
                        @else
                            <span class="text-muted-dash">—</span>
                        @endif
                    </td>

                    {{-- Requested By --}}
                    <td>
                        @if($request->requestedBy)
                            {{ $request->requestedBy->name }}
                        @else
                            <span class="text-muted-dash">—</span>
                        @endif
                    </td>

                    {{-- Reason --}}
                    <td>
                        <span title="{{ $request->reason }}">
                            {{ Str::limit($request->reason, 60) }}
                        </span>
                    </td>

                    {{-- Status --}}
                    <td>
                        @if($request->status === 'pending')
                            <span class="status-badge status-pending">
                                <i class="bi bi-clock-fill"></i> Pending
                            </span>
                        @elseif($request->status === 'approved')
                            <span class="status-badge status-approved">
                                <i class="bi bi-check-circle-fill"></i> Approved
                            </span>
                        @else
                            <span class="status-badge status-denied">
                                <i class="bi bi-x-circle-fill"></i> Denied
                            </span>
                        @endif
                    </td>

                    {{-- Date --}}
                    <td>
                        {{ $request->created_at->format('M d, Y') }}
                        <div class="student-detail">{{ $request->created_at->format('h:i A') }}</div>
                    </td>

                    {{-- Actions --}}
                    <td>
                        @if($request->status === 'pending' && $admin->isSchoolAdmin())
                            <div class="action-buttons">
                                <button class="btn-action btn-approve" onclick="openApproveModal({{ $request->id }})">
                                    <i class="bi bi-check-lg"></i> Approve
                                </button>
                                <button class="btn-action btn-deny" onclick="openDenyModal({{ $request->id }})">
                                    <i class="bi bi-x-lg"></i> Deny
                                </button>
                            </div>
                        @elseif($request->status !== 'pending')
                            <div class="reviewed-info">
                                @if($request->reviewedBy)
                                    <strong>By:</strong> {{ $request->reviewedBy->name }}<br>
                                @endif
                                @if($request->reviewed_at)
                                    <strong>On:</strong> {{ \Carbon\Carbon::parse($request->reviewed_at)->format('M d, Y') }}<br>
                                @endif
                                @if($request->review_notes)
                                    <strong>Notes:</strong> {{ Str::limit($request->review_notes, 40) }}
                                @endif
                            </div>
                        @else
                            <span class="text-muted-dash">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <i class="bi bi-clipboard-check"></i>
        <h3>No Action Requests</h3>
        <p>There are no student action requests at this time.
            @if($admin->isBranchSecretary())
                Use the buttons above to submit a new request.
            @endif
        </p>
    </div>
    @endif
</div>

{{-- ======================== MODALS ======================== --}}

{{-- Add Student Request Modal (Secretary only) --}}
@if($admin->isBranchSecretary())
<div class="modal-overlay" id="addStudentModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5><i class="bi bi-person-plus-fill icon-add-green"></i> Request Add Student</h5>
            <button class="btn-close-modal" onclick="closeModal('addStudentModal')">&times;</button>
        </div>
        <form action="{{ route('schools.admin.student-action-requests.add', $school) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="add_student_name">Student Full Name <span class="required-asterisk">*</span></label>
                    <input type="text" id="add_student_name" name="student_name" required
                           placeholder="Enter student's full name" value="{{ old('student_name') }}">
                </div>
                <div class="form-group">
                    <label for="add_student_email">Student Email <span class="required-asterisk">*</span></label>
                    <input type="email" id="add_student_email" name="student_email" required
                           placeholder="Enter student's email" value="{{ old('student_email') }}">
                </div>
                <div class="form-group">
                    <label for="add_student_contact">Student Contact Number</label>
                    <input type="text" id="add_student_contact" name="student_contact" inputmode="numeric" pattern="[0-9]*" autocomplete="tel" maxlength="15"
                           placeholder="Enter student's contact number" value="{{ old('student_contact') }}">
                    <div class="form-hint">Optional but recommended.</div>
                </div>
                <div class="form-group">
                    <label for="add_reason">Reason <span class="required-asterisk">*</span></label>
                    <textarea id="add_reason" name="reason" required
                              placeholder="Explain why this student should be added...">{{ old('reason') }}</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('addStudentModal')">Cancel</button>
                <button type="submit" class="btn-success">
                    <i class="bi bi-send-fill"></i> Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Remove Student Request Modal (Secretary only) --}}
<div class="modal-overlay" id="removeStudentModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5><i class="bi bi-person-dash-fill icon-remove-red"></i> Request Remove Student</h5>
            <button class="btn-close-modal" onclick="closeModal('removeStudentModal')">&times;</button>
        </div>
        <form action="{{ route('schools.admin.student-action-requests.remove', $school) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="remove_student_id">Student ID <span class="required-asterisk">*</span></label>
                    <input type="number" id="remove_student_id" name="student_id" required
                           placeholder="Enter the student's ID number" value="{{ old('student_id') }}">
                    <div class="form-hint">Enter the ID of the student to be removed from your branch.</div>
                </div>
                <div class="form-group">
                    <label for="remove_reason">Reason <span class="required-asterisk">*</span></label>
                    <textarea id="remove_reason" name="reason" required
                              placeholder="Explain why this student should be removed...">{{ old('reason') }}</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('removeStudentModal')">Cancel</button>
                <button type="submit" class="btn-danger">
                    <i class="bi bi-send-fill"></i> Submit Request
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Approve Request Modal (School Admin only) --}}
@if($admin->isSchoolAdmin())
<div class="modal-overlay" id="approveModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5><i class="bi bi-check-circle-fill icon-approve-green"></i> Approve Request</h5>
            <button class="btn-close-modal" onclick="closeModal('approveModal')">&times;</button>
        </div>
        <form id="approveForm" method="POST">
            @csrf
            <div class="modal-body">
                <p class="modal-confirm-text">
                    Are you sure you want to <strong class="text-approve-strong">approve</strong> this student action request?
                    This will execute the requested action.
                </p>
                <div class="form-group">
                    <label for="approve_review_notes">Review Notes (Optional)</label>
                    <textarea id="approve_review_notes" name="review_notes"
                              placeholder="Add any notes about this approval..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('approveModal')">Cancel</button>
                <button type="submit" class="btn-success">
                    <i class="bi bi-check-lg"></i> Confirm Approve
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Deny Request Modal (School Admin only) --}}
<div class="modal-overlay" id="denyModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5><i class="bi bi-x-circle-fill icon-deny-red"></i> Deny Request</h5>
            <button class="btn-close-modal" onclick="closeModal('denyModal')">&times;</button>
        </div>
        <form id="denyForm" method="POST">
            @csrf
            <div class="modal-body">
                <p class="modal-confirm-text">
                    Are you sure you want to <strong class="text-deny-strong">deny</strong> this student action request?
                </p>
                <div class="form-group">
                    <label for="deny_review_notes">Review Notes <span class="required-asterisk">*</span></label>
                    <textarea id="deny_review_notes" name="review_notes" required
                              placeholder="Explain why this request is being denied..."></textarea>
                    <div class="form-hint">You must provide a reason for denying the request.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('denyModal')">Cancel</button>
                <button type="submit" class="btn-danger">
                    <i class="bi bi-x-lg"></i> Confirm Deny
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function enforceNumericOnly(input) {
        if (!input) return;
        const sanitize = function() {
            input.value = input.value.replace(/\D+/g, '');
        };
        input.addEventListener('input', sanitize);
        input.addEventListener('paste', function() { setTimeout(sanitize, 0); });
    }

    document.addEventListener('DOMContentLoaded', function() {
        enforceNumericOnly(document.getElementById('add_student_contact'));
    });

    // Generic modal open/close
    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    // Secretary modals
    function openAddModal() {
        openModal('addStudentModal');
    }

    function openRemoveModal() {
        openModal('removeStudentModal');
    }

    // Admin modals – set form action dynamically
    function openApproveModal(requestId) {
        var form = document.getElementById('approveForm');
        var baseUrl = "{{ route('schools.admin.student-action-requests.approve', [$school, '__ID__']) }}";
        form.action = baseUrl.replace('__ID__', requestId);
        document.getElementById('approve_review_notes').value = '';
        openModal('approveModal');
    }

    function openDenyModal(requestId) {
        var form = document.getElementById('denyForm');
        var baseUrl = "{{ route('schools.admin.student-action-requests.deny', [$school, '__ID__']) }}";
        form.action = baseUrl.replace('__ID__', requestId);
        document.getElementById('deny_review_notes').value = '';
        openModal('denyModal');
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

    // Modal Restoration Logic
    document.addEventListener('DOMContentLoaded', function() {
        @if($errors->any())
            @if(old('student_name'))
                openAddModal();
            @elseif(old('student_id'))
                openRemoveModal();
            @endif
        @endif
    });
</script>
@endsection
