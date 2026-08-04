<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') | Foodflix Admin</title>
    <style>
        :root { --primary:#f04e23; --cta:#e8271a; --text:#1f2937; --muted:#6b7280; --danger:#b91c1c; }
        * { box-sizing:border-box; }
        body { min-height:100vh; margin:0; display:grid; place-items:center; padding:30px 18px; background:#f5f6f8; color:var(--text); font-family:Inter,ui-sans-serif,system-ui,sans-serif; }
        .shell { width:min(100%,430px); }
        .brand { margin:0 0 22px; color:var(--primary); text-align:center; font-size:27px; font-weight:850; }
        .card { padding:32px; border-radius:15px; background:#fff; box-shadow:0 18px 48px rgba(31,41,55,.09); }
        h1 { margin:0; text-align:center; font-size:24px; }
        .subtitle { margin:8px 0 25px; color:var(--muted); text-align:center; font-size:13px; }
        .field { margin-bottom:17px; }
        label { display:block; margin-bottom:7px; font-size:13px; font-weight:700; }
        input { width:100%; height:47px; padding:0 13px; border:1px solid #dfe1e6; border-radius:9px; outline:0; background:#f3f4f6; }
        input:focus { border-color:rgba(240,78,35,.6); background:#fff; box-shadow:0 0 0 3px rgba(240,78,35,.1); }
        .error { margin:6px 0 0; color:var(--danger); font-size:12px; }
        .success { margin:0 0 18px; padding:11px 13px; border-radius:8px; background:#f0fdf4; color:#166534; font-size:13px; }
        button { width:100%; height:48px; margin-top:6px; border:0; border-radius:9px; background:var(--cta); color:#fff; cursor:pointer; font-weight:800; }
        .back { display:block; margin-top:17px; color:var(--primary); text-align:center; font-size:12px; font-weight:700; text-decoration:none; }
    </style>
</head>
<body>
    <main class="shell">
        <p class="brand">Foodflix</p>
        <section class="card">@yield('form')</section>
    </main>
</body>
</html>
