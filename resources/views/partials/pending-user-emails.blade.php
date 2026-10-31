<details class="table-card" style="margin:20px 0;padding:20px;background:#fff;border:1px solid #e5e7eb;border-radius:12px;">
    <summary style="cursor:pointer;font-weight:600;padding:8px 0;">Pending Emails ({{ $pendingInvitations->count() }})</summary>
    <p style="margin:16px 0 20px;line-height:1.7;max-width:70ch;">Student and instructor account setup emails awaiting completion.</p>
    <div style="overflow-x:auto;">
        <table class="table">
            <thead><tr><th>Recipient</th><th>Role</th><th>Status</th><th>Sent</th><th>Expires</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($pendingInvitations as $invitation)
                <tr>
                    <td style="padding:16px;line-height:1.7;"><strong style="display:block;">{{ $invitation->payload['name'] ?? 'Account setup' }}</strong><span style="display:block;overflow-wrap:anywhere;color:#475569;">{{ $invitation->email }}</span></td>
                    <td>{{ ucfirst($invitation->role) }}</td>
                    <td>{{ $invitation->isExpired() ? 'Expired' : 'Pending' }}</td>
                    <td>{{ $invitation->created_at->format('M d, Y H:i') }}</td>
                    <td>{{ $invitation->expires_at?->format('M d, Y H:i') ?? 'No expiry' }}</td>
                    <td>
                        @if($admin->isSchoolAdmin())
                        <form method="POST" action="{{ route('schools.admin.invitations.resend', ['school' => $school, 'invitation' => $invitation]) }}" style="display:inline">@csrf<button class="btn btn-primary" type="submit">Resend Email</button></form>
                        <form method="POST" action="{{ route('schools.admin.invitations.cancel', ['school' => $school, 'invitation' => $invitation]) }}" style="display:inline" onsubmit="return confirm('Remove this pending account setup?')">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Remove</button></form>
                        @else
                        School admin manages setup emails.
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No pending emails.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</details>
