@php
    $isNew = ! $article->exists;
    $dateValue = fn (string $field): ?string => old($field, $article->{$field}?->format('Y-m-d\TH:i'));
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
                    <h2 class="form-section-title">Contenu</h2>
                    <p class="form-section-help">Texte affiché sur la page Actualités du site.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="field col-span-full">
                        <label class="label" for="title">Titre <span class="req">*</span></label>
                        <input type="text" id="title" name="title" value="{{ old('title', $article->title) }}" required maxlength="255" class="input @error('title') is-invalid @enderror">
                        @error('title')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="type">Type <span class="req">*</span></label>
                        <select id="type" name="type" required class="select @error('type') is-invalid @enderror">
                            @foreach(\App\Enums\NewsType::cases() as $type)
                                <option value="{{ $type->value }}" @selected(old('type', $article->type?->value) === $type->value)>{{ $type->label() }}</option>
                            @endforeach
                        </select>
                        @error('type')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="book_id">Livre lié</label>
                        <select id="book_id" name="book_id" class="select @error('book_id') is-invalid @enderror">
                            <option value="">Aucun</option>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}" @selected(old('book_id', $article->book_id) == $book->id)>{{ $book->title }} — {{ $book->author }}</option>
                            @endforeach
                        </select>
                        <p class="help">Utile pour une sortie de livre : un lien vers sa fiche est ajouté.</p>
                        @error('book_id')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field col-span-full">
                        <label class="label" for="excerpt">Extrait <span class="req">*</span></label>
                        <textarea id="excerpt" name="excerpt" rows="2" required maxlength="500" class="textarea @error('excerpt') is-invalid @enderror">{{ old('excerpt', $article->excerpt) }}</textarea>
                        <p class="help">Résumé de 500 caractères max., repris dans les listes et les aperçus de partage.</p>
                        @error('excerpt')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field col-span-full">
                        <label class="label" for="content">Contenu <span class="req">*</span></label>
                        <textarea id="content" name="content" rows="12" required class="textarea @error('content') is-invalid @enderror">{{ old('content', $article->content) }}</textarea>
                        <p class="help">Séparez les paragraphes par une ligne vide.</p>
                        @error('content')<p class="error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="form-section-title">Événement</h2>
                    <p class="form-section-help">Facultatif : renseignez la date et le lieu pour un événement ou une conférence.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="field">
                        <label class="label" for="event_date">Date et heure</label>
                        <input type="datetime-local" id="event_date" name="event_date" value="{{ $dateValue('event_date') }}" class="input @error('event_date') is-invalid @enderror">
                        @error('event_date')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="location">Lieu</label>
                        <input type="text" id="location" name="location" value="{{ old('location', $article->location) }}" maxlength="255" class="input @error('location') is-invalid @enderror" placeholder="Ex : Abidjan, Plateau">
                        @error('location')<p class="error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-header"><h2 class="form-section-title">Image de couverture</h2></div>
            <div class="card-body">
                <div class="field">
                    @if($article->cover_image)
                        <div class="file-current"><img src="{{ asset($article->cover_image) }}" alt=""><span>Image actuelle</span></div>
                    @endif
                    <input type="file" id="cover_file" name="cover_file" accept="image/jpeg,image/png,image/webp" class="input @error('cover_file') is-invalid @enderror" aria-label="Image de couverture">
                    <p class="help">JPG, PNG ou WebP · 4 Mo max. Sans image, la couverture du livre lié est utilisée.</p>
                    @error('cover_file')<p class="error">{{ $message }}</p>@enderror
                    @if($article->cover_image)
                        <label class="check" style="margin-top: .5rem;">
                            <input type="checkbox" name="remove_cover" value="1">
                            <span><span class="check-title">Retirer l'image actuelle</span></span>
                        </label>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <aside class="stack">
        <section class="card">
            <div class="card-header"><h2 class="form-section-title">Publication</h2></div>
            <div class="card-body stack" style="gap: .6rem;">
                <label class="check">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $article->is_published))>
                    <span><span class="check-title">Publier sur le site</span><span class="check-help">Décochez pour garder un brouillon.</span></span>
                </label>
                <div class="field">
                    <label class="label" for="published_at">Date de publication</label>
                    <input type="datetime-local" id="published_at" name="published_at" value="{{ $dateValue('published_at') }}" class="input @error('published_at') is-invalid @enderror">
                    <p class="help">Vide : publiée immédiatement. Une date future programme la publication.</p>
                    @error('published_at')<p class="error">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <div class="stack" style="gap: .5rem;">
            <button type="submit" class="btn btn-primary btn-block">{{ $isNew ? 'Créer l\'actualité' : 'Enregistrer les modifications' }}</button>
            @if($article->exists && $article->isVisible())
                <a href="{{ route('news.show', $article->slug) }}" class="btn btn-secondary btn-block" target="_blank" rel="noopener">
                    <x-admin.icon name="external" />
                    <span>Voir sur le site</span>
                </a>
            @endif
            <a href="{{ route('admin.news.index') }}" class="btn btn-secondary btn-block">Annuler</a>
        </div>
    </aside>
</div>
