@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Students')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $instructorId = Auth::guard('instructor')->id();
    
    // Calculate statistics
    $totalStudents = $statsTotalStudents ?? $students->total();
    $activeStudents = $statsActiveStudents ?? $students->where('status', 'active')->count();
    $inactiveStudents = $statsInactiveStudents ?? $students->where('status', 'inactive')->count();
    $upcomingStudents = $statsUpcomingStudents ?? $students->filter(function ($student) {
        return !empty($student->next_session);
    })->count();
    $activeCardFilter = $activeCardFilter ?? request('card', 'all');
    $filterLabelMap = [
        'all' => 'All Students',
        'active' => 'Active Students',
        'inactive' => 'Inactive Students',
        'upcoming' => 'With Upcoming Session',
    ];
    $currentFilterLabel = $filterLabelMap[$activeCardFilter] ?? 'All Students';
    $schoolRoute = $schoolRoute ?? (fn(string $name, array $params = []) => school_route($name, $params, $school));
@endphp

@include('school.admin.partials.admin-styles')

<style>
    /* ── Controls Bar ── */
    .controls-bar {
        background: white;
        padding: 16px 20px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        flex-wrap: wrap;
    }

    .search-wrapper {
        flex: 1;
        min-width: 220px;
    }

    .search-input {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.2s;
    }

    .search-input:focus { border-color: {{ $primaryColor }}; }

    .stat-card.card-filter {
        cursor: pointer;
    }

    .stat-card.card-filter-active {
        box-shadow: 0 0 0 2px {{ $primaryColor }}40;
        border-color: {{ $primaryColor }} !important;
        transform: translateY(-1px);
    }

    .filter-select {
        padding: 10px 32px 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: white;
        font-size: 0.875rem;
        cursor: pointer;
        outline: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        transition: border-color 0.2s;
    }

    .filter-select:focus { border-color: {{ $primaryColor }}; }

    .view-count {
        color: #6b7280;
        font-size: 0.85rem;
        font-weight: 500;
    }

    /* ── Students Grid ── */
    .students-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 16px;
        margin-bottom: 30px;
    }

    .student-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid #f3f4f6;
    }

    .student-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-color: {{ $primaryColor }};
    }

    .student-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .student-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: {{ $primaryColor }};
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .student-info { flex: 1; min-width: 0; }

    .student-name {
        font-size: 1.05rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 2px;
    }

    .student-detail {
        font-size: 0.82rem;
        color: #6b7280;
        margin: 1px 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .student-detail strong { color: #374151; font-weight: 600; }

    .student-grade {
        font-size: 0.95rem;
        color: {{ $primaryColor }};
        font-weight: 700;
        margin-top: 4px;
    }

    .assignment-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-top: 4px;
    }

    .badge-assigned { background: #dbeafe; color: #1e40af; }
    .badge-unassigned { background: #f3f4f6; color: #6b7280; }

    .no-students {
        grid-column: 1 / -1;
        text-align: center;
        padding: 48px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }

    .no-students-icon { font-size: 3rem; color: #d1d5db; margin-bottom: 12px; }
    .no-students-text { font-size: 1.1rem; color: #6b7280; font-weight: 500; }

    /* ── Export Dropdown ── */
    .export-dropdown { position: relative; display: inline-block; }

    .btn-export-trigger {
        padding: 10px 16px;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-export-trigger:hover { background: #059669; }

    .export-menu {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        margin-top: 4px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        min-width: 180px;
        z-index: 100;
        overflow: hidden;
    }

    .export-menu.show { display: block; }

    .export-menu a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        color: #374151;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: background 0.15s;
    }

    .export-menu a:hover { background: #f3f4f6; }

    .icon-18 {
        width: 18px;
        height: 18px;
    }

    .icon-24 {
        width: 24px;
        height: 24px;
    }

    .table-footer {
        margin-top: 16px;
        padding: 14px 20px;
        border-top: 1px solid #f3f4f6;
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .pagination-info {
        font-size: 0.82rem;
        color: #6b7280;
    }

    @media (max-width: 768px) {
        .students-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .student-card { padding: 12px; }
        .student-card-header { flex-direction: column; text-align: center; gap: 8px; }
        .student-avatar { width: 42px; height: 42px; font-size: 1rem; }
        .student-info { text-align: center; }
        .student-name { font-size: 0.88rem; }
        .student-detail { font-size: 0.72rem; display: none; }
        .student-detail:first-of-type { display: block; }
        .student-grade { font-size: 0.8rem; }
        .assignment-badge { font-size: 0.6rem; }
        .controls-bar { padding: 12px; gap: 8px; }
        .search-wrapper { min-width: 150px; }
        .search-input { padding: 8px 10px; font-size: 0.82rem; }
        .filter-select { padding: 8px 10px; font-size: 0.78rem; }
        .table-footer { flex-direction: column; align-items: stretch; }
    }

    @media (max-width: 480px) {
        .students-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
        .student-card { padding: 10px; }
        .student-avatar { width: 38px; height: 38px; font-size: 0.9rem; }
        .student-name { font-size: 0.82rem; }
    }
</style>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">My Students</h1>
            <p class="page-subtitle">View and manage your assigned students</p>
        </div>
        <div class="export-dropdown">
            <button class="btn-export-trigger" onclick="toggleExportMenu()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export
            </button>
            <div class="export-menu" id="exportMenu">
                <a href="{{ route('schools.instructor.exports.students.pdf', $school) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#ef4444" class="icon-18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Download PDF
                </a>
                <a href="{{ route('schools.instructor.exports.students.excel', $school) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#10b981" class="icon-18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download Excel
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card students card-filter {{ $activeCardFilter === 'all' ? 'card-filter-active' : '' }}" onclick="applyStudentsCardFilter('all')">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">All Students</div>
                        <div class="stat-value">{{ $totalStudents }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">All students in your instructor roster</div>
            </div>
        </div>
        <div class="stat-card instructors card-filter {{ $activeCardFilter === 'active' ? 'card-filter-active' : '' }}" onclick="applyStudentsCardFilter('active')">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Active Students</div>
                        <div class="stat-value">{{ $activeStudents }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Active accounts in your roster</div>
            </div>
        </div>
        <div class="stat-card active card-filter {{ $activeCardFilter === 'inactive' ? 'card-filter-active' : '' }}" onclick="applyStudentsCardFilter('inactive')">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Inactive Students</div>
                        <div class="stat-value">{{ $inactiveStudents }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-1.414 1.414A7.963 7.963 0 0119 12a8 8 0 11-8-8c1.657 0 3.196.504 4.471 1.364l1.414-1.414A9.96 9.96 0 0011 2a10 10 0 1010 10c0-2.761-1.12-5.261-2.929-7.071zM10 8a1 1 0 012 0v3a1 1 0 01-.293.707l-2 2a1 1 0 11-1.414-1.414L10 10.586V8z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Learners needing re-engagement</div>
            </div>
        </div>
        <div class="stat-card growth card-filter {{ $activeCardFilter === 'upcoming' ? 'card-filter-active' : '' }}" onclick="applyStudentsCardFilter('upcoming')">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Upcoming Sessions</div>
                        <div class="stat-value">{{ $upcomingStudents }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Students with scheduled upcoming lessons</div>
            </div>
        </div>
    </div>

    <!-- Controls Bar -->
    <form action="{{ $schoolRoute('instructor.students.index') }}" method="GET" class="controls-bar" id="studentsControlsForm">
        <div class="search-wrapper">
            <input type="text" name="search" class="search-input" id="searchInput" 
                   placeholder="Search students by name or email..." value="{{ request('search') }}">
        </div>
        <div class="view-count">
            @if($students->count() > 0)
                {{ $currentFilterLabel }}: {{ $students->count() }} on this page, {{ $students->total() }} total
            @else
                No students found for {{ strtolower($currentFilterLabel) }}
            @endif
        </div>
    </form>

    <!-- Students Grid -->
    <div class="students-grid" id="studentsGrid">
        @forelse($students as $student)
            <div class="student-card" 
                 data-name="{{ strtolower($student->name) }}"
                 data-email="{{ strtolower($student->email) }}"
                 data-status="{{ $student->status }}"
                 data-progress="{{ $student->avg_progress ?? 0 }}"
                 data-sessions="{{ $student->sessions_count }}"
                 data-assigned="{{ $student->is_assigned ? 'true' : 'false' }}"
                 onclick="window.location.href='{{ $schoolRoute('instructor.students.show', ['id' => $student->id]) }}'">
                <div class="student-card-header">
                    <div class="student-avatar">
                        {{ strtoupper(substr($student->name, 0, 1)) }}
                    </div>
                    <div class="student-info">
                        <div class="student-name">{{ $student->name }}</div>
                        <div class="student-detail"><strong>Email:</strong> {{ $student->email }}</div>
                        <div class="student-detail"><strong>Contact:</strong> {{ $student->contact ?? 'N/A' }}</div>
                        @if($student->avg_progress)
                            <div class="student-grade"><strong>Grade:</strong> {{ number_format($student->avg_progress, 1) }}%</div>
                        @else
                            <div class="student-grade"><strong>Grade:</strong> -</div>
                        @endif
                        <div class="assignment-badge {{ $student->is_assigned ? 'badge-assigned' : 'badge-unassigned' }}">
                            {{ $student->is_assigned ? 'My Student' : 'Other' }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="no-students">
                <div class="no-students-icon"></div>
                <div class="no-students-text">No students found</div>
            </div>
        @endforelse

    </div>

    @if($students->hasPages())
        <div class="table-footer">
            <div class="pagination-info">
                @if($students->count() > 0)
                    Showing {{ $students->firstItem() }}-{{ $students->lastItem() }} of {{ $students->total() }} ({{ $currentFilterLabel }})
                @else
                    No students to display
                @endif
            </div>
            {{ $students->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<script>
var instructorStudentsBaseUrl = @json($schoolRoute('instructor.students.index'));

function getInstructorStudentsFilterState() {
    const params = new URLSearchParams(window.location.search);
    const searchInput = document.getElementById('searchInput');

    return {
        search: searchInput ? (searchInput.value || '') : (params.get('search') || ''),
        card: params.get('card') || @json($activeCardFilter),
    };
}

function buildInstructorStudentsUrl(filters = {}, resetPage = true) {
    const merged = Object.assign({}, getInstructorStudentsFilterState(), filters || {});
    const url = new URL(instructorStudentsBaseUrl || window.location.pathname, window.location.origin);

    const search = (merged.search || '').trim();
    if (search) {
        url.searchParams.set('search', search);
    }

    if (merged.card && merged.card !== 'all') {
        url.searchParams.set('card', merged.card);
    }

    if (!resetPage) {
        const currentPage = new URLSearchParams(window.location.search).get('page');
        if (currentPage) {
            url.searchParams.set('page', currentPage);
        }
    }

    return url;
}

function navigateWithInstructorStudentsFilters(filters = {}, resetPage = true) {
    const targetUrl = buildInstructorStudentsUrl(filters, resetPage);
    const target = targetUrl.pathname + targetUrl.search;

    if (typeof loadContent === 'function') {
        loadContent(target);
        return;
    }

    window.location.href = target;
}

function applyStudentsCardFilter(card) {
    navigateWithInstructorStudentsFilters({ card: card || 'all' }, true);
}

function applyLocalStudentsSearch(rawValue) {
    const studentsGrid = document.getElementById('studentsGrid');
    if (!studentsGrid) {
        return;
    }

    const query = (rawValue || '').trim().toLowerCase();
    const cards = Array.from(studentsGrid.querySelectorAll('.student-card'));
    let visibleCount = 0;

    cards.forEach(function(card) {
        const name = (card.dataset.name || '').toLowerCase();
        const email = (card.dataset.email || '').toLowerCase();
        const show = query === '' || name.indexOf(query) !== -1 || email.indexOf(query) !== -1;
        card.style.display = show ? '' : 'none';
        if (show) {
            visibleCount++;
        }
    });

    let noResults = document.getElementById('studentsSearchNoResults');
    if (visibleCount === 0 && cards.length > 0) {
        if (!noResults) {
            noResults = document.createElement('div');
            noResults.id = 'studentsSearchNoResults';
            noResults.className = 'no-students';
            noResults.innerHTML = '<div class="no-students-text">No students match your search on this page.</div>';
            studentsGrid.appendChild(noResults);
        }
    } else if (noResults) {
        noResults.remove();
    }
}

function bindInstructorStudentsSearchEvents() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput || searchInput.dataset.searchBound === '1') {
        return;
    }

    searchInput.dataset.searchBound = '1';

    searchInput.addEventListener('input', function(event) {
        applyLocalStudentsSearch(event.target.value || '');
    });

    searchInput.addEventListener('keydown', function(event) {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        applyLocalStudentsSearch(event.target.value || '');
    });
}

function initializeInstructorStudentsPage() {
    bindInstructorStudentsSearchEvents();

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        applyLocalStudentsSearch(searchInput.value || '');
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeInstructorStudentsPage);
} else {
    initializeInstructorStudentsPage();
}

// Toggle Export Menu
function toggleExportMenu() {
    document.getElementById('exportMenu').classList.toggle('show');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.export-dropdown')) {
        var menu = document.getElementById('exportMenu');
        if (menu) menu.classList.remove('show');
    }
});
</script>

@endsection
