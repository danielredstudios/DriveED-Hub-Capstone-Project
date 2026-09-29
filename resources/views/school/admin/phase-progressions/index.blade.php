@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Phase Progressions')

@section('content')
    @php
        $school = $school ?? $currentSchool ?? null;
        $settings = $school?->schoolSetting;
        $primaryColor = $settings?->primary_color ?? '#667eea';
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
            border-bottom: 3px solid
                {{ $primaryColor }}
            ;
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

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .stat {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            padding: 14px;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.82rem;
        }

        .stat-value {
            color: #111827;
            font-weight: 700;
            font-size: 1.3rem;
            margin-top: 4px;
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

        .btn {
            border: none;
            border-radius: 8px;
            padding: 9px 12px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.82rem;
        }

        .btn-apply {
            background:
                {{ $primaryColor }}
            ;
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

        .btn-approve {
            background: #16a34a;
            color: #fff;
        }

        .btn-reject {
            background: #dc2626;
            color: #fff;
        }

        .table-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
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
            vertical-align: top;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .actions input {
            width: 180px;
            padding: 7px 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.82rem;
        }

        .empty {
            text-align: center;
            padding: 24px;
            color: #6b7280;
        }

        .pagination-wrap {
            padding: 12px 16px;
        }
    </style>

    <div class="page-wrap">
        <div class="page-header">
            <div>
                <h1 class="page-title">Phase Progressions</h1>
                <div class="subtitle">Review student progression requests between learning phases</div>
            </div>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="stat-label">Pending Requests</div>
                <div class="stat-value">{{ $pendingCount }}</div>
            </div>
            <div class="stat">
                <div class="stat-label">Total Results</div>
                <div class="stat-value">{{ $progressions->total() }}</div>
            </div>
        </div>

        <form class="filters" method="GET"
            action="{{ route('schools.admin.phase-progressions.index', ['school' => $school->slug]) }}">
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div>
                <label for="from_phase">From Phase</label>
                <select id="from_phase" name="from_phase">
                    <option value="">All Phases</option>
                    <option value="theoretical" {{ request('from_phase') === 'theoretical' ? 'selected' : '' }}>Theoretical
                    </option>
                    <option value="practical" {{ request('from_phase') === 'practical' ? 'selected' : '' }}>Practical</option>
                </select>
            </div>

            <div style="display:flex; gap:8px; align-items:end;">
                <button type="submit" class="btn btn-apply">Apply</button>
                <a class="btn btn-clear"
                    href="{{ route('schools.admin.phase-progressions.index', ['school' => $school->slug]) }}">Clear</a>
            </div>
        </form>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Transition</th>
                        <th>Requested</th>
                        <th>Status</th>
                        <th>Reviewed By</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($progressions as $progression)
                        <tr>
                            <td>{{ $progression->enrollment->student->name ?? $progression->enrollment->learner->name ?? 'N/A' }}
                            </td>
                            <td>{{ $progression->enrollment->course->title ?? 'N/A' }}</td>
                            <td>{{ ucfirst($progression->from_phase) }} → {{ ucfirst($progression->to_phase) }}</td>
                            <td>{{ optional($progression->requested_at)->format('M d, Y h:i A') ?? 'N/A' }}</td>
                            <td>
                                <span
                                    class="status-badge status-{{ $progression->status }}">{{ ucfirst($progression->status) }}</span>
                            </td>
                            <td>{{ $progression->reviewedBy->name ?? '—' }}</td>
                            <td>{{ $progression->admin_notes ?? '—' }}</td>
                            <td>
                                @if($progression->status === 'pending')
                                    <div class="actions">
                                        <form method="POST"
                                            action="{{ route('schools.admin.phase-progressions.approve', ['school' => $school->slug, 'phaseProgression' => $progression->id]) }}">
                                            @csrf
                                            <input type="hidden" name="admin_notes" value="Approved by admin">
                                            <button type="submit" class="btn btn-approve">Approve</button>
                                        </form>

                                        <form method="POST"
                                            action="{{ route('schools.admin.phase-progressions.reject', ['school' => $school->slug, 'phaseProgression' => $progression->id]) }}"
                                            style="display:flex; gap:8px;">
                                            @csrf
                                            <input type="text" name="admin_notes" placeholder="Reason required" required>
                                            <button type="submit" class="btn btn-reject">Reject</button>
                                        </form>
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty">No phase progression requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($progressions->hasPages())
                <div class="pagination-wrap">
                    {{ $progressions->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection