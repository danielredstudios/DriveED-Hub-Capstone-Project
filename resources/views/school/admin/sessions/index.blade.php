@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Session Completions')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $activeSessionType = request('session_type', '');
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .page-wrap {
        max-width: 1500px;
        margin: 20px auto;
        padding: 0 20px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 3px solid {{ $primaryColor }};
        padding-bottom: 12px;
    }

    .page-title {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 700;
        color: #111827;
    }

    .subtitle {
        margin-top: 6px;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .filters {
        background: #fff;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 16px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .filters input,
    .filters select {
        width: 100%;
        padding: 9px 10px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.9rem;
    }

    .filters .btn {
        border: none;
        border-radius: 8px;
        padding: 10px 14px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-apply {
        background: {{ $primaryColor }};
        color: #fff;
    }

    .btn-clear {
        background: #f3f4f6;
        color: #374151;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .table-card {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .table-top {
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #374151;
        font-size: 0.9rem;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead th {
        text-align: left;
        background: #f9fafb;
        color: #6b7280;
        font-size: 0.78rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        padding: 11px 14px;
        border-bottom: 1px solid #e5e7eb;
    }

    tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid #f3f4f6;
        color: #1f2937;
        font-size: 0.9rem;
    }

    tbody tr:hover {
        background: #fafafa;
    }

    .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .badge-theoretical { background: #dbeafe; color: #1d4ed8; }
    .badge-practical { background: #ede9fe; color: #6d28d9; }

    .empty {
        text-align: center;
        padding: 24px;
        color: #6b7280;
    }

    .pagination-wrap {
        padding: 12px 16px;
    }

    .card-filter {
        cursor: pointer;
    }

    .card-filter-active {
        border-color: {{ $primaryColor }} !important;
        box-shadow: 0 0 0 2px {{ $primaryColor }}30;
    }

    .search-inline {
        min-width: 280px;
        max-width: 420px;
        width: 100%;
    }

    .search-inline input {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.88rem;
    }

    /* Keep session stat-card icons visually aligned with lighter card icon style used elsewhere. */
    .stats-grid .stat-icon {
        width: 48px;
        height: 48px;
    }

    .stats-grid .stat-icon .icon-24 {
        width: 30px;
        height: 30px;
    }

    .stats-grid .stat-icon .icon-24 path {
        stroke-width: 1.6 !important;
    }
</style>

<div class="page-wrap">
    <div class="page-header">
        <div>
            <h1 class="page-title">Session Completions</h1>
            <div class="subtitle">Review all instructor logged sessions</div>
        </div>
    </div>

    <div class="stats-grid" style="margin-bottom: 16px;">
        <div class="stat-card info card-filter {{ $activeSessionType === '' ? 'card-filter-active' : '' }}" onclick="applySessionTypeFilter('')">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">All Sessions</div>
                        <div class="stat-value">{{ $stats['total_sessions'] ?? 0 }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>
                <div class="stat-detail">All logged sessions for selected date/instructor scope</div>
            </div>
        </div>

        <div class="stat-card students card-filter {{ $activeSessionType === 'theoretical' ? 'card-filter-active' : '' }}" onclick="applySessionTypeFilter('theoretical')">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Theoretical</div>
                        <div class="stat-value">{{ $stats['theoretical_count'] ?? 0 }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422A12.083 12.083 0 0118 14.5C18 16.985 15.314 19 12 19s-6-2.015-6-4.5c0-1.386.688-2.63 1.84-3.422L12 14z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Theory session logs</div>
            </div>
        </div>

        <div class="stat-card growth card-filter {{ $activeSessionType === 'practical' ? 'card-filter-active' : '' }}" onclick="applySessionTypeFilter('practical')">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Practical</div>
                        <div class="stat-value">{{ $stats['practical_count'] ?? 0 }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17h6m-6 0a2 2 0 11-4 0m4 0a2 2 0 104 0m0 0a2 2 0 104 0m-4 0H9m0-8l1.6-3.2A2 2 0 0112.4 5h3.2a2 2 0 011.79 1.11L19 10m-14 0h14M5 10l-.75 3a2 2 0 001.94 2.5h11.62a2 2 0 001.94-2.5L19 10"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Practical driving logs</div>
            </div>
        </div>

        <div class="stat-card active">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Hours</div>
                        <div class="stat-value">{{ number_format((float) ($stats['total_hours'] ?? 0), 1) }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Combined hours for current scope</div>
            </div>
        </div>
    </div>

    <form class="filters" id="adminSessionsFilterForm" method="GET" action="{{ route('schools.admin.sessions.index', ['school' => $school->slug]) }}" onsubmit="applyAdminSessionFormFilters(event)">
        <input type="hidden" id="session_type" name="session_type" value="{{ $activeSessionType }}">

        <div>
            <label for="instructor_id">Instructor</label>
            <select id="instructor_id" name="instructor_id" onchange="applyAdminSessionFormFilters()">
                <option value="">All Instructors</option>
                @foreach($instructors as $instructor)
                    <option value="{{ $instructor->id }}" {{ (string) request('instructor_id') === (string) $instructor->id ? 'selected' : '' }}>
                        {{ $instructor->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="date_from">Date From</label>
            <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}">
        </div>

        <div>
            <label for="date_to">Date To</label>
            <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}">
        </div>

        <div style="display:flex; gap:8px; align-items:end;">
            <button type="submit" class="btn btn-apply">Apply</button>
            <button type="button" class="btn btn-clear" onclick="clearAdminSessionFilters()">Clear</button>
        </div>
    </form>

    <div class="table-card">
        <div class="table-top">
            <span>Total records: {{ $sessions->total() }}</span>
            <div class="search-inline">
                <input type="text" id="adminSessionsSearch" placeholder="Search student, instructor, course, or type...">
            </div>
        </div>

        <table id="adminSessionsTable">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Instructor</th>
                    <th>Type</th>
                    <th>Hours</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr>
                        <td>{{ optional($session->session_date)->format('M d, Y') ?? 'N/A' }}</td>
                        <td>{{ $session->session_time ? \Carbon\Carbon::parse($session->session_time)->format('h:i A') : 'N/A' }}</td>
                        <td>{{ $session->enrollment->student->name ?? $session->enrollment->learner->name ?? 'N/A' }}</td>
                        <td>{{ $session->enrollment->course->title ?? 'N/A' }}</td>
                        <td>{{ $session->instructor->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $session->session_type === 'theoretical' ? 'badge-theoretical' : 'badge-practical' }}">
                                {{ $session->session_type ?? 'N/A' }}
                            </span>
                        </td>
                        <td>{{ number_format((float) ($session->hours_completed ?? 0), 1) }}</td>
                        <td>{{ ucfirst($session->status ?? 'completed') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty">No session completions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($sessions->hasPages())
            <div class="pagination-wrap">
                {{ $sessions->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<script>
var adminSessionsBaseUrl = @json(route('schools.admin.sessions.index', ['school' => $school->slug]));

function getAdminSessionFilterState() {
    const params = new URLSearchParams(window.location.search);
    const sessionType = document.getElementById('session_type');
    const instructor = document.getElementById('instructor_id');
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');

    return {
        session_type: sessionType ? (sessionType.value || '') : (params.get('session_type') || ''),
        instructor_id: instructor ? (instructor.value || '') : (params.get('instructor_id') || ''),
        date_from: dateFrom ? (dateFrom.value || '') : (params.get('date_from') || ''),
        date_to: dateTo ? (dateTo.value || '') : (params.get('date_to') || ''),
    };
}

function buildAdminSessionsUrl(filters = {}, resetPage = true) {
    const merged = Object.assign({}, getAdminSessionFilterState(), filters || {});
    const url = new URL(adminSessionsBaseUrl || window.location.pathname, window.location.origin);

    if (merged.session_type) {
        url.searchParams.set('session_type', merged.session_type);
    }

    if (merged.instructor_id) {
        url.searchParams.set('instructor_id', merged.instructor_id);
    }

    if (merged.date_from) {
        url.searchParams.set('date_from', merged.date_from);
    }

    if (merged.date_to) {
        url.searchParams.set('date_to', merged.date_to);
    }

    if (!resetPage) {
        const currentPage = new URLSearchParams(window.location.search).get('page');
        if (currentPage) {
            url.searchParams.set('page', currentPage);
        }
    }

    return url;
}

function navigateAdminSessions(filters = {}, resetPage = true) {
    const targetUrl = buildAdminSessionsUrl(filters, resetPage);
    const target = targetUrl.pathname + targetUrl.search;

    if (typeof loadContent === 'function') {
        loadContent(target);
        return;
    }

    window.location.href = target;
}

function applySessionTypeFilter(type) {
    const sessionType = document.getElementById('session_type');
    if (sessionType) {
        sessionType.value = type || '';
    }

    navigateAdminSessions({ session_type: type || '' }, true);
}

function applyAdminSessionFormFilters(event) {
    if (event) {
        event.preventDefault();
    }

    const instructor = document.getElementById('instructor_id');
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');

    navigateAdminSessions({
        instructor_id: instructor ? (instructor.value || '') : '',
        date_from: dateFrom ? (dateFrom.value || '') : '',
        date_to: dateTo ? (dateTo.value || '') : '',
    }, true);

    return false;
}

function clearAdminSessionFilters() {
    const sessionType = document.getElementById('session_type');
    const instructor = document.getElementById('instructor_id');
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    const search = document.getElementById('adminSessionsSearch');

    if (sessionType) sessionType.value = '';
    if (instructor) instructor.value = '';
    if (dateFrom) dateFrom.value = '';
    if (dateTo) dateTo.value = '';
    if (search) search.value = '';

    navigateAdminSessions({ session_type: '', instructor_id: '', date_from: '', date_to: '' }, true);
}

function applyLocalSessionsSearch(rawValue) {
    const table = document.getElementById('adminSessionsTable');
    if (!table) {
        return;
    }

    const tbody = table.querySelector('tbody');
    if (!tbody) {
        return;
    }

    const query = (rawValue || '').trim().toLowerCase();
    const rows = Array.from(tbody.querySelectorAll('tr')).filter(function (row) {
        return row.id !== 'adminSessionsNoResultRow';
    });
    const colCount = table.querySelectorAll('thead th').length || 8;

    let visibleCount = 0;
    rows.forEach(function (row) {
        const text = (row.textContent || '').toLowerCase();
        const visible = query === '' || text.indexOf(query) !== -1;
        row.style.display = visible ? '' : 'none';
        if (visible) {
            visibleCount++;
        }
    });

    let noResultRow = document.getElementById('adminSessionsNoResultRow');
    if (visibleCount === 0 && rows.length > 0) {
        if (!noResultRow) {
            noResultRow = document.createElement('tr');
            noResultRow.id = 'adminSessionsNoResultRow';
            noResultRow.innerHTML = '<td colspan="' + colCount + '" class="empty">No session matches your search on this page.</td>';
            tbody.appendChild(noResultRow);
        }
    } else if (noResultRow) {
        noResultRow.remove();
    }
}

function bindAdminSessionsSearch() {
    const search = document.getElementById('adminSessionsSearch');
    if (!search || search.dataset.searchBound === '1') {
        return;
    }

    search.dataset.searchBound = '1';
    search.addEventListener('input', function (event) {
        applyLocalSessionsSearch(event.target.value || '');
    });

    search.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        applyLocalSessionsSearch(event.target.value || '');
    });
}

function initializeAdminSessionsPage() {
    bindAdminSessionsSearch();

    const search = document.getElementById('adminSessionsSearch');
    if (search) {
        applyLocalSessionsSearch(search.value || '');
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAdminSessionsPage);
} else {
    initializeAdminSessionsPage();
}
</script>
@endsection
