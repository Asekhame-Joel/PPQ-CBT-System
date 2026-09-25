@once
    <style>
        :root { --ea-ink:#172554; --ea-muted:#64748b; --ea-brand:#4f46e5; --ea-panel:rgba(255,255,255,.78); --ea-line:rgba(255,255,255,.85); --ea-shadow:0 22px 55px -40px rgba(30,41,59,.48); }
        .fi-body { background:linear-gradient(135deg,#f8fafc,#eef2ff 52%,#ecfeff); }
        .dark .fi-body { background:linear-gradient(135deg,#111827,#172554 52%,#083344); }
        .fi-topbar,.fi-sidebar { background-color:rgba(255,255,255,.80); backdrop-filter:blur(20px); }
        .dark .fi-topbar,.dark .fi-sidebar { background-color:rgba(17,24,39,.82); }
        .fi-section,.fi-ta-ctn { border-color:var(--ea-line); border-radius:1.35rem; background:var(--ea-panel); box-shadow:var(--ea-shadow); backdrop-filter:blur(18px); }
        .dark .fi-section,.dark .fi-ta-ctn { border-color:rgba(255,255,255,.10); background:rgba(15,23,42,.72); }
        .ea-intro { margin-bottom:1.5rem; }
        .ea-eyebrow { color:var(--ea-brand); font-size:.75rem; font-weight:800; letter-spacing:.09em; text-transform:uppercase; }
        .ea-title { margin-top:.35rem; color:var(--ea-ink); font-size:clamp(1.7rem,4vw,2.35rem); font-weight:800; line-height:1.12; letter-spacing:-.03em; }
        .ea-copy { margin-top:.6rem; max-width:44rem; color:var(--ea-muted); font-size:.92rem; line-height:1.6; }
        .dark .ea-title { color:#f8fafc; } .dark .ea-copy { color:#cbd5e1; }
        .ea-steps { display:grid; gap:.75rem; margin-bottom:1.5rem; }
        .ea-step { display:flex; gap:.8rem; align-items:flex-start; padding:1rem; border:1px solid var(--ea-line); border-radius:1rem; background:var(--ea-panel); box-shadow:var(--ea-shadow); }
        .dark .ea-step { border-color:rgba(255,255,255,.10); background:rgba(15,23,42,.68); }
        .ea-step-number { flex:none; display:grid; width:2rem; height:2rem; place-items:center; border-radius:.7rem; color:#fff; background:var(--ea-brand); font-size:.8rem; font-weight:800; }
        .ea-step strong { display:block; color:var(--ea-ink); font-size:.86rem; } .dark .ea-step strong { color:#f8fafc; }
        .ea-step span { display:block; margin-top:.2rem; color:var(--ea-muted); font-size:.78rem; line-height:1.45; } .dark .ea-step span { color:#94a3b8; }
        @media (min-width:768px) { .ea-steps { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    </style>
@endonce
