<style>
    .calendar-container { width:100%;max-width:1400px;box-sizing:border-box;margin-inline:auto;min-width:0; }
    .calendar-grid { grid-template-columns:repeat(7,minmax(0,1fr)); }
    .calendar-day { min-width:0;min-height:clamp(68px,9vw,116px);box-sizing:border-box; }
    .calendar-day > * { max-width:100%; }
    .schedule-main,.schedule-content,.main-view-section { min-width:0; }
    .schedule-item[data-is-past="true"] { border:1px solid #cbd5e1;background:#f8fafc; }
    .schedule-item[data-is-past="true"] .schedule-date-header { background:#475569;color:#fff; }
    .schedule-item[data-is-past="true"]::before { content:"Past schedule";display:block;padding:8px 16px;color:#475569;font-size:.8rem;font-weight:600; }
    details.sidebar-section > summary { cursor:pointer;font-weight:600;padding:4px 0; }
    details.sidebar-section[open] > summary { margin-bottom:16px; }
    .schedule-log-export[open] > summary { margin-bottom:20px; }
    @media(max-width:640px) {
        .calendar-container { padding:10px; }
        .calendar-grid { gap:3px; }
        .calendar-day { padding:4px; }
        .calendar-header { flex-wrap:wrap;gap:12px; }
    }
</style>
<script>
(() => {
    document.querySelectorAll('.schedule-sidebar .sidebar-section, .requests-sidebar .sidebar-section').forEach(section => {
        if (section.tagName === 'DETAILS') return;
        const heading = section.querySelector('h3');
        if (!heading || !/Today's Lessons|Upcoming This Week|Cancelled Schedules/.test(heading.textContent)) return;
        const details = document.createElement('details');
        details.className = section.className;
        const summary = document.createElement('summary');
        summary.textContent = heading.textContent;
        heading.remove();
        details.append(summary, ...section.childNodes);
        section.replaceWith(details);
    });
})();
</script>
