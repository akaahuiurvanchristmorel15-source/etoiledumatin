@php
    $isNew = ! $book->exists;
    $checked = fn (string $field, bool $default = false): bool => (bool) old($field, $isNew ? $default : $book->{$field});
@endphp

@if($errors->any())
    <div class="alert alert-error" role="alert">
        <x-admin.icon name="alert" />
        <div>
            Le formulaire contient {{ $errors->count() }} erreur(s) :
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="grid-aside">
    <div class="stack">
        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="form-section-title">Informations</h2>
                    <p class="form-section-help">Notice éditoriale affichée sur la fiche du livre.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="field col-span-full">
                        <label class="label" for="title">Titre <span class="req">*</span></label>
                        <input type="text" id="title" name="title" value="{{ old('title', $book->title) }}" required maxlength="255" class="input @error('title') is-invalid @enderror">
                        @error('title')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="author">Auteur <span class="req">*</span></label>
                        <input type="text" id="author" name="author" value="{{ old('author', $book->author) }}" required maxlength="255" class="input @error('author') is-invalid @enderror">
                        @error('author')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="category_id">Catégorie <span class="req">*</span></label>
                        <select id="category_id" name="category_id" required class="select @error('category_id') is-invalid @enderror">
                            <option value="">Choisir…</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $book->category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="error">{{ $message }}</p>@enderror
                        @if($categories->isEmpty())
                            <p class="help">Aucune catégorie : <a class="link" href="{{ route('admin.categories.create') }}">créez-en une</a>.</p>
                        @endif
                    </div>

                    <div class="field">
                        <label class="label" for="publisher">Éditeur</label>
                        <input type="text" id="publisher" name="publisher" value="{{ old('publisher', $isNew ? "Éditions NSOROMMA" : $book->publisher) }}" maxlength="255" class="input">
                    </div>

                    <div class="field">
                        <label class="label" for="language">Langue <span class="req">*</span></label>
                        <input type="text" id="language" name="language" value="{{ old('language', $book->language ?? 'Français') }}" required maxlength="50" class="input @error('language') is-invalid @enderror">
                        @error('language')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="pages_count">Nombre de pages</label>
                        <input type="number" id="pages_count" name="pages_count" value="{{ old('pages_count', $book->pages_count) }}" min="1" class="input @error('pages_count') is-invalid @enderror">
                        @error('pages_count')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="published_year">Année de publication</label>
                        <input type="number" id="published_year" name="published_year" value="{{ old('published_year', $isNew ? date('Y') : $book->published_year) }}" min="1800" max="{{ date('Y') + 1 }}" class="input @error('published_year') is-invalid @enderror">
                        @error('published_year')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field col-span-full">
                        <label class="label" for="isbn">ISBN</label>
                        <input type="text" id="isbn" name="isbn" value="{{ old('isbn', $book->isbn) }}" maxlength="50" class="input" placeholder="978-…">
                    </div>

                    <div class="field col-span-full">
                        <label class="label" for="description">Description <span class="req">*</span></label>
                        <textarea id="description" name="description" rows="6" required class="textarea @error('description') is-invalid @enderror">{{ old('description', $book->description) }}</textarea>
                        @error('description')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field col-span-full">
                        <label class="label" for="excerpt">Extrait</label>
                        <textarea id="excerpt" name="excerpt" rows="4" class="textarea">{{ old('excerpt', $book->excerpt) }}</textarea>
                        <p class="help">Utilisé pour l'aperçu gratuit quand aucun PDF n'est disponible.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="form-section-title">Prix & stock</h2>
                    <p class="form-section-help">Montants en francs CFA (FCFA).</p>
                </div>
            </div>
            <div class="card-body">
                <div class="form-grid form-grid-3">
                    <div class="field">
                        <label class="label" for="price_pdf">Prix PDF <span class="req">*</span></label>
                        <input type="number" id="price_pdf" name="price_pdf" value="{{ old('price_pdf', $book->price_pdf ?? 0) }}" min="0" required class="input @error('price_pdf') is-invalid @enderror">
                        @error('price_pdf')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label class="label" for="price_physical">Prix papier <span class="req">*</span></label>
                        <input type="number" id="price_physical" name="price_physical" value="{{ old('price_physical', $book->price_physical ?? 0) }}" min="0" required class="input @error('price_physical') is-invalid @enderror">
                        @error('price_physical')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label class="label" for="price_audio">Prix audio</label>
                        <input type="number" id="price_audio" name="price_audio" value="{{ old('price_audio', $book->price_audio ?? 0) }}" min="0" class="input @error('price_audio') is-invalid @enderror">
                        @error('price_audio')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label class="label" for="stock_physical">Stock papier <span class="req">*</span></label>
                        <input type="number" id="stock_physical" name="stock_physical" value="{{ old('stock_physical', $book->stock_physical ?? 0) }}" min="0" required class="input @error('stock_physical') is-invalid @enderror">
                        @error('stock_physical')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label class="label" for="weight_grams">Poids (g) <span class="req">*</span></label>
                        <input type="number" id="weight_grams" name="weight_grams" value="{{ old('weight_grams', $book->weight_grams ?? 350) }}" min="50" required class="input @error('weight_grams') is-invalid @enderror">
                        @error('weight_grams')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label class="label" for="audio_duration">Durée audio</label>
                        <input type="text" id="audio_duration" name="audio_duration" value="{{ old('audio_duration', $book->audio_duration) }}" maxlength="50" class="input" placeholder="Ex : 5h 15min">
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="form-section-title">Fichiers</h2>
                    <p class="form-section-help">Le PDF et l'audio intégral restent privés : ils ne sont livrés qu'aux acheteurs.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="field">
                        <label class="label" for="cover_file">Couverture</label>
                        @if($book->cover_image)
                            <div class="file-current"><img src="{{ asset($book->cover_image) }}" alt=""><span>Couverture actuelle</span></div>
                        @endif
                        <input type="file" id="cover_file" name="cover_file" accept="image/jpeg,image/png,image/webp" class="input @error('cover_file') is-invalid @enderror">
                        <p class="help">JPG, PNG ou WebP · 4 Mo max.</p>
                        @error('cover_file')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="pdf_file">Livre PDF</label>
                        @if($book->pdf_path)
                            <div class="file-current"><x-admin.icon name="check" /><span>{{ basename($book->pdf_path) }}</span></div>
                        @endif
                        <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf" class="input @error('pdf_file') is-invalid @enderror">
                        <p class="help">PDF · 50 Mo max.</p>
                        @error('pdf_file')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="audio_file">Livre audio intégral</label>
                        @if($book->audio_path)
                            <div class="file-current"><x-admin.icon name="check" /><span>{{ basename($book->audio_path) }}</span></div>
                        @endif
                        <input type="file" id="audio_file" name="audio_file" accept="audio/*" class="input @error('audio_file') is-invalid @enderror">
                        <p class="help">MP3, M4A, WAV… · 100 Mo max.</p>
                        @error('audio_file')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="audio_sample_file">Extrait audio (public)</label>
                        @if($book->audio_sample_path)
                            <div class="file-current"><x-admin.icon name="check" /><span>{{ basename($book->audio_sample_path) }}</span></div>
                        @endif
                        <input type="file" id="audio_sample_file" name="audio_sample_file" accept="audio/*" class="input @error('audio_sample_file') is-invalid @enderror">
                        <p class="help">20 Mo max.</p>
                        @error('audio_sample_file')<p class="error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>
    </div>

    <aside class="stack">
        <section class="card">
            <div class="card-header"><h2 class="form-section-title">Publication</h2></div>
            <div class="card-body stack" style="gap: .6rem;">
                <label class="check">
                    <input type="checkbox" name="is_active" value="1" @checked($checked('is_active', true))>
                    <span><span class="check-title">Visible dans la boutique</span><span class="check-help">Décochez pour masquer le livre.</span></span>
                </label>
                <label class="check">
                    <input type="checkbox" name="is_featured" value="1" @checked($checked('is_featured'))>
                    <span><span class="check-title">Mettre en avant</span><span class="check-help">Affiché parmi les coups de cœur.</span></span>
                </label>
            </div>
        </section>

        <section class="card">
            <div class="card-header"><h2 class="form-section-title">Formats en vente</h2></div>
            <div class="card-body stack" style="gap: .6rem;">
                <label class="check">
                    <input type="checkbox" name="is_pdf_available" value="1" @checked($checked('is_pdf_available', true))>
                    <span><span class="check-title">e-Book PDF</span><span class="check-help">Téléchargement avec filigrane.</span></span>
                </label>
                <label class="check">
                    <input type="checkbox" name="is_physical_available" value="1" @checked($checked('is_physical_available', true))>
                    <span><span class="check-title">Livre papier</span><span class="check-help">Expédié selon le stock.</span></span>
                </label>
                <label class="check">
                    <input type="checkbox" name="is_audio_available" value="1" @checked($checked('is_audio_available'))>
                    <span><span class="check-title">Livre audio</span><span class="check-help">Téléchargement MP3.</span></span>
                </label>
            </div>
        </section>

        <div class="stack" style="gap: .5rem;">
            <button type="submit" class="btn btn-primary btn-block">{{ $isNew ? 'Ajouter le livre' : 'Enregistrer les modifications' }}</button>
            <a href="{{ route('admin.books.index') }}" class="btn btn-secondary btn-block">Annuler</a>
        </div>
    </aside>
</div>
