<!doctype html><html><head><meta charset="utf-8"><title>Student Training List</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:10px;color:#182433}h1{font-size:20px}table{width:100%;border-collapse:collapse}th,td{padding:8px;border:1px solid #cbd5e1;text-align:left}th{background:#f1f5f9}thead{display:table-header-group}tr{page-break-inside:avoid}</style>
</head><body><h1>Student Training List</h1><p>{{ $school->name }}</p>
<table><thead><tr><th>Enrollment</th><th>Student</th><th>Course</th><th>Status</th><th>TDC hours</th><th>PDC hours</th></tr></thead><tbody>
@forelse($enrollments as $enrollment)
<tr><td>{{ $enrollment->uid }}</td><td>{{ $enrollment->learner?->name }}</td><td>{{ $enrollment->course?->title }}</td><td>{{ ucfirst($enrollment->status) }}</td><td>{{ $enrollment->used_tdc_hours }} / {{ $enrollment->tdc_hours_limit }}</td><td>{{ $enrollment->used_pdc_hours }} / {{ $enrollment->pdc_hours_limit }}</td></tr>
@empty<tr><td colspan="6">No training enrollments found.</td></tr>@endforelse
</tbody></table></body></html>
