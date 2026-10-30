<style>
    #scheduleSessionLog label {display:block;margin:16px 0 6px;font-weight:600;}
    #scheduleSessionLog .form-control {display:block;width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;font:inherit;background:#fff;color:#1f2937;box-sizing:border-box;}
    #scheduleSessionLog button {margin-top:16px;}
    #scheduleSessionLogError {margin-top:12px;}
</style>
<dialog id="scheduleSessionLog" aria-labelledby="scheduleSessionLogTitle" style="position:fixed;inset:0;margin:auto;width:min(560px,94vw);max-height:85vh;overflow:auto;padding:24px;border:1px solid #d1d5db;border-radius:12px;">
    <h2 id="scheduleSessionLogTitle">Log Session</h2>
    <form id="scheduleSessionLogForm">
        <input type="hidden" name="booking_id">
        <p id="scheduleSessionLogStudent"></p>
        <label for="scheduleLogAttendance">Attendance</label>
        <select id="scheduleLogAttendance" name="attendance_status" class="form-control" required><option value="attended">Attended</option><option value="late">Late</option><option value="absent">Absent</option></select>
        <label for="scheduleLogGrade">Grade (0 to 100)</label>
        <input id="scheduleLogGrade" class="form-control" type="number" name="session_grade" min="0" max="100" step="0.01">
        <label for="scheduleLogNotes">Session notes</label>
        <textarea id="scheduleLogNotes" class="form-control" name="instructor_feedback" maxlength="1000" rows="4"></textarea>
        <p id="scheduleSessionLogError" role="alert" style="color:#b91c1c;"></p>
        <button class="btn btn-primary" type="submit">Save Session Log</button>
        <button class="btn btn-secondary" type="button" onclick="document.getElementById('scheduleSessionLog').close()">Cancel</button>
    </form>
</dialog>
<script>
async function openScheduleSessionLog(bookingId) {
    const dialog = document.getElementById('scheduleSessionLog');
    const form = document.getElementById('scheduleSessionLogForm');
    form.reset();
    form.elements.booking_id.value = bookingId;
    document.getElementById('scheduleSessionLogError').textContent = '';
    document.getElementById('scheduleSessionLogStudent').textContent = 'Loading lesson…';
    const submit = form.querySelector('[type="submit"]');
    submit.disabled = true;
    dialog.showModal();
    try {
        const response = await fetch('{{ url($school->slug) }}/instructor/lessons/' + bookingId, {headers: {'Accept': 'application/json'}});
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Unable to load lesson.');
        form.elements.attendance_status.value = data.booking.attendance_status || 'attended';
        form.elements.session_grade.value = data.booking.session_grade ?? '';
        form.elements.instructor_feedback.value = data.booking.instructor_feedback || '';
        document.getElementById('scheduleSessionLogStudent').textContent = (data.booking.student?.name || 'Student') + ' · ' + (data.booking.course?.title || 'Lesson');
        submit.disabled = false;
    } catch (error) { document.getElementById('scheduleSessionLogError').textContent = error.message; }
}
document.getElementById('scheduleSessionLogForm').addEventListener('submit', async function (event) {
    event.preventDefault();
    const form = event.currentTarget;
    const button = form.querySelector('[type="submit"]');
    button.disabled = true;
    try {
        const response = await fetch('{{ url($school->slug) }}/instructor/lessons/' + form.elements.booking_id.value + '/update', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
            body: JSON.stringify({attendance_status: form.elements.attendance_status.value, session_status: form.elements.attendance_status.value === 'absent' ? 'no-show' : 'done', session_grade: form.elements.session_grade.value === '' ? null : Number(form.elements.session_grade.value), instructor_feedback: form.elements.instructor_feedback.value})
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Unable to save session.');
        window.location.reload();
    } catch (error) {
        document.getElementById('scheduleSessionLogError').textContent = error.message;
        button.disabled = false;
    }
});
</script>
