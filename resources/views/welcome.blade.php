<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Practise university past questions, track your results, and prepare confidently with ExamForge.">
    <title>ExamForge · University Past Questions</title>
    <style>
        :root { color-scheme:light; --ink:#1e1b4b; --muted:#68677e; --blue:#4f46e5; --blue-dark:#3730a3; --blue-soft:#eef2ff; --line:#dfe7ff; --white:#fff; --shadow:0 24px 70px -45px rgba(30,27,75,.55); }
        * { box-sizing:border-box; }
        html { scroll-behavior:smooth; }
        body { min-width:320px; min-height:100vh; margin:0; color:var(--ink); background:linear-gradient(145deg,#fff 0%,#f6f8ff 52%,#edf5ff 100%); font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; }
        a { color:inherit; text-decoration:none; }
        .shell { width:min(1120px,calc(100% - 36px)); margin-inline:auto; }
        header { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:24px 0; }
        .brand { display:flex; align-items:center; gap:12px; font-weight:800; }
        .mark { display:grid; width:44px; height:44px; place-items:center; border-radius:15px; color:#fff; background:var(--blue); box-shadow:0 12px 28px -14px rgba(79,70,229,.85); font-size:20px; }
        .header-actions,.hero-actions { display:flex; flex-wrap:wrap; gap:10px; }
        .btn { display:inline-flex; min-height:48px; align-items:center; justify-content:center; border:1px solid transparent; border-radius:13px; padding:0 19px; font-weight:750; transition:transform .2s ease,background .2s ease; }
        .btn:hover { transform:translateY(-1px); }
        .btn-primary { color:#fff; background:var(--blue); }
        .btn-primary:hover { background:var(--blue-dark); }
        .btn-secondary { border-color:var(--line); background:rgba(255,255,255,.82); }
        main { padding:64px 0 80px; }
        .hero { display:grid; align-items:center; gap:44px; }
        .eyebrow { color:var(--blue); font-size:12px; font-weight:850; letter-spacing:.1em; text-transform:uppercase; }
        h1 { max-width:760px; margin:13px 0 0; font-size:clamp(40px,7vw,74px); line-height:1.02; letter-spacing:-.055em; }
        .lead { max-width:650px; margin:22px 0 0; color:var(--muted); font-size:clamp(17px,2.3vw,20px); line-height:1.65; }
        .hero-actions { margin-top:30px; }
        .preview { position:relative; padding:24px; border:1px solid rgba(255,255,255,.9); border-radius:28px; background:rgba(255,255,255,.76); box-shadow:var(--shadow); backdrop-filter:blur(20px); }
        .preview-head { display:flex; align-items:center; justify-content:space-between; gap:14px; padding-bottom:20px; border-bottom:1px solid var(--line); }
        .course-code,.status { display:inline-flex; border-radius:999px; padding:7px 10px; font-size:12px; font-weight:800; }
        .course-code { color:var(--blue); background:var(--blue-soft); }
        .status { color:#16845b; background:#dcfce7; }
        .question { margin:24px 0 18px; font-size:clamp(20px,3vw,28px); line-height:1.35; }
        .options { display:grid; gap:10px; }
        .option { display:flex; align-items:center; gap:12px; min-height:58px; padding:11px 13px; border:1px solid var(--line); border-radius:15px; background:#fff; }
        .option:first-child { border-color:var(--blue); background:var(--blue-soft); }
        .letter { display:grid; flex:none; width:34px; height:34px; place-items:center; border-radius:10px; background:#f1f5f9; font-weight:800; }
        .option:first-child .letter { color:#fff; background:var(--blue); }
        .features { display:grid; gap:16px; margin-top:72px; }
        .feature { padding:22px; border:1px solid var(--line); border-radius:22px; background:rgba(255,255,255,.78); box-shadow:var(--shadow); }
        .feature strong { display:block; font-size:17px; }
        .feature p { margin:8px 0 0; color:var(--muted); line-height:1.55; }
        footer { padding:24px 0 34px; border-top:1px solid var(--line); color:var(--muted); font-size:13px; }
        @media (min-width:760px) { .hero { grid-template-columns:minmax(0,1.15fr) minmax(340px,.85fr); } .features { grid-template-columns:repeat(3,minmax(0,1fr)); } }
        @media (max-width:600px) { .shell { width:min(100% - 24px,1120px); } header { padding:16px 0; } .brand span:last-child { display:none; } .header-actions .btn-secondary { display:none; } main { padding-top:38px; } .hero-actions .btn { width:100%; } .preview { padding:17px; border-radius:22px; } }
    </style>
</head>
<body>
    <div class="shell">
        <header>
            <a class="brand" href="/" aria-label="ExamForge home"><span class="mark">E</span><span>ExamForge</span></a>
            <nav class="header-actions" aria-label="Account navigation">
                <a class="btn btn-secondary" href="{{ url('/student/login') }}">Student sign in</a>
                <a class="btn btn-primary" href="{{ url('/student/register') }}">Create account</a>
            </nav>
        </header>

        <main>
            <section class="hero">
                <div>
                    <p class="eyebrow">University exam preparation</p>
                    <h1>Practise past questions with confidence.</h1>
                    <p class="lead">Unlock your courses, take timed practice exams, and learn from clear answer explanations—all in one focused platform.</p>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="{{ url('/student/register') }}">Start practising</a>
                        <a class="btn btn-secondary" href="{{ url('/student/login') }}">I already have an account</a>
                    </div>
                </div>

                <div class="preview" aria-label="Practice exam preview">
                    <div class="preview-head"><span class="course-code">CSC 302</span><span class="status">Practice ready</span></div>
                    <h2 class="question">Which data structure uses FIFO ordering?</h2>
                    <div class="options">
                        <div class="option"><span class="letter">A</span><span>Queue</span></div>
                        <div class="option"><span class="letter">B</span><span>Stack</span></div>
                        <div class="option"><span class="letter">C</span><span>Binary tree</span></div>
                    </div>
                </div>
            </section>

            <section class="features" aria-label="How ExamForge works">
                <article class="feature"><strong>1. Choose a course</strong><p>Find past questions matched to your department and level.</p></article>
                <article class="feature"><strong>2. Unlock access</strong><p>Pay securely and begin practising as soon as payment is confirmed.</p></article>
                <article class="feature"><strong>3. Practise and review</strong><p>Complete timed exams and study every answer explanation.</p></article>
            </section>
        </main>

        <footer>© {{ date('Y') }} ExamForge · <a href="{{ url('/admin/login') }}">Administration</a></footer>
    </div>
</body>
</html>
