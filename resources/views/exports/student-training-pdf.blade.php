<!doctype html>
<html><head><meta charset="utf-8"><title>Student Training Record</title>
<style>
body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#182433}h1{font-size:20px}h2{font-size:15px;margin-top:24px}p{line-height:1.6}table{width:100%;border-collapse:collapse}th,td{padding:8px;border:1px solid #cbd5e1;text-align:left;vertical-align:top}th{background:#f1f5f9}thead{display:table-header-group}tr{page-break-inside:avoid}
</style></head><body>
<h1>Student Training Record</h1>
<p>{{ $school->name }}<br><strong>{{ $enrollment->learner?->name }}</strong><br>{{ $enrollment->learner?->email }}</p>
<p>Enrollment: {{ $enrollment->uid }}<br>Course: {{ $enrollment->course?->title }}<br>Package: {{ $enrollment->package?->name ?? 'Standard' }}<br>Status: {{ ucfirst($enrollment->status) }}</p>
<table><thead><tr><th>Training phase</th><th>Required hours</th><th>Completed hours</th></tr></thead><tbody>
<tr><td>TDC</td><td>{{ $enrollment->tdc_hours_limit }}</td><td>{{ $enrollment->used_tdc_hours }}</td></tr>
<tr><td>PDC</td><td>{{ $enrollment->pdc_hours_limit }}</td><td>{{ $enrollment->used_pdc_hours }}</td></tr>
</tbody></table>
<h2>Training Sessions</h2>
<table><thead><tr><th>Date</th><th>Phase</th><th>Instructor</th><th>Hours</th><th>Status</th><th>Notes</th></tr></thead><tbody>
@forelse($enrollment->sessionCompletions->sortBy('session_date') as $session)
<tr><td>{{ $session->session_date?->format('M d, Y') }}</td><td>{{ ucfirst($session->session_type) }}</td><td>{{ $session->instructor?->name ?? 'Unassigned' }}</td><td>{{ $session->hours_completed }}</td><td>{{ ucfirst($session->status) }}</td><td>{{ $session->notes }}</td></tr>
@empty<tr><td colspan="6">No training sessions recorded.</td></tr>@endforelse
</tbody></table>
</body></html>
