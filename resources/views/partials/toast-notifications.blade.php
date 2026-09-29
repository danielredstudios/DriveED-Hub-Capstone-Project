<div id="toast-container" class="toast-container"></div>

<style>
    .toast-container {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 10001;
        display: flex;
        flex-direction: column;
        gap: 12px;
        max-width: 420px;
        width: calc(100% - 48px);
        pointer-events: none;
    }

    .toast {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: var(--border-radius, 12px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
        padding: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-left: 6px solid var(--primary-color, #2563eb);
        animation: toast-slide-in 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        pointer-events: auto;
        overflow: hidden;
        position: relative;
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s;
    }

    .toast:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 40px rgba(0, 0, 0, 0.18);
    }

    .toast-success { border-left-color: var(--btn-success-bg, #10b981) !important; }
    .toast-error { border-left-color: var(--btn-danger-bg, #ef4444) !important; }
    .toast-warning { border-left-color: var(--badge-pending-bg, #fbbf24) !important; }
    .toast-info { border-left-color: var(--primary-color, #3b82f6) !important; }

    .toast-icon-wrap {
        flex-shrink: 0;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background: rgba(37, 99, 235, 0.1);
        color: var(--primary-color, #2563eb);
    }

    .toast-success .toast-icon-wrap { background: rgba(16, 185, 129, 0.15); color: #059669; }
    .toast-error .toast-icon-wrap { background: rgba(239, 68, 68, 0.15); color: #dc2626; }
    .toast-warning .toast-icon-wrap { background: rgba(245, 158, 11, 0.15); color: #d97706; }
    .toast-info .toast-icon-wrap { background: rgba(59, 130, 246, 0.15); color: #2563eb; }

    .toast-content {
        flex: 1;
        font-size: 14px;
        line-height: 1.5;
        color: #111827;
        font-weight: 500;
        padding-right: 8px;
    }

    .toast-close {
        flex-shrink: 0;
        background: transparent;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        transition: all 0.2s;
        line-height: 1;
    }

    .toast-close:hover {
        background: rgba(0,0,0,0.05);
        color: #374151;
        transform: rotate(90deg);
    }

    /* Progress Bar */
    .toast-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 4px;
        width: 100%;
        background: rgba(0, 0, 0, 0.05);
        transform-origin: left;
    }

    .toast-progress-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 100%;
        width: 100%;
        background: inherit;
        filter: brightness(0.9);
        transform-origin: left;
        background: var(--primary-color, #667eea);
        opacity: 0.3;
    }

    .toast-success .toast-progress-bar { background: var(--btn-success-bg, #10b981); }
    .toast-error .toast-progress-bar { background: var(--btn-danger-bg, #ef4444); }
    .toast-warning .toast-progress-bar { background: var(--badge-pending-bg, #f59e0b); }
    .toast-info .toast-progress-bar { background: var(--primary-color, #3b82f6); }

    @keyframes toast-slide-in {
        from { transform: translateX(120%) scale(0.9); opacity: 0; }
        to { transform: translateX(0) scale(1); opacity: 1; }
    }

    .toast-fade-out {
        animation: toast-fade-out 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
    }

    @keyframes toast-fade-out {
        from { transform: translateX(0) scale(1); opacity: 1; }
        to { transform: translateX(120%) scale(0.9); opacity: 0; }
    }

    /* Mobile adjustments */
    @media (max-width: 480px) {
        .toast-container {
            top: 16px;
            right: 16px;
            width: calc(100% - 32px);
        }
        .toast {
            padding: 12px 14px;
        }
    }
</style>

<script>
    (function() {
        function normalizeToastArgs(arg1, arg2, arg3) {
            const defaultConfig = {
                message: '',
                type: 'info',
                duration: 5000
            };

            const typeSet = { success: true, error: true, warning: true, info: true, danger: true };

            if (arg1 && typeof arg1 === 'object' && !Array.isArray(arg1)) {
                const message = String(arg1.message ?? arg1.text ?? '');
                const rawType = String(arg1.type ?? arg1.level ?? defaultConfig.type).toLowerCase();
                const type = typeSet[rawType] ? rawType : defaultConfig.type;
                const duration = Number.isFinite(Number(arg1.duration)) ? Math.max(0, Number(arg1.duration)) : defaultConfig.duration;
                return { message, type, duration };
            }

            if (typeof arg1 === 'string' && typeSet[arg1.toLowerCase()] && typeof arg2 === 'string') {
                const duration = Number.isFinite(Number(arg3)) ? Math.max(0, Number(arg3)) : defaultConfig.duration;
                return {
                    message: arg2,
                    type: arg1.toLowerCase(),
                    duration
                };
            }

            if (typeof arg1 === 'string' && arg2 && typeof arg2 === 'object' && !Array.isArray(arg2)) {
                const rawType = String(arg2.type ?? arg2.level ?? defaultConfig.type).toLowerCase();
                const type = typeSet[rawType] ? rawType : defaultConfig.type;
                const duration = Number.isFinite(Number(arg2.duration)) ? Math.max(0, Number(arg2.duration)) : defaultConfig.duration;
                return {
                    message: arg1,
                    type,
                    duration
                };
            }

            return {
                message: String(arg1 ?? ''),
                type: (typeof arg2 === 'string' && typeSet[arg2.toLowerCase()]) ? arg2.toLowerCase() : defaultConfig.type,
                duration: Number.isFinite(Number(arg3)) ? Math.max(0, Number(arg3)) : defaultConfig.duration
            };
        }

        function escapeHtml(text) {
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        // Universal notification function
        window.showToast = function(arg1, arg2, arg3) {
            const normalized = normalizeToastArgs(arg1, arg2, arg3);
            const message = normalized.message;
            const type = normalized.type === 'danger' ? 'error' : normalized.type;
            const duration = normalized.duration;

            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            
            // Progress tracking
            let remainingTime = duration;
            let startTime = Date.now();
            let timerId = null;
            let progressInterval = null;

            // Icons mapping
            const icons = {
                success: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>',
                error: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>',
                warning: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
                info: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
            };

            const iconHtml = icons[type] || icons.info;

            toast.innerHTML = `
                <div class="toast-icon-wrap">${iconHtml}</div>
                <div class="toast-content">${escapeHtml(message)}</div>
                <button class="toast-close" title="Dismiss">&times;</button>
                <div class="toast-progress">
                    <div class="toast-progress-bar" id="progress-${Date.now()}"></div>
                </div>
            `;

            const progressBar = toast.querySelector('.toast-progress-bar');

            function startTimer() {
                startTime = Date.now();
                if (duration > 0) {
                    timerId = setTimeout(removeToast, remainingTime);
                    
                    // Smooth progress bar update
                    progressInterval = setInterval(() => {
                        const elapsed = Date.now() - startTime;
                        const currentProgress = ((remainingTime - elapsed) / duration) * 100;
                        if (currentProgress >= 0) {
                            progressBar.style.transform = `scaleX(${currentProgress / 100})`;
                        }
                    }, 10);
                }
            }

            function pauseTimer() {
                clearTimeout(timerId);
                clearInterval(progressInterval);
                remainingTime -= Date.now() - startTime;
            }

            function removeToast() {
                toast.classList.add('toast-fade-out');
                setTimeout(() => {
                    if (toast.parentElement) toast.remove();
                }, 400);
            }

            // Events
            toast.addEventListener('mouseenter', pauseTimer);
            toast.addEventListener('mouseleave', () => {
                if (remainingTime > 0) startTimer();
            });

            toast.querySelector('.toast-close').addEventListener('click', (e) => {
                e.stopPropagation();
                removeToast();
            });

            // Universal click to dismiss (optional)
            toast.addEventListener('click', (e) => {
                if (!e.target.closest('.toast-close')) {
                    removeToast();
                }
            });

            container.appendChild(toast);
            startTimer();
        };

        // Compatibility alias - avoid infinite loop if already defined
        if (!window.showNotification) {
            window.showNotification = function(arg1, arg2, arg3) {
                window.showToast(arg1, arg2, arg3);
            };
        }

        // Object-style compatibility wrapper (Toast.success, Toast.error, etc.)
        if (!window.Toast) {
            window.Toast = {
                success: function(msg, title, duration) { window.showToast(msg, 'success', duration); },
                error: function(msg, title, duration) { window.showToast(msg, 'error', duration); },
                info: function(msg, title, duration) { window.showToast(msg, 'info', duration); },
                warning: function(msg, title, duration) { window.showToast(msg, 'warning', duration); },
                danger: function(msg, title, duration) { window.showToast(msg, 'error', duration); }
            };
        }

        // Initialize from session on load
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                showToast("{!! addslashes(session('success')) !!}", 'success');
            @endif

            @if(session('error'))
                showToast("{!! addslashes(session('error')) !!}", 'error', 7000);
            @endif

            @if(session('status'))
                showToast("{!! addslashes(session('status')) !!}", 'info');
            @endif

            @if(session('info'))
                showToast("{!! addslashes(session('info')) !!}", 'info');
            @endif

            @if(session('warning'))
                showToast("{!! addslashes(session('warning')) !!}", 'warning', 7000);
            @endif

            @if($errors->any())
                @foreach($errors->all() as $error)
                    showToast("{!! addslashes($error) !!}", 'error', 8000);
                @endforeach
            @endif
        });
    })();
</script>

