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
        padding: 20px 24px;
        border-radius: 12px;
        background: {{ $isExpired ? '#fee2e2' : '#fef3c7' }};
        border: 2px solid {{ $isExpired ? '#ef4444' : '#f59e0b' }};
        color: {{ $isExpired ? '#991b1b' : '#92400e' }};
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    ">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
            <div style="display: flex; align-items: flex-start; gap: 16px; flex: 1; min-width: 280px;">
                <div style="
                    width: 44px;
                    height: 44px;
                    border-radius: 10px;
                    background: {{ $isExpired ? '#ef4444' : '#f59e0b' }};
                    color: white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 1.4rem;
                    flex-shrink: 0;
                ">
                    @if($isExpired)
                        <i class="bi bi-exclamation-octagon-fill"></i>
                    @else
                        <i class="bi bi-clock-history"></i>
                    @endif
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 4px;">
                        <h4 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: inherit;">
                            @if($isExpired)
                                Driver's License Expired
                            @else
                                Driver's License Approaching Expiration
                            @endif
                        </h4>
                        <span style="
                            padding: 3px 10px;
                            border-radius: 9999px;
                            font-size: 0.78rem;
                            font-weight: 700;
                            background: {{ $isExpired ? '#ef4444' : '#f59e0b' }};
                            color: white;
                        ">
                            {{ $badge['label'] ?? '' }}
                        </span>
                    </div>
                    
                    <p style="margin: 0 0 10px 0; font-size: 0.92rem; line-height: 1.5; color: inherit;">
                        @if($isExpired)
                            Your Driver's License <strong>{{ $student->license_number ? '(' . $student->license_number . ')' : '' }}</strong> expired on <strong>{{ $student->license_expiration_date->format('F d, Y') }}</strong> ({{ abs($daysRemaining) }} days ago).
                        @else
                            Your Driver's License <strong>{{ $student->license_number ? '(' . $student->license_number . ')' : '' }}</strong> is set to expire on <strong>{{ $student->license_expiration_date->format('F d, Y') }}</strong> ({{ $daysRemaining }} days remaining).
                        @endif
                    </p>

                    <div style="
                        background: rgba(255, 255, 255, 0.75);
                        border-left: 4px solid {{ $isExpired ? '#ef4444' : '#f59e0b' }};
                        padding: 10px 14px;
                        border-radius: 6px;
                        font-size: 0.85rem;
                        line-height: 1.45;
                        color: #374151;
                    ">
                        <strong><i class="bi bi-info-circle-fill" style="margin-right: 4px; color: {{ $isExpired ? '#ef4444' : '#f59e0b' }};"></i> Official LTO Regulatory Notice:</strong>
                        Driver's license renewal must be processed directly through the Land Transportation Office (LTO) via the 
                        <a href="https://portal.lto.gov.ph" target="_blank" rel="noopener noreferrer" style="color: #2563eb; font-weight: 600; text-decoration: underline;">LTMS Portal</a>, 
                        not through the driving school or application. DriveED Hub provides lessons, training certifications, and preparatory guidance to support your renewal.
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
