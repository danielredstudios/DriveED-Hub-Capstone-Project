@php
    $school = $school ?? $currentSchool ?? null;
    $slug = $school?->slug ?? 'default';
    $settings = $school?->schoolSetting;
    
    // School branding
    $schoolName = $school?->name ?? 'DriveEd Hub';
    
    // Get custom colors from school settings or use defaults
    $primaryColor = $settings?->primary_color ?? '#2563eb';
    $secondaryColor = $settings?->secondary_color ?? '#f59e0b';
    
    // Login Header Customization Settings
    $headerLayout = $settings?->login_header_layout ?? 'horizontal';
    $logoImage = $settings?->login_logo_image;
    $logoPosition = $settings?->login_logo_position ?? 'left';
    $logoSize = $settings?->login_logo_size ?? 40;
    $schoolNameText = $settings?->login_school_name_text ?? $schoolName;
    $showSchoolName = $settings?->login_show_school_name ?? true;
    $schoolNamePosition = $settings?->login_school_name_position ?? 'left';
    $schoolNameSize = $settings?->login_school_name_size ?? 24;
    $welcomeText = $welcomeText ?? $settings?->login_welcome_text ?? 'Welcome to ' . $schoolName . '!';
    $subtitleText = $subtitleText ?? null;

    $showWelcomeText = $settings?->login_show_welcome_text ?? false;
    $welcomePosition = $settings?->login_welcome_position ?? 'right';
    $welcomeSize = $settings?->login_welcome_size ?? 16;
    $headerBgType = $settings?->login_header_bg_type ?? 'gradient';
    $headerBgColor = $settings?->login_header_bg_color;
    $headerBgImage = $settings?->login_header_bg_image;
    $headerHeight = $settings?->login_header_height ?? 60;
    $headerTextColor = $settings?->login_header_text_color ?? '#ffffff';
    $headerShadow = $settings?->login_header_shadow ?? true;
    
    // Check if gradient is enabled
    $useGradient = $settings?->use_gradient_header ?? false;
    
    // Generate header background
    if ($headerBgType === 'solid' && $headerBgColor) {
        $headerBackground = $headerBgColor;
    } elseif ($headerBgType === 'image' && $headerBgImage) {
        $headerBackground = "url('" . asset('storage/' . $headerBgImage) . "')";
    } elseif ($useGradient) {
        $headerBackground = "linear-gradient(135deg, {$primaryColor} 0%, {$secondaryColor} 100%)";
    } else {
        $headerBackground = $primaryColor;
    }

    // Page Background Settings (shared across all auth pages)
    $pageBgType = $settings?->login_page_bg_type ?? 'color';
    $pageBgColor = $settings?->login_page_bg_color ?? '#f5f5f5';
    $pageBgImage = $settings?->login_page_bg_image;
    $pageBgOpacity = $settings?->login_page_bg_opacity ?? 100;
    
    // Generate page background
    if ($pageBgType === 'image' && $pageBgImage) {
        $pageBackground = "url('" . asset('storage/' . $pageBgImage) . "')";
    } else {
        $pageBackground = $pageBgColor;
    }

    // Backward compatibility for existing views
    $backgroundImage = $pageBgImage ? asset('storage/' . $pageBgImage) : '';
@endphp

<style>
    /* Customizable Login Header Styles */
    .login-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
        height: {{ $headerHeight }}px;
        background: {{ $headerBgType === 'image' && $headerBgImage ? 'transparent' : $headerBackground }};
        @if($headerBgType === 'image' && $headerBgImage)
        background-image: {{ $headerBackground }};
        background-size: cover;
        background-position: center;
        @endif
        color: {{ $headerTextColor }};
        z-index: 1000;
        @if($headerShadow)
        box-shadow: 0 3px 20px rgba(0,0,0,0.15);
        @endif
        box-sizing: border-box;
    }
    
    .login-header-horizontal {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 25px;
    }
    
    .header-section {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .header-left { justify-content: flex-start; flex: 1; }
    .header-center { justify-content: center; flex: 1; }
    .header-right { justify-content: flex-end; flex: 1; }
    
    .login-header-vertical {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 15px 25px;
    }
    
    .header-vertical-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }
    
    .login-header-centered {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 15px 25px;
    }
    
    .header-centered-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    
    .header-main {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .login-header-logo-only {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 15px 25px;
    }
    
    .header-logo .logo-image {
        height: {{ $logoSize }}px;
        width: auto;
        object-fit: contain;
    }
    
    .header-school-name {
        font-size: {{ $schoolNameSize }}px;
        font-weight: 600;
        text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        white-space: nowrap;
    }
    
    .header-welcome {
        font-size: {{ $welcomeSize }}px;
        font-weight: 500;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }
    
    .header-subtitle {
        font-size: {{ max(12, $welcomeSize - 4) }}px;
        font-weight: 400;
        opacity: 0.9;
        margin-top: 2px;
    }

    /* Page Background & Branding Variables (fixed scope) */
    :root {
        --primary-color: {{ $primaryColor }};
        --secondary-color: {{ $secondaryColor }};
        --header-height: {{ $headerHeight }}px;
        --primary-gradient: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $primaryColor }}dd 100%);
        --secondary-gradient: linear-gradient(135deg, {{ $secondaryColor }} 0%, {{ $secondaryColor }}dd 100%);
        --school-bg: url('{{ $backgroundImage }}');
    }

    body::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        @if($pageBgType === 'image' && $pageBgImage)
        background: {{ $pageBackground }} no-repeat center center fixed;
        background-size: cover;
        @else
        background: {{ $pageBackground }};
        @endif
        opacity: {{ $pageBgOpacity / 100 }};
        z-index: -1;
    }

    /* Standard Main Content Layout for Auth Pages */
    .main-content {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - {{ $headerHeight }}px);
        margin-top: {{ $headerHeight }}px;
        padding: 15px;
        position: relative;
        z-index: 10;
        box-sizing: border-box;
    }

    /* Mobile Header Adjustments */
    @media (max-width: 480px) {
        .login-header {
            height: {{ max(42, $headerHeight - 12) }}px;
            padding: 0 10px;
        }
        
        .header-school-name {
            font-size: {{ max(14, $schoolNameSize - 10) }}px;
        }
        
        .header-logo .logo-image {
            height: {{ max(24, $logoSize - 16) }}px;
        }
    }
</style>

<nav class="login-header login-header-{{ $headerLayout }}">
    @if($headerLayout === 'horizontal')
        <div class="header-section header-left">
            @if($logoImage && $logoPosition === 'left')
                <div class="header-logo">
                    <img src="{{ asset('storage/' . $logoImage) }}" alt="Logo" class="logo-image">
                </div>
            @endif
            @if($showSchoolName && $schoolNamePosition === 'left')
                <div class="header-school-name">
                    {{ $schoolNameText }}
                </div>
            @endif
        </div>
        
        <div class="header-section header-center">
            @if($logoImage && $logoPosition === 'center')
                <div class="header-logo">
                    <img src="{{ asset('storage/' . $logoImage) }}" alt="Logo" class="logo-image">
                </div>
            @endif
            @if($showSchoolName && $schoolNamePosition === 'center')
                <div class="header-school-name">
                    {{ $schoolNameText }}
                </div>
            @endif
        </div>
        
        <div class="header-section header-right">
            @if($logoImage && $logoPosition === 'right')
                <div class="header-logo">
                    <img src="{{ asset('storage/' . $logoImage) }}" alt="Logo" class="logo-image">
                </div>
            @endif
            @if($showSchoolName && $schoolNamePosition === 'right')
                <div class="header-school-name">
                    {{ $schoolNameText }}
                </div>
            @endif
            @if($showWelcomeText && $welcomePosition === 'right')
                <div class="header-welcome">
                    {{ $welcomeText }}
                    @if($subtitleText)
                        <span class="header-subtitle">{{ $subtitleText }}</span>
                    @endif
                </div>
            @endif
        </div>
    @elseif($headerLayout === 'vertical')
        <div class="header-vertical-content">
            @if($logoImage)
                <div class="header-logo">
                    <img src="{{ asset('storage/' . $logoImage) }}" alt="Logo" class="logo-image">
                </div>
            @endif
            @if($showSchoolName)
                <div class="header-school-name">
                    {{ $schoolNameText }}
                </div>
            @endif
            @if($showWelcomeText)
                <div class="header-welcome">
                    {{ $welcomeText }}
                    @if($subtitleText)
                        <span class="header-subtitle">{{ $subtitleText }}</span>
                    @endif
                </div>
            @endif
        </div>
    @elseif($headerLayout === 'centered')
        <div class="header-centered-content">
            <div class="header-main">
                @if($logoImage)
                    <div class="header-logo">
                        <img src="{{ asset('storage/' . $logoImage) }}" alt="Logo" class="logo-image">
                    </div>
                @endif
                @if($showSchoolName)
                    <div class="header-school-name">
                        {{ $schoolNameText }}
                    </div>
                @endif
            </div>
            @if($showWelcomeText)
                <div class="header-welcome">
                    {{ $welcomeText }}
                    @if($subtitleText)
                        <span class="header-subtitle">{{ $subtitleText }}</span>
                    @endif
                </div>
            @endif
        </div>
    @elseif($headerLayout === 'logo-only')
        <div class="header-logo-only">
            @if($logoImage)
                <img src="{{ asset('storage/' . $logoImage) }}" alt="Logo" class="logo-image">
            @else
                <div class="header-school-name">{{ $schoolNameText }}</div>
            @endif
        </div>
    @endif
</nav>
