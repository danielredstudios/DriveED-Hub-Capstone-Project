<style>
    .course-flyer-grid {
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 330px), 1fr));
        align-items: stretch;
    }

    .course-flyer-grid .course-card {
        border: 1px solid #dce3ed;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(21, 35, 59, 0.07);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .course-flyer-grid .course-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 30px rgba(21, 35, 59, 0.12);
    }

    .course-flyer-grid .course-banner {
        height: 210px;
        background: var(--flyer-accent, #425bbd);
    }

    .course-flyer-grid .course-banner img { object-position: center; }

    .course-flyer-grid .course-body { padding: 24px; }

    .course-flyer-grid .course-title {
        font-size: clamp(1.3rem, 2vw, 1.55rem);
        line-height: 1.2;
        letter-spacing: -.02em;
        margin-bottom: 10px;
    }

    .course-flyer-grid .course-description {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
        overflow: hidden;
        text-align: left;
        margin: 0 0 18px;
        color: #46536a;
    }

    .course-flyer-grid .course-features { margin-bottom: 16px; }
    .course-flyer-grid .course-features li { line-height: 1.4; }
    .course-flyer-grid .packages-section,
    .course-flyer-grid .course-info { display: none; }

    .flyer-facts {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin: auto 0 18px;
        padding-top: 17px;
        border-top: 1px solid #e6ebf2;
    }

    .flyer-fact { min-width: 0; }
    .flyer-fact-label { display: block; color: #5c687c; font-size: .75rem; margin-bottom: 4px; }
    .flyer-fact-value { display: block; color: #17243b; font-size: 1rem; font-weight: 700; line-height: 1.25; }
    .flyer-fact-note { display: block; color: #5c687c; font-size: .72rem; margin-top: 2px; }

    .course-flyer-grid .btn-enroll { margin-top: 0; min-height: 44px; }
    .course-flyer-grid .btn-enroll:focus-visible {
        outline: 3px solid var(--flyer-accent, #425bbd);
        outline-offset: 3px;
    }

    .enroll-package-details {
        display: grid;
        gap: 10px;
        margin-bottom: 18px;
    }

    .enroll-package-detail {
        padding: 15px;
        border: 1px solid #dce3ed;
        border-radius: 12px;
        background: #f8fafc;
    }

    .enroll-package-heading {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 12px;
        color: #17243b;
        font-weight: 700;
    }

    .enroll-package-heading strong { white-space: nowrap; }
    .enroll-package-detail p { color: #46536a; font-size: .85rem; line-height: 1.5; margin: 7px 0 0; }
    .enroll-package-detail ul { margin: 8px 0 0; padding-left: 19px; color: #334155; font-size: .82rem; }

    @media (max-width: 600px) {
        .course-flyer-grid .course-banner { height: 180px; }
        .course-flyer-grid .course-body { padding: 18px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .course-flyer-grid .course-card { transition: none; }
        .course-flyer-grid .course-card:hover { transform: none; }
    }
</style>
