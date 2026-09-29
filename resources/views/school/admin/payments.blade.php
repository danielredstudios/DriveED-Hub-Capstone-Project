@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Payments & Transactions')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .method-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.85rem;
        background: #e0e7ff;
        color: #3730a3;
    }
    
    .amount-cell {
        font-weight: 600;
        color: #059669;
    }
    
    .reference-cell {
        font-family: monospace;
        font-size: 0.85rem;
        color: #6b7280;
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
    
    .btn-export-excel {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .btn-export-excel:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        color: white;
    }
    
    /* Mark as Paid Button */
    .btn-mark-paid {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    
    .btn-mark-paid:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
    }

    .icon-24 {
        width: 24px;
        height: 24px;
    }

    .icon-16 {
        width: 16px;
        height: 16px;
        margin-right: 4px;
    }

    .table-scroll {
        overflow-x: auto;
    }

    .admin-table thead {
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        color: #ffffff;
    }

    .admin-table th {
        background: transparent;
        color: inherit;
        padding: 15px;
        text-transform: none;
        letter-spacing: 0;
        font-size: 0.95rem;
    }

    .payment-status-paid {
        color: #059669;
        font-size: 0.85rem;
    }

    .payment-status-none {
        color: #6b7280;
        font-size: 0.85rem;
    }

    .mobile-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .mobile-amount {
        color: #10b981;
    }

    .mobile-action-wrap {
        margin-top: 10px;
    }

    .btn-mark-paid-full {
        width: 100%;
        padding: 10px;
        min-height: 44px;
        text-align: center;
    }
    
    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .admin-container {
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
        }
        
        .page-subtitle {
            font-size: 0.85rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .stat-card {
            padding: 18px;
        }
        
        .stat-value {
            font-size: 1.6rem;
        }
        
        .filter-group {
            flex-direction: column;
        }
        
        .filter-btn {
            width: 100%;
            text-align: center;
        }
        
        .admin-table th,
        .admin-table td {
            padding: 10px 8px;
            font-size: 0.85rem;
        }
        
        /* Hide less important columns on mobile */
        .admin-table th:nth-child(5),
        .admin-table td:nth-child(5),
        .admin-table th:nth-child(6),
        .admin-table td:nth-child(6) {
            display: none;
        }
        
        .method-badge {
            padding: 3px 6px;
            font-size: 0.75rem;
        }
        
        .amount-cell {
            font-size: 0.9rem;
        }
        
        .export-buttons {
            width: 100%;
            justify-content: center;
        }
    }
    
    @media (max-width: 480px) {
        .admin-container {
            padding: 10px;
            margin: 5px auto;
        }
        
        .page-title {
            font-size: 1.2rem;
        }
        
        .stat-card {
            padding: 14px;
        }
        
        .stat-value {
            font-size: 1.4rem;
        }
        
        .stat-label {
            font-size: 0.75rem;
        }
        
        .filter-btn {
            padding: 8px 12px;
            font-size: 0.85rem;
        }
        
        .admin-table th,
        .admin-table td {
            padding: 8px 6px;
            font-size: 0.8rem;
        }
        
        /* Hide more columns on very small screens */
        .admin-table th:nth-child(3),
        .admin-table td:nth-child(3) {
            display: none;
        }
        
        .btn-export {
            padding: 8px 12px;
            font-size: 0.8rem;
        }
    }
    
    /* Mobile card layout */
    .payment-mobile-card {
        display: none;
        background: white;
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 10px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        border-left: 4px solid {{ $primaryColor }};
    }
    
    .payment-mobile-card .card-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .payment-mobile-card .card-row:last-child { border-bottom: none; }
    .payment-mobile-card .card-label { color: #6b7280; font-size: 0.8rem; font-weight: 500; }
    .payment-mobile-card .card-val { font-weight: 600; color: #1f2937; font-size: 0.85rem; }
    
    @media (max-width: 768px) {
        .content-card .table-scroll { display: none; }
        .payment-mobile-card { display: block; }
    }
</style>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Payments & Transactions</h1>
            <p class="page-subtitle">Track and manage all payments for {{ $schoolName }}</p>
        </div>
        <div class="export-buttons">
            <a href="{{ school_route('admin.exports.payments.pdf') }}" class="btn-export btn-export-pdf">
                Export PDF
            </a>
            <a href="{{ school_route('admin.exports.payments.excel') }}" class="btn-export btn-export-excel">
                Export Excel
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-value">₱{{ number_format($stats['total_revenue'], 2) }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card growth">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Approved Payments</div>
                        <div class="stat-value">{{ $stats['approved_count'] }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card inactive">
             <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Pending Verifications</div>
                        <div class="stat-value">{{ $stats['pending_count'] }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-group">
        <button class="filter-btn active" data-filter="all" onclick="filterPayments('all', this)">All</button>
        <button class="filter-btn" data-filter="approved" onclick="filterPayments('approved', this)">Approved</button>
        <button class="filter-btn" data-filter="pending" onclick="filterPayments('pending', this)">Pending</button>
        <button class="filter-btn" data-filter="rejected" onclick="filterPayments('rejected', this)">Rejected</button>
    </div>

    <!-- Payments Table -->
    <div class="content-card">
        <div class="table-scroll">
            <table class="admin-table" id="paymentsTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr data-status="{{ $payment->status }}" id="payment-row-{{ $payment->id }}">
                        <td>
                            {{ $payment->received_at ? $payment->received_at->format('M d, Y') : 
                               ($payment->paid_on ? $payment->paid_on->format('M d, Y') : 
                               $payment->created_at->format('M d, Y')) }}
                        </td>
                        <td><strong>{{ $payment->payer->name ?? 'Guest' }}</strong></td>
                        <td>{{ $payment->booking->course->title ?? $payment->enrollmentRequest->course->title ?? 'N/A' }}</td>
                        <td class="amount-cell">₱{{ number_format($payment->amount, 2) }}</td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 6px; align-items: flex-start;">
                                <span class="method-badge" style="margin-bottom: 2px;">{{ ucfirst($payment->method ?? 'N/A') }}</span>
                                @if($payment->proof_url)
                                    <a href="{{ $payment->proof_url }}" target="_blank" class="text-xs text-blue-600 hover:underline" style="display: inline-flex; align-items: center; gap: 4px; font-weight: 500;">
                                        <i class="bi bi-receipt"></i> View Receipt
                                    </a>
                                @endif
                            </div>
                        </td>
                        <td class="reference-cell">
                            {{ $payment->reference ?? $payment->or_number ?? '-' }}
                            @if($payment->normalized_reference || $payment->normalized_or_number)
                                <div class="text-xs text-gray-400">Norm: {{ $payment->normalized_reference ?? $payment->normalized_or_number }}</div>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusClass = match($payment->status) {
                                    'approved' => 'success',
                                    'pending' => 'warning',
                                    'rejected' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge badge-{{ $statusClass }}" id="payment-badge-{{ $payment->id }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-state-title">No payments found</div>
                                <div class="empty-state-text">Payment records will appear here once transactions are made.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Mobile card view --}}
        @forelse($payments as $payment)
        <div class="payment-mobile-card" data-status="{{ $payment->status }}">
            <div class="mobile-card-header">
                <strong>{{ $payment->booking->student->name ?? 'N/A' }}</strong>
                <span class="badge badge-{{ in_array($payment->status, ['completed', 'approved']) ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">{{ ucfirst($payment->status) }}</span>
            </div>
            <div class="card-row">
                <span class="card-label">Course</span>
                <span class="card-val">{{ $payment->booking->course->title ?? 'N/A' }}</span>
            </div>
            <div class="card-row">
                <span class="card-label">Amount</span>
                <span class="card-val mobile-amount">₱{{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="card-row">
                <span class="card-label">Method</span>
                <span class="card-val">{{ ucfirst($payment->method ?? 'N/A') }}</span>
            </div>
            <div class="card-row">
                <span class="card-label">Date</span>
                <span class="card-val">
                    {{ $payment->received_at ? $payment->received_at->format('M d, Y') : 
                       ($payment->paid_on ? $payment->paid_on->format('M d, Y') : 
                       $payment->created_at->format('M d, Y')) }}
                </span>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <p class="empty-state-title">No payments found</p>
            <p class="empty-state-text">Payment records will appear here</p>
        </div>
        @endforelse
    </div>
    <div class="mt-4">
        {{ $payments->links() }}
    </div>
</div>

<script>
function filterPayments(status, btn) {
    const rows = document.querySelectorAll('#paymentsTable tbody tr[data-status]');
    const buttons = document.querySelectorAll('.filter-btn');
    
    buttons.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    rows.forEach(row => {
        const rowStatus = row.dataset.status;
        if (status === 'all' || rowStatus === status) {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
@endsection
