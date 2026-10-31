<dialog id="calendarDayDialog" aria-labelledby="calendarDayTitle" style="position:fixed;inset:0;margin:auto;width:min(620px,94vw);max-height:85vh;border:1px solid #d1d5db;border-radius:12px;padding:24px;color:#1f2937;">
    <h2 id="calendarDayTitle" style="margin:0 0 20px;font-size:1.35rem;"></h2>
    <div id="calendarDayContent"></div>
    <button class="btn btn-secondary" type="button" onclick="document.getElementById('calendarDayDialog').close()">Close</button>
</dialog>
<style>
    #calendarDayDialog::backdrop { background:rgba(15,23,42,.55); }
    #calendarDayDialog { box-sizing:border-box;overflow-y:auto;box-shadow:0 20px 60px rgba(15,23,42,.2); }
    #calendarDayContent > div { padding:16px 0;border-top:1px solid #e2e8f0;line-height:1.6; }
    #calendarDayContent h3 { margin:0 0 8px;font-size:1rem; }
    #calendarDayContent .btn { margin-top:8px; }
    #calendarDayDialog > button { margin-top:20px;min-height:42px; }
</style>
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
