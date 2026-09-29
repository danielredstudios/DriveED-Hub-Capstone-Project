@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Progress')

@section('content')
@php
    $schoolName = $school->name ?? 'Driving School';
    $settings = $school->schoolSetting;
    $primaryColor = $settings->primary_color ?? '#667eea';
    $secondaryColor = $settings->secondary_color ?? '#764ba2';
@endphp

<style>
.progress-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 4px solid {{ $primaryColor }};
}

.page-title {
    font-size: 2rem;
    color: #111827;
    margin: 0;
    font-weight: 400;
}

.progress-grid {
    display: grid;
    gap: 25px;
}

.progress-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.progress-header h3 {
    font-size: 1.5rem;
    color: #333;
}

.progress-percentage {
    font-size: 3rem;
    font-weight: 700;
    @if($settings->use_gradient_header ?? true)
        background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }});
    @else
        background: {{ $primaryColor }};
    @endif
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.progress-bar-container {
    width: 100%;
    height: 40px;
    background: #e5e7eb;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 25px;
}

.progress-bar-fill {
    height: 100%;
    @if($settings->use_gradient_header ?? true)
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
    @else
        background: {{ $primaryColor }};
    @endif
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 20px;
    transition: width 0.5s ease;
}

.progress-bar-text {
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
}

.progress-status-completed {
    color: #10b981;
}

.progress-status-in-progress {
    color: #3b82f6;
}

.progress-status-not-started {
    color: #6b7280;
}

.progress-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.info-item {
    padding: 20px;
    background: #f9fafb;
    border-radius: 10px;
}

.info-item .label {
    font-size: 0.9rem;
    color: #6b7280;
    margin-bottom: 8px;
}

.info-item .value {
    font-size: 1.2rem;
    color: #1f2937;
    font-weight: 600;
}

.notes-section {
    margin-top: 20px;
    padding: 20px;
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    border-radius: 8px;
}

.notes-section h4 {
    margin: 0 0 10px 0;
    color: #92400e;
}

.notes-section p {
    margin: 0;
    color: #78350f;
    line-height: 1.6;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .progress-container {
        padding: 20px 15px;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .progress-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .progress-card {
        padding: 20px;
    }
    
    .progress-header h3 {
        font-size: 1.5rem;
    }
    
    .progress-percentage {
        font-size: 2rem;
    }
    
    .progress-bar-container {
        height: 30px;
    }
    
    .progress-bar-fill {
        font-size: 13px;
    }
    
    .progress-info {
        padding: 15px;
    }
    
    .info-item {
        padding: 10px;
    }
    
    .info-item .label {
        font-size: 13px;
    }
    
    .info-item .value {
        font-size: 20px;
    }
    
    .notes-section {
        padding: 15px;
    }
}

@media (max-width: 480px) {
    .progress-container {
        padding: 15px 10px;
    }
    
    .page-title {
        font-size: 1.25rem;
    }
    
    .progress-card {
        padding: 15px;
    }
    
    .progress-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .progress-header h3 {
        font-size: 1.25rem;
    }
    
    .progress-percentage {
        font-size: 1.75rem;
    }
    
    .progress-bar-container {
        height: 25px;
    }
    
    .progress-bar-fill {
        font-size: 12px;
    }
    
    .progress-info {
        grid-template-columns: 1fr;
        padding: 12px;
        gap: 8px;
    }
    
    .info-item {
        padding: 8px;
    }
    
    .info-item .label {
        font-size: 12px;
    }
    
    .info-item .value {
        font-size: 18px;
    }
    
    .notes-section {
        padding: 12px;
    }
    
    .notes-section h4 {
        font-size: 14px;
    }
    
    .notes-section p {
        font-size: 13px;
    }
}
</style>

<div class="progress-container">
    <div class="page-header">
        <h1 class="page-title">My Progress</h1>
    </div>

    <div class="progress-grid">
        @forelse($progresses as $progress)
        <div class="progress-card">
            <div class="progress-header">
                <h3>{{ $progress->course->title }}</h3>
                <div class="progress-percentage">{{ $progress->completion_percent }}%</div>
            </div>

            <div class="progress-bar-container">
                <div class="progress-bar-fill progress-fill" data-progress="{{ $progress->completion_percent }}">
                    @if($progress->completion_percent > 10)
                    <span class="progress-bar-text">{{ $progress->completion_percent }}% Complete</span>
                    @endif
                </div>
            </div>

            <div class="progress-info">
                <div class="info-item">
                    <div class="label">Status</div>
                    <div class="value">
                        @if($progress->completion_percent == 100)
                            <span class="progress-status-completed">✓ Completed</span>
                        @elseif($progress->completion_percent > 0)
                            <span class="progress-status-in-progress">● In Progress</span>
                        @else
                            <span class="progress-status-not-started">○ Not Started</span>
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">Last Updated</div>
                    <div class="value">{{ $progress->last_updated ? $progress->last_updated->format('M d, Y') : 'Never' }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Total Hours</div>
                    <div class="value">{{ $progress->course->duration_hours }} hours</div>
                </div>
            </div>

            @if($progress->notes)
            <div class="notes-section">
                <h4>Instructor's Notes</h4>
                <p>{{ $progress->notes }}</p>
            </div>
            @endif
        </div>
        @empty
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <p class="empty-state-title">No progress records found</p>
            <p class="empty-state-text">Your instructor will update your progress after each lesson</p>
        </div>
        @endforelse
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.progress-fill').forEach(function (bar) {
        const value = Number(bar.dataset.progress || 0);
        const clamped = Math.max(0, Math.min(100, value));
        bar.style.width = clamped + '%';
    });
});
</script>
@endsection
