@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Instructor Removal Requests')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $schoolName = $school->name ?? 'Driving School';
    $primaryColor = $settings->primary_color ?? '#667eea';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .requests-container {
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
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .page-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    .badge-count {
        background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        border-bottom: 2px solid #e0e0e0;
    }

    .tab-btn {
        padding: 12px 24px;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        color: #666;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .tab-btn:hover {
        color: #333;
        background: #f5f5f5;
    }

    .tab-btn.active {
        color: #667eea;
        border-bottom-color: #667eea;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .request-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-left: 4px solid #FF9800;
        transition: all 0.3s ease;
    }

    .request-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }

    .request-card.approved {
        border-left-color: #4CAF50;
        opacity: 0.9;
    }

    .request-card.rejected {
        border-left-color: #f44336;
        opacity: 0.9;
    }

    .request-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .request-info {
        flex: 1;
    }

    .instructor-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 5px;
    }

    .time-slot-info {
        color: #666;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 5px;
    }

    .request-status {
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pending {
        background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
        color: white;
    }

    .status-approved {
        background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
        color: white;
    }

    .status-rejected {
        background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
        color: white;
    }

    .request-reason {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
        border-left: 3px solid #667eea;
    }

    .request-reason-label {
        font-weight: 600;
        color: #555;
        margin-bottom: 8px;
        display: block;
    }

    .request-reason-text {
        color: #333;
        line-height: 1.6;
        white-space: pre-wrap;
    }

    .admin-notes {
        background: #fff3cd;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
        border-left: 3px solid #ffc107;
    }

    .request-meta {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        color: #888;
        font-size: 0.85rem;
        margin-bottom: 15px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .request-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-approve {
        background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
        color: white;
    }

    .btn-approve:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
    }

    .btn-reject {
        background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
        color: white;
    }

    .btn-reject:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
    }

    .btn-secondary {
        background: #e0e0e0;
        color: #666;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 15px;
    }

    .empty-state-text {
        font-size: 1.2rem;
        color: #666;
    }

    .empty-state-note {
        color: #999;
        margin-top: 10px;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 16px;
        max-width: 500px;
        width: 90%;
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 32px;
        border-radius: 16px 16px 0 0;
    }

    .modal-title {
        font-size: 1.5rem;
        margin: 0;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #555;
    }

    .form-control {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-family: inherit;
        font-size: 14px;
        resize: vertical;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .modal-description {
        margin-bottom: 20px;
        color: #666;
    }

    .required-mark {
        color: #f44336;
    }

    @media (max-width: 768px) {
        .request-header {
            flex-direction: column;
        }

        .request-actions {
            width: 100%;
        }

        .btn {
            flex: 1;
            justify-content: center;
        }

        .tabs {
            overflow-x: auto;
        }

        .page-title {
            font-size: 1.5rem;
        }
    }
</style>

<div class="requests-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                Instructor Removal Requests
                @if($pendingCount > 0)
                    <span class="badge-count">{{ $pendingCount }}</span>
                @endif
            </h1>
            <p class="page-subtitle">Review and process instructor schedule removal requests for {{ $schoolName }}</p>
        </div>
    </div>



    <div class="tabs">
        <button class="tab-btn active" data-tab="pending" onclick="switchTab('pending')">
            Pending ({{ $pendingCount }})
        </button>
        <button class="tab-btn" data-tab="processed" onclick="switchTab('processed')">
            Processed ({{ $processedCount }})
        </button>
    </div>

    <!-- Pending Requests Tab -->
    <div id="pending-tab" class="tab-content active">
        @if($pendingRequests->count() === 0)
            <div class="empty-state">
                <div class="empty-state-icon">✓</div>
                <div class="empty-state-text">No pending removal requests</div>
                <p class="empty-state-note">All caught up! There are no instructor removal requests waiting for review.</p>
            </div>
        @else
            @foreach($pendingRequests as $request)
                <div class="request-card">
                    <div class="request-header">
                        <div class="request-info">
                            <div class="instructor-name">
                                {{ $request->instructor->name }}
                            </div>
                            <div class="time-slot-info">
                                {{ \Carbon\Carbon::parse($request->timeSlot->date)->format('l, F j, Y') }}
                            </div>
                            <div class="time-slot-info">
                                {{ \Carbon\Carbon::parse($request->timeSlot->start_time)->format('g:i A') }}
                                - {{ \Carbon\Carbon::parse($request->timeSlot->end_time)->format('g:i A') }}
                            </div>
                        </div>
                        <span class="request-status status-pending">Pending</span>
                    </div>

                    <div class="request-reason">
                        <span class="request-reason-label">Reason for Removal Request:</span>
                        <div class="request-reason-text">{{ $request->reason }}</div>
                    </div>

                    <div class="request-meta">
                        <div class="meta-item">
                            <span>Email:</span>
                            <span>{{ $request->instructor->email }}</span>
                        </div>
                        <div class="meta-item">
                            <span>Contact:</span>
                            <span>{{ $request->instructor->contact ?? 'N/A' }}</span>
                        </div>
                        <div class="meta-item">
                            <span>Requested:</span>
                            <span>{{ $request->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    <div class="request-actions">
                        <button class="btn btn-approve" onclick="showApproveModal({{ $request->id }}, '{{ $request->instructor->name }}')">
                            Approve
                        </button>
                        <button class="btn btn-reject" onclick="showRejectModal({{ $request->id }}, '{{ $request->instructor->name }}')">
                            Reject
                        </button>
                    </div>
                </div>
            @endforeach
            <div class="mt-4">
                {{ $pendingRequests->appends(['processed_page' => $processedRequests->currentPage()])->links() }}
            </div>
        @endif
    </div>

    <!-- Processed Requests Tab -->
    <div id="processed-tab" class="tab-content">
        @if($processedRequests->count() === 0)
            <div class="empty-state">
                <div class="empty-state-icon"></div>
                <div class="empty-state-text">No processed requests</div>
                <p class="empty-state-note">There are no approved or rejected requests yet.</p>
            </div>
        @else
            @foreach($processedRequests as $request)
                <div class="request-card {{ $request->status }}">
                    <div class="request-header">
                        <div class="request-info">
                            <div class="instructor-name">
                                {{ $request->instructor->name }}
                            </div>
                            <div class="time-slot-info">
                                {{ \Carbon\Carbon::parse($request->timeSlot->date)->format('l, F j, Y') }}
                            </div>
                            <div class="time-slot-info">
                                {{ \Carbon\Carbon::parse($request->timeSlot->start_time)->format('g:i A') }}
                                - {{ \Carbon\Carbon::parse($request->timeSlot->end_time)->format('g:i A') }}
                            </div>
                        </div>
                        <span class="request-status status-{{ $request->status }}">
                            @if($request->status === 'approved')
                                Approved
                            @else
                                Rejected
                            @endif
                        </span>
                    </div>

                    <div class="request-reason">
                        <span class="request-reason-label">Instructor's Reason:</span>
                        <div class="request-reason-text">{{ $request->reason }}</div>
                    </div>

                    @if($request->admin_notes)
                        <div class="admin-notes">
                            <span class="request-reason-label">Admin Notes:</span>
                            <div class="request-reason-text">{{ $request->admin_notes }}</div>
                        </div>
                    @endif

                    <div class="request-meta">
                        <div class="meta-item">
                            <span>Processed by:</span>
                            <span>{{ $request->processedBy->name ?? 'Unknown' }}</span>
                        </div>
                        <div class="meta-item">
                            <span>Processed:</span>
                            <span>{{ $request->processed_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="mt-4">
                {{ $processedRequests->appends(['pending_page' => $pendingRequests->currentPage()])->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Approve Modal -->
<div class="modal" id="approveModal" onclick="if(event.target === this) closeApproveModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Approve Removal Request</h2>
        </div>
        <div class="modal-body">
            <form id="approveForm" method="POST">
                @csrf
                <input type="hidden" name="modal_type" value="approve">
                <input type="hidden" name="request_id" id="approveRequestId">
                <input type="hidden" name="instructor_name" id="approveInstructorNameInput">
                <p class="modal-description">
                    Are you sure you want to approve this removal request for <strong id="approveInstructorName"></strong>? 
                    The instructor will be removed from the time slot.
                </p>
                <div class="form-group">
                    <label class="form-label">Admin Notes (Optional/Internal):</label>
                    <textarea name="admin_notes" class="form-control" rows="2" placeholder="Internal notes about this approval..."></textarea>
                </div>
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" name="notify_students" value="1" id="notifyStudentsToggle" onchange="toggleNotificationMessage()">
                        Notify affected students via Email & Dashboard
                    </label>
                </div>
                <div id="notificationMessageContainer" style="display: none;">
                    <div class="form-group">
                        <label class="form-label">Custom Notification Message (Optional):</label>
                        <textarea name="notification_message" class="form-control" rows="3" placeholder="Explain the change to students..."></textarea>
                        <small class="text-muted">If empty, a default message will be sent.</small>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeApproveModal()">Cancel</button>
                    <button type="submit" class="btn btn-approve">Confirm Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal" id="rejectModal" onclick="if(event.target === this) closeRejectModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Reject Removal Request</h2>
        </div>
        <div class="modal-body">
            <form id="rejectForm" method="POST">
                @csrf
                <input type="hidden" name="modal_type" value="reject">
                <input type="hidden" name="request_id" id="rejectRequestId">
                <input type="hidden" name="instructor_name" id="rejectInstructorNameInput">
                <p class="modal-description">
                    You are rejecting the removal request for <strong id="rejectInstructorName"></strong>. 
                    Please provide a reason for the rejection.
                </p>
                <div class="form-group">
                    <label class="form-label">
                        Reason for Rejection <span class="required-mark">*</span>
                    </label>
                    <textarea name="admin_notes" class="form-control" rows="4" required placeholder="Explain why this request is being rejected..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn btn-reject">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function switchTab(tabName) {
        // Update buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
            // Add active to the button with matching data-tab attribute
            if (btn.getAttribute('data-tab') === tabName) {
                btn.classList.add('active');
            }
        });

        // Update content
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        document.getElementById(tabName + '-tab').classList.add('active');
    }

    function showApproveModal(requestId, instructorName) {
        const modal = document.getElementById('approveModal');
        const form = document.getElementById('approveForm');
        const nameEl = document.getElementById('approveInstructorName');

        form.action = `{{ route('schools.admin.removalRequests.approve', ['school' => $school->slug, 'id' => '__ID__']) }}`.replace('__ID__', requestId);
        nameEl.textContent = instructorName;
        document.getElementById('approveRequestId').value = requestId;
        document.getElementById('approveInstructorNameInput').value = instructorName;

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeApproveModal() {
        const modal = document.getElementById('approveModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
        
        // Reset notification fields
        const toggle = document.getElementById('notifyStudentsToggle');
        if (toggle) toggle.checked = false;
        const container = document.getElementById('notificationMessageContainer');
        if (container) container.style.display = 'none';
    }

    function toggleNotificationMessage() {
        const toggle = document.getElementById('notifyStudentsToggle');
        const container = document.getElementById('notificationMessageContainer');
        if (toggle && container) {
            container.style.display = toggle.checked ? 'block' : 'none';
        }
    }

    function showRejectModal(requestId, instructorName) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        const nameEl = document.getElementById('rejectInstructorName');

        form.action = `{{ route('schools.admin.removalRequests.reject', ['school' => $school->slug, 'id' => '__ID__']) }}`.replace('__ID__', requestId);
        nameEl.textContent = instructorName;
        document.getElementById('rejectRequestId').value = requestId;
        document.getElementById('rejectInstructorNameInput').value = instructorName;

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeRejectModal() {
        const modal = document.getElementById('rejectModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Initialize removal requests page
    function initializeRemovalRequestsPage() {
        // Close modals on ESC key - use named function to prevent duplicates
        if (!window.removalRequestsEscHandler) {
            window.removalRequestsEscHandler = function(e) {
                if (e.key === 'Escape') {
                    closeApproveModal();
                    closeRejectModal();
                }
            };
            document.addEventListener('keydown', window.removalRequestsEscHandler);
        }
    }

    // Call on DOMContentLoaded (initial page load)
    document.addEventListener('DOMContentLoaded', function() {
        initializeRemovalRequestsPage();

        @if($errors->any())
            @if(old('modal_type') === 'approve')
                showApproveModal({{ old('request_id', 'null') }}, '{{ old('instructor_name', 'Instructor') }}');
            @elseif(old('modal_type') === 'reject')
                showRejectModal({{ old('request_id', 'null') }}, '{{ old('instructor_name', 'Instructor') }}');
            @endif
        @endif
    });
    
    // Also call immediately (for AJAX navigation)
    if (document.readyState !== 'loading') {
        initializeRemovalRequestsPage();
    }
</script>

@endsection
