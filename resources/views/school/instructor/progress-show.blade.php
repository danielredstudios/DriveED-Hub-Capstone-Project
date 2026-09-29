@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Progress Details')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .detail-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        overflow: hidden;
        max-width: 800px;
    }

    .detail-header {
        background: {{ $primaryColor }};
        color: white;
        padding: 20px 24px;
    }

    .detail-header h2 { margin: 0 0 4px 0; font-size: 1.25rem; }
    .detail-header p { margin: 0; opacity: 0.9; font-size: 0.88rem; }

    .detail-body { padding: 24px; }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .info-item {
        padding: 14px;
        background: #f9fafb;
        border-radius: 10px;
    }

    .info-item label {
        display: block;
        font-size: 0.72rem;
        color: #9ca3af;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        font-weight: 600;
    }

    .info-item .value { font-size: 0.95rem; color: #1f2937; font-weight: 600; }

    .progress-visual { margin: 20px 0; }

    .progress-visual-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
        font-size: 0.88rem;
    }

    .progress-bar-container {
        background: #e5e7eb;
        border-radius: 8px;
        height: 26px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, {{ $primaryColor }}, #10b981);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.82rem;
        transition: width 0.5s ease;
    }

    .notes-section {
        margin-top: 16px;
        padding: 14px;
        background: #f9fafb;
        border-radius: 10px;
    }

    .notes-section h3 { margin: 0 0 8px 0; color: #374151; font-size: 0.9rem; }
    .notes-section p { margin: 0; color: #6b7280; line-height: 1.6; font-size: 0.88rem; }

    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid #f3f4f6;
    }

    .btn {
        padding: 10px 18px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-primary-action {
        background: {{ $primaryColor }};
        color: white;
    }

    .btn-primary-action:hover { background: {{ $secondaryColor }}; color: white; }

    .btn-danger {
        background: #ef4444;
        color: white;
    }

    .btn-danger:hover { background: #dc2626; color: white; }

    .inline-delete-form { display: inline; }
</style>

<div class="admin-container">
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Progress Details</h1>
            <p class="page-subtitle">Detailed view of student progress</p>
        </div>
    </div>
    
    <div class="detail-card">
        <div class="detail-header">
            <h2>{{ $progress->student->name ?? 'Student' }}</h2>
            <p>{{ $progress->course->title ?? 'Course' }}</p>
        </div>
        
        <div class="detail-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Student Email</label>
                    <div class="value">{{ $progress->student->email ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <label>Course</label>
                    <div class="value">{{ $progress->course->title ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <label>Last Updated</label>
                    <div class="value">{{ $progress->last_updated ? \Carbon\Carbon::parse($progress->last_updated)->format('M d, Y') : 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <label>Created</label>
                    <div class="value">{{ $progress->created_at ? $progress->created_at->format('M d, Y') : 'N/A' }}</div>
                </div>
            </div>
            
            <div class="progress-visual">
                <label class="progress-visual-label">Completion Progress</label>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" data-progress="{{ $progress->completion_percent ?? 0 }}">
                        {{ number_format($progress->completion_percent ?? 0, 1) }}%
                    </div>
                </div>
            </div>
            
            @if($progress->notes)
            <div class="notes-section">
                <h3>Notes</h3>
                <p>{{ $progress->notes }}</p>
            </div>
            @endif
            
            <div class="action-buttons">
                <a href="{{ $schoolRoute('instructor.progress.edit', ['progress' => $progress->id]) }}" class="btn btn-primary-action" onclick="loadContent(this.href); return false;">
                    Edit Progress
                </a>
                <form action="{{ $schoolRoute('instructor.progress.destroy', ['progress' => $progress->id]) }}" method="POST" class="inline-delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-danger" onclick="showConfirm({title:'Delete Progress',message:'Are you sure you want to delete this progress record?',type:'danger',onConfirm:()=>this.closest('form').submit()})">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.progress-bar-fill[data-progress]').forEach(function (bar) {
            const value = parseFloat(bar.getAttribute('data-progress'));
            const width = Number.isFinite(value) ? Math.max(0, Math.min(100, value)) : 0;
            bar.style.width = width + '%';
        });
    });
</script>
@endsection
