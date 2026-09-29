@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    
    // Core Branding
    $primaryColor = $settings?->primary_color ?? '#2563eb';
    $secondaryColor = $settings?->secondary_color ?? '#f59e0b';
    
    // Header & Logo Layout
    $headerHeight = $settings?->login_header_height ?? 60;
    $logoSize = $settings?->login_logo_size ?? 40;
    $schoolNameSize = $settings?->login_school_name_size ?? 24;
    $welcomeSize = $settings?->login_welcome_size ?? 16;
    
    // Backgrounds
    $pageBgType = $settings?->login_page_bg_type ?? 'color';
    $pageBgImage = $settings?->login_page_bg_image;
    $backgroundImage = $pageBgImage ? asset('storage/' . $pageBgImage) : '';
@endphp
