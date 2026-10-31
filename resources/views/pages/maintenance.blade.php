<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Bientôt disponible — NSOROMMA</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #FAF7F2; --text: #1A1714; --muted: #6B6259; --accent: #B08A4A; --line: #E7DFD3; }
        @media (prefers-color-scheme: dark) {
            :root { --bg: #14110F; --text: #F2ECE4; --muted: #A99F94; --accent: #D2AE6E; --line: #2E2925; }
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; min-height: 100dvh; display: grid; place-items: center; padding: 2rem 1.25rem; background: var(--bg); color: var(--text); font-family: 'Plus Jakarta Sans', system-ui, sans-serif; text-align: center; }
        main { max-width: 34rem; }
        .mark { width: 96px; height: 96px; object-fit: contain; animation: glow 3.2s ease-in-out infinite; }
        .eyebrow { margin: 1.75rem 0 0; font-size: .72rem; font-weight: 600; letter-spacing: .22em; text-transform: uppercase; color: var(--accent); }
        h1 { margin: .6rem 0 0; font-family: 'Cormorant Garamond', Georgia, serif; font-weight: 500; font-size: clamp(2.2rem, 7vw, 3.2rem); line-height: 1.1; }
        p.message { margin: 1.1rem auto 0; max-width: 28rem; color: var(--muted); line-height: 1.7; white-space: pre-line; }
        .rule { width: 48px; height: 1px; margin: 2rem auto; background: var(--line); }
        .brand { font-family: 'Cormorant Garamond', Georgia, serif; letter-spacing: .18em; font-size: .95rem; }
        .staff { display: inline-block; margin-top: 1.5rem; font-size: .75rem; color: var(--muted); text-decoration: none; opacity: .7; }
        .staff:hover { opacity: 1; text-decoration: underline; }
        @keyframes glow { 50% { transform: scale(1.04); filter: drop-shadow(0 0 18px rgba(176, 138, 74, .35)); } }
        @media (prefers-reduced-motion: reduce) { .mark { animation: none; } }
    </style>
</head>
<body>
    <main>
        <img src="{{ asset('images/brand/nsoromma-mark.png') }}" alt="" class="mark" width="96" height="96">
        <p class="eyebrow">Maintenance en cours</p>
        <h1>Le site sera bientôt disponible</h1>
        <p class="message">{{ $message ?: 'Nous effectuons quelques améliorations techniques. Merci de votre patience, revenez dans un instant.' }}</p>
        <div class="rule"></div>
        <div class="brand">NSOROMMA</div>
        <a href="{{ route('admin.login') }}" class="staff">Accès équipe</a>
    </main>
</body>
</html>
