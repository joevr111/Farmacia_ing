 <div class="card bg-white rounded-0 border-0 shadow-sm border-5 border-top border-secondary">
                <div class="card-body">
                    <div class="mb-3">
                    <label for="name" class="form-label">Nombre de la categoria</label>
                    <input type="text" name="name" id="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name' , $category->name ?? '') }}">
                    @error('name')
                         <div class="invalid-feeback">{($message)}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Descripcion</label>
                    <textarea name="description" id="description" rows="3"
                            class="form-control @error('description') is-invalid @enderror">{{ old('name' , $category->name ?? '') }}</textarea>
                            @error('description')
                         <div class="invalid-feeback">{($message)}</div>
                    @enderror
                </div>
                
                <button type="submit" class="btn btn-primary text-white">Guardar</button>
                <a href="" class="btn btn-secondary">Cancelar</a>

                </div>
            </div>
