@php
    $languages = config('audio.languages');
@endphp

<section class="card" id="parties-audio" style="margin-top: 1.5rem;">
    <div class="card-header">
        <div>
            <h2 class="form-section-title">Parties audio</h2>
            <p class="form-section-help">Découpez le livre en parties, puis importez une piste par langue. Les lecteurs s'abonnent partie par partie et écoutent en streaming depuis leur bibliothèque.</p>
        </div>
    </div>

    <div class="card-body stack">
        @forelse($book->parts as $part)
            @php $missing = array_diff_key($languages, $part->availableLanguages()); @endphp
            <article class="card" style="box-shadow: none;">
                <div class="card-body stack" style="gap: .9rem;">
                    <form action="{{ route('admin.parts.update', $part) }}" method="POST" class="form-grid form-grid-3" style="align-items: end;">
                        @csrf
                        @method('PUT')
                        <div class="field">
                            <label class="label" for="part_position_{{ $part->id }}">N°</label>
                            <input type="number" id="part_position_{{ $part->id }}" name="position" value="{{ $part->position }}" min="1" required class="input">
                        </div>
                        <div class="field" style="grid-column: span 2;">
                            <label class="label" for="part_title_{{ $part->id }}">Titre de la partie</label>
                            <input type="text" id="part_title_{{ $part->id }}" name="title" value="{{ $part->title }}" maxlength="255" required class="input">
                        </div>
                        <div class="field" style="grid-column: 1 / -1;">
                            <label class="label" for="part_summary_{{ $part->id }}">Résumé (facultatif)</label>
                            <textarea id="part_summary_{{ $part->id }}" name="summary" rows="2" maxlength="2000" class="input">{{ $part->summary }}</textarea>
                        </div>
                        <div class="cell-actions" style="grid-column: 1 / -1;">
                            <button type="submit" class="btn btn-secondary btn-sm">Enregistrer la partie</button>
                        </div>
                    </form>

                    <div class="badges">
                        @foreach($part->tracks->sortBy(fn ($track) => array_search($track->language, array_keys($languages))) as $track)
                            <span class="badge badge-success" style="gap: .35rem;">
                                {{ $track->languageLabel() }}
                                <form action="{{ route('admin.tracks.destroy', $track) }}" method="POST" class="inline-form" data-confirm="Supprimer la piste {{ $track->languageLabel() }} de la {{ $part->label() }} ?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm btn-icon" aria-label="Supprimer la piste {{ $track->languageLabel() }}"><x-admin.icon name="trash" /></button>
                                </form>
                            </span>
                        @endforeach
                        @foreach($missing as $label)
                            <span class="badge">{{ $label }} · à importer</span>
                        @endforeach
                    </div>

                    <form action="{{ route('admin.parts.tracks.store', $part) }}" method="POST" enctype="multipart/form-data" class="form-grid form-grid-3" style="align-items: end;">
                        @csrf
                        <div class="field">
                            <label class="label" for="track_language_{{ $part->id }}">Langue</label>
                            <select id="track_language_{{ $part->id }}" name="language" class="input" required>
                                @foreach($languages as $code => $label)
                                    <option value="{{ $code }}" @selected(array_key_first($missing) === $code)>{{ $label }}{{ isset($missing[$code]) ? '' : ' (remplacer)' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label class="label" for="track_file_{{ $part->id }}">Fichier audio</label>
                            <input type="file" id="track_file_{{ $part->id }}" name="audio_file" accept="audio/*" required class="input">
                        </div>
                        <div class="cell-actions">
                            <button type="submit" class="btn btn-primary btn-sm">Importer la piste</button>
                        </div>
                    </form>

                    <form action="{{ route('admin.parts.destroy', $part) }}" method="POST" class="inline-form" data-confirm="Supprimer la {{ $part->label() }}, ses pistes et les abonnements qui y donnent accès ?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <x-admin.icon name="trash" />
                            <span>Supprimer la partie</span>
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div class="empty">
                <p class="empty-title">Aucune partie</p>
                <p>Ajoutez la première partie du livre pour proposer l'écoute par abonnement.</p>
            </div>
        @endforelse

        <form action="{{ route('admin.books.parts.store', $book) }}" method="POST" class="form-grid form-grid-3" style="align-items: end;">
            @csrf
            <div class="field">
                <label class="label" for="new_part_position">N°</label>
                <input type="number" id="new_part_position" name="position" value="{{ ($book->parts->max('position') ?? 0) + 1 }}" min="1" required class="input @error('position', 'part') is-invalid @enderror">
                @error('position', 'part')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label class="label" for="new_part_title">Titre de la nouvelle partie</label>
                <input type="text" id="new_part_title" name="title" maxlength="255" required class="input @error('title', 'part') is-invalid @enderror" placeholder="Ex : Chapitres 1 à 3">
                @error('title', 'part')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="cell-actions">
                <button type="submit" class="btn btn-primary btn-sm">
                    <x-admin.icon name="plus" />
                    <span>Ajouter la partie</span>
                </button>
            </div>
        </form>
        @error('audio_file', 'track')<p class="error">{{ $message }}</p>@enderror
        @error('language', 'track')<p class="error">{{ $message }}</p>@enderror
    </div>
</section>
