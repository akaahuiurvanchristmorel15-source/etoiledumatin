@extends('layouts.admin')
@section('title', 'Nouveau Livre — Administration')

@section('content')
<div style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">

    <!-- En-tête -->
    <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 1.25rem; border-bottom: 1px solid rgba(139, 133, 124, 0.1);">
        <div>
            <nav class="breadcrumb" style="margin-bottom: 0.5rem;" aria-label="Fil d'Ariane">
                <ol class="breadcrumb-list">
                    <li class="breadcrumb-item"><a href="{{ route('admin.books.index') }}">Catalogue</a></li>
                    <li class="breadcrumb-separator">›</li>
                    <li class="breadcrumb-item active" aria-current="page">Nouveau Livre</li>
                </ol>
            </nav>
            <h1 class="hero-title font-editorial" style="font-size: 2.2rem; line-height: 1.15; margin: 0;">
                Ajouter un Ouvrage
            </h1>
        </div>
        <a href="{{ route('admin.books.index') }}" class="admin-btn-secondary">
            &larr; Retour au catalogue
        </a>
    </div>

    <!-- Formulaire d'ajout -->
    <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.75rem;">
        @csrf

        <!-- 1. Informations Générales -->
        <div style="background-color: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.25); border-radius: 20px; padding: 2rem; box-shadow: 0 4px 14px rgba(60, 45, 25, 0.04);">
            <h2 class="font-editorial" style="font-size: 1.25rem; font-weight: 800; color: #1E1D1B; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(139, 133, 124, 0.1);">
                1. Notice Éditoriale
            </h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem;">
                <div style="grid-column: 1 / -1;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Titre de l'ouvrage *
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Ex : Nations nègres et culture" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                    @error('title') <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Auteur *
                    </label>
                    <input type="text" name="author" value="{{ old('author') }}" required placeholder="Ex : Cheikh Anta Diop" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                    @error('author') <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Collection / Catégorie *
                    </label>
                    <select name="category_id" required style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                        <option value="">Sélectionnez une collection</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Maison d'édition
                    </label>
                    <input type="text" name="publisher" value="{{ old('publisher', 'Éditions L\'Étoile du Matin') }}" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Langue *
                    </label>
                    <input type="text" name="language" value="{{ old('language', 'Français') }}" required style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Nombre de pages
                    </label>
                    <input type="number" name="pages_count" value="{{ old('pages_count') }}" min="1" placeholder="Ex : 320" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Année d'édition
                    </label>
                    <input type="number" name="published_year" value="{{ old('published_year', date('Y')) }}" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div style="grid-column: 1 / -1;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Code ISBN
                    </label>
                    <input type="text" name="isbn" value="{{ old('isbn') }}" placeholder="Ex : 978-2-9000-0000-0" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div style="grid-column: 1 / -1;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Description / Résumé complet *
                    </label>
                    <textarea name="description" rows="5" required placeholder="Présentation détaillée de l'ouvrage..." style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none; line-height: 1.5;">{{ old('description') }}</textarea>
                    @error('description') <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div style="grid-column: 1 / -1;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Extrait pour feuilletage en ligne
                    </label>
                    <textarea name="excerpt" rows="3" placeholder="Paragraphe d'ouverture ou extrait significatif..." style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none; line-height: 1.5;">{{ old('excerpt') }}</textarea>
                </div>
            </div>
        </div>

        <!-- 2. Tarification, Formats & Stocks -->
        <div style="background-color: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.25); border-radius: 20px; padding: 2rem; box-shadow: 0 4px 14px rgba(60, 45, 25, 0.04);">
            <h2 class="font-editorial" style="font-size: 1.25rem; font-weight: 800; color: #1E1D1B; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(139, 133, 124, 0.1);">
                2. Tarification &amp; Inventaire
            </h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Prix Numérique PDF (FCFA) *
                    </label>
                    <input type="number" name="price_pdf" value="{{ old('price_pdf', 0) }}" min="0" required style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Prix Livre Papier (FCFA) *
                    </label>
                    <input type="number" name="price_physical" value="{{ old('price_physical', 0) }}" min="0" required style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Prix Livre Audio MP3 (FCFA)
                    </label>
                    <input type="number" name="price_audio" value="{{ old('price_audio', 0) }}" min="0" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Durée Audio (ex: 4h 30m)
                    </label>
                    <input type="text" name="audio_duration" value="{{ old('audio_duration') }}" placeholder="Ex: 5h 15m" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Stock Physique Actuel *
                    </label>
                    <input type="number" name="stock_physical" value="{{ old('stock_physical', 50) }}" min="0" required style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Poids en grammes (Livraison) *
                    </label>
                    <input type="number" name="weight_grams" value="{{ old('weight_grams', 350) }}" min="50" required style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>
            </div>

            <!-- Options booléennes -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; padding-top: 1.25rem; border-top: 1px solid rgba(139, 133, 124, 0.1); font-size: 0.82rem; font-weight: 700; color: #1E1D1B;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_pdf_available" value="1" {{ old('is_pdf_available', true) ? 'checked' : '' }} style="accent-color: #D9AE5C; width: 16px; height: 16px;">
                    <span>Activer la vente PDF</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_physical_available" value="1" {{ old('is_physical_available', true) ? 'checked' : '' }} style="accent-color: #D9AE5C; width: 16px; height: 16px;">
                    <span>Activer la vente Papier</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_audio_available" value="1" {{ old('is_audio_available') ? 'checked' : '' }} style="accent-color: #6C3483; width: 16px; height: 16px;">
                    <span>Activer la vente Livre Audio</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} style="accent-color: #D9AE5C; width: 16px; height: 16px;">
                    <span>Mettre en avant (Bestseller)</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="accent-color: #D9AE5C; width: 16px; height: 16px;">
                    <span>Visible sur le catalogue</span>
                </label>
            </div>
        </div>

        <!-- 3. Fichiers & Médias -->
        <div style="background-color: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.25); border-radius: 20px; padding: 2rem; box-shadow: 0 4px 14px rgba(60, 45, 25, 0.04);">
            <h2 class="font-editorial" style="font-size: 1.25rem; font-weight: 800; color: #1E1D1B; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(139, 133, 124, 0.1);">
                3. Couverture, Livres &amp; Fichiers Audio
            </h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Image de Couverture (JPG, PNG, WebP)
                    </label>
                    <input type="file" name="cover_file" accept="image/*" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B;">
                    @error('cover_file') <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Fichier Manuscrit PDF (Privé &amp; Sécurisé)
                    </label>
                    <input type="file" name="pdf_file" accept="application/pdf" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B;">
                    @error('pdf_file') <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Fichier Audio Intégral MP3 (Privé &amp; Sécurisé)
                    </label>
                    <input type="file" name="audio_file" accept="audio/*" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B;">
                    @error('audio_file') <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Extrait Audio Spécimen MP3 (Public pour écoute)
                    </label>
                    <input type="file" name="audio_sample_file" accept="audio/*" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B;">
                    @error('audio_sample_file') <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 1rem; padding-top: 1rem;">
            <a href="{{ route('admin.books.index') }}" class="admin-btn-secondary">
                Annuler
            </a>
            <button type="submit" class="admin-btn-primary">
                Enregistrer l'ouvrage
            </button>
        </div>

    </form>
</div>
@endsection
