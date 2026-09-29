@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Grade Management')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    /* â”€â”€ Controls Bar â”€â”€ */
    .controls-bar {
        background: white;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-input {
        flex: 1;
        min-width: 220px;
        padding: 10px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.2s;
    }

    .search-input:focus { border-color: {{ $primaryColor }}; }

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

    .action-buttons { display: flex; gap: 8px; }

    /* â”€â”€ Export Dropdown â”€â”€ */
    .export-dropdown { position: relative; display: inline-block; }

    .btn-export {
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

    .btn-export:hover { background: #059669; }

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

    .btn-save-all {
        padding: 10px 16px;
        background: {{ $primaryColor }};
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

    .btn-save-all:hover { background: {{ $secondaryColor }}; transform: translateY(-1px); }

    /* â”€â”€ Grades Table â”€â”€ */
    .grades-table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .grades-table {
        width: 100%;
        border-collapse: collapse;
    }

    .grades-table thead {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .grades-table th {
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        font-size: 0.78rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }

    .grades-table th.sortable {
        cursor: pointer;
        user-select: none;
    }

    .grades-table th.sortable:hover { background: #f3f4f6; }

    .grades-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.88rem;
    }

    .grades-table tbody tr:hover { background: #fafbfc; }

    .student-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .student-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: {{ $primaryColor }};
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.88rem;
        flex-shrink: 0;
    }

    .student-info { flex: 1; }
    .student-name { font-weight: 600; color: #1f2937; display: block; margin-bottom: 1px; }
    .student-email { font-size: 0.75rem; color: #6b7280; }

    .grade-input {
        width: 76px;
        padding: 7px 10px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-size: 0.88rem;
        font-weight: 600;
        text-align: center;
        outline: none;
        transition: all 0.2s;
    }

    .grade-input:focus { border-color: {{ $primaryColor }}; }
    .grade-input.changed { border-color: #f59e0b; background: #fffbeb; }
    .comment-input {
        width: 220px;
        text-align: left;
        font-weight: 500;
    }

    .grade-badge {
        padding: 4px 10px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.78rem;
        display: inline-block;
    }

    .grade-excellent { background: #d1fae5; color: #065f46; }
    .grade-good { background: #dbeafe; color: #1e40af; }
    .grade-average { background: #fef3c7; color: #92400e; }
    .grade-poor { background: #fee2e2; color: #991b1b; }
    .grade-none { background: #f3f4f6; color: #6b7280; }

    .sessions-badge {
        background: #e5e7eb;
        color: #374151;
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .action-cell { display: flex; gap: 6px; }

    .btn-icon {
        padding: 6px 10px;
        border: none;
        background: transparent;
        cursor: pointer;
        border-radius: 6px;
        transition: all 0.2s;
        font-size: 0.82rem;
        font-weight: 500;
    }

    .btn-view { color: {{ $primaryColor }}; }
    .btn-view:hover { background: {{ $primaryColor }}15; }
    .btn-save { color: #10b981; }
    .btn-save:hover { background: #d1fae5; }

    .icon-16 {
        width: 16px;
        height: 16px;
    }

    .icon-18 {
        width: 18px;
        height: 18px;
    }

    .icon-28 {
        width: 28px;
        height: 28px;
    }

    .text-muted {
        color: #9ca3af;
    }

    .empty-state {
        text-align: center;
        padding: 48px 20px;
        color: #9ca3af;
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
        .controls-bar { padding: 12px; gap: 8px; }
        .search-input { min-width: 150px; padding: 8px 10px; font-size: 0.82rem; }
        .filter-select { padding: 8px 10px; font-size: 0.78rem; }
        .grades-table-container { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .grades-table { min-width: 700px; }
        .btn-icon { min-height: 44px; min-width: 44px; padding: 8px 12px; }
        .grade-input { min-height: 44px; padding: 8px; }
        .table-footer { flex-direction: column; align-items: stretch; }
    }
    
    @media (max-width: 480px) {
        .controls-bar { flex-direction: column; }
        .search-input { min-width: 100%; }
        .filter-select { width: 100%; }
    }
</style>

<div class="admin-container">
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Grade Management</h1>
            <p class="page-subtitle">Review and manage student grades</p>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="stats-grid">
        <div class="stat-card students">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <span class="stat-label">Total Students</span>
                        <span class="stat-value">{{ $students->count() }}</span>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-28"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card active">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <span class="stat-label">Graded Sessions</span>
                        <span class="stat-value">{{ $gradedSessions }}</span>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-28"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card growth">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <span class="stat-label">Average Grade</span>
                        <span class="stat-value">{{ number_format($averageGrade, 1) }}</span>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-28"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card inactive">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <span class="stat-label">Pending Grades</span>
                        <span class="stat-value">{{ $pendingGrades }}</span>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-28"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Controls Bar -->
    <div class="controls-bar">
        <input type="text" class="search-input" id="searchInput" 
               placeholder="Search students by name or email..." onkeyup="filterTable()">
        
        <select class="filter-select" id="sortFilter" onchange="applySortFilter()">
            <option value="name-asc">Name (A-Z)</option>
            <option value="name-desc">Name (Z-A)</option>
            <option value="enrollment-newest">Enrollment (Newest)</option>
            <option value="enrollment-oldest">Enrollment (Oldest)</option>
            <option value="sessions-most">Most Sessions</option>
            <option value="sessions-least">Least Sessions</option>
            <option value="grade-highest">Highest Grade</option>
            <option value="grade-lowest">Lowest Grade</option>
        </select>

        <div class="action-buttons">
            <div class="export-dropdown">
                <button class="btn-export" onclick="toggleExportMenu()">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export
                </button>
                <div class="export-menu" id="exportMenu">
                    <a href="{{ route('schools.instructor.exports.grades.pdf', $school) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#ef4444" class="icon-18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Download PDF
                    </a>
                    <a href="{{ route('schools.instructor.exports.grades.excel', $school) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#10b981" class="icon-18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Download Excel
                    </a>
                </div>
            </div>
            <button class="btn-save-all" onclick="saveAllChanges()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save All
            </button>
        </div>
    </div>

    <!-- Grades Table -->
    <div class="grades-table-container">
        @if($students->count() > 0)
            <table class="grades-table" id="gradesTable">
                <thead>
                    <tr>
                        <th class="sortable" onclick="sortTable('name')">Student Name</th>
                        <th class="sortable" onclick="sortTable('sessions')">Sessions</th>
                        <th class="sortable" onclick="sortTable('avg')">Avg Grade</th>
                        <th>Last Session</th>
                        <th>Last Grade</th>
                        <th>Quick Grade</th>
                        <th>Quick Comment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="gradesTableBody">
                    @foreach($students as $student)
                        @php
                            $completedSessions = $student->bookings->where('status', 'completed')->count();
                            $avgGrade = $student->bookings->whereNotNull('session_grade')->avg('session_grade');
                            $lastCompletedSession = $student->bookings->where('status', 'completed')->sortByDesc('scheduled_at')->first();
                            $enrollmentTimestamp = $student->enrollment_date ? strtotime($student->enrollment_date) : 0;
                            $gradeCategory = $avgGrade >= 90 ? 'excellent' : ($avgGrade >= 75 ? 'good' : ($avgGrade >= 60 ? 'average' : ($avgGrade ? 'poor' : 'none')));
                        @endphp
                        <tr class="grade-row" 
                            data-name="{{ strtolower($student->name) }}"
                            data-email="{{ strtolower($student->email) }}"
                            data-enrollment="{{ $enrollmentTimestamp }}"
                            data-sessions="{{ $completedSessions }}"
                            data-avg-grade="{{ $avgGrade ?? 0 }}">
                            <td>
                                <div class="student-cell">
                                    <div class="student-avatar">
                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                    </div>
                                    <div class="student-info">
                                        <span class="student-name">{{ $student->name }}</span>
                                        <span class="student-email">{{ $student->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="sessions-badge">{{ $completedSessions }} sessions</span>
                            </td>
                            <td>
                                @if($avgGrade)
                                    <span class="grade-badge grade-{{ $gradeCategory }}">
                                        {{ number_format($avgGrade, 1) }}
                                    </span>
                                @else
                                    <span class="grade-badge grade-none">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($lastCompletedSession && ($lastCompletedSession->scheduled_at || $lastCompletedSession->booking_date))
                                    {{ ($lastCompletedSession->scheduled_at ?? $lastCompletedSession->booking_date)?->format('M d, Y') ?? 'N/A' }}
                                @else
                                    <span style="color: #9ca3af;">No completed sessions</span>
                                @endif
                            </td>
                            <td>
                                @if($lastCompletedSession && $lastCompletedSession->session_grade)
                                    <strong>{{ $lastCompletedSession->session_grade }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <input type="number" 
                                       class="grade-input" 
                                       min="0" 
                                       max="100" 
                                       step="0.1"
                                       data-student-id="{{ $student->id }}"
                                       data-last-booking-id="{{ $lastCompletedSession ? $lastCompletedSession->id : '' }}"
                                       placeholder="0-100"
                                       onchange="markChanged(this)">
                            </td>
                            <td>
                                <input type="text"
                                       class="grade-input comment-input"
                                       data-comment-student-id="{{ $student->id }}"
                                       data-last-booking-id="{{ $lastCompletedSession ? $lastCompletedSession->id : '' }}"
                                       value="{{ $lastCompletedSession->instructor_feedback ?? '' }}"
                                       placeholder="Leave comment"
                                       onchange="markCommentChanged(this)">
                            </td>
                            <td>
                                <div class="action-cell">
                                    <button class="btn-icon btn-view" 
                                            onclick="viewStudentDetails({{ $student->id }})"
                                            title="View Details">
                                        View
                                    </button>
                                    <button class="btn-icon btn-save" 
                                            onclick="saveGrade({{ $student->id }})"
                                            title="Save Grade">
                                        Save Grade
                                    </button>
                                    <button class="btn-icon btn-save"
                                            onclick="saveComment({{ $student->id }})"
                                            title="Save Comment">
                                        Save Comment
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    @if($students->hasPages())
        <div class="table-footer">
            <div class="pagination-info">
                Showing {{ $students->firstItem() }} to {{ $students->lastItem() }} of {{ $students->total() }} students
            </div>
            {{ $students->links() }}
        </div>
    @endif
</div>

<script>
    function filterTable() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.grade-row');
        rows.forEach(row => {
            const name = row.dataset.name;
            const email = row.dataset.email;
            row.style.display = (name.includes(searchInput) || email.includes(searchInput)) ? '' : 'none';
        });
    }

    function applySortFilter() {
        const sortOption = document.getElementById('sortFilter').value;
        const table = document.getElementById('gradesTableBody');
        const rows = Array.from(table.querySelectorAll('.grade-row'));
        rows.sort((a, b) => {
            let aVal, bVal;
            switch(sortOption) {
                case 'name-asc': return a.dataset.name.localeCompare(b.dataset.name);
                case 'name-desc': return b.dataset.name.localeCompare(a.dataset.name);
                case 'enrollment-newest': return (parseInt(b.dataset.enrollment)||0) - (parseInt(a.dataset.enrollment)||0);
                case 'enrollment-oldest': return (parseInt(a.dataset.enrollment)||0) - (parseInt(b.dataset.enrollment)||0);
                case 'sessions-most': return (parseInt(b.dataset.sessions)||0) - (parseInt(a.dataset.sessions)||0);
                case 'sessions-least': return (parseInt(a.dataset.sessions)||0) - (parseInt(b.dataset.sessions)||0);
                case 'grade-highest': return (parseFloat(b.dataset.avgGrade)||0) - (parseFloat(a.dataset.avgGrade)||0);
                case 'grade-lowest': return (parseFloat(a.dataset.avgGrade)||0) - (parseFloat(b.dataset.avgGrade)||0);
                default: return 0;
            }
        });
        rows.forEach(row => table.appendChild(row));
    }

    function markChanged(input) { input.classList.add('changed'); }
    function markCommentChanged(input) { input.classList.add('changed'); }

    let sortDirection = {};
    function sortTable(column) {
        const table = document.getElementById('gradesTableBody');
        const rows = Array.from(table.querySelectorAll('.grade-row'));
        sortDirection[column] = sortDirection[column] === 'asc' ? 'desc' : 'asc';
        rows.sort((a, b) => {
            let aVal, bVal;
            if (column === 'name') { aVal = a.dataset.name; bVal = b.dataset.name; }
            else if (column === 'sessions') { aVal = parseInt(a.querySelectorAll('.sessions-badge')[0].textContent)||0; bVal = parseInt(b.querySelectorAll('.sessions-badge')[0].textContent)||0; }
            else if (column === 'avg') { aVal = parseFloat(a.querySelectorAll('.grade-badge')[0].textContent)||0; bVal = parseFloat(b.querySelectorAll('.grade-badge')[0].textContent)||0; }
            return sortDirection[column] === 'asc' ? (aVal > bVal ? 1 : -1) : (aVal < bVal ? 1 : -1);
        });
        rows.forEach(row => table.appendChild(row));
    }

    function viewStudentDetails(studentId) {
        loadContent('/{{ $school->slug }}/instructor/students/' + studentId);
    }

    function saveGrade(studentId) {
        const input = document.querySelector(`.grade-input[data-student-id="${studentId}"]`);
        const grade = parseFloat(input.value);
        const bookingId = input.dataset.lastBookingId;
        if (Number.isNaN(grade) || grade < 0 || grade > 100) { showToast('Please enter a valid grade between 0 and 100', 'error'); return; }
        if (!bookingId) { showToast('This student has no completed sessions to grade', 'warning'); return; }
        fetch(`/{{ $school->slug }}/instructor/lessons/${bookingId}/update`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ session_grade: grade })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) { input.classList.remove('changed'); showToast('Grade saved successfully!', 'success'); setTimeout(() => location.reload(), 1000); }
            else { showToast('Failed to save grade', 'error'); }
        })
        .catch(error => { console.error('Error:', error); showToast('An error occurred while saving the grade', 'error'); });
    }

    function saveComment(studentId) {
        const input = document.querySelector(`.comment-input[data-comment-student-id="${studentId}"]`);
        const bookingId = input.dataset.lastBookingId;

        if (!bookingId) { showToast('This student has no completed sessions for comments', 'warning'); return; }

        fetch(`/{{ $school->slug }}/instructor/bookings/${bookingId}/feedback`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ instructor_feedback: input.value })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                input.classList.remove('changed');
                showToast('Comment saved successfully!', 'success');
            } else {
                showToast(data.message || 'Failed to save comment', 'error');
            }
        })
        .catch(error => { console.error('Error:', error); showToast('An error occurred while saving the comment', 'error'); });
    }

    function saveAllChanges() {
        const changedGradeInputs = document.querySelectorAll('.grade-input[data-student-id].changed');
        const changedCommentInputs = document.querySelectorAll('.comment-input.changed');
        if ((changedGradeInputs.length + changedCommentInputs.length) === 0) {
            showToast('No changes to save', 'warning');
            return;
        }

        const requests = [];

        changedGradeInputs.forEach(input => {
            const grade = parseFloat(input.value);
            const bookingId = input.dataset.lastBookingId;

            if (Number.isNaN(grade) || grade < 0 || grade > 100 || !bookingId) {
                return;
            }

            requests.push(
                fetch(`/{{ $school->slug }}/instructor/lessons/${bookingId}/update`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ session_grade: grade })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        input.classList.remove('changed');
                        return true;
                    }
                    return false;
                })
                .catch(() => false)
            );
        });

        changedCommentInputs.forEach(input => {
            const bookingId = input.dataset.lastBookingId;

            if (!bookingId) {
                return;
            }

            requests.push(
                fetch(`/{{ $school->slug }}/instructor/bookings/${bookingId}/feedback`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ instructor_feedback: input.value })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        input.classList.remove('changed');
                        return true;
                    }
                    return false;
                })
                .catch(() => false)
            );
        });

        if (requests.length === 0) {
            showToast('No valid changes to save', 'warning');
            return;
        }

        Promise.all(requests).then(results => {
            const savedCount = results.filter(Boolean).length;

            if (savedCount > 0) {
                showToast(`Successfully saved ${savedCount} update(s)!`, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('No updates were saved', 'error');
            }
        });
    }

    function exportGrades() {
        const rows = document.querySelectorAll('.grade-row:not([style*="display: none"])');
        let csv = 'Student Name,Email,Sessions,Average Grade,Last Session,Last Grade\n';
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            csv += `"${cells[0].querySelector('.student-name').textContent}","${cells[0].querySelector('.student-email').textContent}","${cells[1].textContent.trim()}","${cells[2].textContent.trim()}","${cells[3].textContent.trim()}","${cells[4].textContent.trim()}"\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a'); a.href = url;
        a.download = `grades_${new Date().toISOString().split('T')[0]}.csv`;
        a.click(); window.URL.revokeObjectURL(url);
    }

    function toggleExportMenu() {
        event.stopPropagation();
        document.getElementById('exportMenu').classList.toggle('show');
    }
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.export-dropdown')) {
            document.querySelectorAll('.export-menu').forEach(m => m.classList.remove('show'));
        }
    });
</script>

@endsection
