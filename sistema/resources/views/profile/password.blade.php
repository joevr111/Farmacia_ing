@extends('template.administrador')

@section('title', 'Cambiar Contraseña')
@section('name_section', 'Cambiar Contraseña')
@section('description', 'Actualiza la contraseña de tu cuenta')
@section('item_section', 'Cuenta')
@section('route_section', route('profile.password.edit'))
@section('section', 'Contraseña')
@section('module', 'Editar')

@section('content')
@include('partials.alerts')

<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
    <div class="card-body">
        <form action="{{ route('profile.password.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Contraseña actual</label>
                        <input type="password" name="current_password" id="current_password"
                               autocomplete="current-password"
                               class="form-control @error('current_password') is-invalid @enderror">
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva contraseña</label>
                        <input type="password" name="password" id="password"
                               autocomplete="new-password"
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Mínimo 8 caracteres.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmar nueva contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               autocomplete="new-password" class="form-control">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary text-white">Actualizar contraseña</button>
            <a href="{{ route('dashboard.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection