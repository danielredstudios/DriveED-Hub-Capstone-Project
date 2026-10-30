<dialog id="calendarDayDialog" aria-labelledby="calendarDayTitle" style="position:fixed;inset:0;margin:auto;width:min(620px,94vw);max-height:85vh;border:1px solid #d1d5db;border-radius:12px;padding:24px;color:#1f2937;">
    <h2 id="calendarDayTitle"></h2>
    <div id="calendarDayContent"></div>
    <form method="dialog"><button class="btn btn-secondary" type="submit">Close</button></form>
</dialog>
<script>
if (!window.calendarDayDialogBound) {
    window.calendarDayDialogBound = true;
    document.addEventListener('click', function (event) {
        const day = event.target.closest('[data-calendar-date]');
        if (!day) return;
        const dialog = document.getElementById('calendarDayDialog');
        document.getElementById('calendarDayTitle').textContent = day.dataset.calendarDate;
        document.getElementById('calendarDayContent').replaceChildren(day.querySelector('template').content.cloneNode(true));
        dialog.showModal();
    });
    document.addEventListener('keydown', function (event) {
        if ((event.key === 'Enter' || event.key === ' ') && event.target.matches('[data-calendar-date]')) {
            event.preventDefault();
            event.target.click();
        }
    });
}
</script>
