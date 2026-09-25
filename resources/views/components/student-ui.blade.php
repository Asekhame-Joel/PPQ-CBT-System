@once
    <style>
        :root { --ef-ink:#1e1b4b; --ef-muted:#68677e; --ef-brand:#4f46e5; --ef-soft:rgba(79,70,229,.10); --ef-panel:rgba(255,255,255,.72); --ef-line:rgba(255,255,255,.82); --ef-green:#16845b; --ef-green-soft:#dcfce7; --ef-amber:#a16207; --ef-amber-soft:#fef3c7; --ef-shadow:0 22px 55px -38px rgba(30,27,75,.46); }
        .fi-body { background:linear-gradient(135deg,#eef2ff,#e0f2fe 48%,#ecfeff); }
        .dark .fi-body { background:linear-gradient(135deg,#111827,#172554 52%,#083344); }
        .fi-topbar,.fi-sidebar { background-color:rgba(255,255,255,.76); backdrop-filter:blur(22px); }
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
        .ef-result-score { display:grid; width:9rem; height:9rem; place-items:center; border-radius:50%; color:#fff; background:var(--ef-brand); box-shadow:0 0 0 9px var(--ef-soft); }
        .fi-simple-layout { background:linear-gradient(135deg,#eef2ff,#e0f2fe 48%,#ecfeff); }
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
        @media (min-width:640px) { .ef-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (min-width:1280px) { .ef-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } }
        @media (max-width:520px) { .ef-card-footer { align-items:stretch; flex-direction:column; } .ef-card-footer .fi-btn { width:100%; } }
    </style>
@endonce
