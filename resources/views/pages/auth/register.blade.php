@extends('layouts.app')
@section('title', 'Créer un compte — L\'NSOROMMA')
@section('meta_description', 'Créez votre compte lecteur pour télécharger vos e-books certifiés et suivre vos commandes de livres.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/books.css') }}">
    <link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
    <style>
        .auth-hero {
            display: grid;
            grid-template-columns: 1.05fr 1.15fr;
            gap: 3.5rem;
            align-items: start;
            padding: 3rem 3rem 2.5rem 3rem;
        }
        .auth-card-panel {
            background: #FFFDF9;
            border: 1.5px solid rgba(217, 174, 92, 0.28);
            border-radius: 24px;
            padding: 2.2rem 2.4rem;
            box-shadow: 0 10px 30px rgba(60, 45, 25, 0.05);
        }
        .auth-input-group {
            position: relative;
        }
        .auth-input-group input {
            width: 100%;
            background: #F8F1E5;
            border: 1px solid rgba(139, 133, 124, 0.2);
            border-radius: 14px;
            padding: 0.8rem 1rem 0.8rem 2.85rem;
            font-size: 0.88rem;
            color: #1E1D1B;
            outline: none;
            transition: all 0.2s ease;
        }
        .auth-input-group input:focus {
            border-color: #D9AE5C;
            background: #FFFFFF;
            box-shadow: 0 0 0 3px rgba(217, 174, 92, 0.15);
        }
        .auth-input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #8B857C;
            pointer-events: none;
            transition: color 0.2s;
        }
        .auth-input-group input:focus ~ .auth-input-icon {
            color: #D9AE5C;
        }
        .auth-btn-primary {
            background: #191919;
            color: #D9AE5C;
            border: 1px solid #191919;
            border-radius: 16px;
            padding: 0.85rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            width: 100%;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(25, 25, 25, 0.15);
        }
        .auth-btn-primary:hover {
            background: #2C2B29;
            border-color: #D9AE5C;
            color: #FFFFFF;
            transform: translateY(-1px);
        }
        .auth-btn-secondary {
            background: #FAF5ED;
            border: 1.5px solid rgba(217, 174, 92, 0.35);
            color: #1E1D1B;
            font-weight: 700;
            font-size: 0.85rem;
            border-radius: 14px;
            padding: 0.75rem 1.5rem;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            transition: all 0.25s ease;
        }
        .auth-btn-secondary:hover {
            background: #D9AE5C;
            color: #191919;
            border-color: #D9AE5C;
        }
        @media (max-width: 1024px) {
            .auth-hero {
                grid-template-columns: 1fr;
                gap: 2.2rem;
                padding: 2rem;
            }
        }
        @media (max-width: 640px) {
            .auth-hero {
                padding: 1.5rem 1rem;
                gap: 1.8rem;
            }
            .auth-card-panel {
                padding: 1.5rem 1.25rem;
                border-radius: 20px;
            }
        }
    </style>
@endpush

@section('content')
<div>

    <!-- 1. Section Héro & Formulaire d'Inscription (Style BROKS) -->
    <section class="auth-hero" aria-label="Espace d'inscription">
        
        <!-- Zone Gauche : Titre éditorial, Fil d'Ariane & Avantages -->
        <div class="hero-left">
            <nav class="breadcrumb" style="margin-bottom: 0.75rem;" aria-label="Fil d'Ariane">
                <ol class="breadcrumb-list">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                    <li class="breadcrumb-separator">›</li>
                    <li class="breadcrumb-item active" aria-current="page">Inscription</li>
                </ol>
            </nav>

            <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: #F8F1E5; border: 1px solid rgba(217, 174, 92, 0.3); padding: 0.35rem 0.85rem; border-radius: 20px; font-size: 0.72rem; font-weight: 800; color: #D9AE5C; text-transform: uppercase; letter-spacing: 0.1em; width: fit-content; margin-bottom: 1rem;">
                <span>★</span> Rejoindre le Lectorat
            </div>

            <h1 class="hero-title font-editorial" style="font-size: 3.2rem; line-height: 1.1; margin-bottom: 1rem;">
                Rejoignez la<br>Communauté
            </h1>

            <p class="hero-subtitle" style="font-size: 0.98rem; line-height: 1.6; margin-bottom: 2rem; color: #786B5A;">
                Créez votre compte lecteur en quelques instants pour constituer votre bibliothèque personnelle, télécharger vos e-books protégés et suivre vos expéditions en toute sérénité.
            </p>

            <!-- Liste des avantages du compte lecteur -->
            <div style="display: flex; flex-direction: column; gap: 1.25rem; margin-bottom: 2.2rem;">
                
                <div style="display: flex; align-items: flex-start; gap: 0.9rem;">
                    <div style="width: 34px; height: 34px; border-radius: 50%; background: #D9AE5C; color: #191919; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; flex-shrink: 0; margin-top: 2px; box-shadow: 0 4px 10px rgba(217, 174, 92, 0.25);">
                        ★
                    </div>
                    <div>
                        <h2 style="font-size: 0.92rem; font-weight: 700; color: #1E1D1B; margin-bottom: 2px;">Téléchargement Direct des E-books</h2>
                        <p style="font-size: 0.82rem; color: #8B857C; line-height: 1.45;">Accédez immédiatement à vos œuvres PDF après paiement, sans délai d'attente ni manipulation complexe.</p>
                    </div>
                </div>

                <div style="display: flex; align-items: flex-start; gap: 0.9rem;">
                    <div style="width: 34px; height: 34px; border-radius: 50%; background: #D9AE5C; color: #191919; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; flex-shrink: 0; margin-top: 2px; box-shadow: 0 4px 10px rgba(217, 174, 92, 0.25);">
                        ★
                    </div>
                    <div>
                        <h2 style="font-size: 0.92rem; font-weight: 700; color: #1E1D1B; margin-bottom: 2px;">Historique &amp; Factures Centralisées</h2>
                        <p style="font-size: 0.82rem; color: #8B857C; line-height: 1.45;">Retrouvez l'ensemble de vos transactions, reçus de commande et liens de téléchargement au même endroit.</p>
                    </div>
                </div>

                <div style="display: flex; align-items: flex-start; gap: 0.9rem;">
                    <div style="width: 34px; height: 34px; border-radius: 50%; background: #D9AE5C; color: #191919; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; flex-shrink: 0; margin-top: 2px; box-shadow: 0 4px 10px rgba(217, 174, 92, 0.25);">
                        ★
                    </div>
                    <div>
                        <h2 style="font-size: 0.92rem; font-weight: 700; color: #1E1D1B; margin-bottom: 2px;">Accès Prioritaire aux Nouveautés</h2>
                        <p style="font-size: 0.82rem; color: #8B857C; line-height: 1.45;">Bénéficiez des tirages limités, rééditions prestigieuses et réductions exclusives réservées aux membres.</p>
                    </div>
                </div>

            </div>

            <!-- Carte Support WhatsApp (Style audio-card) -->
            <article class="audio-card" style="background: #FAF5ED; border: 1px dashed rgba(217, 174, 92, 0.45); margin-top: auto;" aria-label="Support direct">
                <div class="audio-meta">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(47, 111, 78, 0.15); color: #2F6F4E; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i data-lucide="message-circle" style="width: 22px; height: 22px;"></i>
                    </div>
                    <div>
                        <span class="author-label" style="font-size: 0.68rem;">Assistance Réactive</span>
                        <h3 class="audio-title" style="font-size: 0.92rem;">Une question sur l'inscription ?</h3>
                        <p class="audio-author" style="font-size: 0.78rem;">
                            WhatsApp : <strong>+225 07 00 00 00 01</strong> (Disponible 7j/7)
                        </p>
                    </div>
                </div>
            </article>

        </div>

        <!-- Zone Droite : Panneau Formulaire d'Inscription -->
        <div>
            <div class="auth-card-panel">
                
                <!-- En-tête de la carte -->
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; padding-bottom: 1.25rem; border-bottom: 1px solid rgba(139, 133, 124, 0.1);">
                    <div>
                        <h2 class="font-editorial" style="font-size: 1.75rem; font-weight: 800; color: #1E1D1B; margin-bottom: 0.25rem;">
                            Créer un Compte
                        </h2>
                        <p style="font-size: 0.82rem; color: #8B857C;">Remplissez les informations ci-dessous.</p>
                    </div>
                    <div style="width: 46px; height: 46px; border-radius: 50%; background: #D9AE5C; color: #191919; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.15rem; box-shadow: 0 4px 14px rgba(217, 174, 92, 0.35);">
                        ★
                    </div>
                </div>

                <!-- Messages Flash / Alertes -->
                @if(session('success'))
                    <div style="background: rgba(47, 111, 78, 0.1); border: 1px solid rgba(47, 111, 78, 0.3); color: #2F6F4E; border-radius: 12px; padding: 0.85rem 1rem; font-size: 0.82rem; font-weight: 600; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="check-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div style="background: rgba(225, 29, 72, 0.08); border: 1px solid rgba(225, 29, 72, 0.25); color: #BE123C; border-radius: 12px; padding: 0.85rem 1rem; font-size: 0.82rem; font-weight: 600; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="alert-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Formulaire -->
                <form action="{{ route('register') }}" method="POST" style="display: flex; flex-direction: column; gap: 1.15rem;" x-data="{ showPassword: false }">
                    @csrf

                    <!-- Champ Nom Complet -->
                    <div>
                        <label for="name" style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.45rem;">
                            Nom &amp; Prénoms *
                        </label>
                        <div class="auth-input-group">
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   placeholder="Ex : Koffi Kouassi"
                                   required 
                                   autofocus
                                   @error('name') style="border-color: #BE123C; background: #FFF5F5;" @enderror>
                            <i data-lucide="user" class="auth-input-icon" style="width: 18px; height: 18px;"></i>
                        </div>
                        @error('name')
                            <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.35rem; display: flex; align-items: center; gap: 0.3rem;">
                                <i data-lucide="alert-triangle" style="width: 13px; height: 13px;"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Double colonne Email & Téléphone -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        
                        <!-- Champ Email -->
                        <div>
                            <label for="email" style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.45rem;">
                                Adresse Email *
                            </label>
                            <div class="auth-input-group">
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       placeholder="votre.email@domaine.com"
                                       required
                                       @error('email') style="border-color: #BE123C; background: #FFF5F5;" @enderror>
                                <i data-lucide="mail" class="auth-input-icon" style="width: 18px; height: 18px;"></i>
                            </div>
                            @error('email')
                                <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.35rem; display: flex; align-items: center; gap: 0.3rem;">
                                    <i data-lucide="alert-triangle" style="width: 13px; height: 13px;"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Champ Téléphone -->
                        <div>
                            <label for="phone" style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.45rem;">
                                Téléphone *
                            </label>
                            <div class="auth-input-group">
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone') }}" 
                                       placeholder="+225 07 00 00 00 00"
                                       required
                                       @error('phone') style="border-color: #BE123C; background: #FFF5F5;" @enderror>
                                <i data-lucide="phone" class="auth-input-icon" style="width: 18px; height: 18px;"></i>
                            </div>
                            @error('phone')
                                <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.35rem; display: flex; align-items: center; gap: 0.3rem;">
                                    <i data-lucide="alert-triangle" style="width: 13px; height: 13px;"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>

                    <!-- Double colonne Mot de passe & Confirmation -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        
                        <!-- Mot de passe -->
                        <div>
                            <label for="password" style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.45rem;">
                                Mot de passe *
                            </label>
                            <div class="auth-input-group">
                                <input :type="showPassword ? 'text' : 'password'" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Min. 8 caractères"
                                       required
                                       @error('password') style="border-color: #BE123C; background: #FFF5F5;" @enderror>
                                <i data-lucide="lock" class="auth-input-icon" style="width: 18px; height: 18px;"></i>
                            </div>
                        </div>

                        <!-- Confirmation -->
                        <div>
                            <label for="password_confirmation" style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.45rem;">
                                Confirmation *
                            </label>
                            <div class="auth-input-group">
                                <input :type="showPassword ? 'text' : 'password'" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       placeholder="Répétez le mot de passe"
                                       required>
                                <i data-lucide="lock" class="auth-input-icon" style="width: 18px; height: 18px;"></i>
                            </div>
                        </div>

                    </div>

                    @error('password')
                        <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: -0.25rem; display: flex; align-items: center; gap: 0.3rem;">
                            <i data-lucide="alert-triangle" style="width: 13px; height: 13px;"></i>
                            {{ $message }}
                        </p>
                    @enderror

                    <!-- Afficher les mots de passe -->
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: #786B5A; padding-top: 0.25rem;">
                        <input type="checkbox" id="togglePass" @change="showPassword = !showPassword" style="accent-color: #D9AE5C; width: 16px; height: 16px; border-radius: 4px; cursor: pointer;">
                        <label for="togglePass" style="cursor: pointer; user-select: none;">Afficher les mots de passe</label>
                    </div>

                    <!-- Bouton Inscription -->
                    <button type="submit" class="auth-btn-primary" style="margin-top: 0.5rem;">
                        <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i>
                        <span>Créer mon compte lecteur</span>
                    </button>
                </form>

                <!-- Séparateur & Lien Connexion -->
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(139, 133, 124, 0.1); text-align: center;">
                    <p style="font-size: 0.82rem; color: #8B857C; margin-bottom: 0.85rem;">
                        Déjà inscrit sur NSOROMMA ?
                    </p>
                    <a href="{{ route('login') }}" class="auth-btn-secondary">
                        <i data-lucide="log-in" style="width: 16px; height: 16px;"></i>
                        <span>Se connecter à mon compte</span>
                    </a>
                </div>

            </div>
        </div>

    </section>

    <!-- 2. Section Engagements & Garanties (Style Exact de book.index) -->
    <section style="padding: 2.5rem 3rem 3rem 3rem; border-top: 1px solid rgba(139, 133, 124, 0.08); background-color: #FAF5ED;" aria-label="Engagements et garanties">
        
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; color: #D9AE5C;">Garanties de Confiance</span>
                <h2 class="font-editorial" style="font-size: 1.5rem; font-weight: 800; color: #1E1D1B;">Les Engagements de NSOROMMA</h2>
            </div>
            <a href="{{ route('catalog.index') }}" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.82rem; font-weight: 700; color: #D9AE5C; text-decoration: none;">
                <span>Explorer le catalogue complet</span>
                <i data-lucide="arrow-right" style="width: 15px; height: 15px;"></i>
            </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1.25rem;">
            
            <div style="background: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.2); border-radius: 18px; padding: 1.35rem 1.5rem; box-shadow: 0 4px 12px rgba(60,45,25,0.03);">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(217, 174, 92, 0.15); color: #D9AE5C; display: flex; align-items: center; justify-content: center; margin-bottom: 0.85rem;">
                    <i data-lucide="zap" style="width: 20px; height: 20px;"></i>
                </div>
                <h3 style="font-size: 0.95rem; font-weight: 700; color: #1E1D1B; margin-bottom: 0.35rem;">Téléchargement Instantané</h3>
                <p style="font-size: 0.8rem; color: #8B857C; line-height: 1.45;">Vos e-books PDF sont accessibles en téléchargement dès la validation automatique de votre paiement.</p>
            </div>

            <div style="background: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.2); border-radius: 18px; padding: 1.35rem 1.5rem; box-shadow: 0 4px 12px rgba(60,45,25,0.03);">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(217, 174, 92, 0.15); color: #D9AE5C; display: flex; align-items: center; justify-content: center; margin-bottom: 0.85rem;">
                    <i data-lucide="shield-check" style="width: 20px; height: 20px;"></i>
                </div>
                <h3 style="font-size: 0.95rem; font-weight: 700; color: #1E1D1B; margin-bottom: 0.35rem;">Filigrane FPDI Sécurisé</h3>
                <p style="font-size: 0.8rem; color: #8B857C; line-height: 1.45;">Chaque fichier est gravé de vos informations afin de protéger les droits d'auteur et l'authenticité de l'ouvrage.</p>
            </div>

            <div style="background: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.2); border-radius: 18px; padding: 1.35rem 1.5rem; box-shadow: 0 4px 12px rgba(60,45,25,0.03);">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(217, 174, 92, 0.15); color: #D9AE5C; display: flex; align-items: center; justify-content: center; margin-bottom: 0.85rem;">
                    <i data-lucide="truck" style="width: 20px; height: 20px;"></i>
                </div>
                <h3 style="font-size: 0.95rem; font-weight: 700; color: #1E1D1B; margin-bottom: 0.35rem;">Livraison Partout en CI</h3>
                <p style="font-size: 0.8rem; color: #8B857C; line-height: 1.45;">Expédition soignée à domicile ou en point relais à Abidjan et dans toutes les régions sous 48h.</p>
            </div>

            <div style="background: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.2); border-radius: 18px; padding: 1.35rem 1.5rem; box-shadow: 0 4px 12px rgba(60,45,25,0.03);">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(217, 174, 92, 0.15); color: #D9AE5C; display: flex; align-items: center; justify-content: center; margin-bottom: 0.85rem;">
                    <i data-lucide="smartphone" style="width: 20px; height: 20px;"></i>
                </div>
                <h3 style="font-size: 0.95rem; font-weight: 700; color: #1E1D1B; margin-bottom: 0.35rem;">Paiement Paystack Sécurisé</h3>
                <p style="font-size: 0.8rem; color: #8B857C; line-height: 1.45;">Réglez par Mobile Money (Wave, Orange, MTN, Moov) ou carte bancaire avec chiffrement bancaire certifié.</p>
            </div>

        </div>

    </section>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.initIcons === 'function') {
            window.initIcons();
        }
    });
</script>
@endpush
@endsection
