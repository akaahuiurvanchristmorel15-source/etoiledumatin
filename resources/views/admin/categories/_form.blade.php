<section class="card" style="max-width: 720px;">
    <div class="card-body">
        <div class="form-grid">
            <div class="field col-span-full">
                <label class="label" for="name">Nom <span class="req">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" required maxlength="120" class="input @error('name') is-invalid @enderror">
                @error('name')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label class="label" for="parent_id">Catégorie parente</label>
                <select id="parent_id" name="parent_id" class="select @error('parent_id') is-invalid @enderror">
                    <option value="">Aucune</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>
                    @endforeach
                </select>
                @error('parent_id')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label class="label" for="display_order">Ordre d'affichage</label>
                <input type="number" id="display_order" name="display_order" value="{{ old('display_order', $category->display_order ?? 0) }}" min="0" max="999" class="input @error('display_order') is-invalid @enderror">
                <p class="help">Les plus petits nombres apparaissent en premier.</p>
                @error('display_order')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="field col-span-full">
                <label class="label" for="description">Description</label>
                <textarea id="description" name="description" rows="3" maxlength="1000" class="textarea @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
                @error('description')<p class="error">{{ $message }}</p>@enderror
            </div>

            <label class="check col-span-full">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))>
                <span><span class="check-title">Catégorie active</span><span class="check-help">Visible dans les filtres de la boutique.</span></span>
            </label>
        </div>
    </div>
    <div class="card-footer">
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">{{ $category->exists ? 'Enregistrer' : 'Créer la catégorie' }}</button>
    </div>
</section>
