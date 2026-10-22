@php
    $reportRole = explode('.', request()->route()->getName())[1];
@endphp
<form method="GET" data-no-ajax="1" style="display:flex; flex-wrap:wrap; align-items:end; gap:12px; padding:16px; margin-bottom:20px; background:#fff; border:1px solid #d8e0e7; border-radius:10px;">
    <div style="flex:1 1 180px;"><strong>Training & Session Records</strong><br><small>Download lesson history, grades, notes, and instructor feedback</small></div>
    <label style="font-size:.85rem;">From<br><input aria-label="Report start date" type="date" name="start" style="padding:7px; border:1px solid #9ca3af; border-radius:5px;"></label>
    <label style="font-size:.85rem;">To<br><input aria-label="Report end date" type="date" name="end" style="padding:7px; border:1px solid #9ca3af; border-radius:5px;"></label>
    <button type="submit" formaction="{{ route('schools.'.$reportRole.'.session-report', ['school' => $school->slug, 'format' => 'pdf']) }}" class="btn btn-secondary">Download PDF</button>
    <button type="submit" formaction="{{ route('schools.'.$reportRole.'.session-report', ['school' => $school->slug, 'format' => 'xlsx']) }}" class="btn btn-primary">Download Excel</button>
</form>
