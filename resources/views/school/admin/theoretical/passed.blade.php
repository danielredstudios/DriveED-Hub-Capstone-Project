@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Passed Students - Theoretical')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings->primary_color ?? '#667eea';
    $secondaryColor = $settings->secondary_color ?? '#764ba2';

    // ...existing code...
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .theoretical-container {
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

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #6b7280;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        transition: all 0.2s;
    }

    .back-link:hover {
        color: {{ $primaryColor }};
        border-color: {{ $primaryColor }};
        background: rgba(102, 126, 234, 0.05);
    }

    /* Stats Row */
    .stats-row {
        display: flex;
        gap: 20px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .stat-card-custom {
        flex: 1;
        min-width: 180px;
        max-width: 280px;
        background: white;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .stat-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon-box.success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
    }

    .stat-icon-box.primary {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
    }

    .stat-info .stat-number {
        font-size: 1.6rem;
        font-weight: 700;
        color: #1f2937;
        line-height: 1;
    }

    .stat-info .stat-label {
        font-size: 0.8rem;
        color: #6b7280;
        margin-top: 4px;
    }

    /* Table card */
    .table-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    .table-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f9fafb;
    }

    .table-card-header h2 {
        font-size: 1.05rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .search-box {
        display: flex;
        align-items: center;
        gap: 8px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 6px 12px;
    }

    .search-box svg {
        color: #9ca3af;
        flex-shrink: 0;
    }

    .search-box input {
        border: none;
        outline: none;
        font-size: 0.85rem;
        color: #1f2937;
        min-width: 180px;
        background: transparent;
    }

    .search-box input::placeholder {
        color: #9ca3af;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        color: white;
    }

    th {
        padding: 14px 16px;
        text-align: left;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
    }

    td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    tbody tr {
        transition: background-color 0.2s ease;
    }

    tbody tr:hover {
        background-color: #f8fafc;
    }

    tbody tr:last-child td {
        border-bottom: none;
    }

    /* User cell */
    .user-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .user-info-col {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .user-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.9rem;
    }

    .user-email {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .hours-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        background: #dbeafe;
        color: #1e40af;
    }

    .passed-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        background: #d1fae5;
        color: #065f46;
    }

    .date-text {
        color: #374151;
        font-weight: 500;
    }

    .btn-action {
        padding: 7px 14px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-view {
        background: #0ea5e9;
        color: white;
    }

    .btn-view:hover {
        background: #0284c7;
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(14, 165, 233, 0.3);
        color: white;
    }

    .btn-revoke {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .btn-revoke:hover {
        background: #fecaca;
        transform: translateY(-1px);
    }

    /* Pagination */
    .table-footer {
        padding: 14px 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f9fafb;
    }

    .pagination-info {
        font-size: 0.8rem;
        color: #6b7280;
    }

    /* Empty state */
    .empty-state {
        padding: 60px 30px;
        text-align: center;
    }

    .empty-state-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 16px;
        border-radius: 50%;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
    }

    .empty-state-text {
        font-size: 1rem;
        color: #6b7280;
    }

    .empty-state-sub {
        font-size: 0.85rem;
        color: #9ca3af;
        margin-top: 4px;
    }

    .icon-14 {
        width: 14px;
        height: 14px;
    }

    .icon-16 {
        width: 16px;
        height: 16px;
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

    @media (max-width: 768px) {
        .theoretical-container { padding: 15px; }
        .page-header { flex-direction: column; align-items: flex-start; gap: 10px; }
        .stats-row { flex-direction: column; }
        .stat-card-custom { max-width: 100%; }
        .table-card-header { flex-direction: column; gap: 10px; }
        table { min-width: 700px; }
    }
</style>

<div class="theoretical-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Passed Students</h1>
            <p class="page-subtitle">Students who have successfully completed theoretical training</p>
        </div>
        <a href="{{ school_route('admin.theoretical.index') }}" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Overview
        </a>
    </div>



    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card-custom">
            <div class="stat-icon-box success">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-number">{{ $totalPassed ?? 0 }}</div>
                <div class="stat-label">Total Passed</div>
            </div>
        </div>

        <div class="stat-card-custom">
            <div class="stat-icon-box primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-number">{{ $passedThisMonth ?? 0 }}</div>
                <div class="stat-label">Passed This Month</div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-card-header">
            <h2>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#10b981" class="icon-20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
                Passed Students List
            </h2>
            <div class="search-box">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" id="searchInput" placeholder="Search students...">
            </div>
        </div>

        <div class="table-wrapper">
            @if($passedStudents->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Total Hours</th>
                            <th>Date Passed</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($passedStudents as $student)
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">{{ strtoupper(substr($student->name ?? 'N', 0, 1)) }}</div>
                                        <div class="user-info-col">
                                            <div class="user-name">{{ $student->name ?? 'N/A' }}</div>
                                            <div class="user-email">{{ $student->email ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="hours-badge">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-14">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $student->total_theoretical_hours ?? 0 }} hrs
                                    </span>
                                </td>
                                <td>
                                    <span class="date-text">{{ optional($student->theoretical_passed_at)->format('M d, Y') ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="passed-badge">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-14">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Passed
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ school_route('admin.userManagement') }}"
                                       class="btn-action btn-view" title="View Student Profile">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-14">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-32">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        </svg>
                    </div>
                    <div class="empty-state-text">No students have passed theoretical training yet</div>
                    <div class="empty-state-sub">Students will appear here after being marked as passed</div>
                </div>
            @endif
        </div>

        @if($passedStudents->hasPages())
            <div class="table-footer">
                <div class="pagination-info">
                    Showing {{ $passedStudents->firstItem() }} to {{ $passedStudents->lastItem() }} of {{ $passedStudents->total() }} students
                </div>
                {{ $passedStudents->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>
@endpush

@endsection
