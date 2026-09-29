@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Phase Progressions')

@section('content')
    @php
        $school = $school ?? $currentSchool ?? null;
        $settings = $school?->schoolSetting;
        $primaryColor = $settings?->primary_color ?? '#667eea';
        $secondaryColor = $settings?->secondary_color ?? '#764ba2';
        $activeFromPhaseFilter = request('from_phase', '');
    @endphp

    @include('school.admin.partials.admin-styles')

    <style>
        .phase-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .phase-label-theoretical {
            background: #dbeafe;
            color: #1e40af;
        }

        .phase-label-practical {
            background: #fef3c7;
            color: #92400e;
        }

        .phase-label-completed {
            background: #d1fae5;
            color: #065f46;
        }

        /* Lighter Status Badges to match Phase Labels */
        .badge-approved { background: #d1fae5; color: #065f46; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }

        .transition-arrow {
            color: #9ca3af;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
        }

        .review-notes {
            font-size: 0.85rem;
            color: #6b7280;
            margin-top: 4px;
            font-style: italic;
        }

        .icon-24 {
            width: 24px;
            height: 24px;
        }

        .content-card-mb {
            margin-bottom: 20px;
        }

        .form-control-auto {
            width: auto;
        }

        .content-card-body-no-padding {
            padding: 0;
        }

        .table-overflow-wrap {
            overflow-x: auto;
        }

        .student-name-strong {
            font-weight: 600;
        }

        .student-email-muted {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .transition-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .reviewer-name {
            font-weight: 500;
        }

        .reviewer-date {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        .pending-review-text {
            color: #9ca3af;
            font-size: 0.85rem;
        }

        .pagination-wrap {
            padding: 20px;
        }

        .modal-student-info {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
        }

        .modal-section-label {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .modal-student-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 12px;
        }

        .modal-transition-label {
            font-weight: 600;
            color:
                {{ $primaryColor }}
            ;
        }

        .admin-container {
            padding: 20px;
            margin: 0 auto;
            max-width: 1400px;
        }

        .modal-action-row {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .modal-action-row .btn {
            flex: 1;
        }

        .table-top {
            padding: 14px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            background: #fff;
        }

        .table-top-meta {
            font-size: 0.9rem;
            color: #6b7280;
            font-weight: 500;
        }

        .filter-group .table-top-meta {
            display: inline-flex;
            align-items: center;
            min-height: 40px;
            padding: 0 4px;
        }

        .table-top-search {
            min-width: 260px;
            max-width: 420px;
            width: 100%;
        }

        .table-top-search input {
            width: 100%;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            padding: 9px 10px;
            font-size: 0.88rem;
        }

        .table-top-search input:focus {
            outline: none;
            border-color:
                {{ $primaryColor }}
            ;
        }

        .card-filter {
            cursor: pointer;
        }

        .card-filter-active {
            border-color:
                {{ $primaryColor }}
                !important;
            box-shadow: 0 0 0 2px
                {{ $primaryColor }}
                30;
        }

        /* Keep the filter white bar visually stable (no hover lift/shadow change). */
        .content-card.content-card-mb:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
        }

        .phase-progression-cards .stat-card.info {
            order: 1;
        }

        .phase-progression-cards .stat-card.pending {
            order: 2;
        }

        .phase-progression-cards .stat-card.success {
            order: 3;
        }

        .phase-progression-cards .stat-card.students {
            order: 4;
        }

        .phase-progression-cards .stat-card.growth {
            order: 5;
        }

        .phase-filters-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: nowrap;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #e5e7eb;
        }

        .phase-filters-row .table-top-search {
            flex: 1 1 360px;
            min-width: 260px;
            max-width: none;
            width: auto;
        }

        .phase-filters-row .form-control-auto {
            min-width: 170px;
        }

        .phase-filters-row .table-top-meta {
            margin-left: auto;
            white-space: nowrap;
        }

        .phase-filters-row .btn {
            white-space: nowrap;
        }

        .admin-table thead {
            background: linear-gradient(135deg,
                    {{ $primaryColor }}
                    0%,
                    {{ $secondaryColor }}
                    100%);
            color: #ffffff;
        }

        .admin-table th {
            background: transparent;
            color: inherit;
            padding: 15px;
            text-transform: none;
            letter-spacing: 0;
            font-size: 0.95rem;
        }

        .admin-table thead th:first-child {
            border-top-left-radius: 12px;
        }

        .admin-table thead th:last-child {
            border-top-right-radius: 12px;
        }

        @media (max-width: 992px) {
            .phase-filters-row {
                flex-wrap: wrap;
            }

            .phase-filters-row .table-top-search {
                flex: 1 1 100%;
                max-width: 100%;
            }

            .phase-filters-row .table-top-meta {
                margin-left: 0;
            }
        }
    </style>

    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-left">
                <h1 class="page-title">
                    Phase Progressions
                </h1>
                <p class="page-subtitle">Monitor and approve student transitions between training phases (e.g., Theoretical
                    to Practical). Ensure all curriculum prerequisites and assessments are verified before granting
                    progression to the next learning stage.</p>
            </div>
        </div>

        <!-- Stats Summary -->
        <div class="stats-grid phase-progression-cards">
            <div class="stat-card info card-filter {{ $activeFromPhaseFilter === '' ? 'card-filter-active' : '' }}"
                onclick="applyPhaseCardFilter('')">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Total Records</div>
                            <div class="stat-value">{{ $phaseStats['total_records'] ?? 0 }}</div>
                        </div>
                        <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="#3b82f6" class="icon-24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg></div>
                    </div>
                    <div class="stat-detail">All records for current status scope</div>
                </div>
            </div>

            <div class="stat-card pending">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Pending Reviews</div>
                            <div class="stat-value">{{ $pendingCount }}</div>
                        </div>
                        <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="#f59e0b" class="icon-24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg></div>
                    </div>
                    <div class="stat-detail">Requests awaiting decision</div>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Approved</div>
                            <div class="stat-value">{{ $phaseStats['approved_records'] ?? 0 }}</div>
                        </div>
                        <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="#10b981" class="icon-24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg></div>
                    </div>
                    <div class="stat-detail">Lifetime approved transitions</div>
                </div>
            </div>

            <div class="stat-card students card-filter {{ $activeFromPhaseFilter === 'theoretical' ? 'card-filter-active' : '' }}"
                onclick="applyPhaseCardFilter('theoretical')">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Theoretical</div>
                            <div class="stat-value">{{ $phaseStats['theoretical_records'] ?? 0 }}</div>
                        </div>
                        <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" class="icon-24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 14l6.16-3.422A12.083 12.083 0 0118 14.5C18 16.985 15.314 19 12 19s-6-2.015-6-4.5c0-1.386.688-2.63 1.84-3.422L12 14z" />
                            </svg></div>
                    </div>
                    <div class="stat-detail">Requests from theoretical phase</div>
                </div>
            </div>

            <div class="stat-card growth card-filter {{ $activeFromPhaseFilter === 'practical' ? 'card-filter-active' : '' }}"
                onclick="applyPhaseCardFilter('practical')">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Practical</div>
                            <div class="stat-value">{{ $phaseStats['practical_records'] ?? 0 }}</div>
                        </div>
                        <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" class="icon-24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17h6m-6 0a2 2 0 11-4 0m4 0a2 2 0 104 0m0 0a2 2 0 104 0m-4 0H9m0-8l1.6-3.2A2 2 0 0112.4 5h3.2a2 2 0 011.79 1.11L19 10m-14 0h14M5 10l-.75 3a2 2 0 001.94 2.5h11.62a2 2 0 001.94-2.5L19 10" />
                            </svg></div>
                    </div>
                    <div class="stat-detail">Requests from practical phase</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="content-card-mb">
            <form id="filterForm" action="{{ school_route('admin.phase-progressions.index') }}" method="GET"
                class="filter-group phase-filters-row" onsubmit="return applyPhaseProgressionFilters(event)">
                <div class="table-top-search">
                    <input type="text" id="phaseProgressionServerSearch" name="search"
                        placeholder="Search student, course, phase..." value="{{ request('search') }}"
                        oninput="debounceServerSearch(this.value)">
                </div>

                <select id="phaseProgressionStatusFilter" name="status" class="form-control form-control-auto"
                    onchange="return applyPhaseProgressionFilters()">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                <div class="table-top-meta">Filtered records: {{ $progressions->total() }}</div>
            </form>
        </div>



        <!-- Requests Table -->
        <div class="content-card">
            <div class="content-card-body content-card-body-no-padding">
                <div class="table-overflow-wrap">
                    <table class="admin-table" id="phaseProgressionsTable">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Transition</th>
                                <th>Requested At</th>
                                <th>Status</th>
                                <th>Reviewed By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($progressions as $request)
                                <tr>
                                    <td>
                                        <a href="{{ school_route('admin.theoretical.show', ['enrollment' => $request->enrollment_id]) }}"
                                            class="student-name-link" style="text-decoration: none; display: block;">
                                            <div class="student-name-strong"
                                                style="color: {{ $primaryColor }}; font-weight: 700;">
                                                {{ $request->enrollment->student->name }}</div>
                                            <div class="student-email-muted" style="color: #6b7280; font-size: 0.8rem;">
                                                {{ $request->enrollment->student->email }}</div>
                                        </a>
                                    </td>
                                    <td>{{ $request->enrollment->course->name }}</td>
                                    <td>
                                        <div class="transition-row">
                                            <span
                                                class="phase-badge phase-label-{{ $request->from_phase }}">{{ ucfirst($request->from_phase) }}</span>
                                            <span class="transition-arrow">→</span>
                                            <span
                                                class="phase-badge phase-label-{{ $request->to_phase }}">{{ ucfirst($request->to_phase) }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $request->requested_at->format('M d, Y h:i A') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $request->status }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($request->reviewedBy)
                                            <div class="reviewer-info">
                                                <div class="reviewer-name" style="font-weight: 600; color: #374151;">{{ $request->reviewedBy->name }}</div>
                                                <div class="reviewer-date" style="font-size: 0.75rem; color: #9ca3af;">{{ $request->reviewed_at->format('M d, Y') }}</div>
                                            </div>
                                            @if($request->admin_notes)
                                                <div class="review-notes-container" style="margin-top: 8px;">
                                                    <div class="review-notes" title="{{ $request->admin_notes }}" style="background: #f9fafb; border-radius: 6px; padding: 6px 10px; border-left: 3px solid #d1d5db; font-size: 0.8rem; color: #4b5563; line-height: 1.4;">
                                                        <span style="display: block; font-size: 0.7rem; text-transform: uppercase; font-weight: 700; color: #9ca3af; margin-bottom: 2px;">Note:</span>
                                                        {{ Str::limit($request->admin_notes, 60) }}
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <span class="pending-review-text">Pending Review</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($request->isPending())
                                            <button class="btn btn-primary btn-sm"
                                                onclick="openReviewModal({{ $request->id }}, '{{ $request->enrollment->student->name }}', '{{ $request->getTransitionLabel() }}')">
                                                Review
                                            </button>
                                        @else
                                            <div style="color: #9ca3af; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 14px; height: 14px;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Reviewed
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"></div>
                                            <div class="empty-state-title">No progression requests found</div>
                                            <div class="empty-state-text">Phase transitions will appear here when students
                                                complete their current requirements.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($progressions->hasPages())
                    <div class="pagination-wrap">
                        {{ $progressions->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div id="reviewModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Review Phase Progression</h3>
                <button class="modal-close" onclick="closeReviewModal()">×</button>
            </div>
            <div class="modal-body">
                <div id="modalStudentInfo" class="modal-student-info">
                    <div class="modal-section-label">Student</div>
                    <div id="modalStudentName" class="modal-student-name"></div>

                    <div class="modal-section-label">Transition</div>
                    <div id="modalTransitionLabel" class="modal-transition-label"></div>
                    <form id="reviewForm" onsubmit="submitReview(this); return false;">
                        @csrf
                        <div class="form-group">
                            <label class="modal-section-label">Admin Notes (Required for rejection)</label>
                            <textarea name="admin_notes" class="form-control" rows="3"
                                placeholder="Provide details or reason for the decision..."></textarea>
                        </div>

                        <div class="modal-action-row">
                            <button type="button" class="btn btn-success" onclick="setDecision('approve')">
                                Approve Progression
                            </button>
                            <button type="button" class="btn btn-danger" onclick="setDecision('reject')">
                                Reject Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            const phaseProgressionsBaseUrl = @json(school_route('admin.phase-progressions.index'));
            const initialPhaseProgressionFromPhase = @json(request('from_phase', ''));

            function getPhaseProgressionFilterState() {
                const params = new URLSearchParams(window.location.search);
                const statusFilter = document.getElementById('phaseProgressionStatusFilter');
                const searchInput = document.getElementById('phaseProgressionServerSearch');

                return {
                    status: statusFilter ? (statusFilter.value || '') : (params.get('status') || ''),
                    from_phase: params.get('from_phase') || initialPhaseProgressionFromPhase || '',
                    search: searchInput ? (searchInput.value || '') : (params.get('search') || ''),
                };
            }

            function buildPhaseProgressionUrl(filters = {}, resetPage = true) {
                const merged = Object.assign({}, getPhaseProgressionFilterState(), filters || {});
                const url = new URL(phaseProgressionsBaseUrl, window.location.origin);

                if (merged.status) {
                    url.searchParams.set('status', merged.status);
                }

                if (merged.from_phase) {
                    url.searchParams.set('from_phase', merged.from_phase);
                }

                if (merged.search) {
                    url.searchParams.set('search', merged.search);
                }

                if (!resetPage) {
                    const currentPage = new URLSearchParams(window.location.search).get('page');
                    if (currentPage) {
                        url.searchParams.set('page', currentPage);
                    }
                }

                return url;
            }

            function navigateWithPhaseProgressionFilters(nextFilters, resetPage = true) {
                const targetUrl = buildPhaseProgressionUrl(nextFilters, resetPage);
                const target = targetUrl.pathname + targetUrl.search;

                if (typeof loadContent === 'function') {
                    loadContent(target);
                    return;
                }

                window.location.href = target;
            }

            function applyPhaseProgressionFilters(event) {
                if (event) {
                    event.preventDefault();
                }

                const statusFilter = document.getElementById('phaseProgressionStatusFilter');

                navigateWithPhaseProgressionFilters({
                    status: statusFilter ? (statusFilter.value || '') : '',
                }, true);

                return false;
            }

            function applyPhaseCardFilter(fromPhase) {
                navigateWithPhaseProgressionFilters({ from_phase: fromPhase || '' }, true);
            }

            function applyLocalPhaseProgressionSearch(rawValue) {
                const table = document.getElementById('phaseProgressionsTable');
                if (!table) {
                    return;
                }

                const tbody = table.querySelector('tbody');
                if (!tbody) {
                    return;
                }

                const query = (rawValue || '').trim().toLowerCase();
                const rows = Array.from(tbody.querySelectorAll('tr')).filter(function (row) {
                    return row.id !== 'phaseProgressionSearchNoResultRow';
                });
                const colCount = table.querySelectorAll('thead th').length || 7;

                let visibleCount = 0;
                rows.forEach(function (row) {
                    const rowText = (row.textContent || '').toLowerCase();
                    const isVisible = query === '' || rowText.indexOf(query) !== -1;
                    row.style.display = isVisible ? '' : 'none';
                    if (isVisible) {
                        visibleCount++;
                    }
                });

                let noResultRow = document.getElementById('phaseProgressionSearchNoResultRow');
                if (visibleCount === 0 && rows.length > 0) {
                    if (!noResultRow) {
                        noResultRow = document.createElement('tr');
                        noResultRow.id = 'phaseProgressionSearchNoResultRow';
                        noResultRow.innerHTML = '<td colspan="' + colCount + '"><div class="empty-state"><div class="empty-state-title">No progression request matches your search on this page.</div></div></td>';
                        tbody.appendChild(noResultRow);
                    }
                } else if (noResultRow) {
                    noResultRow.remove();
                }
            }

            let searchTimeout = null;
            function debounceServerSearch(value) {
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }
                searchTimeout = setTimeout(() => {
                    navigateWithPhaseProgressionFilters({ search: value }, true);
                }, 500);
            }

            function initializePhaseProgressionPage() {
                // Intercept pagination links for AJAX loading
                const paginationLinks = document.querySelectorAll('.pagination-wrap a, .admin-pagination-wrapper a');
                paginationLinks.forEach(link => {
                    link.addEventListener('click', function (e) {
                        e.preventDefault();
                        const url = this.getAttribute('href');
                        if (typeof loadContent === 'function') {
                            loadContent(url);
                        } else {
                            window.location.href = url;
                        }
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializePhaseProgressionPage);
            } else {
                initializePhaseProgressionPage();
            }

            let currentRequestId = null;

            function openReviewModal(id, name, transition) {
                currentRequestId = id;
                document.getElementById('modalStudentName').textContent = name;
                document.getElementById('modalTransitionLabel').textContent = transition;
                document.getElementById('reviewModal').classList.add('active');
            }

            function closeReviewModal() {
                document.getElementById('reviewModal').classList.remove('active');
                document.getElementById('reviewForm').reset();
            }

            function setDecision(decision) {
                const form = document.getElementById('reviewForm');
                const slug = '{{ $school->slug }}';

                if (decision === 'reject') {
                    const notes = form.querySelector('[name="admin_notes"]').value.trim();
                    if (!notes) {
                        if (typeof Toast !== 'undefined') {
                            Toast.warning('Please provide a reason for rejection in the notes field.', 'Input Required');
                        } else {
                            alert('Please provide a reason for rejection in the notes field.');
                        }
                        return;
                    }
                }

                form.action = `/${slug}/admin/phase-progressions/${currentRequestId}/${decision}`;
                form.dispatchEvent(new Event('submit'));
            }

            function submitReview(form) {
                const formData = new FormData(form);
                const url = form.action;

                // Show loading state
                const buttons = form.querySelectorAll('.btn');
                buttons.forEach(btn => btn.disabled = true);

                fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                    .then(response => {
                        closeReviewModal();
                        // Since the system uses AJAX navigation (loadContent), we refresh the current view
                        loadContent(window.location.href);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (typeof Toast !== 'undefined') {
                            Toast.error('An error occurred while processing the request.', 'Error');
                        } else {
                            alert('An error occurred while processing the request.');
                        }
                        buttons.forEach(btn => btn.disabled = false);
                    });
            }

            function loadWithFilters(form) {
                applyPhaseProgressionFilters();
            }
        </script>

@endsection