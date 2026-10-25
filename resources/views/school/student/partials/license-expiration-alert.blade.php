@php
    $hasExpiry = $student && $student->hasLicenseExpiration();
    $isExpired = $hasExpiry && $student->isLicenseExpired();
    $isExpiringSoon = $hasExpiry && $student->isLicenseExpiringSoon(60);
    $daysRemaining = $hasExpiry ? $student->getLicenseDaysRemaining() : null;
    $badge = $hasExpiry ? $student->getLicenseStatusBadge() : null;
@endphp

@if($hasExpiry && ($isExpired || $isExpiringSoon))
    <div class="license-alert-banner" style="
        margin-bottom: 24px;
        padding: 22px 24px;
        border-radius: 14px;
        background: {{ $isExpired ? '#fef2f2' : '#fffbeb' }};
        border: 2px solid {{ $isExpired ? '#ef4444' : '#f59e0b' }};
        color: {{ $isExpired ? '#991b1b' : '#92400e' }};
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    ">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
            <div style="display: flex; align-items: flex-start; gap: 16px; flex: 1; min-width: 280px;">
                <div style="
                    width: 48px;
                    height: 48px;
                    border-radius: 12px;
                    background: {{ $isExpired ? '#ef4444' : '#f59e0b' }};
                    color: white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 1.5rem;
                    flex-shrink: 0;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                ">
                    @if($isExpired)
                        <i class="bi bi-exclamation-octagon-fill"></i>
                    @else
                        <i class="bi bi-clock-history"></i>
                    @endif
                </div>
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 6px;">
                        <h4 style="margin: 0; font-size: 1.18rem; font-weight: 700; color: inherit;">
                            @if($isExpired)
                                Driver's License Expired
                            @else
                                Driver's License Approaching Expiration
                            @endif
                        </h4>
                        @if($badge)
                            <span style="
                                padding: 4px 12px;
                                border-radius: 9999px;
                                font-size: 0.78rem;
                                font-weight: 700;
                                background: {{ $isExpired ? '#ef4444' : '#f59e0b' }};
                                color: white;
                            ">
                                {{ $badge['label'] }}
                            </span>
                        @endif
                    </div>
                    
                    <p style="margin: 0 0 12px 0; font-size: 0.94rem; line-height: 1.5; color: inherit;">
                        @if($isExpired)
                            Your Driver's License <strong>{{ $student->license_number ? '(' . $student->license_number . ')' : '' }}</strong> expired on <strong>{{ $student->license_expiration_date->format('F d, Y') }}</strong> ({{ abs($daysRemaining) }} days ago).
                        @else
                            Your Driver's License <strong>{{ $student->license_number ? '(' . $student->license_number . ')' : '' }}</strong> is set to expire on <strong>{{ $student->license_expiration_date->format('F d, Y') }}</strong> ({{ $daysRemaining }} days remaining).
                        @endif
                    </p>

                    <div style="
                        background: rgba(255, 255, 255, 0.85);
                        border-left: 4px solid {{ $isExpired ? '#ef4444' : '#f59e0b' }};
                        padding: 12px 16px;
                        border-radius: 8px;
                        font-size: 0.86rem;
                        line-height: 1.5;
                        color: #374151;
                        margin-bottom: 12px;
                    ">
                        <strong><i class="bi bi-info-circle-fill" style="margin-right: 4px; color: {{ $isExpired ? '#ef4444' : '#f59e0b' }};"></i> Official LTO Regulatory Notice:</strong>
                        Driver's license renewals are officially processed directly via the Land Transportation Office (LTO) through the 
                        <a href="https://portal.lto.gov.ph" target="_blank" rel="noopener noreferrer" style="color: #2563eb; font-weight: 600; text-decoration: underline;">LTMS Portal</a>. 
                        DriveED Hub provides expiration monitoring, renewal checklists, and refresher training courses to support your driving journey.
                    </div>

                    <!-- Expandable Fast-Track Renewal Checklist & DriveED Perk -->
                    <div style="margin-top: 10px;">
                        <button type="button" onclick="document.getElementById('renewalGuideSection').classList.toggle('d-none')" style="
                            background: transparent;
                            border: 1px dashed {{ $isExpired ? '#f87171' : '#fbbf24' }};
                            color: {{ $isExpired ? '#991b1b' : '#92400e' }};
                            padding: 6px 12px;
                            border-radius: 6px;
                            font-size: 0.82rem;
                            font-weight: 600;
                            cursor: pointer;
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                        ">
                            <i class="bi bi-card-checklist"></i> View 4-Step LTO Renewal Guide & Exclusive DriveED Perks <i class="bi bi-chevron-down"></i>
                        </button>

                        <div id="renewalGuideSection" class="d-none" style="
                            margin-top: 12px;
                            padding: 16px;
                            background: #ffffff;
                            border-radius: 10px;
                            border: 1px solid #e5e7eb;
                            font-size: 0.85rem;
                            color: #374151;
                        ">
                            <div style="font-weight: 700; margin-bottom: 8px; color: #111827;">
                                <i class="bi bi-patch-check-fill text-primary"></i> 4 Simple Steps to Renew Your Driver's License:
                            </div>
                            <ol style="margin: 0 0 14px 0; padding-left: 20px; line-height: 1.6;">
                                <li><strong>Online CDE Exam:</strong> Visit <a href="https://portal.lto.gov.ph" target="_blank" rel="noopener noreferrer" style="color: #2563eb; text-decoration: underline;">portal.lto.gov.ph</a> and pass the free 25-item Comprehensive Driver's Education exam to receive your Certificate.</li>
                                <li><strong>Medical Examination:</strong> Undergo an accredited LTO medical test (your clinic transmits this directly to LTMS).</li>
                                <li><strong>Renewal Appointment:</strong> Book your schedule online or walk in to your nearest LTO Licensing Office.</li>
                                <li><strong>Update DriveED Profile:</strong> Keep your driving credentials current by updating your new expiration date here!</li>
                            </ol>

                            <div style="
                                background: #eff6ff;
                                border: 1px solid #bfdbfe;
                                border-radius: 8px;
                                padding: 12px 14px;
                                color: #1e40af;
                                font-size: 0.84rem;
                                line-height: 1.45;
                            ">
                                <strong><i class="bi bi-award me-1"></i> DriveED Advantage — Refresher Training &amp; DL Code Upgrades:</strong>
                                If you want to add Motorcycle (A/A1) or 4-Wheels (B/B1/B2) codes to your renewed license, or want a quick refresher session with our instructors before taking the road, check our 
                                <a href="{{ school_route('student.courses.index', [], $school) }}" style="color: #1d4ed8; font-weight: 700; text-decoration: underline;">Available Courses</a>.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <a href="https://portal.lto.gov.ph" target="_blank" rel="noopener noreferrer" style="
                    background: {{ $isExpired ? '#ef4444' : '#f59e0b' }};
                    color: white;
                    padding: 9px 16px;
                    border-radius: 8px;
                    font-size: 0.88rem;
                    font-weight: 600;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                ">
                    <i class="bi bi-box-arrow-up-right"></i> Open LTO LTMS
                </a>
                <a href="{{ school_route('student.profile', [], $school) }}" style="
                    background: white;
                    color: #374151;
                    border: 1px solid #d1d5db;
                    padding: 9px 16px;
                    border-radius: 8px;
                    font-size: 0.88rem;
                    font-weight: 600;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                ">
                    <i class="bi bi-pencil-square"></i> Update License Details
                </a>
            </div>
        </div>
    </div>
@endif
