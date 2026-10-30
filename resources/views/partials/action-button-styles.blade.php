<style>
    .main-content :is(.btn, .btn-action, .btn-action-icon, .btn-create, .btn-add, .btn-save, .btn-submit, .btn-export-trigger, .btn-mark-done, .btn-mark-no-show, .btn-invite-action) {
        display:inline-flex;align-items:center;justify-content:center;gap:8px;
        width:auto;min-width:36px;min-height:36px;padding:8px 12px;
        border:1px solid transparent;border-radius:var(--button-border-radius, 8px);
        font-family:inherit;font-size:.875rem;font-weight:600;line-height:1.25;
        text-decoration:none;white-space:nowrap;cursor:pointer;box-sizing:border-box;
    }
    .main-content :is(.btn, .btn-action, .btn-action-icon) > :is(svg, i) {order:-1;flex-shrink:0;}
    .main-content :is(.btn-create, .btn-add, .btn-save, .btn-submit, .btn-export-trigger, .btn-mark-done) {background:var(--btn-primary-bg);color:var(--btn-primary-text);}
    .main-content :is(.btn-delete, .btn-danger, .btn-mark-no-show) {background:var(--btn-danger-bg);color:var(--btn-danger-text);}
    .main-content :is(.btn-edit, .btn-view) {background:#eff6ff;color:#1e40af;}
    .main-content :is(button, a, summary):focus-visible {outline:3px solid var(--primary-color);outline-offset:3px;}
    .main-content button:disabled {opacity:.55;cursor:not-allowed;}
    dialog::backdrop {background:rgba(15,23,42,.4);}
</style>
