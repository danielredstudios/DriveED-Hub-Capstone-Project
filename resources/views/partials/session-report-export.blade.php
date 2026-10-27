@php
    $reportRole = explode('.', request()->route()->getName())[1] ?? 'admin';

    $statusOptions = [
        'cancelled' => ['label' => 'Cancelled', 'color' => '#dc2626', 'background' => '#fef2f2'],
        'completed' => ['label' => 'Completed', 'color' => '#059669', 'background' => '#ecfdf5'],
        'incoming' => ['label' => 'Incoming', 'color' => '#2563eb', 'background' => '#eff6ff'],
        'no_show' => ['label' => 'No-show', 'color' => '#b45309', 'background' => '#fffbeb'],
    ];

    $requestedStatuses = request()->has('statuses')
        ? (array) request('statuses')
        : array_keys($statusOptions);
    $selectedStatuses = array_values(array_intersect(array_keys($statusOptions), $requestedStatuses));
    if ($selectedStatuses === []) {
        $selectedStatuses = array_keys($statusOptions);
    }

    $studentQuery = \App\Models\Student::where('school_id', $school->id);
    $instructorQuery = \App\Models\Instructor::where('school_id', $school->id);
    $admin = null;

    if ($reportRole === 'admin') {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if ($admin && $admin->isBranchSecretary() && $admin->branch_id) {
            $studentQuery->where('branch_id', $admin->branch_id);
            $instructorQuery->where('branch_id', $admin->branch_id);
        }
    } elseif ($reportRole === 'instructor') {
        $instructor = \Illuminate\Support\Facades\Auth::guard('instructor')->user();
        if ($instructor) {
            // Instructors only see students they have taught; the export
            // controller also hard-scopes every row to this instructor.
            $studentQuery->whereHas('bookings', fn ($query) => $query->where('instructor_id', $instructor->id));
        }
    }

    $studentsList = $studentQuery->orderBy('name')->get();
    $instructorsList = $instructorQuery->orderBy('name')->get();

    $scopeNote = match ($reportRole) {
        'instructor' => 'Your export is limited to sessions assigned to you.',
        'student' => 'Your export is limited to your own scheduled sessions.',
        default => $admin && $admin->isBranchSecretary() && $admin->branch_id
            ? 'Your export is limited to your branch. Choose a student or instructor to narrow it further.'
            : 'Export all records, or narrow them to one student or instructor.',
    };
@endphp

<style>
    .schedule-log-export {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .schedule-log-export__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 16px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f3f4f6;
    }

    .schedule-log-export__title-wrap {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .schedule-log-export__icon {
        width: 38px;
        height: 38px;
        border-radius: 9px;
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex: 0 0 auto;
    }

    .schedule-log-export__title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: #1f2937;
    }

    .schedule-log-export__subtitle {
        margin: 2px 0 0;
        font-size: 0.82rem;
        color: #6b7280;
    }

    .schedule-log-export__badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 9px;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .schedule-log-export__form {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .schedule-log-export__fields {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 14px;
        align-items: end;
    }

    .schedule-log-export__field {
        min-width: 0;
    }

    .schedule-log-export__label {
        display: block;
        margin-bottom: 5px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #4b5563;
    }

    .schedule-log-export__control {
        width: 100%;
        height: 40px;
        padding: 0 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.875rem;
        color: #1f2937;
        background-color: #ffffff;
    }

    .schedule-log-export__control:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .schedule-log-export__status-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        padding: 12px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #f8fafc;
    }

    .schedule-log-export__status-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #4b5563;
        white-space: nowrap;
    }

    .schedule-log-export__status-options {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .schedule-log-export__status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 11px;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        background: #ffffff;
        color: #334155;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        user-select: none;
        transition: border-color 0.15s ease, background 0.15s ease;
    }

    .schedule-log-export__status:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .schedule-log-export__status input {
        width: 14px;
        height: 14px;
        accent-color: #2563eb;
        cursor: pointer;
    }

    .schedule-log-export__dot {
        width: 7px;
        height: 7px;
        border-radius: 999px;
        display: inline-block;
    }

    .schedule-log-export__footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .schedule-log-export__scope {
        margin: 0;
        font-size: 0.76rem;
        color: #64748b;
        line-height: 1.45;
    }

    .schedule-log-export__actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-left: auto;
    }

    .schedule-log-export__button {
        height: 40px;
        padding: 0 17px;
        border: none;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: filter 0.15s ease, transform 0.15s ease;
    }

    .schedule-log-export__button:hover {
        filter: brightness(0.96);
    }

    .schedule-log-export__button:active {
        transform: translateY(1px);
    }

    .schedule-log-export__button--pdf {
        background: #4b5563;
        color: #ffffff;
    }

    .schedule-log-export__button--excel {
        background: #10b981;
        color: #ffffff;
    }

    @media (max-width: 720px) {
        .schedule-log-export {
            padding: 16px;
        }

        .schedule-log-export__fields {
            grid-template-columns: 1fr;
        }

        .schedule-log-export__actions {
            width: 100%;
            margin-left: 0;
        }

        .schedule-log-export__button {
            flex: 1 1 150px;
        }
    }
</style>

<div class="schedule-log-export">
    <div class="schedule-log-export__header">
        <div class="schedule-log-export__title-wrap">
            <div class="schedule-log-export__icon">
                <i class="bi bi-journal-text"></i>
            </div>
            <div>
                <h4 class="schedule-log-export__title">Schedule Logs</h4>
                <p class="schedule-log-export__subtitle">Export scheduled, completed, cancelled, and upcoming sessions with your chosen filters.</p>
            </div>
        </div>
        <span class="schedule-log-export__badge">
            <i class="bi bi-file-earmark-arrow-down"></i> PDF &amp; Excel
        </span>
    </div>

    <form id="scheduleLogsExportForm" class="schedule-log-export__form" method="GET"
          action="{{ route('schools.'.$reportRole.'.session-report', ['school' => $school->slug, 'format' => 'pdf']) }}"
          data-no-ajax="1">
        <div class="schedule-log-export__fields">
            @if($reportRole === 'admin')
                <div class="schedule-log-export__field">
                    <label class="schedule-log-export__label" for="sle_student_id">
                        <i class="bi bi-person"></i> Student
                    </label>
                    <select id="sle_student_id" name="student_id" class="schedule-log-export__control">
                        <option value="">All students</option>
                        @foreach($studentsList as $studentOption)
                            <option value="{{ $studentOption->id }}" {{ (string) request('student_id') === (string) $studentOption->id ? 'selected' : '' }}>
                                {{ $studentOption->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="schedule-log-export__field">
                    <label class="schedule-log-export__label" for="sle_instructor_id">
                        <i class="bi bi-person-badge"></i> Instructor
                    </label>
                    <select id="sle_instructor_id" name="instructor_id" class="schedule-log-export__control">
                        <option value="">All instructors</option>
                        @foreach($instructorsList as $instructorOption)
                            <option value="{{ $instructorOption->id }}" {{ (string) request('instructor_id') === (string) $instructorOption->id ? 'selected' : '' }}>
                                {{ $instructorOption->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @elseif($reportRole === 'instructor')
                <div class="schedule-log-export__field">
                    <label class="schedule-log-export__label" for="sle_student_id">
                        <i class="bi bi-person"></i> Student
                    </label>
                    <select id="sle_student_id" name="student_id" class="schedule-log-export__control">
                        <option value="">All my students</option>
                        @foreach($studentsList as $studentOption)
                            <option value="{{ $studentOption->id }}" {{ (string) request('student_id') === (string) $studentOption->id ? 'selected' : '' }}>
                                {{ $studentOption->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="schedule-log-export__field">
                <label class="schedule-log-export__label" for="sle_start_date">
                    <i class="bi bi-calendar3"></i> From date
                </label>
                <input id="sle_start_date" class="schedule-log-export__control" type="date" name="start" value="{{ request('start') }}">
            </div>

            <div class="schedule-log-export__field">
                <label class="schedule-log-export__label" for="sle_end_date">
                    <i class="bi bi-calendar3"></i> To date
                </label>
                <input id="sle_end_date" class="schedule-log-export__control" type="date" name="end" value="{{ request('end') }}">
            </div>
        </div>

        <div class="schedule-log-export__status-row">
            <span class="schedule-log-export__status-label" id="sle_status_label">Status</span>
            <div class="schedule-log-export__status-options" role="group" aria-labelledby="sle_status_label">
                @foreach($statusOptions as $statusKey => $status)
                    <label class="schedule-log-export__status">
                        <input type="checkbox" name="statuses[]" value="{{ $statusKey }}"
                               {{ in_array($statusKey, $selectedStatuses, true) ? 'checked' : '' }}>
                        <span class="schedule-log-export__dot" style="background: {{ $status['color'] }};"></span>
                        <span>{{ $status['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="schedule-log-export__footer">
            <p class="schedule-log-export__scope">
                <i class="bi bi-shield-lock"></i> {{ $scopeNote }}
            </p>
            <div class="schedule-log-export__actions">
                <button type="submit"
                        formaction="{{ route('schools.'.$reportRole.'.session-report', ['school' => $school->slug, 'format' => 'pdf']) }}"
                        class="schedule-log-export__button schedule-log-export__button--pdf">
                    <i class="bi bi-file-earmark-pdf-fill" style="color:#fca5a5;"></i> Export PDF
                </button>
                <button type="submit"
                        formaction="{{ route('schools.'.$reportRole.'.session-report', ['school' => $school->slug, 'format' => 'xlsx']) }}"
                        class="schedule-log-export__button schedule-log-export__button--excel">
                    <i class="bi bi-file-earmark-excel-fill" style="color:#d1fae5;"></i> Export Excel
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    (function () {
        if (window.__scheduleLogsExportGuard) {
            return;
        }
        window.__scheduleLogsExportGuard = true;

        document.addEventListener('submit', function (event) {
            var form = event.target;
            if (!form || form.id !== 'scheduleLogsExportForm') {
                return;
            }
            if (!form.querySelector('input[name="statuses[]"]:checked')) {
                event.preventDefault();
                event.stopImmediatePropagation();
                window.alert('Choose at least one status to export.');
            }
        }, true);
    })();
</script>
