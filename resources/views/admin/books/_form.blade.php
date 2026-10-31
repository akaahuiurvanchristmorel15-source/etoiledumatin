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
                        <p class="help" id="pages_count_help" aria-live="polite">Calculé automatiquement à l'import du PDF. À saisir seulement pour un livre sans PDF.</p>
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
                    <h2 class="form-section-title">Abonnement d'écoute audio</h2>
                    <p class="form-section-help">Prix d'une partie du livre pour chaque durée, en FCFA. Laissez vide une durée à ne pas proposer.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="form-grid form-grid-3">
                    @foreach(config('audio.plans') as $days => $planLabel)
                        <div class="field">
                            <label class="label" for="audio_plan_{{ $days }}">{{ $planLabel }}</label>
                            <input type="number" id="audio_plan_{{ $days }}" name="audio_plan_prices[{{ $days }}]" value="{{ old('audio_plan_prices.'.$days, ($book->audio_plan_prices[$days] ?? null) ?: null) }}" min="0" class="input @error('audio_plan_prices.'.$days) is-invalid @enderror">
                            @error('audio_plan_prices.'.$days)<p class="error">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="form-section-title">Fichiers</h2>
                    <p class="form-section-help">Le PDF reste privé : il n'est livré qu'aux acheteurs. Les pistes audio se gèrent partie par partie, plus bas.</p>
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
                    <span><span class="check-title">Écoute audio</span><span class="check-help">Streaming par abonnement, partie par partie.</span></span>
                </label>
            </div>
        </section>

        <div class="stack" style="gap: .5rem;">
            <button type="submit" class="btn btn-primary btn-block">{{ $isNew ? 'Ajouter le livre' : 'Enregistrer les modifications' }}</button>
            <a href="{{ route('admin.books.index') }}" class="btn btn-secondary btn-block">Annuler</a>
        </div>
    </aside>
</div>

@push('scripts')
    <script nonce="{{ Vite::cspNonce() }}">
        /**
         * Compte les pages du PDF dès sa sélection et remplit « Nombre de pages ».
         * Les PDF récents rangent leurs objets dans des flux compressés (/ObjStm) : on les décompresse avant de lire l'arbre des pages.
         */
        (() => {
            const inflate = async (bytes) => {
                const reader = new Blob([bytes]).stream().pipeThrough(new DecompressionStream('deflate')).getReader();
                const chunks = [];

                try {
                    for (let result = await reader.read(); ! result.done; result = await reader.read()) {
                        chunks.push(result.value);
                    }
                } catch {
                    // Octets parasites après la fin du flux : ce qui a déjà été décompressé reste exploitable
                }

                return new TextDecoder('latin1').decode(await new Blob(chunks).arrayBuffer());
            };

            const countPages = async (file) => {
                const bytes = new Uint8Array(await file.arrayBuffer());
                const raw = new TextDecoder('latin1').decode(bytes);
                const sources = [raw];

                for (const match of raw.matchAll(/stream\r?\n/g)) {
                    const dictionary = raw.slice(Math.max(0, match.index - 600), match.index);

                    if (! /\/ObjStm\b/.test(dictionary.slice(dictionary.lastIndexOf(' obj'))) || ! dictionary.includes('/FlateDecode')) {
                        continue;
                    }

                    const start = match.index + match[0].length;
                    const end = raw.indexOf('endstream', start);

                    if (end > start) {
                        sources.push(await inflate(bytes.subarray(start, end)));
                    }
                }

                const text = sources.join('\n');

                // Le nœud racine /Pages porte le total dans /Count ; à défaut, on compte les objets /Page
                const totals = [...text.matchAll(/<<(?:(?!<<|>>)[\s\S])*?\/Type\s*\/Pages\b(?:(?!<<|>>)[\s\S])*?>>/g)]
                    .map((dict) => Number(dict[0].match(/\/Count\s+(\d+)/)?.[1] ?? 0));

                return Math.max(0, ...totals) || text.match(/\/Type\s*\/Page(?![a-zA-Z])/g)?.length || null;
            };

            // Écoute déléguée : le formulaire peut être remplacé par une mise à jour sans rechargement
            document.addEventListener('change', async (event) => {
                const fileInput = event.target.closest('#pdf_file');
                const pagesInput = document.getElementById('pages_count');
                const help = document.getElementById('pages_count_help');
                const file = fileInput?.files[0];

                if (! file || ! pagesInput) {
                    return;
                }

                help.textContent = 'Comptage des pages…';

                const pages = await countPages(file).catch(() => null);

                if (pages) {
                    pagesInput.value = pages;
                    help.textContent = pages + ' pages détectées dans le PDF.';
                } else {
                    help.textContent = 'Nombre de pages non détecté : saisissez-le.';
                }
            });
        })();
    </script>
@endpush
