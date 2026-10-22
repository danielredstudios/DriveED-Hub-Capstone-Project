@if($claimNotice ?? null)
    <aside role="status" style="padding:20px 24px; margin-bottom:24px; border:1px solid #a7d7c5; border-left:5px solid #16704a; border-radius:10px; background:#f0faf4; color:#174631;">
        <h2 style="font-size:1.1rem; margin:0 0 8px;">{{ $claimNotice->title }}</h2>
        <p style="margin:0; white-space:pre-wrap; overflow-wrap:anywhere;">{{ $claimNotice->message }}</p>
        <small style="display:block; margin-top:10px; color:#2c6b4f;">Sent by your driving school on {{ $claimNotice->created_at->timezone($school->timezone ?? 'Asia/Manila')->format('M d, Y \a\t g:i A') }}.</small>
    </aside>
@endif
