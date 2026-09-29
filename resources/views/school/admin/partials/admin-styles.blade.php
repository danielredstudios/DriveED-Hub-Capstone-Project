@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = blank($settings?->primary_color) ? '#667eea' : $settings->primary_color;
    $secondaryColor = blank($settings?->secondary_color) ? '#764ba2' : $settings->secondary_color;
    $accentColor = blank($settings?->accent_color) ? '#1e40af' : $settings->accent_color;
    $useGradient = $settings?->use_gradient_header ?? true;
    $headerTextColor = blank($settings?->header_text_color) ? '#ffffff' : $settings->header_text_color;
@endphp

<style>
    /* ============================================
       SHARED ADMIN STYLES
       Consistent styling across all admin pages
       ============================================ */

    /* Container */
    .admin-container {
        padding: 20px;
        margin: 20px auto;
        max-width: 1600px;
    }

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 3px solid {{ $primaryColor }};
    }

    .page-header-left {
        flex: 1;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    /* Statistics Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .stat-card:hover::before {
        transform: scale(1.1);
        opacity: 0.08;
    }
    
    /* Glowing effect only for specifically highlighted stat cards */
    .stat-card.glow {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }
    
    .stat-card.glow::before {
        opacity: 0.12;
    }
    
    .stat-card.glow:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }
    
    .stat-card.glow:hover::before {
        transform: scale(1.2);
        opacity: 0.18;
    }

    /* Stat card color variants */
    /* Stat card color variants */
    .stat-card.students {
        border-left-color: {{ $primaryColor }};
    }
    .stat-card.students::before {
        background: {{ $primaryColor }};
    }
    .stat-card.students .stat-icon {
        background: {{ $primaryColor }}15;
        color: {{ $primaryColor }};
    }

    .stat-card.instructors {
        border-left-color: {{ $secondaryColor }};
    }
    .stat-card.instructors::before {
        background: {{ $secondaryColor }};
    }
    .stat-card.instructors .stat-icon {
        background: {{ $secondaryColor }}15;
        color: {{ $secondaryColor }};
    }

    .stat-card.growth {
        border-left-color: #10b981;
    }
    .stat-card.growth::before {
        background: #10b981;
    }
    .stat-card.growth .stat-icon {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #047857;
    }

    .stat-card.active {
        border-left-color: {{ $primaryColor }};
    }
    .stat-card.active::before {
        background: {{ $primaryColor }};
    }
    .stat-card.active .stat-icon {
        background: {{ $primaryColor }}15;
        color: {{ $primaryColor }};
    }

    .stat-card.danger {
        border-left-color: #ef4444;
    }
    .stat-card.danger::before {
        background: #ef4444;
    }
    .stat-card.danger .stat-icon {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #b91c1c;
    }

    .stat-card.info {
        border-left-color: {{ $primaryColor }};
    }
    .stat-card.info::before {
        background: {{ $primaryColor }};
    }
    .stat-card.info .stat-icon {
        background: {{ $primaryColor }}15;
        color: {{ $primaryColor }};
    }

    .stat-card.success {
        border-left-color: #10b981;
    }
    .stat-card.success::before {
        background: #10b981;
    }
    .stat-card.success .stat-icon {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #047857;
    }

    .stat-card.warning {
        border-left-color: #f59e0b;
    }
    .stat-card.warning::before {
        background: #f59e0b;
    }
    .stat-card.warning .stat-icon {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
    }

    .stat-card.pending {
        border-left-color: #f59e0b;
    }
    .stat-card.pending::before {
        background: #f59e0b;
    }
    .stat-card.pending .stat-icon {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
    }

    .stat-card.inactive {
        border-left-color: #6b7280;
    }
    .stat-card.inactive::before {
        background: #6b7280;
    }
    .stat-card.inactive .stat-icon {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        color: #374151;
    }

    .stat-card.total {
        border-left-color: {{ $primaryColor }};
    }
    .stat-card.total::before {
        background: {{ $primaryColor }};
    }
    .stat-card.total .stat-icon {
        background: {{ $primaryColor }}15;
        color: {{ $primaryColor }};
    }

    .stat-content {
        position: relative;
        z-index: 1;
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-value {
        font-size: 2.25rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 8px;
        line-height: 1;
    }

    .stat-detail {
        font-size: 0.875rem;
        color: #6b7280;
        line-height: 1.5;
    }

    .stat-detail strong {
        color: #374151;
        font-weight: 600;
    }

    /* Content Cards/Sections */
    .content-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .content-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .content-card-header {
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: #ffffff;
        padding: 15px 20px;
        font-weight: 600;
        font-size: 1rem;
    }

    .content-card-body {
        padding: 20px;
    }

    /* Activity Lists & Items */
    .activity-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .activity-item {
        padding: 16px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.2s ease;
        border-radius: 8px;
        margin-bottom: 4px;
    }

    .activity-item:hover {
        background: #f9fafb;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-name {
        font-weight: 600;
        color: #111827;
        margin-bottom: 4px;
    }

    .activity-email {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .view-all-link {
        display: block;
        text-align: center;
        padding: 12px;
        margin-top: 16px;
        color: {{ $primaryColor }};
        font-weight: 600;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .view-all-link:hover {
        background: #f9fafb;
        color: {{ $secondaryColor }};
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }

    .empty-state-text {
        color: #9ca3af;
        font-size: 0.875rem;
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-primary {
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
    }

    .btn-primary:hover {
        opacity: 0.9;
        color: white;
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .btn-success:hover {
        opacity: 0.9;
        color: white;
    }

    .btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .btn-warning:hover {
        color: white;
        opacity: 0.9;
    }

    .btn-info {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
    }

    .btn-info:hover {
        color: white;
        opacity: 0.9;
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .btn-danger:hover {
        color: white;
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
        color: white;
    }

    .btn-outline {
        background: transparent;
        border: 2px solid {{ $primaryColor }};
        color: {{ $primaryColor }};
    }

    .btn-outline:hover {
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 0.8rem;
    }

    .btn-lg {
        padding: 14px 28px;
        font-size: 1rem;
    }

    /* Tables */
    .admin-table {
        width: 100%;
        border-collapse: collapse;
    }

    .admin-table th {
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: #ffffff;
        padding: 14px 16px;
        text-align: left;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .admin-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 0.9rem;
        color: #374151;
    }

    .admin-table tbody tr:hover {
        background: #f9fafb;
    }

    .admin-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-secondary {
        background: #f3f4f6;
        color: #374151;
    }

    /* Alerts */
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    .alert-danger, .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    .alert-warning {
        background: #fef3c7;
        color: #92400e;
        border-left: 4px solid #f59e0b;
    }

    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border-left: 4px solid #3b82f6;
    }

    .alert .close-btn {
        margin-left: auto;
        background: none;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        color: inherit;
        opacity: 0.6;
        padding: 0;
    }

    .alert .close-btn:hover {
        opacity: 1;
    }

    /* Forms */
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: 500;
        margin-bottom: 8px;
        color: #374151;
        font-size: 0.9rem;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.2s;
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: {{ $primaryColor }};
        box-shadow: 0 0 0 3px {{ $primaryColor }}20;
    }

    .form-control::placeholder {
        color: #9ca3af;
    }

    /* Filter Buttons */
    .filter-group {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 10px 20px;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 500;
        font-size: 0.9rem;
        color: #374151;
    }

    .filter-btn:hover {
        border-color: {{ $primaryColor }};
        color: {{ $primaryColor }};
    }

    .filter-btn.active {
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
        border-color: transparent;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }

    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 10px;
    }

    .empty-state-text {
        font-size: 0.9rem;
        color: #9ca3af;
    }

    /* Modals */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s;
    }

    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        transform: scale(0.9);
        transition: transform 0.3s;
    }

    .modal-overlay.active .modal-content {
        transform: scale(1);
    }

    .modal-header {
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: #ffffff;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        color: #ffffff;
        font-size: 1.5rem;
        cursor: pointer;
        opacity: 0.8;
        padding: 0;
        line-height: 1;
    }

    .modal-close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        padding: 15px 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    /* Action Buttons Group */
    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .action-btn {
        padding: 6px 10px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.8rem;
    }

    .action-btn-view {
        background: #dbeafe;
        color: #1e40af;
    }

    .action-btn-view:hover {
        background: #bfdbfe;
    }

    .action-btn-edit {
        background: #fef3c7;
        color: #92400e;
    }

    .action-btn-edit:hover {
        background: #fde68a;
    }

    .action-btn-delete {
        background: #fee2e2;
        color: #991b1b;
    }

    .action-btn-delete:hover {
        background: #fecaca;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .filter-group {
            flex-direction: column;
        }

        .filter-btn {
            width: 100%;
        }
    }


    /* Session Flash Messages */
    .flash-message {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        animation: slideIn 0.4s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .flash-message.success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border-left: 4px solid #10b981;
    }

    .flash-message.error {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-left: 4px solid #ef4444;
    }

    .flash-message.warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-left: 4px solid #f59e0b;
    }

    .flash-message.info {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border-left: 4px solid #3b82f6;
    }

    .flash-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.1rem;
    }

    .flash-message.success .flash-icon {
        background: #10b981;
        color: white;
    }

    .flash-message.error .flash-icon {
        background: #ef4444;
        color: white;
    }

    .flash-message.warning .flash-icon {
        background: #f59e0b;
        color: white;
    }

    .flash-message.info .flash-icon {
        background: #3b82f6;
        color: white;
    }

    .flash-content {
        flex: 1;
    }

    .flash-title {
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 2px;
    }

    .flash-message.success .flash-title { color: #065f46; }
    .flash-message.error .flash-title { color: #991b1b; }
    .flash-message.warning .flash-title { color: #92400e; }
    .flash-message.info .flash-title { color: #1e40af; }

    .flash-text {
        font-size: 0.85rem;
    }

    .flash-message.success .flash-text { color: #047857; }
    .flash-message.error .flash-text { color: #b91c1c; }
    .flash-message.warning .flash-text { color: #b45309; }
    .flash-message.info .flash-text { color: #1d4ed8; }

    .flash-close {
        background: none;
        border: none;
        padding: 4px;
        cursor: pointer;
        font-size: 1.25rem;
        line-height: 1;
        opacity: 0.6;
        transition: opacity 0.2s;
    }

    .flash-close:hover {
        opacity: 1;
    }

    .flash-message.success .flash-close { color: #065f46; }
    .flash-message.error .flash-close { color: #991b1b; }
    .flash-message.warning .flash-close { color: #92400e; }
    .flash-message.info .flash-close { color: #1e40af; }

    /* Confirmation Modal */
    .confirm-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s;
    }

    .confirm-modal.active {
        opacity: 1;
        visibility: visible;
    }

    .confirm-dialog {
        background: white;
        border-radius: 16px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        transform: scale(0.9);
        transition: transform 0.3s;
        overflow: hidden;
    }

    .confirm-modal.active .confirm-dialog {
        transform: scale(1);
    }

    .confirm-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        margin: 30px auto 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    .confirm-icon.warning {
        background: #fef3c7;
        color: #d97706;
    }

    .confirm-icon.danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .confirm-icon.info {
        background: #dbeafe;
        color: #2563eb;
    }

    .confirm-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 10px;
        padding: 0 30px;
    }

    .confirm-message {
        color: #6b7280;
        font-size: 0.95rem;
        padding: 0 30px;
        margin-bottom: 25px;
        line-height: 1.5;
    }

    .confirm-actions {
        display: flex;
        border-top: 1px solid #e5e7eb;
    }

    .confirm-btn {
        flex: 1;
        padding: 16px;
        border: none;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
    }

    .confirm-btn-cancel {
        background: #f9fafb;
        color: #6b7280;
    }

    .confirm-btn-cancel:hover {
        background: #f3f4f6;
    }

    .confirm-btn-confirm {
        color: white;
    }

    .confirm-btn-confirm.warning {
        background: #f59e0b;
    }

    .confirm-btn-confirm.warning:hover {
        background: #d97706;
    }

    .confirm-btn-confirm.danger {
        background: #ef4444;
    }

    .confirm-btn-confirm.danger:hover {
        background: #dc2626;
    }

    .confirm-btn-confirm.success {
        background: #10b981;
    }

    .confirm-btn-confirm.success:hover {
        background: #059669;
    }

    @media (max-width: 480px) {
        .toast-container {
            left: 10px;
            right: 10px;
            top: 10px;
        }
        
        .toast {
            min-width: auto;
            max-width: none;
        }
    }

    /* â”€â”€ Filter Bar & Custom Select Dropdown â”€â”€ */
    .filter-bar {
        background: white;
        border-radius: 12px;
        padding: 14px 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .filter-bar label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #374151;
    }

    .filter-select {
        padding: 8px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.875rem;
        color: #374151;
        background: white;
        cursor: pointer;
        min-width: 150px;
        outline: none;
        transition: border-color 0.2s;
    }

    .filter-select:focus { border-color: {{ $primaryColor }}; }

    /* Custom Select Wrapper */
    .custom-select-wrapper {
        position: relative;
        display: inline-block;
        min-width: 150px;
    }

    .custom-select-trigger {
        padding: 8px 32px 8px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.875rem;
        color: #374151;
        background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E") no-repeat right 10px center;
        background-size: 14px;
        cursor: pointer;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .custom-select-trigger:hover { border-color: #d1d5db; }
    .custom-select-wrapper.open .custom-select-trigger { 
        border-color: {{ $primaryColor }}; 
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .custom-select-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        max-height: 250px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }

    .custom-select-wrapper.open .custom-select-dropdown { display: block; }

    .custom-select-option {
        padding: 10px 14px;
        font-size: 0.875rem;
        color: #374151;
        cursor: pointer;
        transition: background 0.15s;
    }

    .custom-select-option:hover { background: #f3f4f6; }
    .custom-select-option.selected { 
        background: {{ $primaryColor }}15; 
        color: {{ $primaryColor }}; 
        font-weight: 500;
    }

    .custom-select-option:first-child { border-radius: 8px 8px 0 0; }
    .custom-select-option:last-child { border-radius: 0 0 8px 8px; }

    /* Scrollbar styling for dropdown */
    .custom-select-dropdown::-webkit-scrollbar { width: 6px; }
    .custom-select-dropdown::-webkit-scrollbar-track { background: #f3f4f6; border-radius: 3px; }
    .custom-select-dropdown::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
    .custom-select-dropdown::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

    @media (max-width: 768px) {
        .filter-bar { 
            flex-direction: column; 
            align-items: stretch; 
        }
        .custom-select-wrapper { width: 100%; }
        .custom-select-trigger { width: 100%; }
    }
</style>


<!-- Confirmation Modal -->
<div class="confirm-modal" id="confirmModal">
    <div class="confirm-dialog">
        <div class="confirm-icon warning" id="confirmIcon">
            <span id="confirmIconText">!</span>
        </div>
        <h3 class="confirm-title" id="confirmTitle">Are you sure?</h3>
        <p class="confirm-message" id="confirmMessage">This action cannot be undone.</p>
        <div class="confirm-actions">
            <button class="confirm-btn confirm-btn-cancel" onclick="closeConfirmModal()">Cancel</button>
            <button class="confirm-btn confirm-btn-confirm warning" id="confirmBtn" onclick="executeConfirm()">Confirm</button>
        </div>
    </div>
</div>

<script>

// Confirmation Modal System
var confirmCallback = null;

function showConfirm(options) {
    const modal = document.getElementById('confirmModal');
    const icon = document.getElementById('confirmIcon');
    const iconText = document.getElementById('confirmIconText');
    const title = document.getElementById('confirmTitle');
    const message = document.getElementById('confirmMessage');
    const btn = document.getElementById('confirmBtn');
    
    // Set content
    title.textContent = options.title || 'Are you sure?';
    message.textContent = options.message || 'This action cannot be undone.';
    btn.textContent = options.confirmText || 'Confirm';
    
    // Set type styling
    const type = options.type || 'warning';
    icon.className = `confirm-icon ${type}`;
    btn.className = `confirm-btn confirm-btn-confirm ${type}`;
    
    const icons = {
        warning: '!',
        danger: '✕',
        info: 'i',
        success: '✓'
    };
    iconText.textContent = icons[type] || '!';
    
    // Store callback
    confirmCallback = options.onConfirm;
    
    // Show modal
    modal.classList.add('active');
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    modal.classList.remove('active');
    confirmCallback = null;
}

function executeConfirm() {
    if (confirmCallback) {
        try {
            confirmCallback();
        } catch (e) {
            console.error('Error in confirm callback:', e);
        }
    }
    closeConfirmModal();
}

// Close modal on backdrop click
document.getElementById('confirmModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeConfirmModal();
    }
});

// â”€â”€ Custom Select Dropdown â”€â”€
// Converts native selects with .filter-select class to custom dropdowns with max-height
document.addEventListener('DOMContentLoaded', function() {
    const selects = document.querySelectorAll('select.filter-select');
    
    selects.forEach(function(select) {
        // Skip if already converted or has few options
        if (select.dataset.customized === 'true' || select.options.length <= 6) return;
        
        // Create wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'custom-select-wrapper';
        
        // Create trigger button
        const trigger = document.createElement('div');
        trigger.className = 'custom-select-trigger';
        trigger.textContent = select.options[select.selectedIndex]?.text || 'Select...';
        
        // Create dropdown
        const dropdown = document.createElement('div');
        dropdown.className = 'custom-select-dropdown';
        
        // Populate options
        Array.from(select.options).forEach(function(option, index) {
            const optionEl = document.createElement('div');
            optionEl.className = 'custom-select-option' + (index === select.selectedIndex ? ' selected' : '');
            optionEl.textContent = option.text;
            optionEl.dataset.value = option.value;
            
            optionEl.addEventListener('click', function() {
                // Update native select
                select.value = option.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                
                // Update UI
                trigger.textContent = option.text;
                dropdown.querySelectorAll('.custom-select-option').forEach(function(o) { 
                    o.classList.remove('selected'); 
                });
                optionEl.classList.add('selected');
                wrapper.classList.remove('open');
            });
            
            dropdown.appendChild(optionEl);
        });
        
        // Toggle dropdown
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            // Close other dropdowns first
            document.querySelectorAll('.custom-select-wrapper.open').forEach(function(w) {
                if (w !== wrapper) w.classList.remove('open');
            });
            wrapper.classList.toggle('open');
        });
        
        // Assemble
        wrapper.appendChild(trigger);
        wrapper.appendChild(dropdown);
        
        // Hide original select and insert custom dropdown
        select.style.display = 'none';
        select.dataset.customized = 'true';
        select.parentNode.insertBefore(wrapper, select);
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.custom-select-wrapper.open').forEach(function(w) {
            w.classList.remove('open');
        });
    });
});
</script>

<style>
/* â”€â”€ Filter Bar & Custom Select Dropdown â”€â”€ */
.filter-bar {
    background: white;
    border-radius: 12px;
    padding: 14px 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}

.filter-bar label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
}

.filter-select {
    padding: 8px 14px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.875rem;
    color: #374151;
    background: white;
    cursor: pointer;
    min-width: 150px;
    outline: none;
    transition: border-color 0.2s;
}

.filter-select:focus { border-color: {{ $primaryColor }}; }

/* Custom Select Wrapper */
.custom-select-wrapper {
    position: relative;
    display: inline-block;
    min-width: 150px;
}

.custom-select-trigger {
    padding: 8px 32px 8px 14px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.875rem;
    color: #374151;
    background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E") no-repeat right 10px center;
    background-size: 14px;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.custom-select-trigger:hover { border-color: #d1d5db; }
.custom-select-wrapper.open .custom-select-trigger { 
    border-color: {{ $primaryColor }}; 
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.custom-select-dropdown {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.12);
    max-height: 250px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}

.custom-select-wrapper.open .custom-select-dropdown { display: block; }

.custom-select-option {
    padding: 10px 14px;
    font-size: 0.875rem;
    color: #374151;
    cursor: pointer;
    transition: background 0.15s;
}

.custom-select-option:hover { background: #f3f4f6; }
.custom-select-option.selected { 
    background: {{ $primaryColor }}15; 
    color: {{ $primaryColor }}; 
    font-weight: 500;
}

.custom-select-option:first-child { border-radius: 8px 8px 0 0; }
.custom-select-option:last-child { border-radius: 0 0 8px 8px; }

/* Scrollbar styling for dropdown */
.custom-select-dropdown::-webkit-scrollbar { width: 6px; }
.custom-select-dropdown::-webkit-scrollbar-track { background: #f3f4f6; border-radius: 3px; }
.custom-select-dropdown::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
.custom-select-dropdown::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

@media (max-width: 768px) {
    .filter-bar { 
        flex-direction: column; 
        align-items: stretch; 
    }
    .custom-select-wrapper { width: 100%; }
    .custom-select-trigger { width: 100%; }
}

/* â”€â”€ Compact Pagination Styling (Standardized) â”€â”€ */
.admin-pagination-wrapper .ds-pagination-nav {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #f3f4f6;
}

.admin-pagination-wrapper .ds-pagination-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
}

.admin-pagination-wrapper .ds-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 10px;
    font-size: 0.875rem;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    background: #f9fafb;
    color: #4b5563;
    border: 1px solid #e5e7eb;
}

.admin-pagination-wrapper .ds-page-btn:hover:not(.is-disabled) {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #111827;
    transform: translateY(-1px);
}

.admin-pagination-wrapper .ds-page-btn.is-active {
    background: {{ $primaryColor }};
    color: white;
    border-color: {{ $primaryColor }};
    box-shadow: 0 4px 12px {{ $primaryColor }}40;
}

.admin-pagination-wrapper .ds-page-btn.is-disabled {
    background: #fdfdfd;
    color: #d1d5db;
    cursor: not-allowed;
    border-color: #f3f4f6;
}

.admin-pagination-wrapper .ds-page-ellipsis {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    color: #9ca3af;
    font-size: 0.875rem;
}

@media (max-width: 640px) {
    .admin-pagination-wrapper .ds-pagination-nav {
        justify-content: center;
    }
}

/* Export Dropdown - Unified Global Component */
.export-dropdown {
    position: relative;
    display: inline-block;
}

.btn-export-trigger {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: {{ $useGradient ? "linear-gradient(135deg, $primaryColor 0%, $secondaryColor 100%)" : $primaryColor }};
    color: white;
    box-shadow: 0 4px 14px {{ $primaryColor }}40;
}

.btn-export-trigger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px {{ $primaryColor }}60;
    opacity: 0.95;
}

.btn-export-trigger .chevron {
    transition: transform 0.3s ease;
    font-size: 0.85rem;
}

.export-dropdown.open .chevron {
    transform: rotate(180deg);
}

.export-dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 15px 50px rgba(0,0,0,0.18);
    border: 1px solid #eef2f6;
    min-width: 260px;
    z-index: 2000;
    overflow: hidden;
    transform-origin: top right;
    animation: dropdownPopIn 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes dropdownPopIn {
    from { opacity: 0; transform: scale(0.95) translateY(-10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.export-dropdown.open .export-dropdown-menu {
    display: block;
}

.export-dropdown-menu .dropdown-header {
    padding: 14px 18px 10px;
    font-size: 0.75rem;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
}

.export-dropdown-menu a {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    font-size: 0.9rem;
    color: #334155;
    text-decoration: none;
    transition: all 0.2s ease;
    border-bottom: 1px solid #f8fafc;
}

.export-dropdown-menu a:last-child {
    border-bottom: none;
}

.export-dropdown-menu a:hover {
    background: #fdfdfd;
    color: {{ $primaryColor }};
    padding-left: 22px;
}

.export-dropdown-menu a i {
    font-size: 1.25rem;
    width: 24px;
    text-align: center;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));
}

.dropdown-divider-top {
    border-top: 5px solid #f8fafc;
}
</style>
