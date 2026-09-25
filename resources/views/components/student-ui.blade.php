@once
    <style>
        :root { --ef-ink:#1e1b4b; --ef-muted:#68677e; --ef-brand:#4f46e5; --ef-soft:rgba(79,70,229,.10); --ef-panel:rgba(255,255,255,.72); --ef-line:rgba(255,255,255,.82); --ef-green:#16845b; --ef-green-soft:#dcfce7; --ef-amber:#a16207; --ef-amber-soft:#fef3c7; --ef-shadow:0 22px 55px -38px rgba(30,27,75,.46); }
        .fi-body { color-scheme:light; background:linear-gradient(145deg,#ffffff 0%,#f6f8ff 52%,#edf5ff 100%); }
        .dark .fi-body { background:linear-gradient(135deg,#111827,#172554 52%,#083344); }
        .fi-topbar,.fi-sidebar { background-color:rgba(255,255,255,.94); backdrop-filter:blur(22px); }
        .dark .fi-topbar,.dark .fi-sidebar { background-color:rgba(17,24,39,.80); }
        .ef-page-intro { margin-bottom:1.5rem; }
        .ef-eyebrow { color:var(--ef-brand); font-size:.75rem; font-weight:800; letter-spacing:.09em; text-transform:uppercase; }
        .ef-title { margin-top:.35rem; color:var(--ef-ink); font-size:clamp(1.75rem,4vw,2.55rem); font-weight:800; line-height:1.1; letter-spacing:-.035em; }
        .ef-subtitle { margin-top:.65rem; color:var(--ef-muted); font-size:.95rem; line-height:1.6; }
        .dark .ef-title { color:#f8fafc; } .dark .ef-subtitle { color:#cbd5e1; }
        .ef-grid { display:grid; gap:1rem; }
        .ef-card { display:flex; min-height:17rem; flex-direction:column; padding:1.35rem; border:1px solid var(--ef-line); border-radius:1.5rem; background:var(--ef-panel); box-shadow:var(--ef-shadow); backdrop-filter:blur(20px); transition:transform .2s ease,box-shadow .2s ease; }
        .ef-card:hover { transform:translateY(-2px); box-shadow:0 26px 60px -38px rgba(30,27,75,.58); }
        .dark .ef-card,.dark .ef-empty,.dark .ef-checkout-card { border-color:rgba(255,255,255,.10); background:rgba(15,23,42,.68); }
        .ef-card-top { display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem; }
        .ef-code,.ef-badge { display:inline-flex; width:max-content; border-radius:999px; padding:.38rem .65rem; font-size:.72rem; font-weight:800; }
        .ef-code { color:var(--ef-brand); background:var(--ef-soft); letter-spacing:.04em; }
        .ef-badge--ok { color:var(--ef-green); background:var(--ef-green-soft); } .ef-badge--locked { color:var(--ef-amber); background:var(--ef-amber-soft); }
        .ef-course-name { margin-top:1rem; color:var(--ef-ink); font-size:1.15rem; font-weight:800; line-height:1.3; } .dark .ef-course-name { color:#f8fafc; }
        .ef-description { margin-top:.65rem; color:var(--ef-muted); font-size:.86rem; line-height:1.55; } .dark .ef-description { color:#cbd5e1; }
        .ef-details { display:flex; flex-wrap:wrap; gap:.55rem 1rem; margin-top:1rem; color:var(--ef-muted); font-size:.78rem; } .dark .ef-details { color:#94a3b8; }
        .ef-card-footer { display:flex; align-items:flex-end; justify-content:space-between; gap:1rem; margin-top:auto; padding-top:1.5rem; }
        .ef-meta-label { color:var(--ef-muted); font-size:.72rem; } .ef-meta-value { margin-top:.2rem; color:var(--ef-ink); font-size:1.15rem; font-weight:800; }
        .dark .ef-meta-label { color:#94a3b8; } .dark .ef-meta-value { color:#f8fafc; }
        .ef-empty { padding:2rem; border:1px solid var(--ef-line); border-radius:1.5rem; background:var(--ef-panel); text-align:center; box-shadow:var(--ef-shadow); }
        .ef-checkout { max-width:42rem; margin-inline:auto; } .ef-checkout-card { border:1px solid var(--ef-line); border-radius:1.75rem; background:var(--ef-panel); box-shadow:var(--ef-shadow); backdrop-filter:blur(22px); }
        .ef-checkout-main { padding:clamp(1.35rem,5vw,2.25rem); }
        .ef-price-row { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin:1.5rem 0; padding:1rem; border-radius:1rem; background:rgba(79,70,229,.08); }
        .ef-price { color:var(--ef-ink); font-size:1.5rem; font-weight:800; } .dark .ef-price { color:#fff; }
        .ef-secure { display:flex; gap:.75rem; align-items:flex-start; margin-bottom:1.25rem; color:var(--ef-muted); font-size:.85rem; line-height:1.55; } .dark .ef-secure { color:#cbd5e1; }
        .ef-secure-mark { flex:none; display:grid; width:2rem; height:2rem; place-items:center; border-radius:.7rem; color:var(--ef-green); background:var(--ef-green-soft); font-weight:900; }
        .ef-exam-shell .fi-section,.ef-setup-shell .fi-section,.ef-result-shell .fi-section { border:1px solid var(--ef-line); border-radius:1.5rem; background:var(--ef-panel); box-shadow:var(--ef-shadow); backdrop-filter:blur(20px); }
        .dark .ef-exam-shell .fi-section,.dark .ef-setup-shell .fi-section,.dark .ef-result-shell .fi-section { border-color:rgba(255,255,255,.10); background:rgba(15,23,42,.68); }
        .ef-exam-shell legend { color:var(--ef-ink); font-size:clamp(1.05rem,2.5vw,1.35rem); line-height:1.5; }
        .dark .ef-exam-shell legend { color:#f8fafc; }
        .ef-exam-shell label { min-height:4rem; border-radius:1rem; background:rgba(255,255,255,.62); }
        .dark .ef-exam-shell label { background:rgba(15,23,42,.45); }
        .ef-timer { border-radius:.85rem; box-shadow:0 12px 25px -16px rgba(30,27,75,.75); font-variant-numeric:tabular-nums; }
        .ef-exam-shell { display:grid; gap:1.25rem; align-items:start; }
        .ef-exam-main,.ef-exam-sidebar { display:grid; gap:1rem; }
        .ef-exam-status,.ef-question-card,.ef-palette-card,.ef-submit-card { border:1px solid #e3e8f5; background:#fff; box-shadow:0 18px 45px -32px rgba(30,41,99,.42); }
        .ef-exam-status { position:relative; display:flex; align-items:center; justify-content:space-between; gap:1.25rem; overflow:hidden; padding:1.15rem 1.25rem 1.35rem; border-radius:1.25rem; }
        .ef-exam-status-copy { display:grid; gap:.2rem; }
        .ef-exam-kicker { color:#4f46e5; font-size:.72rem; font-weight:800; letter-spacing:.07em; text-transform:uppercase; }
        .ef-exam-status-copy strong { color:#172554; font-size:1.05rem; }
        .ef-exam-status-copy > span:last-child { color:#64748b; font-size:.8rem; }
        .ef-exam-clock { flex:none; min-width:8.5rem; padding:.7rem 1rem; border-radius:.9rem; color:#fff; background:#1e3a8a; text-align:center; }
        .ef-exam-clock span { display:block; opacity:.8; font-size:.65rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; }
        .ef-exam-clock strong { display:block; margin-top:.1rem; font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:1.35rem; font-variant-numeric:tabular-nums; }
        .ef-exam-clock.is-urgent { background:#dc2626; }
        .ef-progress-track { position:absolute; right:0; bottom:0; left:0; height:.28rem; background:#e8edfa; }
        .ef-progress-track span { display:block; height:100%; border-radius:0 999px 999px 0; background:linear-gradient(90deg,#2563eb,#4f46e5); transition:width .25s ease; }
        .ef-question-card { padding:clamp(1.2rem,3vw,2rem); border-radius:1.4rem; }
        .ef-question-card legend { display:flex; width:100%; gap:.9rem; align-items:flex-start; color:#172554; font-size:clamp(1.05rem,2.2vw,1.3rem); font-weight:750; line-height:1.5; }
        .ef-question-number { display:grid; flex:0 0 2rem; width:2rem; height:2rem; place-items:center; border-radius:.65rem; color:#fff; background:#2563eb; font-size:.85rem; }
        .ef-answer-list { display:grid; gap:.75rem; margin-top:1.5rem; }
        .ef-exam-shell .ef-answer-option { display:grid; grid-template-columns:2.45rem minmax(0,1fr) 1.5rem; min-height:0; gap:.9rem; align-items:center; margin:0; padding:.85rem 1rem; border:1.5px solid #dce3f0; border-radius:1rem; background:#f8faff; cursor:pointer; transition:border-color .16s ease,background .16s ease,transform .16s ease,box-shadow .16s ease; }
        .ef-exam-shell .ef-answer-option:hover { transform:translateY(-1px); border-color:#93b4f8; background:#f2f6ff; box-shadow:0 10px 24px -20px rgba(37,99,235,.8); }
        .ef-answer-option input { position:absolute; width:1px; height:1px; opacity:0; pointer-events:none; }
        .ef-answer-letter { display:grid; width:2.35rem; height:2.35rem; place-items:center; border:1px solid #cbd5e1; border-radius:.75rem; color:#334155; background:#fff; font-weight:800; }
        .ef-answer-text { color:#27344f; font-size:.94rem; line-height:1.5; }
        .ef-answer-check { display:grid; width:1.35rem; height:1.35rem; place-items:center; border:1px solid #cbd5e1; border-radius:50%; color:transparent; background:#fff; font-size:.72rem; font-weight:900; }
        .ef-exam-shell .ef-answer-option.is-selected { border-color:#2563eb; background:#eef5ff; box-shadow:0 0 0 3px rgba(37,99,235,.10); }
        .ef-answer-option.is-selected .ef-answer-letter { border-color:#2563eb; color:#fff; background:#2563eb; }
        .ef-answer-option.is-selected .ef-answer-text { color:#172554; font-weight:650; }
        .ef-answer-option.is-selected .ef-answer-check { border-color:#2563eb; color:#fff; background:#2563eb; }
        .ef-exam-navigation { display:flex; align-items:center; justify-content:space-between; gap:.75rem; }
        .ef-palette-card,.ef-submit-card { padding:1.15rem; border-radius:1.25rem; }
        .ef-palette-heading { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; padding-bottom:1rem; border-bottom:1px solid #e7ebf4; }
        .ef-palette-heading div { display:grid; gap:.15rem; } .ef-palette-heading strong,.ef-submit-card strong { color:#172554; font-size:.95rem; }
        .ef-palette-heading div span { color:#64748b; font-size:.72rem; }
        .ef-palette-heading > span { padding:.3rem .55rem; border-radius:999px; color:#1d4ed8; background:#eff6ff; font-size:.72rem; font-weight:800; }
        .ef-question-palette { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:.5rem; margin-top:1rem; }
        .ef-palette-number { aspect-ratio:1; border:1px solid #dbe2ee; border-radius:.65rem; color:#475569; background:#f8fafc; font-size:.78rem; font-weight:750; cursor:pointer; }
        .ef-palette-number:hover { border-color:#93b4f8; color:#1d4ed8; }
        .ef-palette-number.is-answered { border-color:#bfdbfe; color:#1d4ed8; background:#dbeafe; }
        .ef-palette-number.is-current { outline:2px solid #2563eb; outline-offset:2px; color:#fff; background:#2563eb; }
        .ef-palette-legend { display:flex; gap:1rem; margin-top:1rem; color:#64748b; font-size:.68rem; }
        .ef-palette-legend span { display:flex; align-items:center; gap:.35rem; } .ef-palette-legend i { width:.65rem; height:.65rem; border-radius:.2rem; }
        .ef-palette-legend .answered { background:#dbeafe; } .ef-palette-legend .current { background:#2563eb; }
        .ef-submit-card { display:grid; gap:.65rem; }
        .ef-submit-card p { color:#64748b; font-size:.78rem; line-height:1.5; }
        .ef-submit-card .fi-btn { width:100%; }
        .ef-result-score { display:grid; width:9rem; height:9rem; place-items:center; border-radius:50%; color:#fff; background:var(--ef-brand); box-shadow:0 0 0 9px var(--ef-soft); }
        .ef-setup-shell,.ef-result-shell { display:grid; gap:1.25rem; }
        .ef-setup-course { display:grid; grid-template-columns:auto minmax(0,1fr) auto; gap:1rem; align-items:center; padding:1.25rem; border:1px solid #e3e8f5; border-radius:1.35rem; background:#fff; box-shadow:0 18px 45px -32px rgba(30,41,99,.42); }
        .ef-setup-course-mark { display:grid; width:3.25rem; height:3.25rem; place-items:center; border-radius:1rem; color:#fff; background:linear-gradient(135deg,#2563eb,#4f46e5); font-weight:850; }
        .ef-setup-course-copy { display:grid; gap:.15rem; } .ef-setup-course-copy span { color:#2563eb; font-size:.7rem; font-weight:850; letter-spacing:.06em; }
        .ef-setup-course-copy strong { color:#172554; font-size:1.05rem; } .ef-setup-course-copy small { color:#64748b; font-size:.76rem; }
        .ef-setup-available { min-width:8rem; padding:.65rem .85rem; border-radius:.9rem; color:#166534; background:#ecfdf3; text-align:center; }
        .ef-setup-available strong,.ef-setup-available span { display:block; } .ef-setup-available strong { font-size:1.15rem; } .ef-setup-available span { font-size:.68rem; }
        .ef-setup-form { display:grid; gap:1rem; } .ef-setup-actions { display:flex; flex-wrap:wrap; gap:.65rem; }
        .ef-result-summary { overflow:hidden; border:1px solid #e3e8f5; border-radius:1.4rem; background:#fff; box-shadow:0 18px 45px -32px rgba(30,41,99,.42); }
        .ef-result-overview { display:flex; align-items:center; justify-content:space-between; gap:1.5rem; padding:1.4rem; background:linear-gradient(120deg,#f8fbff,#eef4ff); }
        .ef-result-course { display:grid; gap:.25rem; } .ef-result-course span { color:#2563eb; font-size:.72rem; font-weight:850; letter-spacing:.06em; }
        .ef-result-course strong { color:#172554; font-size:1.2rem; } .ef-result-course small { color:#64748b; font-size:.76rem; }
        .ef-result-score-wrap { display:flex; align-items:center; gap:.9rem; }
        .ef-result-score { display:grid; width:6.2rem; height:6.2rem; place-content:center; border-radius:50%; color:#fff; background:linear-gradient(135deg,#2563eb,#4f46e5); box-shadow:0 0 0 7px rgba(37,99,235,.10); text-align:center; }
        .ef-result-score strong,.ef-result-score span { display:block; } .ef-result-score strong { font-size:1.45rem; } .ef-result-score span { margin-top:.1rem; opacity:.8; font-size:.65rem; text-transform:uppercase; }
        .ef-result-status { padding:.38rem .65rem; border-radius:999px; color:#166534; background:#dcfce7; font-size:.7rem; font-weight:800; }
        .ef-result-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1px; background:#e5eaf3; border-top:1px solid #e5eaf3; }
        .ef-result-stats div { padding:1rem 1.2rem; background:#fff; } .ef-result-stats dt { color:#64748b; font-size:.72rem; } .ef-result-stats dd { margin-top:.15rem; color:#172554; font-size:1.25rem; font-weight:850; }
        .ef-result-stats .is-correct dd { color:#15803d; } .ef-result-stats .is-incorrect dd { color:#dc2626; }
        .ef-review-list { display:grid; gap:1rem; }
        .ef-review-card { padding:1.25rem; border:1px solid #e3e8f5; border-radius:1.25rem; background:#fff; box-shadow:0 16px 38px -32px rgba(30,41,99,.4); }
        .ef-review-heading { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; }
        .ef-review-heading h3 { display:flex; gap:.7rem; color:#172554; font-size:.95rem; font-weight:750; line-height:1.5; }
        .ef-review-heading h3 > span { display:grid; flex:0 0 1.7rem; width:1.7rem; height:1.7rem; place-items:center; border-radius:.55rem; color:#fff; background:#2563eb; font-size:.72rem; }
        .ef-review-badge { flex:none; padding:.35rem .6rem; border-radius:999px; font-size:.68rem; font-weight:800; } .ef-review-badge.is-correct { color:#166534; background:#dcfce7; } .ef-review-badge.is-incorrect { color:#b91c1c; background:#fee2e2; } .ef-review-badge.is-unanswered { color:#475569; background:#e2e8f0; }
        .ef-review-options { display:grid; gap:.55rem; margin-top:1rem; }
        .ef-review-option { display:grid; grid-template-columns:2rem minmax(0,1fr) auto; gap:.75rem; align-items:center; padding:.65rem .8rem; border:1px solid #e2e8f0; border-radius:.8rem; color:#475569; background:#fafcff; font-size:.8rem; }
        .ef-review-letter { display:grid; width:1.85rem; height:1.85rem; place-items:center; border-radius:.55rem; background:#e9eef7; font-weight:800; }
        .ef-review-option.is-correct { border-color:#86efac; color:#166534; background:#f0fdf4; } .ef-review-option.is-correct .ef-review-letter { color:#fff; background:#16a34a; }
        .ef-review-option.is-incorrect { border-color:#fca5a5; color:#991b1b; background:#fef2f2; } .ef-review-option.is-incorrect .ef-review-letter { color:#fff; background:#dc2626; }
        .ef-review-note { font-size:.7rem; font-weight:750; }
        .ef-review-explanation { margin-top:1rem; padding:.85rem 1rem; border-left:3px solid #2563eb; border-radius:0 .75rem .75rem 0; background:#eff6ff; }
        .ef-review-explanation strong { color:#1e3a8a; font-size:.75rem; } .ef-review-explanation p { margin-top:.2rem; color:#475569; font-size:.8rem; line-height:1.55; }
        .fi-simple-layout { color-scheme:light; background:linear-gradient(145deg,#ffffff 0%,#f5f8ff 55%,#eaf3ff 100%); }
        .dark .fi-simple-layout { background:linear-gradient(135deg,#111827,#172554 52%,#083344); }
        .fi-simple-main { border:1px solid var(--ef-line); border-radius:1.75rem; background:rgba(255,255,255,.76); box-shadow:var(--ef-shadow); backdrop-filter:blur(22px); }
        .dark .fi-simple-main { border-color:rgba(255,255,255,.10); background:rgba(15,23,42,.78); }
        .ef-auth-intro { margin-bottom:1.25rem; text-align:center; }
        .ef-auth-mark { display:grid; width:3rem; height:3rem; margin:0 auto .9rem; place-items:center; border-radius:1rem; color:#fff; background:var(--ef-brand); box-shadow:0 12px 28px -14px rgba(79,70,229,.8); font-size:1.15rem; font-weight:900; }
        .ef-auth-title { color:var(--ef-ink); font-size:1.25rem; font-weight:800; } .dark .ef-auth-title { color:#f8fafc; }
        .ef-auth-copy { margin-top:.35rem; color:var(--ef-muted); font-size:.84rem; } .dark .ef-auth-copy { color:#cbd5e1; }
        .ef-dashboard-hero { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1rem; padding:1.25rem; border:1px solid var(--ef-line); border-radius:1.5rem; background:var(--ef-panel); box-shadow:var(--ef-shadow); }
        .dark .ef-dashboard-hero { border-color:rgba(255,255,255,.10); background:rgba(15,23,42,.68); }
        .ef-dashboard-actions { display:flex; flex-wrap:wrap; gap:.65rem; }
        .ef-dashboard-activity .fi-section,.fi-wi-stats-overview-stat { border:1px solid var(--ef-line); border-radius:1.35rem; background:var(--ef-panel); box-shadow:var(--ef-shadow); backdrop-filter:blur(18px); }
        .dark .ef-dashboard-activity .fi-section,.dark .fi-wi-stats-overview-stat { border-color:rgba(255,255,255,.10); background:rgba(15,23,42,.68); }
        .ef-status-card { max-width:42rem; margin-inline:auto; padding:clamp(1.5rem,5vw,2.5rem); border:1px solid var(--ef-line); border-radius:1.75rem; background:#fff; box-shadow:var(--ef-shadow); text-align:center; }
        .ef-status-icon { display:grid; width:4rem; height:4rem; margin:0 auto 1rem; place-items:center; border-radius:1.25rem; font-size:1.75rem; font-weight:900; }
        .ef-status-icon--success { color:var(--ef-green); background:var(--ef-green-soft); }
        .ef-status-icon--pending { color:var(--ef-amber); background:var(--ef-amber-soft); }
        .ef-receipt { max-width:48rem; margin-inline:auto; overflow:hidden; border:1px solid var(--ef-line); border-radius:1.75rem; background:#fff; box-shadow:var(--ef-shadow); }
        .ef-receipt-main { padding:clamp(1.35rem,5vw,2.25rem); }
        .ef-receipt-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; padding-bottom:1.25rem; border-bottom:1px solid #e5e7eb; }
        .ef-receipt-grid { display:grid; gap:1.25rem; margin-top:1.5rem; }
        .ef-receipt-total { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-top:1.5rem; padding:1rem 1.15rem; border-radius:1rem; background:#eef2ff; }
        @media (min-width:640px) { .ef-receipt-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (min-width:640px) { .ef-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (min-width:1280px) { .ef-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } }
        @media (min-width:1024px) { .ef-exam-shell { grid-template-columns:minmax(0,1fr) 18rem; } .ef-exam-sidebar { position:sticky; top:5.5rem; } }
        @media (max-width:639px) { .ef-exam-status { align-items:stretch; flex-direction:column; } .ef-exam-clock { width:100%; } .ef-question-card { padding:1rem; } .ef-exam-shell .ef-answer-option { grid-template-columns:2.2rem minmax(0,1fr) 1.35rem; padding:.75rem; } .ef-answer-letter { width:2.1rem; height:2.1rem; } }
        @media (max-width:520px) { .ef-card-footer { align-items:stretch; flex-direction:column; } .ef-card-footer .fi-btn { width:100%; } }
        @media (max-width:639px) { .ef-setup-course { grid-template-columns:auto 1fr; } .ef-setup-available { grid-column:1/-1; } .ef-setup-actions { display:grid; } .ef-setup-actions .fi-btn { width:100%; } .ef-result-overview { align-items:flex-start; flex-direction:column; } .ef-result-score-wrap { width:100%; justify-content:space-between; } .ef-result-stats { grid-template-columns:repeat(2,minmax(0,1fr)); } .ef-review-option { grid-template-columns:2rem minmax(0,1fr); } .ef-review-note { grid-column:2; } }
    </style>
@endonce
