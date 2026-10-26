@php
    $reportRole = explode('.', request()->route()->getName())[1] ?? 'admin';
    $studentsList = $students ?? \App\Models\Student::where('school_id', $school->id)->orderBy('name')->get();
    $instructorsList = $instructors ?? \App\Models\Instructor::where('school_id', $school->id)->orderBy('name')->get();
@endphp
<div class="session-report-export-card" style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:20px; margin-bottom:25px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px; border-bottom:1px solid #f3f4f6; padding-bottom:12px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div style="width:36px; height:36px; border-radius:8px; background:rgba(37,99,235,0.1); color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:1.1rem;">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div>
                <h4 style="margin:0; font-size:1.05rem; font-weight:700; color:#1f2937;">Training & Session Records</h4>
                <p style="margin:2px 0 0 0; font-size:0.82rem; color:#6b7280;">Download comprehensive session history, grades, instructor notes, and student progress</p>
            </div>
        </div>
        <div style="font-size:0.75rem; color:#9ca3af; font-weight:500;">
            <i class="bi bi-info-circle"></i> Filter by Student, Instructor, and Date Range
        </div>
    </div>

    <form method="GET" action="{{ route('schools.'.$reportRole.'.session-report', ['school' => $school->slug, 'format' => 'pdf']) }}" data-no-ajax="1" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:14px;">
        @if($reportRole === 'admin' || $reportRole === 'instructor')
            <div style="flex:1 1 180px; min-width:180px;">
                <label for="export_student_id" style="display:block; font-size:0.82rem; font-weight:600; color:#4b5563; margin-bottom:5px;">
                    <i class="bi bi-person"></i> Student
                </label>
                <select id="export_student_id" name="student_id" style="width:100%; height:40px; padding:0 12px; border:1px solid #d1d5db; border-radius:8px; font-size:0.875rem; color:#1f2937; background-color:#fff;">
                    <option value="">All Students</option>
                    @foreach($studentsList as $st)
                        <option value="{{ $st->id }}" {{ (string) request('student_id') === (string) $st->id ? 'selected' : '' }}>
                            {{ $st->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if($reportRole === 'admin')
            <div style="flex:1 1 180px; min-width:180px;">
                <label for="export_instructor_id" style="display:block; font-size:0.82rem; font-weight:600; color:#4b5563; margin-bottom:5px;">
                    <i class="bi bi-person-badge"></i> Instructor
                </label>
                <select id="export_instructor_id" name="instructor_id" style="width:100%; height:40px; padding:0 12px; border:1px solid #d1d5db; border-radius:8px; font-size:0.875rem; color:#1f2937; background-color:#fff;">
                    <option value="">All Instructors</option>
                    @foreach($instructorsList as $inst)
                        <option value="{{ $inst->id }}" {{ (string) request('instructor_id') === (string) $inst->id ? 'selected' : '' }}>
                            {{ $inst->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div style="flex:1 1 140px; min-width:140px;">
            <label for="export_start_date" style="display:block; font-size:0.82rem; font-weight:600; color:#4b5563; margin-bottom:5px;">
                <i class="bi bi-calendar3"></i> From Date
            </label>
            <input id="export_start_date" aria-label="Report start date" type="date" name="start" value="{{ request('start') }}" style="width:100%; height:40px; padding:0 10px; border:1px solid #d1d5db; border-radius:8px; font-size:0.875rem; color:#1f2937; background-color:#fff;">
        </div>

        <div style="flex:1 1 140px; min-width:140px;">
            <label for="export_end_date" style="display:block; font-size:0.82rem; font-weight:600; color:#4b5563; margin-bottom:5px;">
                <i class="bi bi-calendar3"></i> To Date
            </label>
            <input id="export_end_date" aria-label="Report end date" type="date" name="end" value="{{ request('end') }}" style="width:100%; height:40px; padding:0 10px; border:1px solid #d1d5db; border-radius:8px; font-size:0.875rem; color:#1f2937; background-color:#fff;">
        </div>

        <div style="display:flex; gap:10px; align-items:center; margin-left:auto; flex-wrap:wrap;">
            <button type="submit" formaction="{{ route('schools.'.$reportRole.'.session-report', ['school' => $school->slug, 'format' => 'pdf']) }}" class="btn btn-secondary" style="height:40px; padding:0 18px; font-size:0.875rem; font-weight:600; border-radius:8px; display:inline-flex; align-items:center; gap:8px; background:#4b5563; color:#fff; border:none; cursor:pointer; box-shadow:0 1px 2px rgba(0,0,0,0.05); transition:background 0.2s;">
                <i class="bi bi-file-earmark-pdf-fill" style="color:#f87171; font-size:1rem;"></i> Download PDF
            </button>
            <button type="submit" formaction="{{ route('schools.'.$reportRole.'.session-report', ['school' => $school->slug, 'format' => 'xlsx']) }}" class="btn btn-success" style="height:40px; padding:0 18px; font-size:0.875rem; font-weight:600; border-radius:8px; display:inline-flex; align-items:center; gap:8px; background:#10b981; color:#fff; border:none; cursor:pointer; box-shadow:0 1px 2px rgba(0,0,0,0.05); transition:background 0.2s;">
                <i class="bi bi-file-earmark-excel-fill" style="color:#d1fae5; font-size:1rem;"></i> Download Excel
            </button>
        </div>
    </form>
</div>
