@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Courses Management')

@section('content')
    @php
        $school = $school ?? $currentSchool ?? null;
        $schoolName = $school->name ?? 'Driving School';
        $settings = $school?->schoolSetting;
    @endphp

    @include('school.admin.partials.admin-styles')

    <style>
        .courses-container {
            padding: 20px;
            margin: 0 auto;
            max-width: 1400px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid
                {{ $settings->primary_color }}
            ;
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

        .btn-create {
            padding: 12px 24px;
            @if($settings?->use_gradient_header)
                background: linear-gradient(135deg,
                        {{ $settings->primary_color }}
                        0%,
                        {{ $settings->secondary_color }}
                        100%);
            @else background:
                {{ $settings->primary_color }}
                ;
            @endif color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px
                {{ $settings->primary_color }}
                40;
        }

        /* Courses Grid */
        .courses-grid {
            display: none;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 30px;
        }

        .courses-grid.active {
            display: grid;
        }

        .course-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
        }

        .course-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background:
                {{ $settings->primary_color }}
            ;
            border-radius: 50%;
            opacity: 0.05;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .course-card:hover::before {
            transform: scale(1.2);
            opacity: 0.08;
        }

        .course-banner {
            width: 100%;
            height: 220px;
            object-fit: cover;
            @if($settings?->use_gradient_header)
                background: linear-gradient(135deg,
                        {{ $settings->primary_color }}
                        0%,
                        {{ $settings->secondary_color }}
                        100%);
            @else background:
                {{ $settings->primary_color }}
                ;
            @endif display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .course-content {
            padding: 25px;
        }

        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .course-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .course-type {
            display: inline-block;
            padding: 5px 12px;
            background: #e2e8f0;
            color: #4a5568;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .course-description {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .course-features {
            list-style: none;
            margin-bottom: 15px;
        }

        .course-features li {
            padding: 6px 0;
            color: #4a5568;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .course-features li:before {
            content: "•";
            color: #10b981;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .packages-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
        }

        .packages-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .packages-title {
            font-weight: 700;
            color: #2d3748;
            font-size: 1.1rem;
        }

        .btn-add-package {
            padding: 6px 12px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-add-package:hover {
            background: #059669;
        }

        .package-item {
            background: #f8fafc;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .package-info {
            flex: 1;
        }

        .package-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
        }

        .package-details {
            font-size: 0.85rem;
            color: #64748b;
        }

        .package-price {
            font-size: 1.3rem;
            font-weight: 700;
            color:
                {{ $settings->primary_color }}
            ;
            margin-right: 15px;
        }

        .course-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }

        .btn-preview,
        .btn-edit,
        .btn-delete,
        .btn-package-edit,
        .btn-package-delete {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .btn-preview {
            background:
                {{ $settings->accent_color }}
            ;
            color: white;
        }

        .btn-preview:hover {
            background:
                {{ $settings->accent_color }}
            ;
            filter: brightness(0.9);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px
                {{ $settings->accent_color }}
                40;
        }

        .btn-edit {
            background: #3b82f6;
            color: white;
        }

        .btn-edit:hover {
            background: #2563eb;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .btn-package-edit {
            padding: 6px 12px;
            background: #3b82f6;
            color: white;
            font-size: 0.8rem;
        }

        .btn-package-delete {
            padding: 6px 12px;
            background: #ef4444;
            color: white;
            font-size: 0.8rem;
        }

        .status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .status-active {
            background: #10b981;
            color: white;
        }

        .status-inactive {
            background: #64748b;
            color: white;
        }

        .featured-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            padding: 8px 16px;
            background: #f59e0b;
            color: white;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .course-banner-wrap {
            position: relative;
        }

        .course-type-vehicle {
            background: #dbeafe;
            color: #1e40af;
        }

        .course-feature-more {
            color:
                {{ $settings->primary_color }}
            ;
            font-weight: 600;
        }

        .package-tag {
            font-size: 0.8rem;
            padding: 2px 8px;
            color: white;
            border-radius: 10px;
            margin-left: 5px;
        }

        .package-tag-vehicle {
            background:
                {{ $settings->accent_color }}
            ;
        }

        .package-tag-transmission-manual {
            background: #fbbf24;
        }

        .package-tag-transmission-auto {
            background: #3b82f6;
        }

        .package-tag-popular {
            font-size: 0.75rem;
            background: #f59e0b;
        }

        .package-actions {
            display: flex;
            gap: 5px;
        }

        /* Interactive Auto-Scroll Ticker */
        .packages-ticker-container {
            max-height: 280px;
            overflow: hidden;
            position: relative;
            mask-image: linear-gradient(to bottom, transparent, black 10%, black 90%, transparent);
            -webkit-mask-image: linear-gradient(to bottom, transparent, black 10%, black 90%, transparent);
            cursor: pointer;
        }

        .packages-ticker {
            padding: 10px 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .packages-ticker-container:hover .packages-ticker {
            animation-play-state: paused;
        }

        @keyframes ticker-scroll {
            0% {
                transform: translateY(0);
            }

            100% {
                transform: translateY(-50%);
            }
        }

        /* High-Fidelity Matrix Badges */
        .matrix-badge-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 15px;
        }

        .matrix-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .badge-lto {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .badge-license {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .badge-transmission {
            background: #e0f2fe;
            color: #075985;
            border: 1px solid #bae6fd;
        }

        .badge-category {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .matrix-badge svg {
            width: 14px;
            height: 14px;
            margin-right: 6px;
            opacity: 0.8;
        }

        /* Spacing Fixes */
        .status-badge,
        .featured-badge {
            top: 20px !important;
            padding: 10px 20px !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            z-index: 5;
        }

        .featured-badge {
            left: 20px !important;
        }

        .status-badge {
            right: 20px !important;
        }

        .btn-add-package-full {
            width: 100%;
        }

        .course-title-list {
            margin-bottom: 10px;
        }

        .course-list-meta {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
        }

        .status-badge-static {
            position: static;
        }

        .status-incomplete {
            background: #fff7ed !important;
            color: #c2410c !important;
            border: 2px solid #fdba74 !important;
            animation: pulse-warning 2s infinite;
        }

        @keyframes pulse-warning {
            0% {
                box-shadow: 0 0 0 0 rgba(251, 146, 60, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(251, 146, 60, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(251, 146, 60, 0);
            }
        }

        .featured-badge-static {
            position: static;
        }

        .featured-icon-sm {
            width: 16px;
            height: 16px;
            margin-right: 4px;
        }

        .course-description-list {
            -webkit-line-clamp: 3;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .course-features-grid {
            margin-top: 15px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .course-list-fill {
            flex: 1;
        }

        .course-list-packages-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .package-inline-tag {
            font-size: 0.75rem;
            padding: 2px 6px;
            color: white;
            border-radius: 8px;
            margin-left: 4px;
        }

        .package-inline-tag-vehicle {
            background:
                {{ $settings->accent_color ?? '#8b5cf6' }}
            ;
        }

        .package-inline-tag-transmission-manual {
            background: #fbbf24;
        }

        .package-inline-tag-transmission-auto {
            background: #3b82f6;
        }

        .package-inline-actions {
            display: flex;
            gap: 5px;
            margin-top: 8px;
        }

        .banner-help-text {
            color: #64748b;
            display: block;
            margin-top: 5px;
        }

        .form-grid-two {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .form-label-inline {
            margin-bottom: 0;
        }

        .preview-modal-content {
            max-width: 900px;
        }

        .preview-modal-header {
            background:
                {{ $settings->modal_header_bg ?? $settings->primary_color ?? '#667eea' }}
            ;
        }

        .preview-modal-body {
            padding: 0;
        }

        .preview-modal-footer {
            background: #f8fafc;
        }

        .package-help-text {
            color: #666;
            font-size: 12px;
            margin-top: 4px;
            display: block;
        }

        .preview-guest-hero {
            background:
                {{ $settings?->use_gradient_header ? 'linear-gradient(135deg, ' . $settings->primary_color . ' 0%, ' . $settings->secondary_color . ' 100%)' : ($settings->primary_color ?? '#667eea') }}
            ;
            padding: 40px;
            color: white;
            text-align: center;
        }

        .preview-guest-banner-img {
            max-width: 600px;
            width: 100%;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .preview-guest-banner-icon {
            font-size: 5rem;
            opacity: 0.8;
            display: block;
            margin-bottom: 20px;
        }

        .preview-guest-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .preview-guest-meta {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .preview-guest-pill {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
        }

        .preview-guest-featured-pill {
            background: #f59e0b;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
        }

        .preview-guest-featured-icon {
            width: 16px;
            height: 16px;
            margin-right: 4px;
        }

        .preview-guest-description {
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.95;
        }

        .preview-guest-body {
            padding: 40px;
        }

        .preview-guest-section {
            margin-bottom: 40px;
        }

        .preview-guest-section-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 25px;
            text-align: center;
        }

        .preview-guest-features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            max-width: 800px;
            margin: 0 auto;
        }

        .preview-guest-feature-item {
            background: #f8fafc;
            padding: 15px 20px;
            border-radius: 10px;
            border-left: 4px solid
                {{ $settings->primary_color ?? '#667eea' }}
            ;
        }

        .preview-guest-feature-icon {
            color: #10b981;
            margin-right: 10px;
        }

        .preview-guest-packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .preview-guest-package-card {
            background: white;
            border: 3px solid #e2e8f0;
            border-radius: 12px;
            padding: 25px;
            position: relative;
            transition: all 0.3s;
        }

        .preview-guest-package-card.popular {
            border-color: #f59e0b;
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
        }

        .preview-guest-popular-ribbon {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: #f59e0b;
            color: white;
            padding: 5px 20px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .preview-guest-package-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 10px;
        }

        .preview-guest-package-title.popular {
            margin-top: 10px;
        }

        .preview-guest-package-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .preview-guest-package-tag {
            color: white;
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .preview-guest-package-tag.vehicle {
            background:
                {{ $settings->accent_color ?? '#8b5cf6' }}
            ;
        }

        .preview-guest-package-tag.manual {
            background: #fbbf24;
        }

        .preview-guest-package-tag.automatic {
            background: #3b82f6;
        }

        .preview-guest-package-hours {
            color: #64748b;
            font-weight: 600;
        }

        .preview-guest-package-description {
            color: #64748b;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .preview-guest-package-features {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .preview-guest-package-feature-item {
            padding: 5px 0;
            color: #475569;
        }

        .preview-guest-package-divider {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
        }

        .preview-guest-package-price {
            font-size: 2.5rem;
            font-weight: 700;
            color:
                {{ $settings->primary_color ?? '#667eea' }}
            ;
        }

        .preview-guest-empty {
            text-align: center;
            color: #64748b;
            font-size: 1.1rem;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 0;
            max-width: 700px;
            width: 90%;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 25px 30px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background:
                {{ $settings->modal_header_bg ?? $settings->primary_color ?? '#667eea' }}
            ;
            color: white;
            border-radius: 16px 16px 0 0;
        }

        .modal-header h5 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            color: white;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .btn-close:hover {
            opacity: 1;
        }

        .modal-body {
            padding: 30px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2d3748;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color:
                {{ $settings->primary_color ?? '#667eea' }}
            ;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-control.is-invalid {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .field-error {
            margin-top: 6px;
            color: #b91c1c;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .modal-error-summary {
            margin-bottom: 18px;
            padding: 12px 14px;
            border: 1px solid #fecaca;
            border-left: 4px solid #ef4444;
            background: #fef2f2;
            border-radius: 8px;
        }

        .modal-error-summary strong {
            color: #991b1b;
            display: block;
            margin-bottom: 6px;
        }

        .modal-error-summary ul {
            margin: 0;
            padding-left: 18px;
            color: #b91c1c;
            font-size: 0.85rem;
        }

        .required-note {
            margin-bottom: 14px;
            padding: 10px 12px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            color: #1e3a8a;
            font-size: 0.85rem;
            font-weight: 600;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .form-check input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .features-list {
            list-style: none;
        }

        .feature-input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .feature-input-group input {
            flex: 1;
        }

        .btn-remove-feature {
            padding: 12px 16px;
            background: #ef4444;
            color: white;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-remove-feature:hover {
            background: #dc2626;
        }

        .btn-add-feature {
            padding: 10px 16px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.2s;
        }

        .btn-add-feature:hover {
            background: #059669;
        }

        .image-preview {
            margin-top: 15px;
            max-width: 100%;
            border-radius: 8px;
            display: none;
        }

        .modal-footer {
            padding: 20px 30px;
            border-top: 2px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .course-modal-btn {
            padding: 12px 24px;
            font-weight: 600;
            min-width: 140px;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .empty-state>i {
            font-size: 5rem;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .empty-state .btn-create {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .empty-state .btn-create i {
            font-size: 1rem;
            margin-bottom: 0;
        }

        .empty-state p {
            font-size: 1.2rem;
            color: #64748b;
            margin-bottom: 30px;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* View Toggle Styles */
        .view-toggle {
            display: flex;
            gap: 0;
            background: #e2e8f0;
            border-radius: 10px;
            padding: 4px;
        }

        .view-toggle-btn {
            padding: 10px 20px;
            background: transparent;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            color: #64748b;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .view-toggle-btn.active {
            background: white;
            color: #667eea;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .view-toggle-btn:hover:not(.active) {
            color: #2d3748;
        }

        /* List View Styles */
        .courses-list {
            display: none;
            flex-direction: column;
            gap: 20px;
            width: 100%;
        }

        .courses-list.active {
            display: flex;
        }

        .courses-grid.active {
            display: grid;
        }

        .course-list-item {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            gap: 25px;
            transition: all 0.3s;
            width: 100%;
        }

        .course-list-item:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            transform: translateX(5px);
        }

        .course-list-banner {
            width: 280px;
            height: 160px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
            @if($settings?->use_gradient_header)
                background: linear-gradient(135deg,
                        {{ $settings->primary_color }}
                        0%,
                        {{ $settings->secondary_color }}
                        100%);
            @else background:
                {{ $settings->primary_color ?? '#667eea' }}
                ;
            @endif display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
        }

        .course-list-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .course-list-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .course-list-title-section {
            flex: 1;
        }

        .course-list-actions {
            display: flex;
            gap: 10px;
        }

        .course-list-info {
            display: flex;
            gap: 30px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .info-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 600;
        }

        .info-value {
            font-size: 1.1rem;
            color: #2d3748;
            font-weight: 700;
        }

        .packages-inline {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .package-inline-item {
            background: #f8fafc;
            padding: 10px 15px;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
        }

        .package-inline-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
            font-size: 0.9rem;
        }

        .package-inline-price {
            font-size: 1.1rem;
            font-weight: 700;
            color:
                {{ $settings->primary_color ?? '#667eea' }}
            ;
        }

        /* Export Buttons */
        .export-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-export {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-export-pdf {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-export-pdf:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

        /* Sort Dropdown Styles */
        .sort-control {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f1f5f9;
            padding: 5px 12px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
        }

        .sort-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            white-space: nowrap;
        }

        .sort-select {
            border: none;
            background: transparent;
            font-size: 0.9rem;
            font-weight: 600;
            color: #2d3748;
            cursor: pointer;
            outline: none;
            padding-right: 20px;
        }

        .sort-select:focus {
            color:
                {{ $settings->primary_color ?? '#667eea' }}
            ;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .courses-container {
                padding: 15px;
                margin: 10px auto;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .page-title {
                font-size: 1.4rem;
                gap: 10px;
            }

            .page-subtitle {
                font-size: 0.85rem;
            }

            .courses-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .course-card {
                margin-bottom: 0;
            }

            .course-banner {
                height: 160px;
                font-size: 2rem;
            }

            .course-content {
                padding: 18px;
            }

            .course-title {
                font-size: 1.2rem;
            }

            .course-stats {
                flex-wrap: wrap;
            }

            .view-toggle {
                width: 100%;
            }

            .view-toggle-btn {
                flex: 1;
                text-align: center;
            }

            .btn-create {
                width: 100%;
                justify-content: center;
            }

            .modal-content {
                width: 95%;
                max-width: 95%;
                margin: 10px;
            }

            .modal-header {
                padding: 18px 20px;
            }

            .modal-header h5 {
                font-size: 1.2rem;
            }

            .modal-body {
                padding: 20px;
            }

            .packages-grid {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr 1fr;
            }

            .export-buttons {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .courses-container {
                padding: 10px;
                margin: 5px auto;
            }

            .page-title {
                font-size: 1.2rem;
            }

            .course-banner {
                height: 140px;
                font-size: 1.5rem;
            }

            .course-content {
                padding: 15px;
            }

            .course-title {
                font-size: 1.1rem;
            }

            .course-description {
                font-size: 0.85rem;
            }

            .course-stat span {
                font-size: 0.75rem;
            }

            .course-stat strong {
                font-size: 0.9rem;
            }

            .modal-header {
                padding: 15px;
            }

            .modal-header h5 {
                font-size: 1.1rem;
            }

            .modal-body {
                padding: 15px;
            }

            .form-group {
                margin-bottom: 18px;
            }

            .form-label {
                font-size: 0.9rem;
            }

            .form-control {
                padding: 10px 12px;
                font-size: 0.95rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .btn-export {
                padding: 8px 12px;
                font-size: 0.8rem;
            }
        }
    </style>

    <div class="courses-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Courses Management</h1>
                <p class="page-subtitle">Manage courses, packages, and pricing for {{ $schoolName }}</p>
            </div>
            <div class="header-actions">
                <div class="export-buttons">
                    <a href="{{ $schoolRoute('admin.exports.courses.pdf', ['sort' => request('sort', 'newest')]) }}"
                        class="btn-export btn-export-pdf">
                        Export PDF
                    </a>
                </div>
                <div class="sort-control">
                    <span class="sort-label"><i class="bi bi-sort-down"></i> Sort by:</span>
                    <select class="sort-select" onchange="applySort(this.value)">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>Title (A-Z)</option>
                        <option value="title_desc" {{ request('sort') == 'title_desc' ? 'selected' : '' }}>Title (Z-A)
                        </option>
                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Lowest Price</option>
                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Highest Price
                        </option>
                        <option value="popularity" {{ request('sort') == 'popularity' ? 'selected' : '' }}>Most Popular
                        </option>
                        <option value="duration" {{ request('sort') == 'duration' ? 'selected' : '' }}>Longest Duration
                        </option>
                        <option value="status" {{ request('sort') == 'status' ? 'selected' : '' }}>Active Status</option>
                        <option value="type" {{ request('sort') == 'type' ? 'selected' : '' }}>Course Type</option>
                    </select>
                </div>
                <div class="view-toggle">
                    <button class="view-toggle-btn active" onclick="switchView('cards')">
                        <i class="bi bi-grid-3x3-gap-fill"></i> Cards
                    </button>
                    <button class="view-toggle-btn" onclick="switchView('list')">
                        <i class="bi bi-list-ul"></i> List
                    </button>
                </div>
                <button class="btn-create" onclick="openCreateModal()">
                    <i class="bi bi-plus-circle"></i> Create New Course
                </button>
            </div>
        </div>
        @php
            $incompleteCourses = $courses->filter(function ($c) {
                return $c->status === 'active' && $c->packages->count() === 0;
            });
        @endphp

        @if($incompleteCourses->count() > 0)
            <div class="alert alert-warning"
                style="margin-bottom: 25px; border-left: 5px solid #f97316; background: #fff7ed; padding: 15px; border-radius: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.5rem; color: #f97316;"></i>
                    <div>
                        <strong style="color: #9a3412; display: block; font-size: 1rem;">Action Required: Incomplete Courses
                            Detected</strong>
                        <p style="margin: 2px 0 0 0; color: #c2410c; font-size: 0.9rem; line-height: 1.4;">
                            {{ $incompleteCourses->count() }} active course(s) have no packages. Students cannot enroll in these
                            courses until you add at least one package with pricing and features.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if($courses->isEmpty())
            <div class="empty-state">
                <i class="bi bi-mortarboard"></i>
                <p>No courses created yet. Start by creating your first course!</p>
                <button class="btn-create" onclick="openCreateModal()">
                    <i class="bi bi-plus-circle-fill"></i>
                    Create Your First Course
                </button>
            </div>
        @else
            <div class="courses-grid active">
                @foreach($courses as $course)
                    <div class="course-card">
                        <div class="course-banner-wrap">
                            @if($course->banner_image && file_exists(public_path($course->banner_image)))
                                <img src="{{ asset($course->banner_image) }}" alt="{{ $course->title }}" class="course-banner">
                            @else
                                <div class="course-banner">
                                    <i class="bi bi-car-front-fill"></i>
                                </div>
                            @endif

                            @if($course->is_featured)
                                <span class="featured-badge">Featured</span>
                            @endif

                            @if($course->status === 'active' && $course->packages->count() === 0)
                                <span class="status-badge status-incomplete"
                                    title="Action Required: Add packages to enable enrollment">Incomplete</span>
                            @else
                                <span class="status-badge status-{{ $course->status }}">{{ ucfirst($course->status) }}</span>
                            @endif
                        </div>

                        <div class="course-content">
                            <div class="course-header">
                                <div>
                                    <h3 class="course-title">{{ $course->title }}</h3>
                                    <div class="matrix-badge-container">
                                        @if($course->license_type)
                                            <span class="matrix-badge badge-license">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" />
                                                </svg>
                                                {{ $course->license_type === 'non_professional' ? 'Non-Pro' : 'Pro' }}
                                            </span>
                                            <span class="matrix-badge badge-lto" title="Target DL Codes">
                                                <i class="bi bi-shield-check"></i>
                                                {{ $course->license_type === 'professional' ? 'A, A1, B, B1, B2, C, D, BE, CE' : 'A, A1, B, B1, B2' }}
                                            </span>
                                        @endif
                                        @php
                                            $mainCategory = $course->packages->first()?->vehicleCategory?->name ?? $course->vehicle_type;
                                        @endphp
                                        @if($mainCategory)
                                            <span class="matrix-badge badge-category">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125a1.125 1.125 0 0 0 1.125-1.125V14.25m-17.25 4.5V12.75l.186-.372a1.125 1.125 0 0 1 .615-.558l.412-.138c.642-.214 1.341-.328 2.046-.337l.459-.006c.729-.01 1.458.011 2.187.062l.685.048c.11.008.219.043.313.103a1.125 1.125 0 0 1 .425.86v2.368a.75.75 0 0 1-.75.75h-7.5a.75.75 0 0 1-.75-.75V12.75m11.25 6h1.125l1.623-4.87a2.25 2.25 0 0 0-2.134-2.963h-1.314c-.66 0-1.306.183-1.865.531l-1.114.696c-.354.22-.767.337-1.187.337H8.25" />
                                                </svg>
                                                {{ $mainCategory }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($course->description)
                                <p class="course-description">{{ $course->description }}</p>
                            @endif

                            @if($course->features && count($course->features) > 0)
                                <ul class="course-features">
                                    @foreach(array_slice($course->features, 0, 2) as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                    @if(count($course->features) > 2)
                                        <li class="course-feature-more" style="font-size: 0.8rem; opacity: 0.8;">
                                            +{{ count($course->features) - 2 }} more details</li>
                                    @endif
                                </ul>
                            @endif

                            @if($course->packages && $course->packages->count() > 0)
                                <div class="packages-section">
                                    <div class="packages-header">
                                        <span class="packages-title">Pricing Matrix ({{ $course->packages->count() }})</span>
                                        <button class="btn-add-package" onclick="openPackageModal({{ $course->id }}, null)">
                                            <i class="bi bi-plus"></i> Add Package
                                        </button>
                                    </div>

                                    <div class="packages-ticker-container">
                                        <div class="packages-ticker">
                                            @foreach($course->packages as $package)
                                                <div class="package-item">
                                                    <div class="package-info">
                                                        <div class="package-name">
                                                            {{ $package->name }}
                                                        </div>
                                                        <div class="package-details">
                                                            <span
                                                                class="package-tag {{ $package->transmission_type == 'manual' ? 'package-tag-transmission-manual' : 'package-tag-transmission-auto' }}">{{ strtoupper($package->transmission_type) }}</span>
                                                            @if($package->tdc_hours > 0) <span title="TDC Hours"
                                                                style="color: {{ $settings->primary_color }}; font-weight: 600;">{{ $package->tdc_hours }}h
                                                            TDC</span> @endif
                                                            @if($package->pdc_hours > 0) <span title="PDC Hours"
                                                                style="color: {{ $settings->secondary_color }}; font-weight: 600;">•
                                                            {{ $package->pdc_hours }}h PDC</span> @endif
                                                            @if($package->vehicleCategory) • {{ $package->vehicleCategory->name }} @endif
                                                        </div>
                                                    </div>
                                                    <div style="text-align: right">
                                                        <div class="package-price">₱{{ number_format($package->price, 0) }}</div>
                                                        <div class="package-actions">
                                                            <button class="btn-package-edit"
                                                                onclick="openPackageModal({{ $course->id }}, {{ $package->id }})"
                                                                title="Edit"><i class="bi bi-pencil"></i></button>
                                                            <button class="btn-package-delete"
                                                                onclick="deletePackage({{ $course->id }}, {{ $package->id }})"
                                                                title="Delete"><i class="bi bi-trash"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="packages-section">
                                    <button class="btn-add-package btn-add-package-full"
                                        onclick="openPackageModal({{ $course->id }}, null)">
                                        <i class="bi bi-plus-circle"></i> Add First Package
                                    </button>
                                </div>
                            @endif

                            <div class="course-actions">
                                <button class="btn-preview" onclick="openPreviewModal({{ $course->id }})"
                                    title="Preview how guests see this course">
                                    <i class="bi bi-eye-fill"></i> Preview
                                </button>
                                <button class="btn-edit" onclick="openEditModal({{ $course->id }})">
                                    <i class="bi bi-pencil-fill"></i> Edit
                                </button>
                                <button class="btn-delete" onclick="deleteCourse({{ $course->id }})">
                                    <i class="bi bi-trash-fill"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- List View -->
            <div class="courses-list">
                @foreach($courses as $course)
                    <div class="course-list-item">
                        @if($course->banner_image && file_exists(public_path($course->banner_image)))
                            <img src="{{ asset($course->banner_image) }}" alt="{{ $course->title }}" class="course-list-banner">
                        @else
                            <div class="course-list-banner">
                                <i class="bi bi-car-front-fill"></i>
                            </div>
                        @endif

                        <div class="course-list-content">
                            <div class="course-list-header">
                                <div class="course-list-title-section">
                                    <h3 class="course-title course-title-list">{{ $course->title }}</h3>
                                    <div class="matrix-badge-container">
                                        @if($course->license_type)
                                            <span class="matrix-badge badge-license">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" />
                                                </svg>
                                                                                                {{ $course->license_type === 'non_professional' ? 'Non-Pro' : 'Pro' }}
                                            </span>
                                            <span class="matrix-badge badge-lto" title="Target DL Codes">
                                                <i class="bi bi-shield-check"></i>
                                                {{ $course->license_type === 'professional' ? 'A, A1, B, B1, B2, C, D, BE, CE' : 'A, A1, B, B1, B2' }}
                                            </span>

                                        @endif
                                        @php
                                            $listCategory = $course->packages->first()?->vehicleCategory?->name ?? $course->vehicle_type;
                                        @endphp
                                        @if($listCategory)
                                            <span class="matrix-badge badge-category">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125a1.125 1.125 0 0 0 1.125-1.125V14.25m-17.25 4.5V12.75l.186-.372a1.125 1.125 0 0 1 .615-.558l.412-.138c.642-.214 1.341-.328 2.046-.337l.459-.006c.729-.01 1.458.011 2.187.062l.685.048c.11.008.219.043.313.103a1.125 1.125 0 0 1 .425.86v2.368a.75.75 0 0 1-.75.75h-7.5a.75.75 0 0 1-.75-.75V12.75m11.25 6h1.125l1.623-4.87a2.25 2.25 0 0 0-2.134-2.963h-1.314c-.66 0-1.306.183-1.865.531l-1.114.696c-.354.22-.767.337-1.187.337H8.25" />
                                                </svg>
                                                {{ $listCategory }}
                                            </span>
                                        @endif
                                        @if($course->status === 'active' && $course->packages->count() === 0)
                                            <span class="status-badge status-incomplete status-badge-static"
                                                title="Action Required: Add packages to enable enrollment">Incomplete</span>
                                        @else
                                            <span
                                                class="status-badge status-{{ $course->status }} status-badge-static">{{ ucfirst($course->status) }}</span>
                                        @endif
                                    </div>
                                    @if($course->description)
                                        <p class="course-description course-description-list">{{ $course->description }}</p>
                                    @endif

                                    @if($course->features && count($course->features) > 0)
                                        <ul class="course-features course-features-grid">
                                            @foreach($course->features as $feature)
                                                <li>{{ $feature }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                <div class="course-list-actions">
                                    <button class="btn-preview" onclick="openPreviewModal({{ $course->id }})"
                                        title="Preview how guests see this course">
                                        <i class="bi bi-eye-fill"></i> Preview
                                    </button>
                                    <button class="btn-edit" onclick="openEditModal({{ $course->id }})">
                                        <i class="bi bi-pencil-fill"></i> Edit
                                    </button>
                                    <button class="btn-delete" onclick="deleteCourse({{ $course->id }})">
                                        <i class="bi bi-trash-fill"></i> Delete
                                    </button>
                                </div>
                            </div>

                            @if($course->packages && $course->packages->count() > 0)
                                <div class="course-list-info">
                                    <div class="info-item">
                                        <span class="info-label">Total Packages</span>
                                        <span class="info-value">{{ $course->packages->count() }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Price Range</span>
                                        <span class="info-value">
                                            ₱{{ number_format($course->packages->min('price'), 0) }} -
                                            ₱{{ number_format($course->packages->max('price'), 0) }}
                                        </span>
                                    </div>
                                    <div class="course-list-fill">
                                        <div class="course-list-packages-header">
                                            <span class="info-label">Available Packages</span>
                                            <button class="btn-add-package" onclick="openPackageModal({{ $course->id }}, null)">
                                                <i class="bi bi-plus"></i> Add
                                            </button>
                                        </div>
                                        <div class="packages-inline">
                                            @foreach($course->packages as $package)
                                                <div class="package-inline-item">
                                                    <div class="package-inline-name">
                                                        {{ $package->name }}
                                                        @if($package->vehicle_type)
                                                            <span class="package-inline-tag package-inline-tag-vehicle">
                                                                {{ $package->vehicle_type }}
                                                            </span>
                                                        @endif
                                                        <span
                                                            class="package-inline-tag {{ $package->transmission_type == 'manual' ? 'package-inline-tag-transmission-manual' : 'package-inline-tag-transmission-auto' }}">
                                                            {{ strtoupper($package->transmission_type) }}
                                                        </span>
                                                        @if($package->tdc_hours > 0)
                                                            <span class="package-inline-tag"
                                                                style="background: {{ $settings->primary_color }};">TDC:
                                                                {{ $package->tdc_hours }}h</span>
                                                        @endif
                                                        @if($package->pdc_hours > 0)
                                                            <span class="package-inline-tag"
                                                                style="background: {{ $settings->secondary_color }};">PDC:
                                                                {{ $package->pdc_hours }}h</span>
                                                        @endif
                                                    </div>
                                                    <div class="package-inline-price">₱{{ number_format($package->price, 0) }}</div>
                                                    <div class="package-inline-actions">
                                                        <button class="btn-package-edit"
                                                            onclick="openPackageModal({{ $course->id }}, {{ $package->id }})">Edit</button>
                                                        <button class="btn-package-delete"
                                                            onclick="deletePackage({{ $course->id }}, {{ $package->id }})">Delete</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="course-list-info">
                                    <button class="btn-add-package btn-add-package-full"
                                        onclick="openPackageModal({{ $course->id }}, null)">
                                        <i class="bi bi-plus-circle"></i> Add First Package
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
                <div class="mt-4">
                    {{ $courses->links() }}
                </div>
            </div>
        @endif
    </div>

        <!-- Create/Edit Course Modal -->
        <div class="modal" id="courseModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="courseModalTitle">Create New Course</h5>
                    <button class="btn-close" onclick="closeCourseModal()">&times;</button>
                </div>
                <form id="courseForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="courseMethod" value="POST">
                    <input type="hidden" name="course_id" id="courseId" value="{{ old('course_id') }}">
                    <input type="hidden" name="modal_type" value="course">

                    <div class="modal-body">
                        <div class="required-note">Fields marked with * are required.</div>

                        <div class="modal-error-summary" id="courseModalErrorSummary" style="display: none;"></div>

                        @if($errors->any())
                            <div class="modal-error-summary" id="courseModalServerErrorSummary">
                                <strong>Please fix the following:</strong>
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <label class="form-label">Course Title *</label>
                            <input type="text" name="title" id="courseTitle"
                                class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}"
                                required placeholder="e.g., Transitional Driving Programs">
                            @error('title')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Description (Optional)</label>
                            <textarea name="description" id="courseDescription"
                                class="form-control @error('description') is-invalid @enderror" rows="4"
                                placeholder="Describe the course details...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Banner Image (Optional, Max 2MB)</label>
                            <input type="file" name="banner_image" id="courseBanner"
                                class="form-control @error('banner_image') is-invalid @enderror"
                                accept=".jpg,.jpeg,.png,.webp" onchange="previewImage(this)">
                            <img id="imagePreview" class="image-preview">
                            <small class="banner-help-text">Recommended size: 1200x400px for best results</small>
                            @error('banner_image')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Course Type *</label>
                            <select name="type" id="courseType" class="form-control @error('type') is-invalid @enderror"
                                required>
                                <option value="standard">Standard</option>
                                <option value="intensive">Intensive</option>
                                <option value="refresher">Refresher</option>
                                <option value="transitional">Transitional</option>
                                <option value="defensive">Defensive Driving</option>
                                <option value="advanced">Advanced</option>
                            </select>
                            @error('type')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Vehicle Type (Optional)</label>
                            <select name="vehicle_type" id="courseVehicleType"
                                class="form-control @error('vehicle_type') is-invalid @enderror">
                                <option value="">Select vehicle type</option>
                                <option value="Sedan">Sedan (Vios / Mirage)</option>
                                <option value="SUV">SUV (Innova / L300)</option>
                                <option value="Hi-Lux">Hi-Lux (Pick-up Truck)</option>
                                <option value="Montero">Montero / Fortuner</option>
                                <option value="Motor">Motor (Motorcycle)</option>
                            </select>
                            @error('vehicle_type')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Course Features (Optional)</label>
                            <div id="featuresContainer">
                                <div class="feature-input-group">
                                    <input type="text" name="features[]"
                                        class="form-control {{ $errors->has('features') || $errors->has('features.*') ? 'is-invalid' : '' }}"
                                        placeholder="e.g., UP TO 23 HOURS OF DRIVING LESSONS"
                                        value="{{ old('features.0') }}">
                                    <button type="button" class="btn-remove-feature"
                                        onclick="removeFeature(this)">−</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-feature" onclick="addFeature()">+ Add Feature</button>
                            @if($errors->has('features') || $errors->has('features.*'))
                                <div class="field-error">{{ $errors->first('features') ?: $errors->first('features.*') }}</div>
                            @endif
                        </div>

                        <div class="form-grid-two">
                            <div class="form-group">
                                <label class="form-label">Course Category *</label>
                                <select name="course_type" id="courseCourseType"
                                    class="form-control @error('course_type') is-invalid @enderror" required
                                    onchange="toggleComboCoursesContainer()">
                                    <option value="">Select category</option>
                                    <option value="theoretical">Theoretical</option>
                                    <option value="practical">Practical</option>
                                    <option value="combo">Combo</option>
                                </select>
                                @error('course_type')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">License Type *</label>
                                <select name="license_type" id="courseLicenseType"
                                    class="form-control @error('license_type') is-invalid @enderror" required
                                    onchange="updateDlCodePreview()">
                                    <option value="">Select license type</option>
                                    <option value="non_professional">Non-Professional</option>
                                    <option value="professional">Professional</option>
                                </select>
                                <div id="dlCodePreview" style="margin-top: 5px; display: none; background: #f8fafc; padding: 8px; border-radius: 8px; border: 1px dashed #e2e8f0;">
                                    <div style="font-size: 0.7rem; color: #64748b; font-weight: 600; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Covered DL Codes</div>
                                    <div id="dlCodeBadges" class="matrix-badge-container" style="margin-bottom: 0; gap: 4px;"></div>
                                </div>
                                @error('license_type')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div id="comboCoursesContainer" style="display: none; margin-bottom: 1.25rem; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 1rem;">
                            <label class="form-label" style="margin-bottom: 0.25rem; font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-collection-fill text-primary"></i> Bundle Existing Courses into this Combo:
                            </label>
                            <p style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.75rem;">
                                Select individual courses to combine their curriculum and schedule offerings:
                            </p>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 0.5rem; max-height: 180px; overflow-y: auto; padding-right: 4px;">
                                @foreach($allCourses ?? $courses as $availCourse)
                                    @if($availCourse->course_type !== 'combo')
                                        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; padding: 6px 10px; background: white; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; user-select: none;">
                                            <input type="checkbox" name="combo_course_ids[]" value="{{ $availCourse->id }}" class="combo-course-checkbox">
                                            <span><strong>{{ $availCourse->title }}</strong> <span style="color: #64748b; font-size: 0.75rem;">({{ ucfirst($availCourse->course_type) }})</span></span>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>


                        <div style="display: none;">
                            <input type="number" name="hours_required" id="courseHoursRequired" value="1">
                            <input type="number" name="price" id="coursePrice" value="0">
                        </div>

                        <div class="form-grid-two">
                            <div class="form-group">
                                <label class="form-label">Sort Priority</label>
                                <input type="number" name="sort_order" id="courseSortOrder" class="form-control" min="0"
                                    value="0">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Status *</label>
                                <select name="status" id="courseStatus"
                                    class="form-control @error('status') is-invalid @enderror" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                @error('status')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" name="is_featured" id="courseFeatured">
                            <label for="courseFeatured" class="form-label form-label-inline">Mark as Featured Course
                                (Optional)</label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary course-modal-btn"
                            onclick="closeCourseModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary course-modal-btn">Save Course</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Modal (Guest View) -->
        <div class="modal" id="previewModal">
            <div class="modal-content preview-modal-content">
                <div class="modal-header preview-modal-header">
                    <h5><i class="bi bi-eye-fill"></i> Course Preview (Guest View)</h5>
                    <button class="btn-close" onclick="closePreviewModal()">&times;</button>
                </div>
                <div class="modal-body preview-modal-body" id="previewContent">
                    <!-- Preview content will be injected here -->
                </div>
                <div class="modal-footer preview-modal-footer">
                    <button type="button" class="btn btn-secondary course-modal-btn"
                        onclick="closePreviewModal()">Close</button>
                </div>
            </div>
        </div>

        <!-- Package Modal -->
        <div class="modal" id="packageModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="packageModalTitle">Add Package</h5>
                    <button class="btn-close" onclick="closePackageModal()">&times;</button>
                </div>
                <form id="packageForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="packageMethod" value="POST">
                    <input type="hidden" name="modal_type" value="package">
                    <input type="hidden" name="course_id" id="packageCourseId" value="{{ old('course_id') }}">
                    <input type="hidden" name="package_id" id="packagePackageId" value="{{ old('package_id') }}">

                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Package Name *</label>
                            <input type="text" name="name" id="packageName" class="form-control" required
                                placeholder="e.g., Smart Basic, Smart All-In">
                        </div>

                        <div class="form-grid-two">
                            <div class="form-group">
                                <label class="form-label">Vehicle Category</label>
                                <select name="vehicle_category_id" id="packageVehicleCategory" class="form-control">
                                    <option value="">Select Category</option>
                                    @foreach($vehicleCategories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Transmission Type *</label>
                                <select name="transmission_type" id="packageTransmission" class="form-control" required>
                                    <option value="manual">Manual</option>
                                    <option value="automatic">Automatic</option>
                                </select>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="form-label">Price (₱) *</label>
                            <input type="number" name="price" id="packagePrice" class="form-control" required min="0"
                                step="0.01" placeholder="0.00">
                        </div>

                        <div class="form-grid-two">
                            <div class="form-group" id="tdcHoursGroup">
                                <label class="form-label"
                                    style="color: {{ $settings->primary_color }}; font-weight: 700;">TDC Hours
                                    (Theoretical)</label>
                                <input type="number" name="tdc_hours" id="packageTdcHours" class="form-control" min="0"
                                    step="0.5" placeholder="e.g., 15">
                                <small class="package-help-text">Classroom hours limit</small>
                            </div>
                            <div class="form-group" id="pdcHoursGroup">
                                <label class="form-label"
                                    style="color: {{ $settings->secondary_color }}; font-weight: 700;">PDC Hours
                                    (Practical)</label>
                                <input type="number" name="pdc_hours" id="packagePdcHours" class="form-control" min="0"
                                    step="0.5" placeholder="e.g., 8">
                                <small class="package-help-text">Driving hours limit</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="packageDescription" class="form-control" rows="3"
                                placeholder="Brief description..."></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Package Features</label>
                            <div id="packageFeaturesContainer">
                                <div class="feature-input-group">
                                    <input type="text" name="features[]" class="form-control"
                                        placeholder="e.g., 15h TDC + 8h PDC">
                                    <button type="button" class="btn-remove-feature"
                                        onclick="removePackageFeature(this)">−</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-feature" onclick="addPackageFeature()">+ Add
                                Feature</button>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" name="is_popular" id="packagePopular" value="1">
                            <label for="packagePopular" class="form-label form-label-inline">Mark as Popular Package</label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary course-modal-btn"
                            onclick="closePackageModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary course-modal-btn">Save Package</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            const coursesData = @json($courses->items());
            let currentCourseId = null;
            let currentPackageId = null;

            function clearCourseFormValidation() {
                const form = document.getElementById('courseForm');
                if (!form) return;

                form.querySelectorAll('.form-control.is-invalid').forEach(input => input.classList.remove('is-invalid'));
                form.querySelectorAll('.field-error.dynamic').forEach(error => error.remove());

                const dynamicSummary = document.getElementById('courseModalErrorSummary');
                if (dynamicSummary) {
                    dynamicSummary.style.display = 'none';
                    dynamicSummary.innerHTML = '';
                }

                const serverSummary = document.getElementById('courseModalServerErrorSummary');
                if (serverSummary) {
                    serverSummary.style.display = 'none';
                }
            }

            function renderCourseFormErrors(errors = {}, summaryMessage = 'Please fix the following:') {
                clearCourseFormValidation();

                const form = document.getElementById('courseForm');
                const summary = document.getElementById('courseModalErrorSummary');
                if (!form || !summary) return;

                const messages = [];
                Object.values(errors).forEach(fieldErrors => {
                    if (Array.isArray(fieldErrors)) {
                        fieldErrors.forEach(msg => messages.push(String(msg)));
                    } else if (fieldErrors) {
                        messages.push(String(fieldErrors));
                    }
                });

                if (messages.length > 0) {
                    const title = document.createElement('strong');
                    title.textContent = summaryMessage;

                    const list = document.createElement('ul');
                    messages.forEach(msg => {
                        const li = document.createElement('li');
                        li.textContent = msg;
                        list.appendChild(li);
                    });

                    summary.innerHTML = '';
                    summary.appendChild(title);
                    summary.appendChild(list);
                    summary.style.display = 'block';
                }

                Object.entries(errors).forEach(([field, fieldErrors]) => {
                    const firstError = Array.isArray(fieldErrors) ? fieldErrors[0] : fieldErrors;
                    if (!firstError) return;

                    let input = null;
                    if (field === 'features' || field.startsWith('features.')) {
                        input = form.querySelector('#featuresContainer input[name="features[]"]');
                    } else {
                        input = form.querySelector(`[name="${field}"]`);
                    }

                    if (!input) return;

                    input.classList.add('is-invalid');
                    const group = input.closest('.form-group') || input.parentElement;
                    if (!group) return;

                    if (!group.querySelector(`.field-error.dynamic[data-field="${field}"]`)) {
                        const errorEl = document.createElement('div');
                        errorEl.className = 'field-error dynamic';
                        errorEl.dataset.field = field;
                        errorEl.textContent = String(firstError);
                        group.appendChild(errorEl);
                    }
                });

                document.getElementById('courseModal').style.display = 'flex';
            }

            function initCourseFormSubmitHandler() {
                const form = document.getElementById('courseForm');
                if (!form || form.dataset.ajaxBound === '1') return;

                form.dataset.ajaxBound = '1';

                form.addEventListener('submit', async function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    clearCourseFormValidation();

                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalButtonHtml = submitButton ? submitButton.innerHTML : '';
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.textContent = 'Saving...';
                    }

                    try {
                        const formData = new FormData(form);
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData,
                            credentials: 'same-origin',
                        });

                        let payload = {};
                        try {
                            payload = await response.json();
                        } catch (e) {
                            payload = {};
                        }

                        if (response.ok) {
                            if (window.showToast) {
                                window.showToast(payload.message || 'Course saved successfully.', 'success');
                            }

                            if (window.loadPage) {
                                window.loadPage(window.location.pathname + window.location.search);
                            } else {
                                window.location.reload();
                            }

                            return;
                        }

                        if (response.status === 422 && payload.errors) {
                            renderCourseFormErrors(payload.errors, payload.message || 'Please fix the following:');
                            return;
                        }

                        if (window.showToast) {
                            window.showToast(payload.message || 'Unable to save course at this time.', 'error', 7000);
                        }
                    } catch (error) {
                        if (window.showToast) {
                            window.showToast('Unable to save course at this time.', 'error', 7000);
                        }
                    } finally {
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.innerHTML = originalButtonHtml;
                        }
                    }
                }, true);
            }

            function toggleComboCoursesContainer() {
                const select = document.getElementById('courseCourseType');
                const container = document.getElementById('comboCoursesContainer');
                if (container && select) {
                    container.style.display = (select.value === 'combo') ? 'block' : 'none';
                }
            }

            // Course Modal Functions
            function openCreateModal() {
                clearCourseFormValidation();
                document.getElementById('courseModalTitle').textContent = 'Create New Course';
                document.getElementById('courseForm').action = '{{ route("schools.admin.courses.store", $school) }}';
                document.getElementById('courseMethod').value = 'POST';
                document.getElementById('courseId').value = '';
                document.getElementById('courseForm').reset();
                document.getElementById('imagePreview').style.display = 'none';
                document.querySelectorAll('.combo-course-checkbox').forEach(cb => cb.checked = false);
                toggleComboCoursesContainer();
                updateDlCodePreview();
                document.getElementById('courseModal').style.display = 'flex';
            }

            function openEditModal(courseId) {
                const course = coursesData.find(c => c.id === courseId);
                if (!course) return;

                clearCourseFormValidation();

                document.getElementById('courseModalTitle').textContent = 'Edit Course';
                document.getElementById('courseForm').action = `{{ url($school->slug . '/admin/courses') }}/${courseId}`;
                document.getElementById('courseMethod').value = 'PUT';
                document.getElementById('courseId').value = courseId;

                document.getElementById('courseTitle').value = course.title || '';
                document.getElementById('courseDescription').value = course.description || '';
                document.getElementById('courseType').value = course.type || 'standard';
                document.getElementById('courseVehicleType').value = course.vehicle_type || '';
                document.getElementById('courseCourseType').value = course.course_type || '';
                document.getElementById('courseLicenseType').value = course.license_type || '';
                document.getElementById('courseHoursRequired').value = course.hours_required || '';
                document.getElementById('coursePrice').value = course.price || '0';
                document.getElementById('courseStatus').value = course.status || 'active';
                document.getElementById('courseFeatured').checked = course.is_featured || false;
                document.getElementById('courseSortOrder').value = course.sort_order || '0';

                // Sync combo checkboxes
                const comboIds = Array.isArray(course.combo_course_ids) ? course.combo_course_ids.map(id => String(id)) : [];
                document.querySelectorAll('.combo-course-checkbox').forEach(cb => {
                    cb.checked = comboIds.includes(String(cb.value));
                });
                toggleComboCoursesContainer();

                // Load features
                const featuresContainer = document.getElementById('featuresContainer');
                featuresContainer.innerHTML = '';
                if (course.features && course.features.length > 0) {
                    course.features.forEach(feature => {
                        addFeature(feature);
                    });
                } else {
                    addFeature();
                }

                // Show banner preview if exists
                if (course.banner_image) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = '{{ asset("") }}' + course.banner_image;
                    preview.style.display = 'block';
                }

                updateDlCodePreview();
                document.getElementById('courseModal').style.display = 'flex';
            }

            function closeCourseModal() {
                document.getElementById('courseModal').style.display = 'none';
            }

            function deleteCourse(courseId) {
                showConfirm({
                    type: 'danger',
                    title: 'Delete Course',
                    message: 'Are you sure you want to delete this course? This will also delete all associated packages.',
                    confirmText: 'Yes, Delete Course',
                    onConfirm: () => {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `{{ url($school->slug . '/admin/courses') }}/${courseId}`;

                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';

                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'DELETE';

                        form.appendChild(csrfToken);
                        form.appendChild(methodInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }

            // Package Modal Functions
            function openPackageModal(courseId, packageId) {
                currentCourseId = courseId;
                currentPackageId = packageId;

                const course = coursesData.find(c => c.id === courseId);
                const courseType = course ? course.course_type : 'combo';

                // Conditional visibility for hours
                const tdcGroup = document.getElementById('tdcHoursGroup');
                const pdcGroup = document.getElementById('pdcHoursGroup');

                if (tdcGroup && pdcGroup) {
                    if (courseType === 'theoretical') {
                        tdcGroup.style.display = 'block';
                        pdcGroup.style.display = 'none';
                    } else if (courseType === 'practical') {
                        tdcGroup.style.display = 'none';
                        pdcGroup.style.display = 'block';
                    } else {
                        // combo or default
                        tdcGroup.style.display = 'block';
                        pdcGroup.style.display = 'block';
                    }
                }

                if (packageId) {
                    // Edit mode
                    const package = course.packages.find(p => p.id === packageId);

                    document.getElementById('packageModalTitle').textContent = 'Edit Package';
                    document.getElementById('packageForm').action = `{{ url($school->slug . '/admin/courses') }}/${courseId}/packages/${packageId}`;
                    document.getElementById('packageMethod').value = 'PUT';
                    document.getElementById('packageCourseId').value = courseId;
                    document.getElementById('packagePackageId').value = packageId;

                    document.getElementById('packageName').value = package.name || '';
                    document.getElementById('packageVehicleCategory').value = package.vehicle_category_id || '';
                    document.getElementById('packageTransmission').value = package.transmission_type || 'manual';
                    document.getElementById('packagePrice').value = package.price || '';
                    document.getElementById('packageTdcHours').value = package.tdc_hours || '';
                    document.getElementById('packagePdcHours').value = package.pdc_hours || '';
                    document.getElementById('packageDescription').value = package.description || '';
                    document.getElementById('packagePopular').checked = package.is_popular || false;

                    // Load package features
                    const featuresContainer = document.getElementById('packageFeaturesContainer');
                    featuresContainer.innerHTML = '';
                    if (package.features && package.features.length > 0) {
                        package.features.forEach(feature => {
                            addPackageFeature(feature);
                        });
                    } else {
                        addPackageFeature();
                    }
                } else {
                    // Create mode
                    document.getElementById('packageModalTitle').textContent = 'Add New Package';
                    document.getElementById('packageForm').action = `{{ url($school->slug . '/admin/courses') }}/${courseId}/packages`;
                    document.getElementById('packageMethod').value = 'POST';
                    document.getElementById('packageCourseId').value = courseId;
                    document.getElementById('packagePackageId').value = '';
                    document.getElementById('packageForm').reset();

                    const featuresContainer = document.getElementById('packageFeaturesContainer');
                    featuresContainer.innerHTML = '';
                    addPackageFeature();
                }

                document.getElementById('packageModal').style.display = 'flex';
            }

            function closePackageModal() {
                document.getElementById('packageModal').style.display = 'none';
                currentCourseId = null;
                currentPackageId = null;
            }

            function deletePackage(courseId, packageId) {
                showConfirm({
                    type: 'danger',
                    title: 'Delete Package',
                    message: 'Are you sure you want to delete this package?',
                    confirmText: 'Yes, Delete',
                    onConfirm: () => {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `{{ url($school->slug . '/admin/courses') }}/${courseId}/packages/${packageId}`;

                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';

                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'DELETE';

                        form.appendChild(csrfToken);
                        form.appendChild(methodInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }

            // Feature Management
            function addFeature(value = '') {
                const container = document.getElementById('featuresContainer');
                const div = document.createElement('div');
                div.className = 'feature-input-group';
                div.innerHTML = `
                <input type="text" name="features[]" class="form-control" placeholder="Enter a feature" value="${value}">
                <button type="button" class="btn-remove-feature" onclick="removeFeature(this)">−</button>
            `;
                container.appendChild(div);
            }

            function removeFeature(button) {
                const container = document.getElementById('featuresContainer');
                if (container.children.length > 1) {
                    button.parentElement.remove();
                }
            }

            function addPackageFeature(value = '') {
                const container = document.getElementById('packageFeaturesContainer');
                const div = document.createElement('div');
                div.className = 'feature-input-group';
                div.innerHTML = `
                <input type="text" name="features[]" class="form-control" placeholder="Enter a feature" value="${value}">
                <button type="button" class="btn-remove-feature" onclick="removePackageFeature(this)">−</button>
            `;
                container.appendChild(div);
            }

            function removePackageFeature(button) {
                const container = document.getElementById('packageFeaturesContainer');
                if (container.children.length > 1) {
                    button.parentElement.remove();
                }
            }

            // Image Preview
            function previewImage(input) {
                const preview = document.getElementById('imagePreview');
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            function updateDlCodePreview() {
                const select = document.getElementById('courseLicenseType');
                const preview = document.getElementById('dlCodePreview');
                const container = document.getElementById('dlCodeBadges');
                
                if (!select || !preview || !container) return;

                const value = select.value;
                if (!value) {
                    preview.style.display = 'none';
                    return;
                }

                let codes = [];
                let badgeClass = '';
                
                if (value === 'professional') {
                    codes = ['A', 'A1', 'B', 'B1', 'B2', 'C', 'D', 'BE', 'CE'];
                    badgeClass = 'badge-lto';
                } else {
                    codes = ['A', 'A1', 'B', 'B1', 'B2'];
                    badgeClass = 'badge-license';
                }

                container.innerHTML = codes.map(code => `<span class="matrix-badge ${badgeClass}" style="margin-bottom: 5px;">${code}</span>`).join('');
                preview.style.display = 'block';
            }

            // Close modals when clicking outside
            window.onclick = function (event) {
                const courseModal = document.getElementById('courseModal');
                const packageModal = document.getElementById('packageModal');

                if (event.target == courseModal) {
                    closeCourseModal();
                }
                if (event.target == packageModal) {
                    closePackageModal();
                }
            }

            // Preview Modal Functions
            function openPreviewModal(courseId) {
                const course = coursesData.find(c => c.id === courseId);
                if (!course) return;

                currentCourseId = courseId;

                // Build guest-view HTML
                const previewHTML = `
                <div class="preview-guest-hero">
                    ${course.banner_image ?
                        `<img src="{{ asset('') }}${course.banner_image}" class="preview-guest-banner-img">` :
                        `<i class="bi bi-car-front-fill preview-guest-banner-icon"></i>`
                    }
                    <h2 class="preview-guest-title">${course.title}</h2>
                    <div class="preview-guest-meta">
                        <span class="preview-guest-pill">${course.type}</span>
                        ${course.vehicle_type ? `<span class="preview-guest-pill">${course.vehicle_type}</span>` : ''}
                        ${course.is_featured ? `<span class="preview-guest-featured-pill"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="preview-guest-featured-icon"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" /></svg>Featured</span>` : ''}
                    </div>
                    ${course.description ? `<p class="preview-guest-description">${course.description}</p>` : ''}
                </div>

                <div class="preview-guest-body">
                    ${course.features && course.features.length > 0 ? `
                        <div class="preview-guest-section">
                            <h3 class="preview-guest-section-title">✨ What's Included</h3>
                            <div class="preview-guest-features-grid">
                                ${course.features.map(feature => `
                                    <div class="preview-guest-feature-item">
                                        <i class="bi bi-check-circle-fill preview-guest-feature-icon"></i>
                                        ${feature}
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}

                    ${course.packages && course.packages.length > 0 ? `
                        <div>
                            <h3 class="preview-guest-section-title">Choose Your Package</h3>
                            <div class="preview-guest-packages-grid">
                                ${course.packages.map(pkg => `
                                    <div class="preview-guest-package-card ${pkg.is_popular ? 'popular' : ''}">
                                        ${pkg.is_popular ? `<div class="preview-guest-popular-ribbon">MOST POPULAR</div>` : ''}
                                        <h4 class="preview-guest-package-title ${pkg.is_popular ? 'popular' : ''}">${pkg.name}</h4>
                                        <div class="preview-guest-package-meta">
                                            ${pkg.vehicle_type ? `<span class="preview-guest-package-tag vehicle">${pkg.vehicle_type}</span>` : ''}
                                            <span class="preview-guest-package-tag ${pkg.transmission_type === 'manual' ? 'manual' : 'automatic'}">
                                                ${pkg.transmission_type.toUpperCase()}
                                            </span>
                                            ${pkg.tdc_hours > 0 ? `<span class="preview-guest-package-hours"><i class="bi bi-journal-text"></i> ${pkg.tdc_hours}h TDC</span>` : ''}
                                            ${pkg.pdc_hours > 0 ? `<span class="preview-guest-package-hours"><i class="bi bi-car-front"></i> ${pkg.pdc_hours}h PDC</span>` : ''}
                                        </div>
                                        ${pkg.description ? `<p class="preview-guest-package-description">${pkg.description}</p>` : ''}
                                        ${pkg.features && pkg.features.length > 0 ? `
                                            <ul class="preview-guest-package-features">
                                                ${pkg.features.map(f => `<li class="preview-guest-package-feature-item"><i class="bi bi-check-circle-fill preview-guest-feature-icon"></i>${f}</li>`).join('')}
                                            </ul>
                                        ` : ''}
                                        <div class="preview-guest-package-divider">
                                            <div class="preview-guest-package-price">₱${new Intl.NumberFormat().format(pkg.price)}</div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : '<p class="preview-guest-empty">No packages available yet.</p>'}
                </div>
            `;

                document.getElementById('previewContent').innerHTML = previewHTML;
                document.getElementById('previewModal').style.display = 'flex';
            }

            function closePreviewModal() {
                document.getElementById('previewModal').style.display = 'none';
                currentCourseId = null;
            }

            function openCourseModalFromValidation(oldData) {
                clearCourseFormValidation();

                const isEditMode = String(oldData.method || 'POST').toUpperCase() === 'PUT' && oldData.course_id;

                document.getElementById('courseModalTitle').textContent = isEditMode ? 'Edit Course' : 'Create New Course';
                document.getElementById('courseForm').action = isEditMode
                    ? `{{ url($school->slug . '/admin/courses') }}/${oldData.course_id}`
                    : '{{ route("schools.admin.courses.store", $school) }}';
                document.getElementById('courseMethod').value = isEditMode ? 'PUT' : 'POST';
                document.getElementById('courseId').value = oldData.course_id || '';

                document.getElementById('courseTitle').value = oldData.title || '';
                document.getElementById('courseDescription').value = oldData.description || '';
                document.getElementById('courseType').value = oldData.type || 'standard';
                document.getElementById('courseVehicleType').value = oldData.vehicle_type || '';
                document.getElementById('courseCourseType').value = oldData.course_type || '';
                document.getElementById('courseLicenseType').value = oldData.license_type || '';
                document.getElementById('courseHoursRequired').value = oldData.hours_required || '';
                document.getElementById('coursePrice').value = oldData.price || '';
                document.getElementById('courseStatus').value = oldData.status || 'active';
                document.getElementById('courseFeatured').checked = !!oldData.is_featured;

                const featuresContainer = document.getElementById('featuresContainer');
                featuresContainer.innerHTML = '';
                const restoredFeatures = Array.isArray(oldData.features)
                    ? oldData.features.filter(feature => feature !== null && String(feature).trim() !== '')
                    : [];

                if (restoredFeatures.length > 0) {
                    restoredFeatures.forEach(feature => addFeature(String(feature)));
                } else {
                    addFeature();
                }

                document.getElementById('imagePreview').style.display = 'none';
                updateDlCodePreview();
                document.getElementById('courseModal').style.display = 'flex';
            }

            function openPackageModalFromValidation(oldData) {
                const courseId = oldData.course_id;
                const packageId = oldData.package_id;
                
                openPackageModal(courseId, packageId);
                
                // Override with old values
                document.getElementById('packageName').value = oldData.name || '';
                document.getElementById('packageVehicleCategory').value = oldData.vehicle_category_id || '';
                document.getElementById('packageTransmission').value = oldData.transmission_type || 'manual';
                document.getElementById('packagePrice').value = oldData.price || '';
                document.getElementById('packageTdcHours').value = oldData.tdc_hours || '';
                document.getElementById('packagePdcHours').value = oldData.pdc_hours || '';
                document.getElementById('packageDescription').value = oldData.description || '';
                document.getElementById('packagePopular').checked = !!oldData.is_popular;

                const featuresContainer = document.getElementById('packageFeaturesContainer');
                featuresContainer.innerHTML = '';
                const restoredFeatures = Array.isArray(oldData.features)
                    ? oldData.features.filter(f => f !== null && String(f).trim() !== '')
                    : [];

                if (restoredFeatures.length > 0) {
                    restoredFeatures.forEach(f => addPackageFeature(String(f)));
                } else {
                    addPackageFeature();
                }
            }

            // View Toggle Functions
            function switchView(view) {
                const gridView = document.querySelector('.courses-grid');
                const listView = document.querySelector('.courses-list');
                const gridBtn = document.querySelector('.view-toggle-btn:nth-child(1)');
                const listBtn = document.querySelector('.view-toggle-btn:nth-child(2)');

                if (view === 'cards') {
                    if (gridView) gridView.classList.add('active');
                    if (listView) listView.classList.remove('active');
                    if (gridBtn) gridBtn.classList.add('active');
                    if (listBtn) listBtn.classList.remove('active');
                    localStorage.setItem('coursesView', 'cards');
                } else {
                    if (gridView) gridView.classList.remove('active');
                    if (listView) listView.classList.add('active');
                    if (gridBtn) gridBtn.classList.remove('active');
                    if (listBtn) listBtn.classList.add('active');
                    localStorage.setItem('coursesView', 'list');
                }
            }

            function applySort(sort) {
                const url = new URL(window.location.href);
                url.searchParams.set('sort', sort);
                // Reset to page 1 on new sort
                url.searchParams.set('page', 1);

                // Use our global loadPage function if available (consistent with sidebar ajax navigation)
                if (window.loadPage) {
                    window.loadPage(url.pathname + url.search);
                } else {
                    window.location.href = url.href;
                }
            }

            function initPackageTickers() {
                const containers = document.querySelectorAll('.packages-ticker-container');
                containers.forEach(container => {
                    const ticker = container.querySelector('.packages-ticker');
                    const items = ticker.querySelectorAll('.package-item');

                    // Only auto-scroll if there are enough items to warrant it (e.g. > 3)
                    if (items.length > 3) {
                        // Remove existing duplicates if any (for re-init)
                        const originalItems = Array.from(items);
                        ticker.innerHTML = '';
                        originalItems.forEach(item => ticker.appendChild(item.cloneNode(true)));

                        // Duplicate content for seamless scrolling
                        originalItems.forEach(item => ticker.appendChild(item.cloneNode(true)));

                        // Calculate animation duration based on content height
                        const height = ticker.scrollHeight / 2;
                        const speed = 25; // pixels per second
                        const duration = height / speed;

                        ticker.style.animation = `ticker-scroll ${duration}s linear infinite`;

                        // Add pause on hover logic via JS to ensure it works with manual scroll
                        container.addEventListener('mouseenter', () => {
                            ticker.style.animationPlayState = 'paused';
                        });
                        container.addEventListener('mouseleave', () => {
                            ticker.style.animationPlayState = 'running';
                        });
                    } else {
                        container.style.maxHeight = 'none';
                        container.style.maskImage = 'none';
                        container.style.webkitMaskImage = 'none';
                    }
                });
            }

            // Restore view preference on load
            document.addEventListener('DOMContentLoaded', function () {
                initCourseFormSubmitHandler();
                initPackageTickers();

                const savedView = localStorage.getItem('coursesView');
                if (savedView && savedView !== 'cards') {
                    switchView(savedView);
                }

                @if($errors->any())
                    @if(old('modal_type') === 'package')
                        openPackageModalFromValidation({
                            course_id: @json(old('course_id')),
                            package_id: @json(old('package_id')),
                            name: @json(old('name')),
                            vehicle_category_id: @json(old('vehicle_category_id')),
                            transmission_type: @json(old('transmission_type')),
                            price: @json(old('price')),
                            tdc_hours: @json(old('tdc_hours')),
                            pdc_hours: @json(old('pdc_hours')),
                            description: @json(old('description')),
                            is_popular: @json((bool) old('is_popular')),
                            features: @json(old('features', [])),
                        });
                    @else
                        openCourseModalFromValidation({
                            method: @json(old('_method', 'POST')),
                            course_id: @json(old('course_id')),
                            title: @json(old('title')),
                            description: @json(old('description')),
                            type: @json(old('type', 'standard')),
                            vehicle_type: @json(old('vehicle_type')),
                            course_type: @json(old('course_type')),
                            license_type: @json(old('license_type')),
                            hours_required: @json(old('hours_required')),
                            price: @json(old('price')),
                            status: @json(old('status', 'active')),
                            is_featured: @json((bool) old('is_featured')),
                            features: @json(old('features', [])),
                        });
                    @endif
                @endif
        });

            initCourseFormSubmitHandler();
            initPackageTickers();

            // Auto-hide alerts
            setTimeout(() => {
                const alerts = document.querySelectorAll('.flash-message');
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                });
            }, 5000);
        </script>

@endsection