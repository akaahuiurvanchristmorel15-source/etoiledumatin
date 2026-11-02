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
    @php
        // Ciel de 2600 × 2000 px : chaque étoile est une ombre portée d'un seul élément, sans image ni JavaScript
        $stars = fn (int $count): string => collect(range(1, $count))
            ->map(fn (): string => random_int(0, 2600).'px '.random_int(0, 2000).'px #fff')
            ->implode(',');
    @endphp
    <style>
        :root { color-scheme: dark; --text: #F2ECE4; --muted: #B4BAC2; --accent: #D2AE6E; --line: rgba(242, 236, 228, .18); }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; min-height: 100dvh; display: grid; place-items: center; padding: 2rem 1.25rem; background: #090A0F; color: var(--text); font-family: 'Plus Jakarta Sans', system-ui, sans-serif; text-align: center; }
        .sky { position: fixed; inset: 0; overflow: hidden; background: radial-gradient(ellipse at bottom, #1B2735 0%, #090A0F 100%); }
        .stars { position: absolute; top: 0; left: 0; background: transparent; animation: drift linear infinite; }
        .stars::after { content: ''; position: absolute; top: 2000px; left: 0; width: inherit; height: inherit; box-shadow: inherit; }
        .stars-small { width: 1px; height: 1px; animation-duration: 50s; box-shadow: {{ $stars(700) }}; }
        .stars-medium { width: 2px; height: 2px; animation-duration: 100s; box-shadow: {{ $stars(200) }}; }
        .stars-big { width: 3px; height: 3px; animation-duration: 150s; box-shadow: {{ $stars(100) }}; }
        main { position: relative; isolation: isolate; max-width: 34rem; }
        main::before { content: ''; position: absolute; inset: -4rem; z-index: -1; background: radial-gradient(closest-side, rgba(9, 10, 15, .8) 55%, transparent); }
        .mark { width: 96px; height: 96px; margin: 0 auto; animation: glow 3.2s ease-in-out infinite; }
        .mark::before { content: ''; display: block; height: 100%; background: var(--accent); -webkit-mask: url('{{ asset('images/brand/nsoromma-mark.png') }}') center / contain no-repeat; mask: url('{{ asset('images/brand/nsoromma-mark.png') }}') center / contain no-repeat; }
        .eyebrow { margin: 1.75rem 0 0; font-size: .72rem; font-weight: 600; letter-spacing: .22em; text-transform: uppercase; color: var(--accent); }
        h1 { margin: .6rem 0 0; font-family: 'Cormorant Garamond', Georgia, serif; font-weight: 500; font-size: clamp(2.2rem, 7vw, 3.2rem); line-height: 1.1; }
        p.message { margin: 1.1rem auto 0; max-width: 28rem; color: var(--muted); line-height: 1.7; white-space: pre-line; }
        .rule { width: 48px; height: 1px; margin: 2rem auto; background: var(--line); }
        .brand { font-family: 'Cormorant Garamond', Georgia, serif; letter-spacing: .18em; font-size: .95rem; }
        .staff { display: inline-block; margin-top: 1.5rem; font-size: .75rem; color: var(--muted); text-decoration: none; opacity: .7; }
        .staff:hover { opacity: 1; text-decoration: underline; }
        @keyframes drift { to { transform: translateY(-2000px); } }
        @keyframes glow { 50% { transform: scale(1.04); filter: drop-shadow(0 0 18px rgba(210, 174, 110, .45)); } }
        @media (prefers-reduced-motion: reduce) { .mark, .stars { animation: none; } }
    </style>
</head>
<body>
    <div class="sky" aria-hidden="true">
        <div class="stars stars-small"></div>
        <div class="stars stars-medium"></div>
        <div class="stars stars-big"></div>
    </div>

    <main>
        <div class="mark"></div>
        <p class="eyebrow">Maintenance en cours</p>
        <h1>Le site sera bientôt disponible</h1>
        <p class="message">{{ $message ?: 'Nous effectuons quelques améliorations techniques. Merci de votre patience, revenez dans un instant.' }}</p>
        <div class="rule"></div>
        <div class="brand">NSOROMMA</div>
        <a href="{{ route('admin.login') }}" class="staff">Accès équipe</a>
    </main>
</body>
</html>
