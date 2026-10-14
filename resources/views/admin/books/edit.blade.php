@extends('layouts.admin')
@section('title', 'Modifier : ' . $book->title . ' — Administration')

@section('content')
<div style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">

    <!-- En-tête -->
    <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 1.25rem; border-bottom: 1px solid rgba(139, 133, 124, 0.1);">
        <div>
            <nav class="breadcrumb" style="margin-bottom: 0.5rem;" aria-label="Fil d'Ariane">
                <ol class="breadcrumb-list">
                    <li class="breadcrumb-item"><a href="{{ route('admin.books.index') }}">Catalogue</a></li>
                    <li class="breadcrumb-separator">›</li>
                    <li class="breadcrumb-item active" aria-current="page">Modifier</li>
                </ol>
            </nav>
            <h1 class="hero-title font-editorial" style="font-size: 2.2rem; line-height: 1.15; margin: 0;">
                Modifier : {{ $book->title }}
            </h1>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <a href="{{ route('catalog.show', $book->slug) }}" target="_blank" class="admin-btn-secondary" title="Voir sur le site public">
                <span>Voir le livre ↗</span>
            </a>
            <a href="{{ route('admin.books.index') }}" class="admin-btn-secondary">
                &larr; Retour
            </a>
        </div>
    </div>

    <!-- Formulaire d'édition -->
    <form action="{{ route('admin.books.update', $book) }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.75rem;">
        @csrf
        @method('PUT')

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
                    <input type="text" name="title" value="{{ old('title', $book->title) }}" required style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                    @error('title') <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Auteur *
                    </label>
                    <input type="text" name="author" value="{{ old('author', $book->author) }}" required style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                    @error('author') <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Collection / Catégorie *
                    </label>
                    <select name="category_id" required style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $book->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Maison d'édition
                    </label>
                    <input type="text" name="publisher" value="{{ old('publisher', $book->publisher) }}" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Langue *
                    </label>
                    <input type="text" name="language" value="{{ old('language', $book->language) }}" required style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Nombre de pages
                    </label>
                    <input type="number" name="pages_count" value="{{ old('pages_count', $book->pages_count) }}" min="1" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Année d'édition
                    </label>
                    <input type="number" name="published_year" value="{{ old('published_year', $book->published_year) }}" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div style="grid-column: 1 / -1;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Code ISBN
                    </label>
                    <input type="text" name="isbn" value="{{ old('isbn', $book->isbn) }}" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div style="grid-column: 1 / -1;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Description / Résumé complet *
                    </label>
                    <textarea name="description" rows="5" required style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none; line-height: 1.5;">{{ old('description', $book->description) }}</textarea>
                    @error('description') <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div style="grid-column: 1 / -1;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Extrait pour feuilletage en ligne
                    </label>
                    <textarea name="excerpt" rows="3" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none; line-height: 1.5;">{{ old('excerpt', $book->excerpt) }}</textarea>
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
                    <input type="number" name="price_pdf" value="{{ old('price_pdf', $book->price_pdf) }}" min="0" required style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Prix Livre Papier (FCFA) *
                    </label>
                    <input type="number" name="price_physical" value="{{ old('price_physical', $book->price_physical) }}" min="0" required style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Prix Livre Audio MP3 (FCFA)
                    </label>
                    <input type="number" name="price_audio" value="{{ old('price_audio', $book->price_audio) }}" min="0" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Durée Audio (ex: 4h 30m)
                    </label>
                    <input type="text" name="audio_duration" value="{{ old('audio_duration', $book->audio_duration) }}" placeholder="Ex: 5h 15m" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Stock Physique Actuel *
                    </label>
                    <input type="number" name="stock_physical" value="{{ old('stock_physical', $book->stock_physical) }}" min="0" required style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Poids en grammes (Livraison) *
                    </label>
                    <input type="number" name="weight_grams" value="{{ old('weight_grams', $book->weight_grams) }}" min="50" required style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1E1D1B; outline: none;">
                </div>
            </div>

            <!-- Options booléennes -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; padding-top: 1.25rem; border-top: 1px solid rgba(139, 133, 124, 0.1); font-size: 0.82rem; font-weight: 700; color: #1E1D1B;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_pdf_available" value="1" {{ old('is_pdf_available', $book->is_pdf_available) ? 'checked' : '' }} style="accent-color: #D9AE5C; width: 16px; height: 16px;">
                    <span>Vente PDF activée</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_physical_available" value="1" {{ old('is_physical_available', $book->is_physical_available) ? 'checked' : '' }} style="accent-color: #D9AE5C; width: 16px; height: 16px;">
                    <span>Vente Papier activée</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_audio_available" value="1" {{ old('is_audio_available', $book->is_audio_available) ? 'checked' : '' }} style="accent-color: #6C3483; width: 16px; height: 16px;">
                    <span>Vente Livre Audio activée</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $book->is_featured) ? 'checked' : '' }} style="accent-color: #D9AE5C; width: 16px; height: 16px;">
                    <span>Mettre en avant (Bestseller)</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $book->is_active) ? 'checked' : '' }} style="accent-color: #D9AE5C; width: 16px; height: 16px;">
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
                        Remplacer l'Image de Couverture
                    </label>
                    @if($book->cover_image)
                        <div style="display: flex; align-items: center; gap: 0.85rem; padding: 0.5rem 0.85rem; background: #FAF5ED; border-radius: 12px; border: 1px solid rgba(217, 174, 92, 0.2); margin-bottom: 0.65rem;">
                            <img src="{{ asset($book->cover_image) }}" alt="Couverture" style="width: 38px; height: 56px; object-fit: cover; border-radius: 6px;">
                            <span style="font-size: 0.75rem; color: #8B857C;">Couverture actuellement active</span>
                        </div>
                    @endif
                    <input type="file" name="cover_file" accept="image/*" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B;">
                    @error('cover_file') <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Remplacer le Manuscrit PDF
                    </label>
                    @if($book->pdf_path)
                        <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.85rem; background: rgba(47, 111, 78, 0.1); border-radius: 12px; border: 1px solid rgba(47, 111, 78, 0.25); color: #2F6F4E; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.65rem;">
                            <span>✓ Manuscrit configuré ({{ $book->pdf_path }})</span>
                        </div>
                    @endif
                    <input type="file" name="pdf_file" accept="application/pdf" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B;">
                    @error('pdf_file') <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Fichier Audio Intégral MP3
                    </label>
                    @if($book->audio_path)
                        <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.85rem; background: rgba(108, 52, 131, 0.1); border-radius: 12px; border: 1px solid rgba(108, 52, 131, 0.25); color: #6C3483; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.65rem;">
                            <span>✓ Audio configuré ({{ $book->audio_path }})</span>
                        </div>
                    @endif
                    <input type="file" name="audio_file" accept="audio/*" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B;">
                    @error('audio_file') <p style="color: #BE123C; font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.4rem;">
                        Extrait Audio Spécimen MP3
                    </label>
                    @if($book->audio_sample_path)
                        <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.85rem; background: rgba(108, 52, 131, 0.1); border-radius: 12px; border: 1px solid rgba(108, 52, 131, 0.25); color: #6C3483; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.65rem;">
                            <span>✓ Extrait configuré ({{ $book->audio_sample_path }})</span>
                        </div>
                    @endif
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
                Enregistrer les modifications
            </button>
        </div>

    </form>
</div>
@endsection
