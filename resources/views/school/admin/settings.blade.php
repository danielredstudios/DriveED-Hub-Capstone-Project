@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'School Customization')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $settings = $school->schoolSetting ?? null;
    $gcashSetting = $gcashSetting ?? null;
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .customization-container {
        padding: 20px;
        margin: 20px auto;
        max-width: 1400px;
        position: relative;
    }

    /* Page Header */
    .page-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 3px solid var(--primary-color);
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 10px 0;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin: 0;
    }

    /* Settings Card */
    .settings-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 30px;
        opacity: 0.9;
    }

    .settings-form {
        padding: 20px;
    }

    .form-section {
        margin-bottom: 25px;
        padding: 20px;
        background: #f9fafb;
        border-radius: 8px;
        border: 2px solid #e5e7eb;
        transition: all 0.3s;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        cursor: pointer;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .section-title::before {
        content: '';
        width: 4px;
        height: 20px;
        background: var(--primary-color);
        border-radius: 2px;
    }

    /* Settings Navigation */
    .tab-btn {
        padding: 8px 14px;
        background: rgba(249, 250, 251, var(--settings-nav-control-opacity, 1));
        border: 1px solid #d1d5db;
        color: #374151;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        text-decoration: none;
        white-space: nowrap;
    }

    .tab-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
        transform: translateY(-1px);
    }

    .tab-btn.active {
        background: rgba(var(--primary-rgb), var(--settings-nav-control-opacity, 1));
        color: white;
        border-color: var(--primary-color);
    }

    .settings-nav-toolbar {
        display: flex;
        flex-direction: column;
        gap: 10px;
        transition: gap 0.2s ease;
    }

    .settings-nav-tools {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        transition: gap 0.2s ease;
    }

    .settings-search-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 260px;
        flex: 1;
    }

    .settings-search-input {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 13px;
        color: #111827;
        background: rgba(255, 255, 255, var(--settings-nav-control-opacity, 1));
        transition: padding 0.2s ease, font-size 0.2s ease;
    }

    .settings-search-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.12);
    }

    .nav-action-btn {
        padding: 8px 10px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: rgba(255, 255, 255, var(--settings-nav-control-opacity, 1));
        color: #374151;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .nav-action-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .settings-dirty-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 10px;
        border-radius: 999px;
        background: rgba(245, 158, 11, 0.12);
        color: #92400e;
        border: 1px solid rgba(245, 158, 11, 0.4);
        font-size: 12px;
        font-weight: 700;
        transition: padding 0.2s ease, font-size 0.2s ease;
    }

    .settings-dirty-indicator::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #f59e0b;
    }

    .preview-toggle {
        padding: 6px 12px;
        background: white;
        border: 2px solid var(--primary-color);
        color: var(--primary-color);
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .preview-toggle.active {
        background: var(--primary-color);
        color: white;
    }

    .preview-toggle::after {
        content: '▼';
        font-size: 0.7rem;
        transition: transform 0.3s;
    }

    .preview-toggle.active::after {
        transform: rotate(180deg);
    }

    .section-inputs {
        display: block;
    }

    .section-inputs.collapsed {
        display: none;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-label {
        display: block;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
        font-size: 0.9rem;
    }

    .color-input-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .color-picker {
        width: 50px;
        height: 40px;
        border: 2px solid #d1d5db;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .color-text {
        flex: 1;
        padding: 10px 12px;
        border: 2px solid #d1d5db;
        border-radius: 6px;
        font-family: monospace;
        font-size: 0.9rem;
        transition: all 0.3s;
    }

    .color-text:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
    }

    .number-input {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.9rem;
        transition: all 0.3s;
    }

    .number-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
    }

    .form-control,
    .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        color: #374151;
        background: white;
        transition: all 0.3s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M10.293 3.293L6 7.586 1.707 3.293A1 1 0 00.293 4.707l5 5a1 1 0 001.414 0l5-5a1 1 0 10-1.414-1.414z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 40px;
        cursor: pointer;
    }

    .form-control:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        background-color: white;
    }

    .form-control option,
    .form-select option {
        padding: 12px;
        background: white;
        color: #374151;
    }

    .form-control-compact {
        padding: 10px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
    }

    .form-row-two {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .checkbox-inline {
        margin-right: 8px;
    }

    .login-preview-note {
        background: #f0f9ff;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #3b82f6;
        margin-top: 20px;
    }

    .login-preview-note-title {
        color: #1e40af;
        display: block;
        margin-bottom: 8px;
    }

    .login-preview-note-text {
        color: #1e3a8a;
        font-size: 0.9rem;
        margin: 0;
    }

    .login-preview-note-code {
        background: #dbeafe;
        padding: 2px 6px;
        border-radius: 4px;
    }

    .bg-image-group-hidden {
        display: none;
    }

    .current-bg-wrap {
        margin-top: 10px;
    }

    .current-bg-image {
        max-width: 200px;
        border-radius: 8px;
        border: 2px solid #e5e7eb;
    }

    .current-bg-caption {
        font-size: 0.85rem;
        color: #666;
        margin-top: 5px;
    }

    .opacity-control-row {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .opacity-range {
        flex: 1;
    }

    .opacity-value {
        min-width: 45px;
        font-weight: 600;
        color: var(--primary-color);
    }

    .opacity-help {
        color: #666;
        font-size: 0.85rem;
    }

    .help-text-block {
        display: block;
        margin-top: 5px;
    }

    .help-text-muted {
        color: #666;
        font-size: 0.85rem;
    }

    .login-intro-text {
        color: #6b7280;
        font-size: 0.9rem;
        margin-bottom: 20px;
    }

    .login-subsection {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid #e5e7eb;
    }

    .save-button {
        width: 100%;
        padding: 12px;
        @if(($settings->button_style ?? 'solid') === 'gradient')
        background: linear-gradient(135deg, var(--btn-primary-bg) 0%, var(--btn-secondary-bg) 100%);
        @else
        background: var(--btn-primary-bg);
        @endif
        color: var(--btn-primary-text);
        border: none;
        border-radius: var(--button-border-radius);
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.10);
    }
    
    .save-button:hover {
        filter: brightness(1.1);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.18);
    }
    
    .reset-button {
        width: 100%;
        padding: 10px;
        background: white;
        color: var(--btn-primary-bg);
        border: 2px solid var(--btn-primary-bg);
        border-radius: var(--button-border-radius);
        font-weight: 500;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 10px;
    }
    .reset-button:hover {
        background: var(--btn-primary-bg);
        color: var(--btn-primary-text);
        border-color: var(--btn-primary-bg);
    }

    .floating-actions {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 120;
        width: min(280px, calc(100vw - 32px));
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 0;
        border: none;
        background: transparent;
        backdrop-filter: none;
        box-shadow: none;
    }

    .floating-actions .save-button,
    .floating-actions .reset-button {
        width: 100%;
        margin-top: 0;
    }

    /* Preview Area */
    .preview-area {
        display: none;
        margin-top: 20px;
        padding: 20px;
        background: white;
        border: 2px solid var(--primary-color);
        border-radius: 8px;
        box-shadow: 0 4px 12px var(--brand-shadow);
        animation: slideDown 0.3s ease;
    }

    .preview-area.active {
        display: block;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            max-height: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            max-height: 1000px;
            transform: translateY(0);
        }
    }

    .preview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--primary-color);
    }

    .preview-header h4 {
        font-size: 1.1rem;
        color: var(--primary-color);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .preview-close {
        background: #fee2e2;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        color: #991b1b;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s;
    }

    .preview-content {
        margin-top: 15px;
    }

    /* Preview Components */
    .preview-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .preview-card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 15px;
    }

    /* Sidebar Preview */
    .sidebar-preview {
        width: 250px;
        padding: 20px;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .sidebar-item {
        padding: 12px 15px;
        border-radius: 6px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: 500;
    }

    /* Button Preview */
    .button-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
    }

    .preview-button {
        padding: 12px 20px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-align: center;
    }

    /* Modal Preview */
    .modal-preview {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        max-width: 500px;
    }

    .modal-header-preview {
        padding: 20px;
        color: white;
    }

    .modal-body-preview {
        padding: 20px;
        background: white;
    }

    /* Badge Preview */
    .badge-preview-grid {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .preview-badge {
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .tabs-container-frame {
        --settings-nav-opacity: 1;
        --settings-nav-control-opacity: 1;
        margin-bottom: 24px;
        background: rgba(255, 255, 255, var(--settings-nav-opacity));
        border-radius: 12px;
        padding: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        box-sizing: border-box;
        position: relative;
        z-index: 95;
        transition: padding 0.2s ease, top 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        backdrop-filter: saturate(140%) blur(3px);
        -webkit-backdrop-filter: saturate(140%) blur(3px);
    }

    .tabs-container-frame.is-pinned {
        position: fixed;
        top: var(--settings-sticky-top, 104px);
        left: var(--settings-nav-left, 0px);
        width: var(--settings-nav-width, 100%);
        z-index: 995;
    }

    .settings-nav-spacer {
        display: none;
        height: 0;
    }

    .settings-nav-spacer.active {
        display: block;
    }

    #section-operations,
    #section-branding,
    #section-auth {
        scroll-margin-top: 190px;
    }

    .settings-group {
        margin-bottom: 28px;
    }

    .settings-group:last-of-type {
        margin-bottom: 0;
    }

    .settings-group-header {
        margin-bottom: 16px;
        padding: 12px 14px;
        border: 1px solid #e5e7eb;
        border-left: 4px solid var(--primary-color);
        border-radius: 10px;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        cursor: pointer;
        user-select: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .settings-group-header:hover {
        border-color: rgba(var(--primary-rgb), 0.45);
        box-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.12);
    }

    .settings-group-header-content {
        flex: 1;
    }

    .settings-group-toggle {
        width: 26px;
        height: 26px;
        border-radius: 999px;
        border: 1px solid #d1d5db;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        background: #ffffff;
        flex-shrink: 0;
    }

    .settings-group-toggle::before {
        content: '▼';
        font-size: 11px;
        line-height: 1;
        transition: transform 0.2s ease;
    }

    .settings-group.collapsed .settings-group-toggle::before {
        transform: rotate(-90deg);
    }

    .settings-group.collapsed > :not(.settings-group-header) {
        display: none;
    }

    .settings-group.collapsed {
        margin-bottom: 16px;
    }

    .settings-group-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
    }

    .settings-group-note {
        margin: 4px 0 0 0;
        font-size: 0.85rem;
        color: #6b7280;
    }

    .operations-subsection {
        margin-bottom: 20px;
        padding: 0 0 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    .operations-subsection:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .operations-subsection .section-header {
        margin-bottom: 12px;
    }

    .tabs-row {
        display: flex;
        gap: 8px;
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: 2px;
        scrollbar-width: thin;
    }

    .preview-buttons-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .preview-buttons-note {
        color: #6b7280;
        font-size: 0.85rem;
        margin-top: 15px;
    }

    .modal-preview-frame {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }

    .modal-preview-title {
        margin: 0;
        font-size: 1.2rem;
    }

    .modal-preview-subtitle {
        margin: 8px 0 0 0;
        opacity: 0.9;
        font-size: 0.9rem;
    }

    .modal-preview-text {
        margin: 0;
        color: #6b7280;
    }

    .calendar-header-preview-shell {
        padding: 20px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .calendar-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .calendar-nav-btn-shell {
        padding: 10px 18px;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.25);
        background: rgba(255, 255, 255, 0.15);
        cursor: pointer;
        font-weight: 600;
    }

    .calendar-month-shell {
        font-size: 1.5rem;
        font-weight: 700;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .calendar-preview-title {
        margin: 0 0 10px 0;
        color: #374151;
    }

    .calendar-preview-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
    }

    .calendar-weekday-cell {
        text-align: center;
        font-weight: 600;
        padding: 8px;
        background: #f8f9fa;
        border-radius: 4px;
        font-size: 0.85rem;
    }

    .calendar-day-shell {
        min-height: 60px;
        padding: 8px;
        background: white;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .calendar-day-shell.today-preview {
        background: #fffbf0;
        font-weight: 600;
    }

    .calendar-day-today-label {
        font-size: 0.7rem;
        opacity: 0.8;
    }

    .calendar-preview-note {
        color: #6b7280;
        font-size: 0.85rem;
        margin-top: 10px;
    }

    .badge-preview-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .badge-preview-item {
        border-radius: 6px;
        color: white;
    }

    .badge-preview-note {
        color: #6b7280;
        font-size: 0.85rem;
        margin-top: 15px;
    }

    .badge-icon-sm {
        width: 16px;
        height: 16px;
    }

    .color-preview-stack {
        display: grid;
        gap: 20px;
    }

    .color-preview-title {
        margin: 0 0 10px 0;
        color: #374151;
    }

    .background-preview-box {
        width: 100%;
        height: 150px;
        border-radius: 8px;
        border: 2px solid #d1d5db;
        position: relative;
        overflow: hidden;
    }

    .background-preview-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #374151;
        font-weight: 500;
        text-shadow: 0 0 5px white, 0 0 10px white;
    }

    .swatches-stack {
        display: grid;
        gap: 15px;
    }

    .swatch-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .swatch-box {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        border: 2px solid #d1d5db;
    }

    .swatch-note {
        color: #6b7280;
        font-size: 0.85rem;
        margin: 4px 0 0 0;
    }

    .sidebar-preview-compact {
        padding: 15px;
    }

    .sidebar-item-compact {
        margin-bottom: 5px;
    }

    /* Page Header Preview */
    .page-header-preview {
        padding: 20px;
        border-radius: 8px;
        border-bottom: 3px solid;
    }

    /* Card Preview */
    .card-preview-sample {
        border: 2px solid;
        border-radius: 8px;
        overflow: hidden;
    }

    .card-header-preview {
        padding: 15px;
    }

    .card-body-preview {
        padding: 15px;
        background: white;
    }

    /* Alert Messages */
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        position: sticky;
        top: 0;
        z-index: 100;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    /* Scrollbar Styling */
    .settings-panel::-webkit-scrollbar,
    .preview-panel::-webkit-scrollbar {
        width: 8px;
    }

    .settings-panel::-webkit-scrollbar-track,
    .preview-panel::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .settings-panel::-webkit-scrollbar-thumb,
    .preview-panel::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .settings-panel::-webkit-scrollbar-thumb:hover,
    .preview-panel::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .customization-container {
            padding: 10px;
        }

        .tabs-container-frame {
            padding: 10px;
        }

        .settings-nav-tools {
            flex-direction: column;
            align-items: stretch;
        }

        .settings-search-wrap {
            min-width: 0;
        }

        .nav-action-btn {
            width: 100%;
        }

        .settings-dirty-indicator {
            justify-content: center;
        }

        .floating-actions {
            right: 12px;
            left: 12px;
            width: auto;
            bottom: 12px;
        }

        .preview-panel {
            width: 100%;
            top: 0;
            height: 100vh;
        }

        .settings-card {
            padding: 20px;
        }
    }
</style>

<div class="customization-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">School Customization</h1>
            <p class="page-subtitle">Personalize your school's appearance with colors, styles, and branding</p>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Toast.success("{{ session('success') }}");
            @endif
            @if(session('error'))
                Toast.error("{{ session('error') }}");
            @endif
            @if($errors->any())
                @foreach($errors->all() as $error)
                    Toast.error("{{ $error }}");
                @endforeach
            @endif
        });
    </script>

    <!-- Section Navigation -->
    <div class="tabs-container tabs-container-frame" id="settingsNavigation">
        <div class="settings-nav-toolbar">
            <div class="tabs tabs-row" id="settingsNavLinks">
                <a class="tab-btn active" href="#section-operations" data-nav-link data-section-id="section-operations">Operations</a>
                <a class="tab-btn" href="#section-branding" data-nav-link data-section-id="section-branding">Branding</a>
                <a class="tab-btn" href="#section-auth" data-nav-link data-section-id="section-auth">Login & Signup</a>
            </div>

            <div class="settings-nav-tools">
                <div class="settings-search-wrap">
                    <input type="search" id="settingsNavSearch" class="settings-search-input" placeholder="Jump to a section (e.g. operations, branding, login)">
                    <button type="button" class="nav-action-btn" id="settingsNavSearchBtn">Go</button>
                </div>
                <button type="button" class="nav-action-btn" onclick="expandAllSettingsSections()">Expand All</button>
                <button type="button" class="nav-action-btn" onclick="collapseAllSettingsSections()">Collapse All</button>
                <span class="settings-dirty-indicator" id="settingsDirtyIndicator" hidden>Unsaved changes</span>
            </div>
        </div>
    </div>
    <div id="settingsNavSpacer" class="settings-nav-spacer" aria-hidden="true"></div>

    <!-- Settings Card -->
    <div class="settings-card">
        <form method="POST" action="{{ route('schools.admin.settings.update', $school) }}" id="settingsForm" enctype="multipart/form-data" class="native-form" data-no-ajax="1" data-no-submit-guard="1">
            @csrf

            <!-- Operations -->
            <section id="section-operations" class="settings-group" data-nav-section>
                <div class="settings-group-header" onclick="toggleSettingsGroup(this)" role="button" aria-expanded="true" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleSettingsGroup(this)}">
                    <div class="settings-group-header-content">
                        <h2 class="settings-group-title">Operations</h2>
                        <p class="settings-group-note">Scheduling, onboarding, staffing, alerts, email, payments, and account setup.</p>
                    </div>
                    <span class="settings-group-toggle" aria-hidden="true"></span>
                </div>
                <!-- Scheduling & Timezone Settings -->
                <div class="operations-subsection" id="section-scheduling" data-nav-section>
                    <div class="section-header" onclick="toggleSection(this)">
                        <h3 class="section-title">Scheduling & Timezone</h3>
                    </div>
                    
                    <div class="section-inputs">
                    <div class="form-group">
                        <label class="form-label">School Timezone</label>
                        <select class="form-select" name="timezone">
                            @foreach($timezones as $tz)
                                <option value="{{ $tz }}" {{ old('timezone', $school->timezone ?? config('app.timezone')) == $tz ? 'selected' : '' }}>
                                    {{ $tz }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted help-text-block">
                            Canonical timezone for all school operations and booking cutoffs
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Booking Cutoff Hours</label>
                        <input type="number" class="number-input" name="booking_cutoff_hours" value="{{ old('booking_cutoff_hours', $settings->booking_cutoff_hours ?? 0) }}" min="0" max="168">
                        <small class="text-muted help-text-block">
                            Hours before a time slot when bookings are disabled (0-168 hours)
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Advance Scheduling Days</label>
                        <input type="number" class="number-input" name="advance_booking_days" value="{{ old('advance_booking_days', $settings->advance_booking_days ?? 0) }}" min="0" max="30">
                        <small class="text-muted help-text-block">
                            Minimum days in advance students must schedule (0 = same-day scheduling allowed)
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="enable_booking_queue" value="1" {{ old('enable_booking_queue', $settings->enable_booking_queue ?? true) ? 'checked' : '' }} class="checkbox-inline">
                            Enable Schedule Queue/Cart System
                        </label>
                        <small class="text-muted help-text-block">
                            When enabled, schedules go to a queue for admin review before confirmation
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Schedule Queue Days</label>
                        <input type="number" class="number-input" name="booking_queue_days" value="{{ old('booking_queue_days', $settings->booking_queue_days ?? 3) }}" min="1" max="14">
                        <small class="text-muted help-text-block">
                            Days schedules stay in queue before auto-confirming (if queue enabled)
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Instructor Selection Mode</label>
                        <select class="form-control form-control-compact" name="instructor_selection_mode">
                            <option value="auto_assign" {{ old('instructor_selection_mode', $settings->instructor_selection_mode ?? 'auto_assign') == 'auto_assign' ? 'selected' : '' }}>
                                Auto Assign - System randomly assigns instructors
                            </option>
                            <option value="student_chooses" {{ old('instructor_selection_mode', $settings->instructor_selection_mode ?? 'auto_assign') == 'student_chooses' ? 'selected' : '' }}>
                                Student Chooses - Students pick their preferred instructor
                            </option>
                            <option value="admin_assigns" {{ old('instructor_selection_mode', $settings->instructor_selection_mode ?? 'auto_assign') == 'admin_assigns' ? 'selected' : '' }}>
                                Admin Assigns - Admins manually assign instructors after scheduling
                            </option>
                        </select>
                        <small class="text-muted help-text-block">
                            Controls how instructors are assigned to student schedules
                        </small>
                    </div>
                </div>

                <!-- Onboarding & Security Settings -->
                <div class="operations-subsection" id="section-onboarding" data-nav-section>
                    <div class="section-header" onclick="toggleSection(this)">
                        <h3 class="section-title">Onboarding & Security</h3>
                    </div>
                    
                    <div class="section-inputs">
                        <div class="form-group">
                            <label class="form-label">Account Setup Link Expiry (Days)</label>
                            <input type="number" class="number-input" name="invitation_expiry_days" value="{{ old('invitation_expiry_days', $settings->invitation_expiry_days ?? 7) }}" min="1" max="30">
                            <small class="text-muted help-text-block">
                                Number of days an account setup link remains valid after being sent (1-30 days)
                            </small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Maximum File Upload Size (MB)</label>
                            <input type="number" class="number-input" name="max_file_size_mb" value="{{ old('max_file_size_mb', $settings->max_file_size_mb ?? 5) }}" min="1" max="20">
                            <small class="text-muted help-text-block">
                                Sets the platform-wide limit for images (profile pictures, banners, licenses, etc.)
                            </small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" name="enforce_ph_contact" value="1" {{ old('enforce_ph_contact', $settings->enforce_ph_contact ?? true) ? 'checked' : '' }} class="checkbox-inline">
                                Enforce Philippine Mobile Format (+63)
                            </label>
                            <small class="text-muted help-text-block">
                                When enabled, all contact inputs will show the +63 prefix and validate for PH mobile numbers.
                            </small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" name="enable_pii_masking" value="1" {{ old('enable_pii_masking', $settings->enable_pii_masking ?? false) ? 'checked' : '' }} class="checkbox-inline">
                                Mask Sensitive PII (Personally Identifiable Information)
                            </label>
                            <small class="text-muted help-text-block">
                                When enabled, sensitive fields like Phone and Email will be masked in management tables. Admins can click to reveal.
                            </small>
                        </div>

                        <div class="nav-divider"></div>

                        <div class="form-group">
                            <label class="form-label">Minimum TDC (Theoretical) Session Duration (Minutes)</label>
                            <input type="number" class="number-input" name="min_tdc_duration_minutes" value="{{ old('min_tdc_duration_minutes', $settings->min_tdc_duration_minutes ?? 60) }}" min="1" max="720">
                            <small class="text-muted help-text-block">
                                Minimum required duration for classroom/theoretical sessions.
                            </small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Minimum PDC (Practical) Session Duration (Minutes)</label>
                            <input type="number" class="number-input" name="min_pdc_duration_minutes" value="{{ old('min_pdc_duration_minutes', $settings->min_pdc_duration_minutes ?? 60) }}" min="1" max="720">
                            <small class="text-muted help-text-block">
                                Minimum required duration for driving/practical sessions.
                            </small>
                        </div>

                        <div class="nav-divider"></div>

                        <!-- Grouped License Roadmap Section -->
                        <div class="roadmap-config-card" style="border: 2px solid #e5e7eb; border-radius: 15px; margin: 20px 0; overflow: hidden; background: #f8fafc;">
                            <div class="roadmap-config-header" onclick="toggleRoadmapSection(this)" style="padding: 15px 20px; background: #f1f5f9; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e7eb;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="background: var(--primary-color); color: white; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                        </svg>
                                    </div>
                                    <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #1e293b;">License Acquisition Roadmap</h4>
                                </div>
                                <svg class="roadmap-toggle-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20" style="transition: transform 0.3s; transform: rotate(180deg);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            
                            <div class="roadmap-config-body" style="padding: 20px;">
                                <p class="help-text-muted" style="margin-bottom: 20px; font-size: 0.85rem; color: #64748b;">
                                    Define the sequential steps for acquiring a license. These will be displayed as a visual progress timeline on student and guest dashboards.
                                </p>
                                
                                <div id="licenseStepsContainer">
                                    @php
                                        $licenseSteps = $settings->license_instructions ?? [];
                                        if (!is_array($licenseSteps)) $licenseSteps = [];
                                        
                                        $availableMilestones = [
                                            'none' => 'Manual (No System Link)',
                                            'tdc' => 'TDC Completion (Theoretical Course)',
                                            'permit' => 'Student Permit Verified',
                                            'pdc' => 'PDC Completion (Practical Course)',
                                            'license' => 'Official License (Final Step)',
                                        ];
                                    @endphp
                                    
                                    @forelse($licenseSteps as $index => $step)
                                        <div class="license-step-row" style="margin-bottom: 20px; padding: 18px; background: white; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                                <h5 style="margin: 0; font-size: 0.9rem; font-weight: 700; color: var(--primary-color); display: flex; align-items: center; gap: 8px;">
                                                    <span class="step-index-badge" style="background: #e0e7ff; color: #4338ca; width: 22px; height: 22px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">{{ $index + 1 }}</span>
                                                    Step Configuration
                                                </h5>
                                                <button type="button" class="nav-action-btn" style="padding: 4px 10px; border-color: #fee2e2; color: #991b1b; background: #fff1f1; font-size: 0.75rem;" onclick="removeLicenseStep(this)">Remove</button>
                                            </div>
                                            
                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 12px;">
                                                <div>
                                                    <label style="display: block; font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Title</label>
                                                    <input type="text" name="license_steps[{{ $index }}][title]" class="form-control" style="font-weight: 600;" placeholder="e.g., Theoretical Driving Course (TDC)" value="{{ $step['title'] ?? '' }}" required>
                                                </div>
                                                <div>
                                                    <label style="display: block; font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Link to System Progress</label>
                                                    <select name="license_steps[{{ $index }}][milestone]" class="form-control" style="font-size: 0.85rem;">
                                                        @foreach($availableMilestones as $val => $label)
                                                            <option value="{{ $val }}" {{ ($step['milestone'] ?? 'none') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <label style="display: block; font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Description</label>
                                                <textarea name="license_steps[{{ $index }}][description]" class="form-control" rows="2" placeholder="Explain the requirements for this step..." required>{{ $step['description'] ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="no-steps-text" style="padding: 40px; text-align: center; background: #fff; border: 2px dashed #e2e8f0; border-radius: 12px; color: #94a3b8;">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="32" height="32" style="margin-bottom: 10px; opacity: 0.5;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                            </svg>
                                            <p style="margin: 0; font-weight: 500;">No roadmap steps configured.</p>
                                        </div>
                                    @endforelse
                                </div>
                                
                                <button type="button" class="nav-action-btn" style="margin-top: 15px; width: 100%; border-color: var(--primary-color); color: var(--primary-color); background: white; padding: 12px; font-weight: 700;" onclick="addLicenseStep()">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" style="margin-right: 8px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add New Step
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Max Branch Managers Per Branch</label>
                            <input type="number" class="number-input" name="branch_secretary_limit_per_branch" value="{{ old('branch_secretary_limit_per_branch', $settings->branch_secretary_limit_per_branch ?? 1) }}" min="1" max="50">
                            <small class="text-muted help-text-block">
                                Maximum number of active and pending branch manager slots allowed per branch (minimum 1)
                            </small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" name="require_instructor_license" value="1" {{ old('require_instructor_license', $settings->require_instructor_license ?? true) ? 'checked' : '' }} class="checkbox-inline">
                                Mandatory Instructor License
                            </label>
                            <small class="text-muted help-text-block">
                                When enabled, instructors MUST provide a license number during account setup
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Staff Settings -->
                <div class="operations-subsection" id="section-staff" data-nav-section>
                    <div class="section-header" onclick="toggleSection(this)">
                        <h3 class="section-title">Staff Settings</h3>
                    </div>
                    
                    <div class="section-inputs">
                        <div class="form-group">
                            <label class="form-label">Instructor Removal Notice Days</label>
                            <input type="number" class="number-input" name="instructor_removal_notice_days" value="{{ old('instructor_removal_notice_days', $school->instructor_removal_notice_days ?? 7) }}" min="0" max="30">
                            <small class="text-muted help-text-block">
                                Minimum days notice required for instructor schedule removal requests
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Operational Alerts -->
                <div class="operations-subsection" id="section-operational-alerts" data-nav-section>
                    <div class="section-header" onclick="toggleSection(this)">
                        <h3 class="section-title">Operational Alerts</h3>
                    </div>
                    
                    <div class="section-inputs">
                    <div class="form-group">
                        <label class="form-label">Pending Request Alert Threshold</label>
                        <input type="number" class="number-input" name="alert_threshold_pending" value="{{ old('alert_threshold_pending', $settings->alert_threshold_pending ?? 999) }}" min="0" max="999">
                        <small class="text-muted help-text-block">
                            Alert staff when pending enrollment requests exceed this count (Default: 999)
                        </small>
                    </div>
                    </div>
                </div>

                <!-- Email Settings -->
                <div class="operations-subsection" id="section-email" data-nav-section>
                    <div class="section-header" onclick="toggleSection(this)">
                        <h3 class="section-title">Email Settings</h3>
                    </div>
                    
                    <div class="section-inputs">
                    <div class="form-group">
                        <label class="form-label">School Contact Email</label>
                        <input type="email" class="form-control-compact" style="width:100%; padding:10px;" name="contact_email" value="{{ old('contact_email', $settings->contact_email ?? '') }}" placeholder="e.g. info@yourschool.com">
                        <small class="text-muted help-text-block">
                            Displayed in automated emails as the school's contact address. If left blank, emails will say "the school office".
                        </small>
                    </div>
                    </div>
                </div>

                <!-- Payment Settings -->
                <div class="operations-subsection" id="section-payments" data-nav-section>
                    <div class="section-header" onclick="toggleSection(this)">
                        <h3 class="section-title">Payment Settings (GCash)</h3>
                    </div>

                    <div class="section-inputs">
                    {{--
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="gcash_enabled" value="1" {{ old('gcash_enabled', $gcashSetting?->is_active ?? false) ? 'checked' : '' }} class="checkbox-inline">
                            Enable GCash payment option for students
                        </label>
                    </div>
                    --}}

                    <div class="form-group">
                        <label class="form-label">GCash Account Name</label>
                        <input type="text" class="form-control" name="gcash_account_name" value="{{ old('gcash_account_name', $gcashSetting->account_name ?? '') }}" placeholder="e.g. John Doe">
                    </div>

                    <div class="form-group">
                        <label class="form-label">GCash Account Number</label>
                        <input type="text" class="form-control" name="gcash_account_number" value="{{ old('gcash_account_number', $gcashSetting->account_number ?? '') }}" placeholder="e.g. 09123456789">
                    </div>

                    <div class="form-group">
                        <label class="form-label">GCash Payment QR Image {{ empty($gcashSetting?->qr_path) ? '(required for first-time setup)' : '(upload to replace)' }}</label>
                        <input type="file" class="form-control" name="gcash_qr" accept="image/*">
                        <small class="text-muted help-text-block">
                            Upload a single image containing the QR code. Accepted formats: JPG, PNG, WEBP. Max size: 5MB.
                        </small>
                        @error('gcash_qr')
                            <small class="text-danger help-text-block">{{ $message }}</small>
                        @enderror
                    </div>

                    @if(!empty($gcashSetting?->qr_path))
                    <div class="form-group">
                        <label class="form-label">Current GCash Payment Image</label>
                        <div class="current-bg-wrap">
                            <img class="current-bg-image" src="{{ route('schools.guest.storage.gcash-qr', ['school' => $school, 'gcashSetting' => $gcashSetting]) }}" alt="Current GCash payment image">
                        </div>
                    </div>
                    @endif
                    </div>
                </div>

                <!-- UI Settings -->
                <div class="operations-subsection" id="section-ui" data-nav-section>
                    <div class="section-header" onclick="toggleSection(this)">
                        <h3 class="section-title">UI Settings</h3>
                    </div>
                    
                    <div class="section-inputs">
                    <div class="form-group">
                        <label class="form-label">Card Border Radius (px)</label>
                        <input type="number" class="number-input" id="border_radius" name="border_radius" value="{{ old('border_radius', $settings->border_radius ?? 8) }}" min="0" max="30" onchange="updatePreview()">
                    </div>
                    </div>
                </div>

                <!-- Pending Accounts -->
                <div class="operations-subsection" id="section-invitations" data-nav-section>
                    <div class="section-header" onclick="toggleSection(this)">
                        <h3 class="section-title">Pending Accounts</h3>
                    </div>

                    <div class="section-inputs">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Recipient</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Sent At</th>
                                        <th>Expires At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendingInvitations as $invitation)
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="font-weight-bold">{{ $invitation->name }}</span>
                                                    <small class="text-muted">{{ $invitation->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color: {{ $invitation->role === 'student' ? ($settings->role_student_bg ?? '#dbeafe') : ($settings->role_instructor_bg ?? '#e0f2fe') }}; color: {{ $invitation->role === 'student' ? ($settings->role_student_text ?? '#1e40af') : ($settings->role_instructor_text ?? '#0369a1') }};">
                                                    {{ ucfirst($invitation->role) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($invitation->isExpired())
                                                    <span class="badge bg-danger">Expired</span>
                                                @else
                                                    <span class="badge bg-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td>{{ $invitation->created_at->format('M d, Y H:i') }}</td>
                                            <td>{{ $invitation->expires_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Resend Setup Link" onclick="submitInvitationAction('{{ route('schools.admin.invitations.resend', ['school' => $school, 'invitation' => $invitation]) }}', 'POST')">
                                                        <i class="fas fa-sync"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Remove Account Setup" onclick="submitInvitationAction('{{ route('schools.admin.invitations.cancel', ['school' => $school, 'invitation' => $invitation]) }}', 'DELETE', 'Are you sure you want to remove this pending account?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                No pending accounts found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </section>

            <!-- Colors & Branding -->
            <section id="section-branding" class="settings-group" data-nav-section>
            <div class="settings-group-header" onclick="toggleSettingsGroup(this)" role="button" aria-expanded="true" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleSettingsGroup(this)}">
                <div class="settings-group-header-content">
                    <h2 class="settings-group-title">Branding</h2>
                    <p class="settings-group-note">Theme colors, UI components, badges, and role visuals.</p>
                </div>
                <span class="settings-group-toggle" aria-hidden="true"></span>
            </div>

            <!-- Primary Colors -->
            <div class="form-section" id="section-primary-colors" data-nav-section>
                <div class="section-header" onclick="toggleSection(this)">
                    <h3 class="section-title">Primary Colors</h3>
                    <button type="button" class="preview-toggle" onclick="event.stopPropagation(); showPreview('colors')">
                        Preview
                    </button>
                </div>
                
                <div class="section-inputs">
                <div class="form-group">
                    <label class="form-label">Primary Color</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="primary_color" name="primary_color" value="{{ old('primary_color', $settings->primary_color ?? '#667eea') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('primary_color', $settings->primary_color ?? '#667eea') }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Secondary Color</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="secondary_color" name="secondary_color" value="{{ old('secondary_color', $settings->secondary_color ?? '#764ba2') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('secondary_color', $settings->secondary_color ?? '#764ba2') }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Accent Color</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="accent_color" name="accent_color" value="{{ old('accent_color', $settings->accent_color ?? '#5568d3') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('accent_color', $settings->accent_color ?? '#5568d3') }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Background Type</label>
                    <select class="form-select" id="background_type" name="background_type" onchange="toggleBackgroundOptions()">
                        <option value="color" {{ old('background_type', $settings->background_type ?? 'color') == 'color' ? 'selected' : '' }}>Solid Color</option>
                        <option value="image" {{ old('background_type', $settings->background_type ?? 'color') == 'image' ? 'selected' : '' }}>Image</option>
                    </select>
                </div>

                <div class="form-group" id="background_color_group">
                    <label class="form-label">Background Color</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="background_color" name="background_color" value="{{ old('background_color', $settings->background_color ?? '#f5f5f5') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('background_color', $settings->background_color ?? '#f5f5f5') }}" readonly>
                    </div>
                </div>

                <div class="form-group bg-image-group-hidden" id="background_image_group">
                    <label class="form-label">Background Image</label>
                    <input type="file" class="form-control" name="background_image" accept="image/*" onchange="previewBackgroundImage(event)">
                    @error('background_image')
                        <small class="text-danger help-text-block">{{ $message }}</small>
                    @enderror
                    @if($settings && $settings->background_image)
                        <div class="current-bg-wrap">
                            <img src="{{ asset('storage/' . $settings->background_image) }}" class="current-bg-image">
                            <p class="current-bg-caption">Current background image</p>
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="form-label">Background Opacity (%)</label>
                    <div class="opacity-control-row">
                        <input type="range" class="form-range opacity-range" id="background_opacity" name="background_opacity" value="{{ old('background_opacity', $settings->background_opacity ?? 100) }}" min="0" max="100" oninput="updateOpacityValue(); updatePreview()">
                        <span id="opacity_value" class="opacity-value">{{ old('background_opacity', $settings->background_opacity ?? 100) }}%</span>
                    </div>
                    <small class="help-text-muted">Lower values make the background more transparent</small>
                </div>
                </div>

                <!-- Preview Area for Colors -->
                <div class="preview-area" id="preview-colors">
                    <div class="preview-header">
                        <h4>Color & Background Preview</h4>
                        <button type="button" class="preview-close" onclick="closePreview('colors')">Close</button>
                    </div>
                    <div class="preview-content">
                        <div class="color-preview-stack">
                            <!-- Background Preview -->
                            <div>
                                <h5 class="color-preview-title">Background Preview</h5>
                                <div id="background-preview" class="background-preview-box">
                                    <div class="background-preview-overlay">
                                        Sample Content Area
                                    </div>
                                </div>
                            </div>

                            <!-- Color Swatches -->
                            <div>
                                <h5 class="color-preview-title">Color Swatches</h5>
                                <div class="swatches-stack">
                                    <div class="swatch-row">
                                        <div id="primary-color-swatch" class="swatch-box"></div>
                                        <div>
                                            <strong>Primary Color</strong>
                                            <p class="swatch-note">Used for main brand elements</p>
                                        </div>
                                    </div>
                                    <div class="swatch-row">
                                        <div id="secondary-color-swatch" class="swatch-box"></div>
                                        <div>
                                            <strong>Secondary Color</strong>
                                            <p class="swatch-note">Complementary accent color</p>
                                        </div>
                                    </div>
                                    <div class="swatch-row">
                                        <div id="accent-color-swatch" class="swatch-box"></div>
                                        <div>
                                            <strong>Accent Color</strong>
                                            <p class="swatch-note">Highlight elements</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Colors -->
            <div class="form-section" id="section-sidebar" data-nav-section>
                <div class="section-header" onclick="toggleSection(this)">
                    <h3 class="section-title">Sidebar</h3>
                    <button type="button" class="preview-toggle" onclick="event.stopPropagation(); showPreview('sidebar')">
                        Preview
                    </button>
                </div>
                
                <div class="section-inputs">
                <div class="form-group">
                    <label class="form-label">Background Color</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="sidebar_bg_color" name="sidebar_bg_color" value="{{ old('sidebar_bg_color', $settings->sidebar_bg_color ?? '#ffffff') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('sidebar_bg_color', $settings->sidebar_bg_color ?? '#ffffff') }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Text Color</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="sidebar_text_color" name="sidebar_text_color" value="{{ old('sidebar_text_color', $settings->sidebar_text_color ?? '#333333') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('sidebar_text_color', $settings->sidebar_text_color ?? '#333333') }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Hover Color</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="sidebar_hover_color" name="sidebar_hover_color" value="{{ old('sidebar_hover_color', $settings->sidebar_hover_color ?? '#f5f5f5') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('sidebar_hover_color', $settings->sidebar_hover_color ?? '#f5f5f5') }}" readonly>
                    </div>
                </div>
                </div>

                <!-- Preview Area for Sidebar -->
                <div class="preview-area" id="preview-sidebar">
                    <div class="preview-header">
                        <h4>Sidebar Navigation Preview</h4>
                        <button type="button" class="preview-close" onclick="closePreview('sidebar')">Close</button>
                    </div>
                    <div class="preview-content">
                        <div class="sidebar-preview sidebar-preview-compact" id="sidebar-preview">
                            <div class="sidebar-item sidebar-item-compact">Dashboard</div>
                            <div class="sidebar-item sidebar-item-compact">Courses</div>
                            <div class="sidebar-item sidebar-item-compact">Schedules</div>
                            <div class="sidebar-item sidebar-item-compact">Students</div>
                            <div class="sidebar-item sidebar-item-compact">Instructors</div>
                            <div class="sidebar-item sidebar-item-compact">Settings</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Button Colors -->
            <div class="form-section" id="section-buttons" data-nav-section>
                <div class="section-header" onclick="toggleSection(this)">
                    <h3 class="section-title">Buttons</h3>
                    <button type="button" class="preview-toggle" onclick="event.stopPropagation(); showPreview('buttons')">
                        Preview
                    </button>
                </div>
                
                <div class="section-inputs">
                <div class="form-group">
                    <label class="form-label">Button Style</label>
                    <select class="form-select" id="button_style" name="button_style" onchange="toggleButtonGradient(); updatePreview()">
                        <option value="solid" {{ old('button_style', $settings->button_style ?? 'solid') == 'solid' ? 'selected' : '' }}>Solid Color</option>
                        <option value="gradient" {{ old('button_style', $settings->button_style ?? 'solid') == 'gradient' ? 'selected' : '' }}>Gradient (Primary → Secondary)</option>
                    </select>
                    <small class="help-text-muted help-text-block">
                        Gradient creates a smooth blend from Primary to Secondary color
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Primary Button Background</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="button_primary_bg" name="button_primary_bg" value="{{ old('button_primary_bg', $settings->button_primary_bg ?? '#667eea') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('button_primary_bg', $settings->button_primary_bg ?? '#667eea') }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Secondary Button Background</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="button_secondary_bg" name="button_secondary_bg" value="{{ old('button_secondary_bg', $settings->button_secondary_bg ?? '#6c757d') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('button_secondary_bg', $settings->button_secondary_bg ?? '#6c757d') }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Success Button Background</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="button_success_bg" name="button_success_bg" value="{{ old('button_success_bg', $settings->button_success_bg ?? '#28a745') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('button_success_bg', $settings->button_success_bg ?? '#28a745') }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Danger Button Background</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="button_danger_bg" name="button_danger_bg" value="{{ old('button_danger_bg', $settings->button_danger_bg ?? '#dc3545') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('button_danger_bg', $settings->button_danger_bg ?? '#dc3545') }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Button Border Radius (px)</label>
                    <input type="number" class="number-input" id="button_border_radius" name="button_border_radius" value="{{ old('button_border_radius', $settings->button_border_radius ?? 8) }}" min="0" max="30" onchange="updatePreview()">
                </div>
                </div>

                <!-- Preview Area for Buttons -->
                <div class="preview-area" id="preview-buttons">
                    <div class="preview-header">
                        <h4>Action Buttons Preview</h4>
                        <button type="button" class="preview-close" onclick="closePreview('buttons')">Close</button>
                    </div>
                    <div class="preview-content">
                        <div class="preview-buttons-row">
                            <button type="button" class="preview-button" id="button-primary-preview">Primary Button</button>
                            <button type="button" class="preview-button" id="button-secondary-preview">Secondary Button</button>
                            <button type="button" class="preview-button" id="button-success-preview">Success Button</button>
                            <button type="button" class="preview-button" id="button-danger-preview">Danger Button</button>
                        </div>
                        <p class="preview-buttons-note">These are preview buttons - they won't submit the form. Adjust colors above to see changes in real-time!</p>
                    </div>
                </div>
            </div>

            <!-- Modal Colors -->
            <div class="form-section" id="section-modals" data-nav-section>
                <div class="section-header" onclick="toggleSection(this)">
                    <h3 class="section-title">Modals</h3>
                    <button type="button" class="preview-toggle" onclick="event.stopPropagation(); showPreview('modal')">
                        Preview
                    </button>
                </div>
                
                <div class="section-inputs">
                <div class="form-group">
                    <label class="form-label">Header Background</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="modal_header_bg" name="modal_header_bg" value="{{ old('modal_header_bg', $settings->modal_header_bg ?? '#667eea') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('modal_header_bg', $settings->modal_header_bg ?? '#667eea') }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Header Text Color</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="modal_header_text" name="modal_header_text" value="{{ old('modal_header_text', $settings->modal_header_text ?? '#ffffff') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('modal_header_text', $settings->modal_header_text ?? '#ffffff') }}" readonly>
                    </div>
                </div>
                </div>

                <!-- Preview Area for Modals -->
                <div class="preview-area" id="preview-modal">
                    <div class="preview-header">
                        <h4>Modal Window Preview</h4>
                        <button type="button" class="preview-close" onclick="closePreview('modal')">Close</button>
                    </div>
                    <div class="preview-content">
                        <div class="modal-preview-frame">
                            <div class="modal-header-preview" id="modal-header-preview">
                                <h5 class="modal-preview-title">Create New Course</h5>
                                <p class="modal-preview-subtitle">Fill in the details below</p>
                            </div>
                            <div class="modal-body-preview">
                                <p class="modal-preview-text">Modal content goes here. The header colors will change based on your settings.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar & Header Colors -->
            <div class="form-section" id="section-calendar" data-nav-section>
                <div class="section-header" onclick="toggleSection(this)">
                    <h3 class="section-title">Calendar & Header</h3>
                    <button type="button" class="preview-toggle" onclick="event.stopPropagation(); showPreview('calendar')">
                        Preview
                    </button>
                </div>
                
                <div class="section-inputs">
                <div class="form-group">
                    <label class="form-label">Use Gradient Header</label>
                    <select class="form-select" id="use_gradient_header" name="use_gradient_header" onchange="updatePreview()">
                        <option value="1" {{ old('use_gradient_header', $settings->use_gradient_header ?? false) ? 'selected' : '' }}>Yes - Use Gradient (Primary → Secondary)</option>
                        <option value="0" {{ !old('use_gradient_header', $settings->use_gradient_header ?? false) ? 'selected' : '' }}>No - Use Solid Primary Color</option>
                    </select>
                    <small class="help-text-muted help-text-block">
                        Applies to calendar header and other gradient elements
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Header Text Color</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="header_text_color" name="header_text_color" value="{{ old('header_text_color', $settings->header_text_color ?? '#ffffff') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('header_text_color', $settings->header_text_color ?? '#ffffff') }}" readonly>
                    </div>
                    <small class="help-text-muted help-text-block">
                        Text color for calendar headers and navigation buttons
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Calendar Day Border Color</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="calendar_day_border" name="calendar_day_border" value="{{ old('calendar_day_border', $settings->calendar_day_border ?? '#dee2e6') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('calendar_day_border', $settings->calendar_day_border ?? '#dee2e6') }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Calendar Day Hover Color</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="calendar_day_hover" name="calendar_day_hover" value="{{ old('calendar_day_hover', $settings->calendar_day_hover ?? $settings->primary_color ?? '#667eea') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('calendar_day_hover', $settings->calendar_day_hover ?? $settings->primary_color ?? '#667eea') }}" readonly>
                    </div>
                    <small class="help-text-muted help-text-block">
                        Border color when hovering over calendar days
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Today Highlight Color</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="calendar_today_color" name="calendar_today_color" value="{{ old('calendar_today_color', $settings->calendar_today_color ?? $settings->primary_color ?? '#667eea') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('calendar_today_color', $settings->calendar_today_color ?? $settings->primary_color ?? '#667eea') }}" readonly>
                    </div>
                    <small class="help-text-muted help-text-block">
                        Border color for today's date in the calendar
                    </small>
                </div>
                </div>

                <!-- Preview Area for Calendar -->
                <div class="preview-area" id="preview-calendar">
                    <div class="preview-header">
                        <h4>Calendar Header Preview</h4>
                        <button type="button" class="preview-close" onclick="closePreview('calendar')">Close</button>
                    </div>
                    <div class="preview-content">
                        <!-- Calendar Header Preview -->
                        <div class="calendar-header-preview calendar-header-preview-shell" id="calendar-header-preview">
                            <div class="calendar-header-row">
                                <button type="button" class="calendar-nav-btn-preview calendar-nav-btn-shell">← Previous</button>
                                <span class="calendar-month-preview calendar-month-shell">November 2025</span>
                                <button type="button" class="calendar-nav-btn-preview calendar-nav-btn-shell">Next →</button>
                            </div>
                        </div>

                        <!-- Mini Calendar Grid Preview -->
                        <div>
                            <h5 class="calendar-preview-title">Calendar Days Preview</h5>
                            <div class="calendar-preview-grid">
                                <div class="calendar-weekday-cell">Sun</div>
                                <div class="calendar-weekday-cell">Mon</div>
                                <div class="calendar-weekday-cell">Tue</div>
                                <div class="calendar-weekday-cell">Wed</div>
                                <div class="calendar-weekday-cell">Thu</div>
                                <div class="calendar-weekday-cell">Fri</div>
                                <div class="calendar-weekday-cell">Sat</div>
                                <div class="calendar-day-preview calendar-day-shell">1</div>
                                <div class="calendar-day-preview calendar-day-shell">2</div>
                                <div class="calendar-day-preview calendar-day-shell">3</div>
                                <div class="calendar-day-preview calendar-day-shell">4</div>
                                <div class="calendar-day-preview calendar-day-shell today-preview" id="calendar-today-preview">5<br><small class="calendar-day-today-label">Today</small></div>
                                <div class="calendar-day-preview calendar-day-shell">6</div>
                                <div class="calendar-day-preview calendar-day-shell">7</div>
                            </div>
                            <p class="calendar-preview-note">Hover over calendar days to see the hover effect!</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Badge Colors -->
            <div class="form-section" id="section-badges" data-nav-section>
                <div class="section-header" onclick="toggleSection(this)">
                    <h3 class="section-title">Status Badges</h3>
                    <button type="button" class="preview-toggle" onclick="event.stopPropagation(); showPreview('badges')">
                        Preview
                    </button>
                </div>
                
                <div class="section-inputs">
                <div class="form-group">
                    <label class="form-label">Pending Badge</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="badge_pending_bg" name="badge_pending_bg" value="{{ old('badge_pending_bg', $settings->badge_pending_bg ?? '#fbbf24') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('badge_pending_bg', $settings->badge_pending_bg ?? '#fbbf24') }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Approved Badge</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="badge_approved_bg" name="badge_approved_bg" value="{{ old('badge_approved_bg', $settings->badge_approved_bg ?? '#10b981') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('badge_approved_bg', $settings->badge_approved_bg ?? '#10b981') }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Cancelled Badge</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="badge_cancelled_bg" name="badge_cancelled_bg" value="{{ old('badge_cancelled_bg', $settings->badge_cancelled_bg ?? '#ef4444') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('badge_cancelled_bg', $settings->badge_cancelled_bg ?? '#ef4444') }}" readonly>
                    </div>
                </div>
                </div>

                <!-- Preview Area for Badges -->
                <div class="preview-area" id="preview-badges">
                    <div class="preview-header">
                        <h4>Status Badges Preview</h4>
                        <button type="button" class="preview-close" onclick="closePreview('badges')">Close</button>
                    </div>
                    <div class="preview-content">
                        <div class="badge-preview-row">
                            <span class="preview-badge badge-preview-item" id="badge-pending-preview">Pending</span>
                            <span class="preview-badge badge-preview-item" id="badge-approved-preview"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="badge-icon-sm"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Approved</span>
                            <span class="preview-badge badge-preview-item" id="badge-cancelled-preview"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="badge-icon-sm"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg> Cancelled</span>
                        </div>
                        <p class="badge-preview-note">Used for request statuses throughout the system</p>
                    </div>
                </div>
            </div>

            <!-- Role Branding -->
            <div class="form-section" id="section-roles" data-nav-section>
                <div class="section-header" onclick="toggleSection(this)">
                    <h3 class="section-title">Role Branding</h3>
                    <button type="button" class="preview-toggle" onclick="event.stopPropagation(); showPreview('roles')">
                        Preview
                    </button>
                </div>
                
                <div class="section-inputs">
                <div class="form-group">
                    <label class="form-label">Student Badge Background</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="role_student_bg" name="role_student_bg" value="{{ old('role_student_bg', $settings->role_student_bg ?? '#dbeafe') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('role_student_bg', $settings->role_student_bg ?? '#dbeafe') }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Student Badge Text</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="role_student_text" name="role_student_text" value="{{ old('role_student_text', $settings->role_student_text ?? '#1e40af') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('role_student_text', $settings->role_student_text ?? '#1e40af') }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Instructor Badge Background</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="role_instructor_bg" name="role_instructor_bg" value="{{ old('role_instructor_bg', $settings->role_instructor_bg ?? '#e0f2fe') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('role_instructor_bg', $settings->role_instructor_bg ?? '#e0f2fe') }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Instructor Badge Text</label>
                    <div class="color-input-group">
                        <input type="color" class="color-picker" id="role_instructor_text" name="role_instructor_text" value="{{ old('role_instructor_text', $settings->role_instructor_text ?? '#0369a1') }}" onchange="updatePreview()">
                        <input type="text" class="color-text" value="{{ old('role_instructor_text', $settings->role_instructor_text ?? '#0369a1') }}" readonly>
                    </div>
                </div>
                </div>

                <!-- Preview Area for Role Badges -->
                <div class="preview-area" id="preview-roles">
                    <div class="preview-header">
                        <h4>Role Badges Preview</h4>
                        <button type="button" class="preview-close" onclick="closePreview('roles')">Close</button>
                    </div>
                    <div class="preview-content">
                        <div class="badge-preview-row">
                            <span class="preview-badge" id="role-student-preview">Student</span>
                            <span class="preview-badge" id="role-instructor-preview">Instructor</span>
                        </div>
                        <p class="badge-preview-note">These colors are used for user roles in tables and profiles</p>
                    </div>
                </div>
            </div>

            </section>

            <!-- Login/Signup Header Customization -->
            <section id="section-auth" class="settings-group" data-nav-section>
            <div class="settings-group-header" onclick="toggleSettingsGroup(this)" role="button" aria-expanded="true" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleSettingsGroup(this)}">
                <div class="settings-group-header-content">
                    <h2 class="settings-group-title">Login & Signup</h2>
                    <p class="settings-group-note">Header layout, welcome copy, and page background customization.</p>
                </div>
                <span class="settings-group-toggle" aria-hidden="true"></span>
            </div>
            <div class="form-section" id="section-login-header" data-nav-section>
                <div class="section-header" onclick="toggleSection(this)">
                    <h3 class="section-title">Login/Signup Header Customization</h3>
                </div>
                
                <div class="section-inputs">
                    <p class="login-intro-text">
                        Customize the header appearance on your login and registration pages. Changes apply to both pages.
                    </p>

                    <!-- Layout Selection -->
                    <div class="form-group">
                        <label class="form-label">Header Layout</label>
                        <select class="form-control form-control-compact" name="login_header_layout">
                            <option value="horizontal" {{ old('login_header_layout', $settings->login_header_layout ?? 'horizontal') == 'horizontal' ? 'selected' : '' }}>
                                Horizontal - Left, center, and right sections
                            </option>
                            <option value="vertical" {{ old('login_header_layout', $settings->login_header_layout ?? 'horizontal') == 'vertical' ? 'selected' : '' }}>
                                Vertical - Stacked elements
                            </option>
                            <option value="centered" {{ old('login_header_layout', $settings->login_header_layout ?? 'horizontal') == 'centered' ? 'selected' : '' }}>
                                Centered - All elements centered
                            </option>
                            <option value="logo-only" {{ old('login_header_layout', $settings->login_header_layout ?? 'horizontal') == 'logo-only' ? 'selected' : '' }}>
                                Logo Only - Minimal header with just logo/name
                            </option>
                        </select>
                    </div>

                    <!-- Logo Settings -->
                    <div class="form-group">
                        <label class="form-label">Logo Image Path (optional)</label>
                        <input type="text" class="form-control" name="login_logo_image" value="{{ old('login_logo_image', $settings->login_logo_image ?? '') }}" placeholder="e.g., logos/school-logo.png">
                        <small class="text-muted">Upload logo to storage/app/public/ folder first</small>
                    </div>

                    <div class="form-row form-row-two">
                        <div class="form-group">
                            <label class="form-label">Logo Position</label>
                            <select class="form-control form-control-compact" name="login_logo_position">
                                <option value="left" {{ old('login_logo_position', $settings->login_logo_position ?? 'left') == 'left' ? 'selected' : '' }}>Left</option>
                                <option value="center" {{ old('login_logo_position', $settings->login_logo_position ?? 'left') == 'center' ? 'selected' : '' }}>Center</option>
                                <option value="right" {{ old('login_logo_position', $settings->login_logo_position ?? 'left') == 'right' ? 'selected' : '' }}>Right</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Logo Size (px)</label>
                            <input type="number" class="form-control" name="login_logo_size" value="{{ old('login_logo_size', $settings->login_logo_size ?? 40) }}" min="20" max="100">
                        </div>
                    </div>

                    <!-- School Name Settings -->
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="login_show_school_name" value="1" {{ old('login_show_school_name', $settings->login_show_school_name ?? true) ? 'checked' : '' }} class="checkbox-inline">
                            Show School Name
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Custom School Name Text (optional)</label>
                        <input type="text" class="form-control" name="login_school_name_text" value="{{ old('login_school_name_text', $settings->login_school_name_text ?? '') }}" placeholder="Leave empty to use {{ $schoolName }}">
                    </div>

                    <div class="form-row form-row-two">
                        <div class="form-group">
                            <label class="form-label">School Name Position</label>
                            <select class="form-control form-control-compact" name="login_school_name_position">
                                <option value="left" {{ old('login_school_name_position', $settings->login_school_name_position ?? 'left') == 'left' ? 'selected' : '' }}>Left</option>
                                <option value="center" {{ old('login_school_name_position', $settings->login_school_name_position ?? 'left') == 'center' ? 'selected' : '' }}>Center</option>
                                <option value="right" {{ old('login_school_name_position', $settings->login_school_name_position ?? 'left') == 'right' ? 'selected' : '' }}>Right</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">School Name Size (px)</label>
                            <input type="number" class="form-control" name="login_school_name_size" value="{{ old('login_school_name_size', $settings->login_school_name_size ?? 24) }}" min="16" max="48">
                        </div>
                    </div>

                    <!-- Welcome Text Settings -->
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="login_show_welcome_text" value="1" {{ old('login_show_welcome_text', $settings->login_show_welcome_text ?? true) ? 'checked' : '' }} class="checkbox-inline">
                            Show Welcome Text
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Login Page Welcome Text</label>
                        <input type="text" class="form-control" name="login_welcome_text" value="{{ old('login_welcome_text', $settings->login_welcome_text ?? 'Welcome!') }}" placeholder="e.g., Welcome to {{ $schoolName }}!">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Registration Page Welcome Text</label>
                        <input type="text" class="form-control" name="register_welcome_text" value="{{ old('register_welcome_text', $settings->register_welcome_text ?? 'Student Registration') }}" placeholder="e.g., Join {{ $schoolName }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Registration Subtitle (optional)</label>
                        <input type="text" class="form-control" name="register_subtitle_text" value="{{ old('register_subtitle_text', $settings->register_subtitle_text ?? '') }}" placeholder="e.g., Start your driving journey today!">
                    </div>

                    <div class="form-row form-row-two">
                        <div class="form-group">
                            <label class="form-label">Welcome Text Position</label>
                            <select class="form-control form-control-compact" name="login_welcome_position">
                                <option value="left" {{ old('login_welcome_position', $settings->login_welcome_position ?? 'right') == 'left' ? 'selected' : '' }}>Left</option>
                                <option value="center" {{ old('login_welcome_position', $settings->login_welcome_position ?? 'right') == 'center' ? 'selected' : '' }}>Center</option>
                                <option value="right" {{ old('login_welcome_position', $settings->login_welcome_position ?? 'right') == 'right' ? 'selected' : '' }}>Right</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Welcome Text Size (px)</label>
                            <input type="number" class="form-control" name="login_welcome_size" value="{{ old('login_welcome_size', $settings->login_welcome_size ?? 16) }}" min="12" max="32">
                        </div>
                    </div>

                    <!-- Header Background -->
                    <div class="form-group">
                        <label class="form-label">Header Background Type</label>
                        <select class="form-control form-control-compact" name="login_header_bg_type">
                            <option value="gradient" {{ old('login_header_bg_type', $settings->login_header_bg_type ?? 'gradient') == 'gradient' ? 'selected' : '' }}>
                                Gradient (uses primary & secondary colors)
                            </option>
                            <option value="solid" {{ old('login_header_bg_type', $settings->login_header_bg_type ?? 'gradient') == 'solid' ? 'selected' : '' }}>
                                Solid Color
                            </option>
                            <option value="image" {{ old('login_header_bg_type', $settings->login_header_bg_type ?? 'gradient') == 'image' ? 'selected' : '' }}>
                                Background Image
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Solid Background Color (if selected above)</label>
                        <input type="color" class="color-input" name="login_header_bg_color" value="{{ old('login_header_bg_color', $settings->login_header_bg_color ?? '#2563eb') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Background Image Path (if selected above)</label>
                        <input type="text" class="form-control" name="login_header_bg_image" value="{{ old('login_header_bg_image', $settings->login_header_bg_image ?? '') }}" placeholder="e.g., backgrounds/login-header.jpg">
                        <small class="text-muted">Upload image to storage/app/public/ folder first</small>
                    </div>

                    <div class="form-row form-row-two">
                        <div class="form-group">
                            <label class="form-label">Header Height (px)</label>
                            <input type="number" class="form-control" name="login_header_height" value="{{ old('login_header_height', $settings->login_header_height ?? 60) }}" min="50" max="200">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Header Text Color</label>
                            <input type="color" class="color-input" name="login_header_text_color" value="{{ old('login_header_text_color', $settings->login_header_text_color ?? '#ffffff') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="login_header_shadow" value="1" {{ old('login_header_shadow', $settings->login_header_shadow ?? true) ? 'checked' : '' }} class="checkbox-inline">
                            Enable Header Shadow
                        </label>
                    </div>

                    <div class="login-preview-note">
                        <strong class="login-preview-note-title">Preview Your Changes</strong>
                        <p class="login-preview-note-text">
                            Visit your login page at <code class="login-preview-note-code">/{{ $school->slug }}/login</code> to see your customizations in action!
                        </p>
                    </div>

                    <!-- Login/Signup Page Background Customization -->
                    <div class="login-subsection" id="section-login-background" data-nav-section>
                        <div class="section-header" onclick="toggleSection(this)">
                            <h3 class="section-title">Login/Signup Page Background</h3>
                        </div>

                        <div class="section-inputs">
                            <div class="form-group">
                                <label class="form-label">Background Type</label>
                                <select name="login_page_bg_type" class="form-select" id="loginBgType" onchange="toggleLoginBgOptions()">
                                    <option value="color" {{ old('login_page_bg_type', $settings->login_page_bg_type ?? 'color') === 'color' ? 'selected' : '' }}>Solid Color</option>
                                    <option value="image" {{ old('login_page_bg_type', $settings->login_page_bg_type ?? 'color') === 'image' ? 'selected' : '' }}>Background Image</option>
                                </select>
                            </div>

                            <div class="form-group" id="loginBgColorGroup">
                                <label class="form-label">Background Color</label>
                                <div class="color-input-group">
                                    <input type="color" class="color-picker" name="login_page_bg_color" value="{{ old('login_page_bg_color', $settings->login_page_bg_color ?? '#f5f5f5') }}">
                                    <input type="text" class="color-text" value="{{ old('login_page_bg_color', $settings->login_page_bg_color ?? '#f5f5f5') }}" readonly>
                                </div>
                            </div>

                            <div class="form-group bg-image-group-hidden" id="loginBgImageGroup">
                                <label class="form-label">Background Image</label>
                                <input type="file" class="form-control" name="login_page_bg_image" accept="image/*">
                                @error('login_page_bg_image')
                                    <small class="text-danger help-text-block">{{ $message }}</small>
                                @enderror
                                @if($settings && $settings->login_page_bg_image)
                                    <div class="current-bg-wrap">
                                        <img src="{{ asset('storage/' . $settings->login_page_bg_image) }}" class="current-bg-image">
                                        <p class="current-bg-caption">Current background image</p>
                                    </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label class="form-label">Background Opacity (%)</label>
                                <div class="opacity-control-row">
                                    <input type="range" class="form-range opacity-range" name="login_page_bg_opacity" value="{{ old('login_page_bg_opacity', $settings->login_page_bg_opacity ?? 100) }}" min="0" max="100" oninput="this.nextElementSibling.textContent = this.value + '%'">
                                    <span class="opacity-value">{{ old('login_page_bg_opacity', $settings->login_page_bg_opacity ?? 100) }}%</span>
                                </div>
                                <small class="opacity-help">Lower values make the background more transparent</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </section>

            <div class="floating-actions">
                <button type="submit" class="save-button">Save Changes</button>
                <button type="button" class="reset-button" onclick="resetToDefaults()">↺ Reset to Defaults</button>
            </div>
        </form>
    </div>
</div>

<script>
var settingsCurrentPreview = null;
var settingsFormDirty = false;
var settingsNavScrollTicking = false;
var settingsNavigationBound = false;
var settingsNavPinStart = 0;
var settingsNavMaxTransparency = 0.6;
var settingsNavControlMaxTransparency = 0.3;
var settingsNavFadeDistance = 220;

function setSettingsDirty(isDirty) {
    settingsFormDirty = !!isDirty;
    const indicator = document.getElementById('settingsDirtyIndicator');
    if (indicator) {
        indicator.hidden = !settingsFormDirty;
    }
}

function updateSettingsStickyTopOffset() {
    const navigation = document.getElementById('settingsNavigation');
    if (!navigation) {
        return;
    }

    const topbar = document.querySelector('.topbar');
    const breadcrumb = document.getElementById('breadcrumbBar');
    const topbarBottom = topbar ? topbar.getBoundingClientRect().bottom : 0;
    const breadcrumbBottom = breadcrumb ? breadcrumb.getBoundingClientRect().bottom : 0;
    const fixedStackBottom = Math.max(topbarBottom, breadcrumbBottom);
    const stickyTop = Math.max(8, Math.ceil(fixedStackBottom + 8));

    navigation.style.setProperty('--settings-sticky-top', stickyTop + 'px');
}

function measureSettingsNavigationLayout() {
    const navigation = document.getElementById('settingsNavigation');
    const spacer = document.getElementById('settingsNavSpacer');
    if (!navigation || !spacer) {
        return;
    }

    const wasPinned = navigation.classList.contains('is-pinned');
    if (wasPinned) {
        navigation.classList.remove('is-pinned');
        spacer.classList.remove('active');
        spacer.style.height = '0px';
    }

    const rect = navigation.getBoundingClientRect();
    settingsNavPinStart = rect.top + window.pageYOffset;
    navigation.style.setProperty('--settings-nav-left', rect.left + 'px');
    navigation.style.setProperty('--settings-nav-width', rect.width + 'px');

    if (wasPinned) {
        navigation.classList.add('is-pinned');
        spacer.classList.add('active');
        spacer.style.height = navigation.offsetHeight + 'px';
        updatePinnedSettingsNavigationGeometry();
    }
}

function updatePinnedSettingsNavigationGeometry() {
    const navigation = document.getElementById('settingsNavigation');
    const spacer = document.getElementById('settingsNavSpacer');
    if (!navigation || !spacer || !navigation.classList.contains('is-pinned')) {
        return;
    }

    const referenceRect = spacer.getBoundingClientRect();
    navigation.style.setProperty('--settings-nav-left', referenceRect.left + 'px');
    navigation.style.setProperty('--settings-nav-width', referenceRect.width + 'px');
}

function setSettingsNavigationPinned(shouldPin) {
    const navigation = document.getElementById('settingsNavigation');
    const spacer = document.getElementById('settingsNavSpacer');
    if (!navigation || !spacer) {
        return;
    }

    if (shouldPin === navigation.classList.contains('is-pinned')) {
        if (shouldPin) {
            updatePinnedSettingsNavigationGeometry();
        }
        return;
    }

    navigation.classList.toggle('is-pinned', shouldPin);
    spacer.classList.toggle('active', shouldPin);
    spacer.style.height = shouldPin ? (navigation.offsetHeight + 'px') : '0px';

    if (shouldPin) {
        updatePinnedSettingsNavigationGeometry();
    }
}

function updateSettingsNavigationPinState() {
    const navigation = document.getElementById('settingsNavigation');
    if (!navigation) {
        return;
    }

    const stickyTop = parseInt(window.getComputedStyle(navigation).getPropertyValue('--settings-sticky-top'), 10) || 104;
    const shouldPin = (window.pageYOffset + stickyTop) >= settingsNavPinStart;
    setSettingsNavigationPinned(shouldPin);
}

function updateSettingsNavigationTransparency() {
    const navigation = document.getElementById('settingsNavigation');
    if (!navigation) {
        return;
    }

    const scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
    const fadeProgress = Math.min(1, Math.max(0, scrollY / settingsNavFadeDistance));
    const navOpacity = 1 - (settingsNavMaxTransparency * fadeProgress);
    const controlOpacity = 1 - (settingsNavControlMaxTransparency * fadeProgress);
    navigation.style.setProperty('--settings-nav-opacity', navOpacity.toFixed(3));
    navigation.style.setProperty('--settings-nav-control-opacity', controlOpacity.toFixed(3));
}

function getSettingsNavOffset() {
    const navigation = document.getElementById('settingsNavigation');
    const navHeight = navigation ? navigation.offsetHeight : 0;
    const stickyTop = navigation
        ? (parseInt(window.getComputedStyle(navigation).getPropertyValue('--settings-sticky-top'), 10) || 0)
        : 0;
    return stickyTop + navHeight + 12;
}

function setActiveSettingsNavLink(sectionId) {
    document.querySelectorAll('[data-nav-link]').forEach(link => {
        link.classList.toggle('active', link.dataset.sectionId === sectionId);
    });
}

function scrollToSettingsSection(sectionId, behavior = 'smooth') {
    ensureSettingsGroupExpandedForSection(sectionId);

    const target = document.getElementById(sectionId);
    if (!target) {
        return;
    }

    const targetTop = target.getBoundingClientRect().top + window.pageYOffset;
    const scrollTo = Math.max(0, targetTop - getSettingsNavOffset());
    window.scrollTo({ top: scrollTo, behavior: behavior });
}

function updateSettingsNavFromScroll() {
    const navLinks = Array.from(document.querySelectorAll('[data-nav-link]'));
    if (!navLinks.length) {
        return;
    }

    const offset = window.pageYOffset + getSettingsNavOffset() + 12;
    let activeSectionId = navLinks[0].dataset.sectionId;

    navLinks.forEach(link => {
        const sectionId = link.dataset.sectionId;
        const section = document.getElementById(sectionId);
        if (!section) {
            return;
        }

        const sectionTop = section.getBoundingClientRect().top + window.pageYOffset;
        if (offset >= sectionTop) {
            activeSectionId = sectionId;
        }
    });

    setActiveSettingsNavLink(activeSectionId);
}

function handleSettingsNavScroll() {
    if (settingsNavScrollTicking) {
        return;
    }

    settingsNavScrollTicking = true;
    window.requestAnimationFrame(function() {
        updateSettingsNavigationTransparency();
        updateSettingsNavigationPinState();
        updateSettingsNavFromScroll();
        settingsNavScrollTicking = false;
    });
}

function jumpToSettingsSectionFromSearch() {
    const searchInput = document.getElementById('settingsNavSearch');
    if (!searchInput) {
        return;
    }

    const query = searchInput.value.trim().toLowerCase();
    if (!query) {
        return;
    }

    const navLinks = Array.from(document.querySelectorAll('[data-nav-link]'));
    let match = navLinks.find(link => link.textContent.toLowerCase().includes(query));

    if (!match) {
        match = navLinks.find(link => {
            const target = document.getElementById(link.dataset.sectionId);
            return target && target.textContent.toLowerCase().includes(query);
        });
    }

    if (match) {
        const sectionId = match.dataset.sectionId;
        scrollToSettingsSection(sectionId);
        setActiveSettingsNavLink(sectionId);
        return;
    }

    if (window.Toast && typeof window.Toast.info === 'function') {
        window.Toast.info('No matching settings section found.');
    }
}

function toggleSettingsGroup(header, forceExpanded = null) {
    const group = header ? header.closest('.settings-group') : null;
    if (!group) {
        return;
    }

    const expand = forceExpanded === null
        ? group.classList.contains('collapsed')
        : !!forceExpanded;

    group.classList.toggle('collapsed', !expand);
    header.setAttribute('aria-expanded', expand ? 'true' : 'false');
}

function ensureSettingsGroupExpandedForSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (!section) {
        return;
    }

    const group = section.classList.contains('settings-group') ? section : section.closest('.settings-group');
    if (!group || !group.classList.contains('collapsed')) {
        return;
    }

    const groupHeader = group.querySelector(':scope > .settings-group-header');
    if (groupHeader) {
        toggleSettingsGroup(groupHeader, true);
    } else {
        group.classList.remove('collapsed');
    }
}

function expandAllSettingsSections() {
    document.querySelectorAll('.settings-group .settings-group-header').forEach(header => {
        toggleSettingsGroup(header, true);
    });

    document.querySelectorAll('.form-section .section-inputs, .operations-subsection .section-inputs, .login-subsection .section-inputs').forEach(inputs => {
        inputs.classList.remove('collapsed');
    });
}

function collapseAllSettingsSections() {
    document.querySelectorAll('.settings-group .settings-group-header').forEach(header => {
        toggleSettingsGroup(header, false);
    });

    document.querySelectorAll('.form-section .section-inputs, .operations-subsection .section-inputs, .login-subsection .section-inputs').forEach(inputs => {
        inputs.classList.add('collapsed');
    });
}

function initializeSettingsNavigation() {
    updateSettingsStickyTopOffset();
    measureSettingsNavigationLayout();

    const navLinks = document.querySelectorAll('[data-nav-link]');
    navLinks.forEach(link => {
        if (link.dataset.navBound === '1') {
            return;
        }

        link.dataset.navBound = '1';
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const sectionId = this.dataset.sectionId;
            if (!sectionId) {
                return;
            }

            scrollToSettingsSection(sectionId);
            setActiveSettingsNavLink(sectionId);
        });
    });

    const searchInput = document.getElementById('settingsNavSearch');
    const searchBtn = document.getElementById('settingsNavSearchBtn');

    if (searchBtn && searchBtn.dataset.navBound !== '1') {
        searchBtn.dataset.navBound = '1';
        searchBtn.addEventListener('click', jumpToSettingsSectionFromSearch);
    }

    if (searchInput && searchInput.dataset.navBound !== '1') {
        searchInput.dataset.navBound = '1';
        searchInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                jumpToSettingsSectionFromSearch();
            }
        });
    }

    if (!settingsNavigationBound) {
        settingsNavigationBound = true;
        window.addEventListener('scroll', handleSettingsNavScroll, { passive: true });
        window.addEventListener('resize', function() {
            updateSettingsStickyTopOffset();
            measureSettingsNavigationLayout();
            handleSettingsNavScroll();
        });
    }

    updateSettingsNavigationTransparency();
    updateSettingsNavigationPinState();
    updateSettingsNavFromScroll();
}

function initializeSettingsDirtyTracking() {
    const settingsForm = document.getElementById('settingsForm');
    if (!settingsForm || settingsForm.dataset.dirtyBound === '1') {
        return;
    }

    settingsForm.dataset.dirtyBound = '1';
    const markDirty = function() { setSettingsDirty(true); };

    settingsForm.addEventListener('input', markDirty);
    settingsForm.addEventListener('change', markDirty);
    settingsForm.addEventListener('submit', function() {
        setSettingsDirty(false);
    });
}

function initializeSettingsPageUI() {
    toggleLoginBgOptions();
    toggleBackgroundOptions();
    initializeSettingsNavigation();
    initializeSettingsDirtyTracking();
}

// Toggle login background options based on type
function toggleLoginBgOptions() {
    const bgTypeSelect = document.getElementById('loginBgType');
    const bgColorGroup = document.getElementById('loginBgColorGroup');
    const bgImageGroup = document.getElementById('loginBgImageGroup');
    if (!bgTypeSelect || !bgColorGroup || !bgImageGroup) {
        return;
    }

    const bgType = bgTypeSelect.value;
    bgColorGroup.style.display = bgType === 'color' ? 'block' : 'none';
    bgImageGroup.style.display = bgType === 'image' ? 'block' : 'none';
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeSettingsPageUI);
} else {
    initializeSettingsPageUI();
}

function submitInvitationAction(actionUrl, method, confirmMessage = null) {
    if (confirmMessage && !confirm(confirmMessage)) {
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = actionUrl;
    form.classList.add('native-form');
    form.setAttribute('data-no-ajax', '1');
    form.setAttribute('data-no-submit-guard', '1');

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);

    if (method && method.toUpperCase() !== 'POST') {
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = method.toUpperCase();
        form.appendChild(methodInput);
    }

    document.body.appendChild(form);
    form.submit();
}

// Toggle section collapse
function toggleSection(header) {
    const section = header.parentElement;
    const inputs = section.querySelector('.section-inputs');
    inputs.classList.toggle('collapsed');
}

// Show specific preview
function showPreview(type) {
    // Close any other open previews
    document.querySelectorAll('.preview-area').forEach(area => {
        if (area.id !== `preview-${type}`) {
            area.classList.remove('active');
        }
    });
    
    // Toggle the selected preview
    const previewArea = document.getElementById(`preview-${type}`);
    const toggleButton = event.target;
    
    if (previewArea.classList.contains('active')) {
        previewArea.classList.remove('active');
        toggleButton.classList.remove('active');
        settingsCurrentPreview = null;
    } else {
        previewArea.classList.add('active');
        toggleButton.classList.add('active');
        settingsCurrentPreview = type;
        updatePreview();
    }
    
    // Remove active state from other toggle buttons
    document.querySelectorAll('.preview-toggle').forEach(btn => {
        if (btn !== toggleButton) {
            btn.classList.remove('active');
        }
    });
}

function closePreview(type) {
    const previewArea = document.getElementById(`preview-${type}`);
    previewArea.classList.remove('active');
    
    document.querySelectorAll('.preview-toggle').forEach(btn => {
        btn.classList.remove('active');
    });
    
    settingsCurrentPreview = null;
}

// Update text input when color picker changes
document.querySelectorAll('.color-picker').forEach(picker => {
    picker.addEventListener('input', function() {
        this.nextElementSibling.value = this.value;
        updatePreview();
    });
});

// Update preview in real-time
function updatePreview() {
    if (!settingsCurrentPreview) return;
    
    // Background
    const backgroundType = document.getElementById('background_type').value;
    const backgroundColor = document.getElementById('background_color').value;
    const backgroundOpacity = document.getElementById('background_opacity').value / 100;
    const backgroundPreview = document.getElementById('background-preview');
    
    if (backgroundPreview) {
        if (backgroundType === 'color') {
            const rgb = hexToRgb(backgroundColor);
            backgroundPreview.style.backgroundColor = `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${backgroundOpacity})`;
            backgroundPreview.style.backgroundImage = 'none';
        } else {
            const fileInput = document.querySelector('input[name="background_image"]');
            if (fileInput && fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    backgroundPreview.style.backgroundImage = `url(${e.target.result})`;
                    backgroundPreview.style.backgroundSize = 'cover';
                    backgroundPreview.style.backgroundPosition = 'center';
                    backgroundPreview.style.opacity = backgroundOpacity;
                };
                reader.readAsDataURL(fileInput.files[0]);
            }
        }
    }
    
    // Sidebar
    const sidebarBg = document.getElementById('sidebar_bg_color').value;
    const sidebarText = document.getElementById('sidebar_text_color').value;
    const sidebarHover = document.getElementById('sidebar_hover_color').value;
    const sidebarPreview = document.getElementById('sidebar-preview');
    
    if (sidebarPreview) {
        sidebarPreview.style.backgroundColor = sidebarBg;
        document.querySelectorAll('.sidebar-item').forEach(item => {
            item.style.color = sidebarText;
            item.onmouseenter = () => item.style.backgroundColor = sidebarHover;
            item.onmouseleave = () => item.style.backgroundColor = 'transparent';
        });
    }

    // Buttons
    const buttonBorderRadius = document.getElementById('button_border_radius').value + 'px';
    const buttonStyle = document.getElementById('button_style').value;
    const buttonPrimary = document.getElementById('button_primary_bg').value;
    const buttonSecondary = document.getElementById('button_secondary_bg').value;
    const buttonSuccess = document.getElementById('button_success_bg').value;
    const buttonDanger = document.getElementById('button_danger_bg').value;
    
    if (document.getElementById('button-primary-preview')) {
        const primaryBg = buttonStyle === 'gradient' 
            ? `linear-gradient(135deg, ${buttonPrimary} 0%, ${buttonSecondary} 100%)`
            : buttonPrimary;
        
        document.getElementById('button-primary-preview').style.cssText = `background: ${primaryBg}; color: white; border-radius: ${buttonBorderRadius}; padding: 10px 20px; border: none; font-weight: 600;`;
        document.getElementById('button-secondary-preview').style.cssText = `background: ${buttonSecondary}; color: white; border-radius: ${buttonBorderRadius}; padding: 10px 20px; border: none; font-weight: 600;`;
        document.getElementById('button-success-preview').style.cssText = `background: ${buttonSuccess}; color: white; border-radius: ${buttonBorderRadius}; padding: 10px 20px; border: none; font-weight: 600;`;
        document.getElementById('button-danger-preview').style.cssText = `background: ${buttonDanger}; color: white; border-radius: ${buttonBorderRadius}; padding: 10px 20px; border: none; font-weight: 600;`;
    }

    // Modal
    const modalHeaderBg = document.getElementById('modal_header_bg').value;
    const modalHeaderText = document.getElementById('modal_header_text').value;
    
    if (document.getElementById('modal-header-preview')) {
        document.getElementById('modal-header-preview').style.cssText = `background: ${modalHeaderBg}; color: ${modalHeaderText}; padding: 20px;`;
    }

    // Calendar & Header
    const primaryColor = document.getElementById('primary_color').value;
    const secondaryColor = document.getElementById('secondary_color').value;
    const useGradient = document.getElementById('use_gradient_header').value === '1';
    const headerTextColor = document.getElementById('header_text_color').value;
    const calendarDayBorder = document.getElementById('calendar_day_border').value;
    const calendarDayHover = document.getElementById('calendar_day_hover').value;
    const calendarTodayColor = document.getElementById('calendar_today_color').value;
    
    if (document.getElementById('calendar-header-preview')) {
        const headerBg = useGradient 
            ? `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`
            : primaryColor;
        
        document.getElementById('calendar-header-preview').style.cssText = `background: ${headerBg}; color: ${headerTextColor}; padding: 20px 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);`;
        
        // Update nav buttons
        document.querySelectorAll('.calendar-nav-btn-preview').forEach(btn => {
            btn.style.color = headerTextColor;
            btn.style.borderColor = `rgba(${headerTextColor === '#ffffff' || headerTextColor === '#fff' ? '255, 255, 255' : '0, 0, 0'}, 0.25)`;
            btn.style.background = `rgba(${headerTextColor === '#ffffff' || headerTextColor === '#fff' ? '255, 255, 255' : '0, 0, 0'}, 0.15)`;
        });
        
        // Update month text
        document.querySelector('.calendar-month-preview').style.color = headerTextColor;
    }
    
    // Update calendar day styles
    if (document.querySelectorAll('.calendar-day-preview').length > 0) {
        document.querySelectorAll('.calendar-day-preview').forEach(day => {
            day.style.border = `1px solid ${calendarDayBorder}`;
            
            // Add hover effect
            day.onmouseenter = () => {
                day.style.borderColor = calendarDayHover;
                day.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.08)';
                day.style.transform = 'translateY(-2px)';
            };
            day.onmouseleave = () => {
                if (!day.classList.contains('today-preview')) {
                    day.style.borderColor = calendarDayBorder;
                    day.style.boxShadow = 'none';
                    day.style.transform = 'translateY(0)';
                } else {
                    day.style.borderColor = calendarTodayColor;
                }
            };
        });
        
        // Update today preview
        const todayPreview = document.getElementById('calendar-today-preview');
        if (todayPreview) {
            todayPreview.style.borderColor = calendarTodayColor;
        }
    }

    // Badges
    const badgePending = document.getElementById('badge_pending_bg').value;
    const badgeApproved = document.getElementById('badge_approved_bg').value;
    const badgeCancelled = document.getElementById('badge_cancelled_bg').value;
    
    if (document.getElementById('badge-pending-preview')) {
        document.getElementById('badge-pending-preview').style.backgroundColor = badgePending;
        document.getElementById('badge-approved-preview').style.backgroundColor = badgeApproved;
        document.getElementById('badge-cancelled-preview').style.backgroundColor = badgeCancelled;
    }

    // Role Badges
    const roleStudentBg = document.getElementById('role_student_bg').value;
    const roleStudentText = document.getElementById('role_student_text').value;
    const roleInstructorBg = document.getElementById('role_instructor_bg').value;
    const roleInstructorText = document.getElementById('role_instructor_text').value;

    if (document.getElementById('role-student-preview')) {
        document.getElementById('role-student-preview').style.backgroundColor = roleStudentBg;
        document.getElementById('role-student-preview').style.color = roleStudentText;
        document.getElementById('role-instructor-preview').style.backgroundColor = roleInstructorBg;
        document.getElementById('role-instructor-preview').style.color = roleInstructorText;
    }
}

function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : { r: 245, g: 245, b: 245 };
}

function toggleBackgroundOptions() {
    const type = document.getElementById('background_type').value;
    const colorGroup = document.getElementById('background_color_group');
    const imageGroup = document.getElementById('background_image_group');
    
    if (type === 'color') {
        colorGroup.style.display = 'block';
        imageGroup.style.display = 'none';
    } else {
        colorGroup.style.display = 'none';
        imageGroup.style.display = 'block';
    }
    
    updatePreview();
}

function toggleButtonGradient() {
    const style = document.getElementById('button_style').value;
    // Just update the preview, the actual change happens on form submit
    updatePreview();
}

function updateOpacityValue() {
    const opacityValue = document.getElementById('background_opacity').value;
    document.getElementById('opacity_value').textContent = opacityValue + '%';
}

function previewBackgroundImage(event) {
    updatePreview();
}

function resetToDefaults() {
    showConfirm({
        type: 'warning',
        title: 'Reset Settings',
        message: 'Are you sure you want to reset all settings to their default values? This cannot be undone.',
        confirmText: 'Reset',
        onConfirm: function() {
            document.getElementById('primary_color').value = '#667eea';
            document.getElementById('secondary_color').value = '#764ba2';
            document.getElementById('accent_color').value = '#5568d3';
            document.getElementById('sidebar_bg_color').value = '#ffffff';
            document.getElementById('sidebar_text_color').value = '#333333';
            document.getElementById('sidebar_hover_color').value = '#f5f5f5';
            document.getElementById('button_style').value = 'solid';
            document.getElementById('button_primary_bg').value = '#667eea';
            document.getElementById('button_secondary_bg').value = '#6c757d';
            document.getElementById('button_success_bg').value = '#28a745';
            document.getElementById('button_danger_bg').value = '#dc3545';
            document.getElementById('button_border_radius').value = '8';
            document.getElementById('modal_header_bg').value = '#667eea';
            document.getElementById('modal_header_text').value = '#ffffff';
            document.getElementById('badge_pending_bg').value = '#fbbf24';
            document.getElementById('badge_approved_bg').value = '#10b981';
            document.getElementById('badge_cancelled_bg').value = '#ef4444';
            document.getElementById('role_student_bg').value = '#dbeafe';
            document.getElementById('role_student_text').value = '#1e40af';
            document.getElementById('role_instructor_bg').value = '#e0f2fe';
            document.getElementById('role_instructor_text').value = '#0369a1';
            document.getElementById('border_radius').value = '8';
            
            // Update text inputs
            document.querySelectorAll('.color-picker').forEach(picker => {
                picker.nextElementSibling.value = picker.value;
            });
            
            if (settingsCurrentPreview) {
                updatePreview();
            }

            setSettingsDirty(true);
            
            Toast.success('Settings have been reset to defaults');
        }
    });
}

    function addLicenseStep() {
        const container = document.getElementById('licenseStepsContainer');
        const noStepsText = container.querySelector('.no-steps-text');
        if (noStepsText) noStepsText.remove();
        
        const index = container.querySelectorAll('.license-step-row').length;
        const row = document.createElement('div');
        row.className = 'license-step-row';
        row.style = 'margin-bottom: 20px; padding: 18px; background: white; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);';
        
        row.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h5 style="margin: 0; font-size: 0.9rem; font-weight: 700; color: var(--primary-color); display: flex; align-items: center; gap: 8px;">
                    <span class="step-index-badge" style="background: #e0e7ff; color: #4338ca; width: 22px; height: 22px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">${index + 1}</span>
                    Step Configuration
                </h5>
                <button type="button" class="nav-action-btn" style="padding: 4px 10px; border-color: #fee2e2; color: #991b1b; background: #fff1f1; font-size: 0.75rem;" onclick="removeLicenseStep(this)">Remove</button>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 12px;">
                <div>
                    <label style="display: block; font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Title</label>
                    <input type="text" name="license_steps[${index}][title]" class="form-control" style="font-weight: 600;" placeholder="e.g., Theoretical Driving Course (TDC)" required>
                </div>
                <div>
                    <label style="display: block; font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Link to System Progress</label>
                    <select name="license_steps[${index}][milestone]" class="form-control" style="font-size: 0.85rem;">
                        <option value="none">Manual (No System Link)</option>
                        <option value="tdc">TDC Completion (Theoretical Course)</option>
                        <option value="permit">Student Permit Verified</option>
                        <option value="pdc">PDC Completion (Practical Course)</option>
                        <option value="license">Official License (Final Step)</option>
                    </select>
                </div>
            </div>
            <div>
                <label style="display: block; font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Description</label>
                <textarea name="license_steps[${index}][description]" class="form-control" rows="2" placeholder="Explain the requirements for this step..." required></textarea>
            </div>
        `;
        
        container.appendChild(row);
        markSettingsDirty();
    }

    function removeLicenseStep(btn) {
        const row = btn.closest('.license-step-row');
        const container = document.getElementById('licenseStepsContainer');
        row.remove();
        
        // Re-index steps
        const rows = container.querySelectorAll('.license-step-row');
        rows.forEach((r, i) => {
            r.querySelector('.step-index-badge').textContent = i + 1;
            const titleInput = r.querySelector('input[type="text"]');
            const milestoneSelect = r.querySelector('select');
            const descTextarea = r.querySelector('textarea');
            titleInput.name = `license_steps[${i}][title]`;
            milestoneSelect.name = `license_steps[${i}][milestone]`;
            descTextarea.name = `license_steps[${i}][description]`;
        });
        
        if (rows.length === 0) {
            container.innerHTML = `
                <div class="no-steps-text" style="padding: 40px; text-align: center; background: #fff; border: 2px dashed #e2e8f0; border-radius: 12px; color: #94a3b8;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="32" height="32" style="margin-bottom: 10px; opacity: 0.5;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <p style="margin: 0; font-weight: 500;">No roadmap steps configured.</p>
                </div>
            `;
        }
        
        markSettingsDirty();
    }

    function toggleRoadmapSection(header) {
        const body = header.nextElementSibling;
        const icon = header.querySelector('.roadmap-toggle-icon');
        const isCollapsed = body.style.display === 'none';
        
        body.style.display = isCollapsed ? 'block' : 'none';
        icon.style.transform = isCollapsed ? 'rotate(180deg)' : 'rotate(0deg)';
        header.style.borderRadius = isCollapsed ? '0' : '15px'; // Adjust border radius based on state
    }
</script>

@endsection
