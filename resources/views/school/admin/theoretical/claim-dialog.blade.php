<dialog id="claimNoticeDialog" style="width:calc(100% - 32px); max-width:520px; border:1px solid #d1d5db; border-radius:12px; padding:24px; color:#1f2937;">
    <form method="POST" data-no-ajax="1" id="claimNoticeForm">
        @csrf
        <h2 style="font-size:1.2rem; margin:0 0 8px;">Notify student to pick up documents</h2>
        <p id="claimNoticeStudent" style="margin-bottom:16px; font-weight:600; color:#173c54;"></p>
        <label for="claimNoticeKind">What is ready for pickup?</label>
        <select id="claimNoticeKind" name="kind" class="form-control" style="margin:8px 0 16px;" required>
            <option value="claim_stub">Official claim stub</option>
            <option value="certificate">Official Certificate of Completion</option>
        </select>
        <label for="claimNoticeMessage">Message for the student</label>
        <textarea id="claimNoticeMessage" name="message" class="form-control" rows="5" maxlength="1000" required style="margin:8px 0; width:100%;"></textarea>
        <p style="font-size:.85rem; color:#526476;">The student will see this on their dashboard and in their alerts. Let them know where and when to pick it up.</p>
        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
            <button type="button" class="btn-action btn-view" id="closeClaimNotice">Cancel</button>
            <button type="submit" class="btn-action btn-mark-passed">Send message</button>
        </div>
    </form>
</dialog>
<script>
(() => {
    const dialog = document.getElementById('claimNoticeDialog');
    const form = document.getElementById('claimNoticeForm');
    const defaults = {
        claim_stub: 'Your claim stub is ready for pickup! Please visit the school office during business hours to get it.',
        certificate: 'Your certificate of completion is ready! Please visit the school office during business hours to pick it up.'
    };
    document.querySelectorAll('.claim-notice-trigger').forEach(button => {
        button.addEventListener('click', () => {
            form.reset();
            form.action = button.dataset.action;
            document.getElementById('claimNoticeStudent').textContent = button.dataset.studentName;
            document.getElementById('claimNoticeMessage').value = defaults.claim_stub;
            dialog.showModal();
        });
    });
    document.getElementById('closeClaimNotice').addEventListener('click', () => dialog.close());
    document.getElementById('claimNoticeKind').addEventListener('change', event => {
        const message = document.getElementById('claimNoticeMessage');
        if (Object.values(defaults).includes(message.value) || !message.value.trim()) {
            message.value = defaults[event.target.value] || '';
        }
    });
})();
</script>
