@extends('layouts.system-admin')
@section('title', 'Payments')
@section('page-title', 'All Payments')
@section('styles')
<style>
    .payments-value-paid { color: #059669; }
    .payments-value-pending { color: #f59e0b; }
</style>
@endsection
@section('content')
<div class="stats-grid stats-grid-two">
    <div class="stat-card">
        <h3>Total Approved</h3>
        <div class="value payments-value-paid">₱{{ number_format($totalPaid, 2) }}</div>
    </div>
    <div class="stat-card">
        <h3>Pending Payments</h3>
        <div class="value payments-value-pending">₱{{ number_format($totalPending, 2) }}</div>
    </div>
</div>
<div class="card">
    <div class="card-header"><h3>Payments ({{ $payments->total() }})</h3></div>
    <div class="card-body">
        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Method</th>
                    <th>School</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td>{{ $payment->booking->student->name ?? 'N/A' }}</td>
                    <td><strong>₱{{ number_format($payment->amount, 2) }}</strong></td>
                    <td>
                        <span class="badge {{ $payment->status === 'approved' ? 'badge-success' : 'badge-warning' }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </td>
                    <td>{{ ucfirst($payment->method ?? 'N/A') }}</td>
                    <td>{{ $payment->school->name }}</td>
                    <td>{{ $payment->received_at ? $payment->received_at->format('M d, Y') : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        {{ $payments->links() }}
    </div>
</div>
@endsection
