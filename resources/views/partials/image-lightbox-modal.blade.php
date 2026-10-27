<!-- DriveED Hub - Reusable Image Preview & Zoom Lightbox Modal (For receipts, IDs, and general images) -->
<div id="driveedImageLightbox" class="driveed-lightbox" aria-hidden="true" role="dialog" aria-modal="true" style="display: none;">
    <!-- Backdrop -->
    <div class="driveed-lightbox-backdrop" onclick="closeImageLightbox()"></div>

    <!-- Main Container -->
    <div class="driveed-lightbox-container" role="document">
        <!-- Top Toolbar -->
        <header class="driveed-lightbox-header">
            <div class="driveed-lightbox-info">
                <span class="driveed-lightbox-icon">
                    <i class="fas fa-search-plus" id="driveedLightboxHeaderIcon"></i>
                </span>
                <div class="driveed-lightbox-text">
                    <h3 class="driveed-lightbox-title" id="driveedLightboxTitle">Image Preview</h3>
                    <p class="driveed-lightbox-subtitle" id="driveedLightboxSubtitle">Zoom, pan, rotate, or download</p>
                </div>
                <div class="driveed-lightbox-qr-badge" id="driveedLightboxQrBadge" style="display: none;">
                    <i class="fas fa-qrcode"></i>
                    <span>Ready for Camera / GCash Scanning</span>
                </div>
            </div>

            <!-- Controls -->
            <div class="driveed-lightbox-actions">
                <button type="button" class="driveed-lb-btn" onclick="lightboxZoomIn()" title="Zoom In (+)" aria-label="Zoom In">
                    <svg class="lb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                    <span class="lb-btn-text">Zoom In</span>
                </button>
                <button type="button" class="driveed-lb-btn" onclick="lightboxZoomOut()" title="Zoom Out (-)" aria-label="Zoom Out">
                    <svg class="lb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                    <span class="lb-btn-text">Zoom Out</span>
                </button>
                <button type="button" class="driveed-lb-btn" onclick="lightboxReset()" title="Reset Size (0)" aria-label="Reset Zoom">
                    <svg class="lb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
                    <span class="lb-btn-text">Reset</span>
                </button>
                <button type="button" class="driveed-lb-btn" onclick="lightboxRotate()" title="Rotate 90° (R)" aria-label="Rotate">
                    <svg class="lb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path></svg>
                    <span class="lb-btn-text">Rotate</span>
                </button>
                <button type="button" class="driveed-lb-btn" onclick="lightboxToggleFullscreen()" title="Fullscreen" aria-label="Fullscreen">
                    <svg class="lb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg>
                    <span class="lb-btn-text">Fullscreen</span>
                </button>
                <a id="driveedLightboxDownload" href="#" download class="driveed-lb-btn" title="Download Image" target="_blank" aria-label="Download">
                    <svg class="lb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    <span class="lb-btn-text">Download</span>
                </a>
                <button type="button" class="driveed-lb-btn driveed-lb-btn-close" onclick="closeImageLightbox()" title="Close (Esc)" aria-label="Close Lightbox">
                    <svg class="lb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </header>

        <!-- Viewport / Stage -->
        <div class="driveed-lightbox-viewport" id="driveedLightboxViewport" ondblclick="lightboxToggleDoubleZoom(event)">
            <!-- Loading Indicator -->
            <div class="driveed-lightbox-loader" id="driveedLightboxLoader">
                <div class="lb-spinner"></div>
                <span>Loading image...</span>
            </div>

            <!-- Image Stage -->
            <div class="driveed-lightbox-stage" id="driveedLightboxStage">
                <img id="driveedLightboxImage" src="" alt="Preview Image" draggable="false">
            </div>

            <!-- Floating Scan / Zoom Tip Bar -->
            <div class="driveed-lightbox-footer-tip" id="driveedLightboxFooterTip">
                <span class="lb-zoom-level" id="driveedLightboxZoomLevel" onclick="lightboxReset()" title="Click to reset to 100%">100%</span>
                <span class="lb-tip-divider">•</span>
                <span class="lb-tip-message" id="driveedLightboxTipMsg">
                    <i class="fas fa-mouse me-1"></i> Scroll to zoom &nbsp;|&nbsp; <i class="fas fa-hand-paper me-1"></i> Drag to pan &nbsp;|&nbsp; Double-click to enlarge
                </span>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
     DriveED Hub - Dedicated QR Code Scan Modal Popup
     High-contrast white card specifically optimized for camera & GCash scanning
     ========================================================================== -->
<div id="driveedQrScanModal" class="driveed-qr-modal" aria-hidden="true" role="dialog" aria-modal="true" style="display: none;">
    <!-- Backdrop -->
    <div class="driveed-qr-backdrop" onclick="closeQrScanModal()"></div>

    <!-- Dialog Card -->
    <div class="driveed-qr-dialog" role="document">
        <!-- Header -->
        <div class="driveed-qr-header">
            <div class="driveed-qr-header-left">
                <div class="driveed-qr-header-icon">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div>
                    <h3 class="driveed-qr-title" id="driveedQrModalTitle">Scan QR Code to Pay</h3>
                    <p class="driveed-qr-subtitle" id="driveedQrModalSubtitle">Scan with GCash or your mobile banking app</p>
                </div>
            </div>
            <button type="button" class="driveed-qr-close" onclick="closeQrScanModal()" aria-label="Close QR Modal" title="Close (Esc)">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <!-- Body -->
        <div class="driveed-qr-body">
            <!-- Ready Scan Pill -->
            <div class="driveed-qr-badge">
                <span class="qr-pulse-dot"></span>
                <span>Camera-Ready • High Contrast QR</span>
            </div>

            <!-- QR Stage Container -->
            <div class="driveed-qr-stage">
                <div class="driveed-qr-img-frame" id="driveedQrImgFrame">
                    <img id="driveedQrModalImg" src="" alt="Payment QR Code" draggable="false">
                </div>

                <!-- Zoom Controls -->
                <div class="driveed-qr-zoom-bar">
                    <button type="button" class="qr-zoom-btn" onclick="qrZoomOut()" title="Zoom Out (-)" aria-label="Zoom Out">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                        <span>Zoom Out</span>
                    </button>
                    <span class="qr-zoom-level" id="driveedQrZoomLevel">100%</span>
                    <button type="button" class="qr-zoom-btn" onclick="qrZoomIn()" title="Zoom In (+)" aria-label="Zoom In">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                        <span>Zoom In</span>
                    </button>
                    <button type="button" class="qr-zoom-btn qr-zoom-reset" onclick="qrResetZoom()" title="Reset Size" aria-label="Reset Size">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Account Details Card -->
            <div class="driveed-qr-info-card" id="driveedQrInfoCard">
                <div class="qr-info-row">
                    <span class="qr-info-label">GCash Account Name</span>
                    <strong class="qr-info-value" id="driveedQrAccName">DriveED Hub Admin</strong>
                </div>
                <div class="qr-info-row qr-number-row">
                    <div>
                        <span class="qr-info-label">GCash Mobile Number</span>
                        <div class="qr-info-number" id="driveedQrAccNum">0919 345 6789</div>
                    </div>
                    <button type="button" class="qr-copy-pill" id="driveedQrCopyBtn" onclick="copyQrScanNumber()" title="Copy Mobile Number">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        <span id="driveedQrCopyLabel">Copy</span>
                    </button>
                </div>
            </div>

            <!-- Step by step simple scan guide -->
            <div class="driveed-qr-instructions">
                <div class="qr-step">
                    <span class="qr-step-badge">1</span>
                    <span class="qr-step-text">Open <strong>GCash</strong> or banking app and tap <strong>QR / Scan</strong>.</span>
                </div>
                <div class="qr-step">
                    <span class="qr-step-badge">2</span>
                    <span class="qr-step-text">Scan this screen or upload downloaded QR code from your gallery.</span>
                </div>
                <div class="qr-step">
                    <span class="qr-step-badge">3</span>
                    <span class="qr-step-text">Send exact amount and save screenshot of the confirmation receipt.</span>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="driveed-qr-footer">
            <a id="driveedQrDownloadLink" href="#" download="gcash_payment_qr.png" target="_blank" class="driveed-qr-action-btn primary">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <span>Download QR Code</span>
            </a>
            <button type="button" class="driveed-qr-action-btn secondary" onclick="closeQrScanModal()">
                Close
            </button>
        </div>
    </div>
</div>

<style>
/* ==========================================================================
   DriveED Hub Image Lightbox & Zoom Modal Styles
   ========================================================================== */
.driveed-lightbox {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 1000000;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.25s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.25s ease;
}

.driveed-lightbox.is-open {
    opacity: 1;
    visibility: visible;
}

.driveed-lightbox-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(10, 15, 29, 0.92);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.driveed-lightbox-container {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 96vw;
    max-width: 1300px;
    height: 94vh;
    max-height: 900px;
    background: #0f172a;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 16px;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.05);
    overflow: hidden;
    transform: scale(0.96) translateY(8px);
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.driveed-lightbox.is-open .driveed-lightbox-container {
    transform: scale(1) translateY(0);
}

/* Header & Controls */
.driveed-lightbox-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 12px 20px;
    background: rgba(15, 23, 42, 0.95);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    z-index: 10;
    flex-wrap: wrap;
}

.driveed-lightbox-info {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.driveed-lightbox-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: rgba(37, 99, 235, 0.2);
    color: #60a5fa;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.driveed-lightbox-text {
    min-width: 0;
}

.driveed-lightbox-title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: #f8fafc;
    letter-spacing: -0.01em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 320px;
}

.driveed-lightbox-subtitle {
    margin: 2px 0 0 0;
    font-size: 0.78rem;
    color: #94a3b8;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.driveed-lightbox-qr-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    background: rgba(16, 185, 129, 0.15);
    border: 1px solid rgba(16, 185, 129, 0.35);
    border-radius: 9999px;
    color: #34d399;
    font-size: 0.76rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    animation: qrBadgePulse 2.5s infinite ease-in-out;
}

@keyframes qrBadgePulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
    50% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
}

.driveed-lightbox-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}

.driveed-lb-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 7px 12px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 8px;
    color: #e2e8f0;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
    user-select: none;
}

.driveed-lb-btn:hover {
    background: rgba(255, 255, 255, 0.16);
    border-color: rgba(255, 255, 255, 0.25);
    color: #ffffff;
    transform: translateY(-1px);
}

.driveed-lb-btn:active {
    transform: translateY(0);
}

.driveed-lb-btn .lb-icon {
    width: 17px;
    height: 17px;
    flex-shrink: 0;
}

.driveed-lb-btn-close {
    background: rgba(239, 68, 68, 0.2);
    border-color: rgba(239, 68, 68, 0.35);
    color: #f87171;
    padding: 7px 10px;
    margin-left: 6px;
}

.driveed-lb-btn-close:hover {
    background: #ef4444;
    border-color: #ef4444;
    color: #ffffff;
}

/* Viewport & Image Stage */
.driveed-lightbox-viewport {
    position: relative;
    flex: 1;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: radial-gradient(circle at center, #1e293b 0%, #0a0f1d 100%);
    cursor: grab;
    user-select: none;
    -webkit-user-select: none;
}

.driveed-lightbox-viewport.is-dragging {
    cursor: grabbing;
}

.driveed-lightbox-stage {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transform-origin: center center;
    will-change: transform;
    transition: transform 0.06s cubic-bezier(0, 0, 0.2, 1);
}

.driveed-lightbox-stage img {
    max-width: 82vw;
    max-height: 74vh;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.6);
    background: #ffffff;
    padding: 10px;
    display: block;
    pointer-events: none;
}

/* Special High-Contrast QR Styling in Lightbox */
.driveed-lightbox.is-qr-mode .driveed-lightbox-stage img {
    background: #ffffff !important;
    padding: 20px !important;
    border-radius: 16px !important;
    border: 4px solid #ffffff !important;
    box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1), 0 20px 40px -10px rgba(0, 0, 0, 0.7);
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
    max-width: 440px;
    max-height: 440px;
}

/* Loader */
.driveed-lightbox-loader {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    background: rgba(15, 23, 42, 0.75);
    color: #e2e8f0;
    font-size: 0.9rem;
    font-weight: 500;
    z-index: 5;
    pointer-events: none;
    transition: opacity 0.2s ease;
}

.lb-spinner {
    width: 36px;
    height: 36px;
    border: 3px solid rgba(255, 255, 255, 0.15);
    border-top-color: #38bdf8;
    border-radius: 50%;
    animation: lbSpin 0.8s linear infinite;
}

@keyframes lbSpin {
    to { transform: rotate(360deg); }
}

/* Bottom tip bar */
.driveed-lightbox-footer-tip {
    position: absolute;
    bottom: 14px;
    left: 50%;
    transform: translateX(-50%);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 6px 16px;
    background: rgba(15, 23, 42, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 9999px;
    backdrop-filter: blur(8px);
    color: #cbd5e1;
    font-size: 0.78rem;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
    pointer-events: auto;
    z-index: 10;
}

.lb-zoom-level {
    font-weight: 700;
    color: #38bdf8;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 4px;
    background: rgba(56, 189, 248, 0.12);
}

.lb-zoom-level:hover {
    background: rgba(56, 189, 248, 0.25);
}

.lb-tip-divider {
    color: rgba(255, 255, 255, 0.2);
}

/* ==========================================================================
   Dedicated QR Scan Modal Popup Styles
   ========================================================================== */
.driveed-qr-modal {
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    z-index: 1000005;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    box-sizing: border-box;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.22s ease;
}

.driveed-qr-modal.is-open {
    opacity: 1;
    visibility: visible;
}

.driveed-qr-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.driveed-qr-dialog {
    position: relative;
    width: 100%;
    max-width: 440px;
    max-height: 92vh;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 25px 60px -10px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transform: scale(0.95) translateY(8px);
    transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 1000006;
}

.driveed-qr-modal.is-open .driveed-qr-dialog {
    transform: scale(1) translateY(0);
}

.driveed-qr-header {
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    color: #ffffff;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.driveed-qr-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.driveed-qr-header-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.driveed-qr-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: #ffffff;
    line-height: 1.2;
}

.driveed-qr-subtitle {
    margin: 2px 0 0 0;
    font-size: 0.78rem;
    color: rgba(255, 255, 255, 0.85);
    line-height: 1.3;
}

.driveed-qr-close {
    background: rgba(255, 255, 255, 0.15);
    border: 0;
    color: #ffffff;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
    flex-shrink: 0;
}

.driveed-qr-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.05);
}

.driveed-qr-body {
    padding: 18px 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    background: #ffffff;
}

.driveed-qr-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 5px 14px;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    border-radius: 9999px;
    color: #065f46;
    font-size: 0.78rem;
    font-weight: 600;
    margin-bottom: 14px;
}

.qr-pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: qrDotPulse 1.8s infinite;
}

@keyframes qrDotPulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.driveed-qr-stage {
    width: 100%;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    padding: 16px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.04);
    margin-bottom: 14px;
}

.driveed-qr-img-frame {
    width: 260px;
    height: 260px;
    max-width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    overflow: hidden;
    border-radius: 8px;
}

#driveedQrModalImg {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
    transform-origin: center center;
    transition: transform 0.15s ease-out;
}

.driveed-qr-zoom-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px dashed #e2e8f0;
    width: 100%;
}

.qr-zoom-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 600;
    color: #334155;
    cursor: pointer;
    transition: all 0.15s ease;
}

.qr-zoom-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.qr-zoom-btn.qr-zoom-reset {
    padding: 5px 8px;
}

.qr-zoom-level {
    font-size: 0.82rem;
    font-weight: 700;
    color: #0284c7;
    min-width: 44px;
    text-align: center;
}

.driveed-qr-info-card {
    width: 100%;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 16px;
    box-sizing: border-box;
    margin-bottom: 14px;
}

.qr-info-row {
    margin-bottom: 8px;
}

.qr-info-row:last-child {
    margin-bottom: 0;
}

.qr-info-label {
    display: block;
    font-size: 0.72rem;
    text-transform: uppercase;
    font-weight: 700;
    color: #64748b;
    letter-spacing: 0.03em;
    margin-bottom: 2px;
}

.qr-info-value {
    font-size: 0.95rem;
    color: #0f172a;
    font-weight: 700;
}

.qr-number-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.qr-info-number {
    font-size: 1.15rem;
    font-weight: 800;
    color: #0284c7;
    letter-spacing: 0.05em;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

.qr-copy-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #0284c7;
    color: #ffffff;
    border: none;
    border-radius: 9999px;
    font-size: 0.76rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
}

.qr-copy-pill:hover {
    background: #0369a1;
}

.qr-copy-pill.is-copied {
    background: #10b981 !important;
}

.driveed-qr-instructions {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 8px;
    background: #f1f5f9;
    border-radius: 10px;
    padding: 10px 14px;
    box-sizing: border-box;
    font-size: 0.78rem;
    color: #475569;
}

.qr-step {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    line-height: 1.35;
}

.qr-step-badge {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #cbd5e1;
    color: #1e293b;
    font-size: 0.7rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
}

.driveed-qr-footer {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.driveed-qr-action-btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 0.88rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s ease;
    text-align: center;
}

.driveed-qr-action-btn.primary {
    background: #0284c7;
    color: #ffffff;
    border: none;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
}

.driveed-qr-action-btn.primary:hover {
    background: #0369a1;
    color: #ffffff;
    transform: translateY(-1px);
}

.driveed-qr-action-btn.secondary {
    background: #ffffff;
    color: #475569;
    border: 1px solid #cbd5e1;
    flex: 0 0 auto;
    min-width: 80px;
}

.driveed-qr-action-btn.secondary:hover {
    background: #f1f5f9;
    color: #0f172a;
}

/* Visual cues for clickable QR containers */
.qr-zoom-interactive {
    cursor: zoom-in !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
}

.qr-zoom-interactive:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.15) !important;
}

.qr-zoom-hint-badge {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(15, 23, 42, 0.85);
    color: #ffffff;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 9999px;
    backdrop-filter: blur(4px);
    pointer-events: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

/* Mobile responsiveness */
@media (max-width: 640px) {
    .driveed-lightbox-container {
        width: 100vw;
        height: 100vh;
        max-width: 100vw;
        max-height: 100vh;
        border-radius: 0;
        border: none;
    }
    
    .driveed-lightbox-header {
        padding: 8px 12px;
    }
    
    .lb-btn-text {
        display: none;
    }
    
    .driveed-lb-btn {
        padding: 8px;
    }

    .driveed-qr-dialog {
        max-width: 96vw;
        border-radius: 16px;
    }

    .driveed-qr-img-frame {
        width: 220px;
        height: 220px;
    }
}
</style>

<script>
(function() {
    'use strict';

    // State for General Lightbox
    const state = {
        isOpen: false,
        isQr: false,
        scale: 1,
        minScale: 0.5,
        maxScale: 4.0,
        step: 0.25,
        rotation: 0,
        posX: 0,
        posY: 0,
        isDragging: false,
        startX: 0,
        startY: 0,
        lastTouchDistance: null,
        src: '',
        title: ''
    };

    // State for Dedicated QR Scan Modal
    const qrState = {
        isOpen: false,
        scale: 1,
        minScale: 0.8,
        maxScale: 2.0,
        step: 0.2,
        src: '',
        accountNumber: ''
    };

    function getElements() {
        return {
            modal: document.getElementById('driveedImageLightbox'),
            viewport: document.getElementById('driveedLightboxViewport'),
            stage: document.getElementById('driveedLightboxStage'),
            image: document.getElementById('driveedLightboxImage'),
            title: document.getElementById('driveedLightboxTitle'),
            subtitle: document.getElementById('driveedLightboxSubtitle'),
            qrBadge: document.getElementById('driveedLightboxQrBadge'),
            headerIcon: document.getElementById('driveedLightboxHeaderIcon'),
            loader: document.getElementById('driveedLightboxLoader'),
            downloadBtn: document.getElementById('driveedLightboxDownload'),
            zoomLevel: document.getElementById('driveedLightboxZoomLevel'),
            tipMsg: document.getElementById('driveedLightboxTipMsg')
        };
    }

    function getQrElements() {
        return {
            modal: document.getElementById('driveedQrScanModal'),
            img: document.getElementById('driveedQrModalImg'),
            title: document.getElementById('driveedQrModalTitle'),
            subtitle: document.getElementById('driveedQrModalSubtitle'),
            zoomLevel: document.getElementById('driveedQrZoomLevel'),
            accName: document.getElementById('driveedQrAccName'),
            accNum: document.getElementById('driveedQrAccNum'),
            copyBtn: document.getElementById('driveedQrCopyBtn'),
            copyLabel: document.getElementById('driveedQrCopyLabel'),
            downloadLink: document.getElementById('driveedQrDownloadLink')
        };
    }

    function updateTransform() {
        const { stage, zoomLevel } = getElements();
        if (!stage) return;
        stage.style.transform = `translate(${state.posX}px, ${state.posY}px) scale(${state.scale}) rotate(${state.rotation}deg)`;
        if (zoomLevel) {
            zoomLevel.textContent = `${Math.round(state.scale * 100)}%`;
        }
    }

    function isQrImage(src, title, alt) {
        const text = `${src || ''} ${title || ''} ${alt || ''}`.toLowerCase();
        return text.includes('qr') || text.includes('gcash') || text.includes('scan') || text.includes('barcode');
    }

    /**
     * Dedicated QR Scan Modal Popup
     * Opens a high-contrast, camera-ready card modal popup specifically designed for scanning
     * @param {string} src QR code image URL
     * @param {object} options Optional settings { accountName, accountNumber, title }
     */
    window.openQrScanModal = function(src, options = {}) {
        const qel = getQrElements();
        if (!qel.modal) return;

        // Auto-detect image source if not passed directly
        if (!src) {
            const pageQr = document.querySelector('.epm-qr-img, .qr-container img, #paymentModal img');
            if (pageQr && pageQr.src) {
                src = pageQr.src;
            }
        }
        if (!src) return;

        qrState.src = src;
        qrState.scale = 1;
        qrState.isOpen = true;

        // Populate Image & Download
        qel.img.src = src;
        qel.downloadLink.href = src;
        qel.downloadLink.setAttribute('download', 'gcash_payment_qr.png');
        updateQrTransform();

        // Resolve Account Name
        let accountName = options.accountName;
        if (!accountName) {
            const nameEl = document.querySelector('.epm-acc-value') ||
                           document.querySelector('.account-card .h6') ||
                           document.querySelector('[data-gcash-name]');
            if (nameEl) accountName = nameEl.textContent.trim();
        }
        qel.accName.textContent = accountName || 'DriveED Hub Official GCash';

        // Resolve Account Number
        let accountNumber = options.accountNumber;
        if (!accountNumber) {
            const numEl = document.getElementById('epmGcashNumber') ||
                          document.getElementById('gcashNumber') ||
                          document.querySelector('.epm-acc-number') ||
                          document.querySelector('[data-gcash-number]');
            if (numEl) accountNumber = numEl.textContent.trim();
        }
        qrState.accountNumber = accountNumber || '0919 345 6789';
        qel.accNum.textContent = qrState.accountNumber;

        // Reset copy button
        if (qel.copyBtn) {
            qel.copyBtn.classList.remove('is-copied');
            if (qel.copyLabel) qel.copyLabel.textContent = 'Copy';
        }

        // Title if customized
        if (options.title && qel.title) {
            qel.title.textContent = options.title;
        } else if (qel.title) {
            qel.title.textContent = 'Scan QR Code to Pay';
        }

        // Open QR modal popup
        qel.modal.style.display = 'flex';
        qel.modal.offsetHeight; // force reflow
        qel.modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    };

    /**
     * Close QR Scan Modal Popup
     */
    window.closeQrScanModal = function() {
        const qel = getQrElements();
        if (!qel.modal || !qrState.isOpen) return;

        qel.modal.classList.remove('is-open');
        qrState.isOpen = false;
        document.body.style.overflow = '';

        setTimeout(() => {
            if (!qrState.isOpen) {
                qel.modal.style.display = 'none';
            }
        }, 220);
    };

    function updateQrTransform() {
        const qel = getQrElements();
        if (!qel.img) return;
        qel.img.style.transform = `scale(${qrState.scale})`;
        if (qel.zoomLevel) {
            qel.zoomLevel.textContent = `${Math.round(qrState.scale * 100)}%`;
        }
    }

    window.qrZoomIn = function() {
        qrState.scale = Math.min(qrState.maxScale, Math.round((qrState.scale + qrState.step) * 100) / 100);
        updateQrTransform();
    };

    window.qrZoomOut = function() {
        qrState.scale = Math.max(qrState.minScale, Math.round((qrState.scale - qrState.step) * 100) / 100);
        updateQrTransform();
    };

    window.qrResetZoom = function() {
        qrState.scale = 1;
        updateQrTransform();
    };

    window.copyQrScanNumber = function() {
        const qel = getQrElements();
        const textToCopy = (qrState.accountNumber || qel.accNum?.textContent || '').replace(/\s+/g, '');
        if (!textToCopy) return;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(textToCopy).then(showCopiedFeedback).catch(() => fallbackCopy(textToCopy));
        } else {
            fallbackCopy(textToCopy);
        }

        function fallbackCopy(val) {
            const el = document.createElement('textarea');
            el.value = val;
            el.setAttribute('readonly', '');
            el.style.position = 'absolute';
            el.style.left = '-9999px';
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            showCopiedFeedback();
        }

        function showCopiedFeedback() {
            if (qel.copyBtn) {
                qel.copyBtn.classList.add('is-copied');
                if (qel.copyLabel) qel.copyLabel.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
                setTimeout(() => {
                    qel.copyBtn.classList.remove('is-copied');
                    if (qel.copyLabel) qel.copyLabel.textContent = 'Copy';
                }, 2000);
            }
        }
    };

    /**
     * Backward-compatible QR modal function
     */
    window.openQrModal = function(imageUrl, title, options = {}) {
        window.openQrScanModal(imageUrl, Object.assign({}, options, { title: title || 'Scan QR Code to Pay' }));
    };

    window.closeQrModal = function() {
        window.closeQrScanModal();
        window.closeImageLightbox();
    };

    /**
     * Open Lightbox with specified image
     * If the image is a payment QR, redirects to the dedicated QR Scan Modal Popup
     * @param {string} src Image URL
     * @param {string} title Image title/caption
     * @param {object} options Optional settings
     */
    window.openImageLightbox = function(src, title, options = {}) {
        if (!src) return;

        const isQr = options.isQr !== undefined ? options.isQr : isQrImage(src, title, options.alt);
        if (isQr) {
            window.openQrScanModal(src, Object.assign({}, options, { title: title }));
            return;
        }

        const el = getElements();
        if (!el.modal) return;

        state.src = src;
        state.title = title || 'Image Preview';
        state.scale = options.initialScale || 1;
        state.rotation = 0;
        state.posX = 0;
        state.posY = 0;
        state.isDragging = false;
        state.isOpen = true;
        state.isQr = false;

        // Configure UI
        el.title.textContent = state.title;
        el.subtitle.textContent = 'Use controls or mouse to zoom & inspect';
        el.downloadBtn.href = src;
        el.downloadBtn.setAttribute('download', (title || 'image').replace(/[^a-z0-9]/gi, '_').toLowerCase() + '.png');
        el.modal.classList.remove('is-qr-mode');
        el.qrBadge.style.display = 'none';
        el.headerIcon.className = 'fas fa-search-plus';
        el.tipMsg.innerHTML = '<i class="fas fa-mouse me-1"></i> Scroll to zoom &nbsp;|&nbsp; <i class="fas fa-hand-paper me-1"></i> Drag to pan &nbsp;|&nbsp; Double-click to enlarge';

        // Show Loader & Load image
        el.loader.style.opacity = '1';
        el.loader.style.display = 'flex';
        el.image.onload = function() {
            el.loader.style.opacity = '0';
            setTimeout(() => { el.loader.style.display = 'none'; }, 200);
            updateTransform();
        };
        el.image.onerror = function() {
            el.loader.style.opacity = '0';
            setTimeout(() => { el.loader.style.display = 'none'; }, 200);
        };
        el.image.src = src;

        // Open modal
        el.modal.style.display = 'flex';
        el.modal.offsetHeight; // force reflow
        el.modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        updateTransform();
    };

    /**
     * Close Lightbox
     */
    window.closeImageLightbox = function() {
        const el = getElements();
        if (!el.modal || !state.isOpen) return;

        el.modal.classList.remove('is-open');
        state.isOpen = false;
        document.body.style.overflow = '';

        setTimeout(() => {
            if (!state.isOpen) {
                el.modal.style.display = 'none';
                if (el.image) el.image.src = '';
            }
        }, 250);
    };

    // Zoom Functions for Lightbox
    window.lightboxZoomIn = function(delta) {
        const step = delta || state.step;
        state.scale = Math.min(state.maxScale, Math.round((state.scale + step) * 100) / 100);
        updateTransform();
    };

    window.lightboxZoomOut = function(delta) {
        const step = delta || state.step;
        state.scale = Math.max(state.minScale, Math.round((state.scale - step) * 100) / 100);
        if (state.scale <= 1) {
            state.posX = 0;
            state.posY = 0;
        }
        updateTransform();
    };

    window.lightboxReset = function() {
        state.scale = 1;
        state.rotation = 0;
        state.posX = 0;
        state.posY = 0;
        updateTransform();
    };

    window.lightboxRotate = function() {
        state.rotation = (state.rotation + 90) % 360;
        updateTransform();
    };

    window.lightboxToggleFullscreen = function() {
        const el = getElements();
        const container = el.modal;
        if (!document.fullscreenElement) {
            if (container.requestFullscreen) container.requestFullscreen();
            else if (container.webkitRequestFullscreen) container.webkitRequestFullscreen();
            else if (container.msRequestFullscreen) container.msRequestFullscreen();
        } else {
            if (document.exitFullscreen) document.exitFullscreen();
        }
    };

    window.lightboxToggleDoubleZoom = function(e) {
        if (!state.isOpen) return;
        if (state.scale > 1.2) {
            state.scale = 1;
            state.posX = 0;
            state.posY = 0;
        } else {
            state.scale = 2.2;
            if (e && e.clientX && e.clientY) {
                const el = getElements();
                const rect = el.viewport.getBoundingClientRect();
                const offsetX = (e.clientX - rect.left) - rect.width / 2;
                const offsetY = (e.clientY - rect.top) - rect.height / 2;
                state.posX = -offsetX * 0.8;
                state.posY = -offsetY * 0.8;
            }
        }
        updateTransform();
    };

    // Keyboard handlers (handles both QR Modal & General Lightbox)
    document.addEventListener('keydown', function(e) {
        if (qrState.isOpen) {
            if (e.key === 'Escape') {
                closeQrScanModal();
            } else if (e.key === '+' || e.key === '=') {
                qrZoomIn();
            } else if (e.key === '-' || e.key === '_') {
                qrZoomOut();
            } else if (e.key === '0' || e.key.toLowerCase() === 'r') {
                qrResetZoom();
            }
            return;
        }

        if (state.isOpen) {
            if (e.key === 'Escape') {
                closeImageLightbox();
            } else if (e.key === '+' || e.key === '=') {
                lightboxZoomIn();
            } else if (e.key === '-' || e.key === '_') {
                lightboxZoomOut();
            } else if (e.key === '0' || e.key.toLowerCase() === 'r') {
                lightboxReset();
            } else if (e.key === 'ArrowLeft') {
                state.posX += 40;
                updateTransform();
            } else if (e.key === 'ArrowRight') {
                state.posX -= 40;
                updateTransform();
            } else if (e.key === 'ArrowUp') {
                state.posY += 40;
                updateTransform();
            } else if (e.key === 'ArrowDown') {
                state.posY -= 40;
                updateTransform();
            }
        }
    });

    // Panning logic on Viewport
    document.addEventListener('DOMContentLoaded', initLightboxInteractions);
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
        initLightboxInteractions();
    }

    function initLightboxInteractions() {
        const el = getElements();
        if (!el.viewport) return;

        // Wheel Zoom
        el.viewport.addEventListener('wheel', function(e) {
            if (!state.isOpen) return;
            e.preventDefault();
            const delta = e.deltaY < 0 ? 0.25 : -0.25;
            if (delta > 0) {
                lightboxZoomIn(delta);
            } else {
                lightboxZoomOut(-delta);
            }
        }, { passive: false });

        // Mouse Drag Panning
        el.viewport.addEventListener('mousedown', function(e) {
            if (!state.isOpen) return;
            if (e.target.closest('.driveed-lightbox-header') || e.target.closest('.driveed-lightbox-footer-tip')) return;
            state.isDragging = true;
            state.startX = e.clientX - state.posX;
            state.startY = e.clientY - state.posY;
            el.viewport.classList.add('is-dragging');
        });

        window.addEventListener('mousemove', function(e) {
            if (!state.isOpen || !state.isDragging) return;
            state.posX = e.clientX - state.startX;
            state.posY = e.clientY - state.startY;
            updateTransform();
        });

        window.addEventListener('mouseup', function() {
            if (state.isDragging) {
                state.isDragging = false;
                const el = getElements();
                if (el.viewport) el.viewport.classList.remove('is-dragging');
            }
        });

        // Touch drag & Pinch zoom for mobile
        el.viewport.addEventListener('touchstart', function(e) {
            if (!state.isOpen) return;
            if (e.touches.length === 1) {
                state.isDragging = true;
                state.startX = e.touches[0].clientX - state.posX;
                state.startY = e.touches[0].clientY - state.posY;
            } else if (e.touches.length === 2) {
                state.isDragging = false;
                state.lastTouchDistance = Math.hypot(
                    e.touches[0].clientX - e.touches[1].clientX,
                    e.touches[0].clientY - e.touches[1].clientY
                );
            }
        }, { passive: true });

        window.addEventListener('touchmove', function(e) {
            if (!state.isOpen) return;
            if (e.touches.length === 1 && state.isDragging) {
                state.posX = e.touches[0].clientX - state.startX;
                state.posY = e.touches[0].clientY - state.startY;
                updateTransform();
            } else if (e.touches.length === 2 && state.lastTouchDistance) {
                const newDist = Math.hypot(
                    e.touches[0].clientX - e.touches[1].clientX,
                    e.touches[0].clientY - e.touches[1].clientY
                );
                const diff = (newDist - state.lastTouchDistance) * 0.005;
                state.scale = Math.min(state.maxScale, Math.max(state.minScale, state.scale + diff));
                state.lastTouchDistance = newDist;
                updateTransform();
            }
        }, { passive: true });

        window.addEventListener('touchend', function(e) {
            if (e.touches.length < 2) {
                state.lastTouchDistance = null;
            }
            if (e.touches.length === 0) {
                state.isDragging = false;
            }
        });

        // Global Event Delegation for QR & Uploaded Images
        document.addEventListener('click', function(e) {
            // Check for explicit QR trigger
            const qrTrigger = e.target.closest('.qr-container, .epm-qr-box, .qr-code-wrapper, [data-qr-modal]');
            if (qrTrigger) {
                const img = qrTrigger.querySelector('img') || qrTrigger;
                if (img && img.src && !img.src.includes('data:image/svg')) {
                    e.preventDefault();
                    window.openQrScanModal(img.src);
                    return;
                }
            }

            // Check for explicit data-lightbox or data-zoomable attribute
            const trigger = e.target.closest('[data-lightbox], [data-zoomable], .zoomable-image-trigger');
            if (trigger) {
                const src = trigger.dataset.lightbox || trigger.dataset.zoomable || trigger.getAttribute('src') || trigger.querySelector('img')?.getAttribute('src');
                const title = trigger.dataset.title || trigger.getAttribute('alt') || 'Image Preview';
                if (src) {
                    e.preventDefault();
                    window.openImageLightbox(src, title);
                    return;
                }
            }

            // Upload preview click
            const uploadPreviewImg = e.target.closest('.upload-preview img, #epmPreviewImg, #elmPreviewImg');
            if (uploadPreviewImg && uploadPreviewImg.src && uploadPreviewImg.src !== window.location.href) {
                e.preventDefault();
                window.openImageLightbox(uploadPreviewImg.src, 'Uploaded Document / Receipt Preview');
                return;
            }
        });

        // Enhance any QR images on page load
        enhanceZoomableElements();
    }

    // Auto-enhance QR containers with scan hint
    function enhanceZoomableElements() {
        const qrContainers = document.querySelectorAll('.qr-container, .epm-qr-box');
        qrContainers.forEach(container => {
            const img = container.querySelector('img');
            if (img && !container.classList.contains('qr-zoom-interactive')) {
                container.classList.add('qr-zoom-interactive');
                container.title = 'Click to open QR popup to scan';
                if (!container.querySelector('.qr-zoom-hint-badge') && container.offsetWidth > 90) {
                    const badge = document.createElement('span');
                    badge.className = 'qr-zoom-hint-badge';
                    badge.innerHTML = '<i class="fas fa-qrcode"></i> Scan';
                    container.appendChild(badge);
                }
            }
        });
    }

    // Observe dynamic elements added to DOM
    if (window.MutationObserver) {
        const observer = new MutationObserver(function() {
            enhanceZoomableElements();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }
})();
</script>
