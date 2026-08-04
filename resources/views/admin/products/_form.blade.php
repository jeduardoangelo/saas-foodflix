@push('styles')
<style>
    .form-card { padding: 27px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .field.full { grid-column: 1 / -1; }
    .field label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 650; }
    .required { color: var(--danger); }
    .field input, .field select, .field textarea { width: 100%; border: 1px solid transparent; border-radius: 8px; outline: 0; background: var(--input); color: var(--text); font-size: 14px; }
    .field input, .field select { height: 47px; padding: 0 13px; }
    .field textarea { min-height: 105px; padding: 13px; resize: vertical; }
    .field input[type="file"] { height: auto; padding: 12px; }
    .field input:focus, .field select:focus, .field textarea:focus { border-color: rgba(240,78,35,.55); background: white; box-shadow: 0 0 0 3px rgba(240,78,35,.09); }
    .field-error { margin: 6px 1px 0; color: var(--danger); font-size: 12px; }
    .field-help { margin: 6px 1px 0; color: var(--muted); font-size: 11px; }
    .active-control { min-height: 47px; display: flex; align-items: center; gap: 10px; }
    .active-control input { width: 18px; height: 18px; accent-color: var(--primary); }
    .active-control label { margin: 0; }
    .current-image { width: 88px; height: 88px; margin-top: 10px; overflow: hidden; border-radius: 9px; background: var(--input); }
    .current-image img { width: 100%; height: 100%; object-fit: cover; }
    .form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 27px; padding-top: 22px; border-top: 1px solid var(--border); }
    @media (max-width: 720px) { .form-grid { grid-template-columns: 1fr; } .field.full { grid-column: auto; } }
</style>
@endpush

@if ($errors->any())
    <div class="alert alert-error" role="alert">Revise os campos destacados antes de salvar o produto.</div>
@endif

<form class="card form-card" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <div class="field">
            <label for="name">Nome <span class="required">*</span></label>
            <input id="name" name="name" type="text" value="{{ old('name', isset($product) ? $product->name : '') }}" maxlength="255" required autofocus>
            @error('name') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="category_id">Categoria <span class="required">*</span></label>
            <select id="category_id" name="category_id" required>
                <option value="">Selecione uma categoria</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @if ((string) old('category_id', isset($product) ? $product->category_id : '') === (string) $category->id) selected @endif>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field full">
            <label for="description">Descrição</label>
            <textarea id="description" name="description">{{ old('description', isset($product) ? $product->description : '') }}</textarea>
            @error('description') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field full">
            <label for="ingredients">Ingredientes</label>
            <textarea id="ingredients" name="ingredients">{{ old('ingredients', isset($product) ? $product->ingredients : '') }}</textarea>
            @error('ingredients') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="price">Preço <span class="required">*</span></label>
            <input id="price" name="price" type="number" value="{{ old('price', isset($product) ? $product->price : '') }}" min="0.01" max="99999999.99" step="0.01" inputmode="decimal" required>
            @error('price') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label>Status</label>
            <div class="active-control">
                <input name="active" type="hidden" value="0">
                <input id="active" name="active" type="checkbox" value="1" @if (old('active', isset($product) ? $product->active : true)) checked @endif>
                <label for="active">Produto ativo no catálogo</label>
            </div>
            @error('active') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field full">
            <label for="image">Imagem principal</label>
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
            <p class="field-help">JPG, PNG ou WEBP, com no máximo 2 MB.</p>
            @error('image') <p class="field-error">{{ $message }}</p> @enderror
            @if (isset($product) && $product->image_url)
                <div class="current-image"><img src="{{ asset('storage/'.$product->image_url) }}" alt="Imagem atual de {{ $product->name }}"></div>
            @endif
        </div>
    </div>

    <div class="form-actions">
        <a class="button button-secondary" href="{{ route('admin.products.index') }}">Cancelar</a>
        <button class="button button-primary" type="submit">{{ $submitLabel }}</button>
    </div>
</form>
