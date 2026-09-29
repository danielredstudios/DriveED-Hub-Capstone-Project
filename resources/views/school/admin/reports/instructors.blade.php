@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Instructor Reports')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $schoolName = $school->name ?? 'Driving School';
    $primaryColor = $settings->primary_color ?? '#667eea';
@endphp

<style>
    .report-placeholder-container {
        padding: 20px;
        margin: 20px auto;
        max-width: 1600px;
    }

    .report-placeholder-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 3px solid {{ $primaryColor }};
    }

    .report-placeholder-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .report-placeholder-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    .report-placeholder-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        padding: 60px 40px;
        text-align: center;
    }

    .report-placeholder-icon-wrap {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, #764ba2 100%);
        border-radius: 50%;
        margin: 0 auto 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .report-placeholder-heading {
        font-size: 1.25rem;
        color: #1f2937;
        margin-bottom: 10px;
    }

    .report-placeholder-text {
        color: #6b7280;
        max-width: 400px;
        margin: 0 auto;
    }
</style>

<div class="report-placeholder-container">
    <div class="report-placeholder-header">
        <div>
            <h1 class="report-placeholder-title">Instructor Reports</h1>
            <p class="report-placeholder-subtitle">Instructor performance and activity reports for {{ $schoolName }}</p>
        </div>
    </div>
    
    <div class="report-placeholder-card">
        <div class="report-placeholder-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        </div>
        <h3 class="report-placeholder-heading">Instructor Reports Coming Soon</h3>
        <p class="report-placeholder-text">Comprehensive instructor performance, availability, and activity reports will be available here.</p>
    </div>
</div>
@endsection