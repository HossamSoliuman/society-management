<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Society Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,600&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#10243e; --muted:#65758b; --line:#dbe3ec; --accent:#e84b1e; --paper:#fffdfa; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; display:grid; place-items:center; padding:24px; font-family:Manrope,sans-serif; color:var(--ink); background:radial-gradient(circle at 12% 18%,rgba(232,75,30,.12),transparent 28%),linear-gradient(145deg,#eef3f7,#f8f4ed); }
        .shell { width:min(100%,470px); background:var(--paper); border:1px solid rgba(16,36,62,.09); border-radius:18px; padding:38px; box-shadow:0 24px 70px rgba(16,36,62,.13); }
        .mark { width:48px; height:48px; display:grid; place-items:center; border-radius:13px; color:white; font:700 20px Manrope; background:var(--accent); box-shadow:0 8px 20px rgba(232,75,30,.25); }
        h1 { margin:22px 0 8px; font:600 34px/1.05 Newsreader,serif; letter-spacing:-.02em; }
        .lead { margin:0 0 26px; color:var(--muted); font-size:14px; line-height:1.65; }
        label { display:block; margin:16px 0 7px; font-size:12px; font-weight:700; }
        input { width:100%; border:1px solid var(--line); border-radius:9px; padding:12px 13px; font:14px Manrope; outline:none; background:white; }
        input:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(232,75,30,.1); }
        button { width:100%; margin-top:22px; border:0; border-radius:9px; padding:13px; color:white; font:700 14px Manrope; background:var(--accent); cursor:pointer; }
        button:hover { background:#c93e16; }
        .back { display:block; margin-top:20px; text-align:center; color:var(--muted); font-size:12px; text-decoration:none; }
        .notice,.errors { margin:18px 0; padding:11px 13px; border-radius:9px; font-size:12px; line-height:1.5; }
        .notice { color:#166534; background:#dcfce7; }
        .errors { color:#991b1b; background:#fee2e2; }
        @media (max-width: 480px) {
            body { padding:16px; }
            .shell { padding:26px 20px; border-radius:14px; }
            h1 { font-size:28px; }
            input, button { font-size:16px; }
        }
    </style>
</head>
<body>
    <main class="shell">
        <div class="mark">SM</div>
        @yield('content')
    </main>
</body>
</html>
