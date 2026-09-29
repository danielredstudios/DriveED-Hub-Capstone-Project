<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #667eea; padding-bottom: 15px; }
        .header h1 { color: #667eea; margin: 5px 0; font-size: 24px; }
        .header p { color: #666; margin: 3px 0; }
        .info-box { background: #f3f4f6; padding: 10px; border-radius: 5px; margin: 15px 0; }
        .summary-grid { display: flex; justify-content: space-between; margin: 15px 0; }
        .summary-item { text-align: center; padding: 10px; background: #f9fafb; border-radius: 5px; flex: 1; margin: 0 5px; }
        .summary-value { font-size: 18px; font-weight: bold; color: #059669; }
        .summary-label { font-size: 10px; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #667eea; color: white; padding: 10px; text-align: left; font-weight: 600; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
        .status-badge { padding: 3px 8px; border-radius: 3px; font-size: 10px; font-weight: 600; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-failed { background: #fee2e2; color: #991b1b; }
        .amount { font-weight: 600; color: #059669; }
        .reference { font-family: monospace; font-size: 10px; color: #6b7280; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $school->name }}</h1>
        <p>Payments Report {{ $status !== 'all' ? '(' . ucfirst($status) . ' Only)' : '' }}</p>
        <p>Generated: {{ $generatedAt->format('F d, Y h:i A') }}</p>
    </div>

    <div class="info-box">
        <strong>Total Payments:</strong> {{ $payments->count() }} |
        <strong>Total Revenue:</strong> ₱{{ number_format($payments->whereIn('status', ['completed', 'paid'])->sum('amount'), 2) }}
    </div>

    <table style="margin: 15px 0; width: 100%;">
        <tr>
            <td style="padding: 10px; background: #d1fae5; text-align: center; border-radius: 5px;">
                <div style="font-size: 16px; font-weight: bold; color: #065f46;">₱{{ number_format($payments->where('status', 'completed')->sum('amount'), 2) }}</div>
                <div style="font-size: 10px; color: #047857;">Completed ({{ $payments->where('status', 'completed')->count() }})</div>
            </td>
            <td style="padding: 10px; background: #fef3c7; text-align: center; border-radius: 5px;">
                <div style="font-size: 16px; font-weight: bold; color: #92400e;">₱{{ number_format($payments->where('status', 'pending')->sum('amount'), 2) }}</div>
                <div style="font-size: 10px; color: #b45309;">Pending ({{ $payments->where('status', 'pending')->count() }})</div>
            </td>
            <td style="padding: 10px; background: #fee2e2; text-align: center; border-radius: 5px;">
                <div style="font-size: 16px; font-weight: bold; color: #991b1b;">₱{{ number_format($payments->where('status', 'failed')->sum('amount'), 2) }}</div>
                <div style="font-size: 10px; color: #b91c1c;">Failed ({{ $payments->where('status', 'failed')->count() }})</div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>#</th>
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
            @foreach($payments as $index => $payment)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $payment->paid_on ? $payment->paid_on->format('M d, Y') : 'N/A' }}</td>
                <td>{{ $payment->booking->student->name ?? 'N/A' }}</td>
                <td>{{ $payment->booking->course->title ?? 'N/A' }}</td>
                <td class="amount">₱{{ number_format($payment->amount, 2) }}</td>
                <td>{{ ucfirst($payment->method ?? 'N/A') }}</td>
                <td class="reference">{{ $payment->reference ?? '-' }}</td>
                <td>
                    <span class="status-badge status-{{ $payment->status }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report is confidential and for internal use only.</p>
        <p>&copy; {{ date('Y') }} {{ $school->name }}. All rights reserved.</p>
    </div>
</body>
</html>
