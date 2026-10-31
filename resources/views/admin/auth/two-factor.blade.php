<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Code de connexion — NSOROMMA</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/shell.css') }}?v={{ filemtime(public_path('css/shell.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
</head>
<body class="admin-auth">
    <x-toasts />

    <main class="admin-auth-layout">
        <aside class="admin-auth-brand" aria-hidden="true">
            <img src="{{ asset('images/brand/nsoromma-mark.png') }}" alt="" class="admin-auth-mark" width="160" height="160">
            <div>
                <p class="admin-auth-quote">« Enfant des cieux, étoiles »</p>
                <p class="admin-auth-caption">NSOROMMA — Espace d'administration</p>
            </div>
        </aside>

        <section class="admin-auth-panel">
            <a href="{{ route('admin.login') }}" class="admin-auth-back">
                <x-admin.icon name="arrow-left" />
                <span>Changer de compte</span>
            </a>

            <div class="admin-auth-form">
                <p class="admin-auth-eyebrow">Vérification en deux étapes</p>
                <h1 class="page-title">Code de connexion</h1>
                <p class="page-subtitle">Nous venons d'envoyer un code à 6 chiffres à <strong>{{ $maskedEmail }}</strong>. Il expire dans {{ \App\Http\Controllers\AdminTwoFactorController::CODE_VALID_MINUTES }} minutes.</p>

                <form action="{{ route('admin.two-factor.verify') }}" method="POST" class="stack" style="margin-top: 2rem; gap: 1.4rem;">
                    @csrf

                    <div class="field">
                        <label class="label" for="code">Code reçu par e-mail</label>
                        <input id="code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9 ]*" maxlength="7" required autofocus
                               class="input two-factor-code @error('code') is-invalid @enderror" placeholder="000 000">
                        @error('code')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <label class="admin-auth-remember">
                        <input type="checkbox" name="trust_device" value="1" checked>
                        <span>Faire confiance à cet appareil pendant {{ \App\Http\Controllers\AdminTwoFactorController::TRUSTED_DEVICE_DAYS }} jours</span>
                    </label>

                    <button type="submit" class="btn btn-primary btn-block" style="min-height: 46px;">Valider et me connecter</button>
                </form>

                <form action="{{ route('admin.two-factor.resend') }}" method="POST" class="admin-auth-help">
                    @csrf
                    Rien reçu ? Vérifiez vos courriers indésirables, ou
                    <button type="submit" class="link" style="padding: 0; border: 0; background: none; cursor: pointer;">renvoyez un nouveau code</button>.
                </form>
            </div>
        </section>
    </main>
</body>
</html>
