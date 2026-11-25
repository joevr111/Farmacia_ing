<div class="row">
    <div class="col-12 col-md-6 mb-3">
        <label for="name" class="form-label">Nombre del medicamento</label>
        <input type="text" name="name" id="name" maxlength="120"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $medicine->name ?? '') }}">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label for="category_id" class="form-label">Categoría</label>
        <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror">
            <option value="">-- Seleccione --</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $medicine->category_id ?? '') == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label for="price" class="form-label">Precio (S/)</label>
        <input type="number" name="price" id="price" step="0.01" min="0"
               class="form-control @error('price') is-invalid @enderror"
               value="{{ old('price', $medicine->price ?? '') }}">
        @error('price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label for="stock" class="form-label">Stock</label>
        <input type="number" name="stock" id="stock" step="1" min="0"
               class="form-control @error('stock') is-invalid @enderror"
               value="{{ old('stock', $medicine->stock ?? 0) }}">
        @error('stock')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 mb-3">
        <label for="image" class="form-label">
            Imagen <small class="text-muted">(opcional, jpg/png/webp, máx. 2 MB)</small>
        </label>
        <input type="file" name="image" id="image" accept="image/*"
               class="form-control @error('image') is-invalid @enderror">
        @error('image')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        {{-- Al editar, muestra la imagen actual (funciona gracias a storage:link) --}}
        @if (!empty($medicine?->image))
            <div class="mt-2">
                <small class="text-muted d-block mb-1">Imagen actual:</small>
                <img src="{{ asset('storage/' . $medicine->image) }}" alt="{{ $medicine->name }}"
                     class="img-thumbnail" style="max-height: 120px">
            </div>
        @endif
    </div>
</div>

<button type="submit" class="btn btn-primary text-white">Guardar</button>
<a href="{{ route('medicines.index') }}" class="btn btn-secondary">Cancelar</a>