<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Connexion à l'administration — NSOROMMA</title>

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
            <a href="{{ route('home') }}" class="admin-auth-back">
                <x-admin.icon name="arrow-left" />
                <span>Retour à la boutique</span>
            </a>

            <div class="admin-auth-form">
                <p class="admin-auth-eyebrow">Administration</p>
                <h1 class="page-title">Connexion</h1>
                <p class="page-subtitle">Réservé à l'équipe NSOROMMA : gestion du catalogue, des commandes et des paramètres.</p>

                @if($errors->any())
                    <div class="alert alert-error" role="alert" style="margin-top: 1.5rem;">
                        <x-admin.icon name="alert" />
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="stack" style="margin-top: 2rem; gap: 1.4rem;">
                    @csrf

                    <div class="field">
                        <label class="label" for="email">Adresse e-mail</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input @error('email') is-invalid @enderror">
                    </div>

                    <div class="field">
                        <label class="label" for="password">Mot de passe</label>
                        <div class="admin-auth-password">
                            <input id="password" type="password" name="password" required autocomplete="current-password" class="input @error('password') is-invalid @enderror">
                            <button type="button" class="admin-auth-toggle" data-toggle-password aria-label="Afficher le mot de passe" aria-pressed="false">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <label class="admin-auth-remember">
                        <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                        <span>Rester connecté sur cet appareil</span>
                    </label>

                    <button type="submit" class="btn btn-primary btn-block" style="min-height: 46px;">Se connecter</button>
                </form>

                <p class="admin-auth-help">Mot de passe oublié ? <a href="{{ route('password.request') }}" class="link">Réinitialisez-le par e-mail</a>.</p>
            </div>
        </section>
    </main>

    <script>
        document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = document.getElementById('password');
                var visible = input.type === 'text';
                input.type = visible ? 'password' : 'text';
                button.setAttribute('aria-pressed', visible ? 'false' : 'true');
                button.setAttribute('aria-label', visible ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
            });
        });
    </script>
</body>
</html>
