@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'System Logs')

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
            <h1 class="report-placeholder-title">System Logs</h1>
            <p class="report-placeholder-subtitle">Recent activities and audit logs for {{ $schoolName }}</p>
        </div>
    </div>
    
    <div class="report-placeholder-card">
        <div class="report-placeholder-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
        </div>
        <h3 class="report-placeholder-heading">System Logs Coming Soon</h3>
        <p class="report-placeholder-text">Activity tracking and audit logs for your school will be available here.</p>
    </div>
</div>
@endsection