@extends('template.administrador')

@section('title', 'Mi Perfil')
@section('name_section', 'Mi Perfil')
@section('description', 'Actualiza tus datos personales')
@section('item_section', 'Cuenta')
@section('route_section', route('profile.edit'))
@section('section', 'Mi Perfil')
@section('module', 'Editar')

@section('content')
@include('partials.alerts')

<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
    <div class="card-body">
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text" name="name" id="name" maxlength="255"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name) }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" name="email" id="email" maxlength="255"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $user->email) }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Teléfono</label>
                    <input type="text" name="phone" id="phone" maxlength="15"
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $user->phone) }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Rol</label>
                    <input type="text" class="form-control" value="{{ $user->role->name }}" disabled>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">DNI</label>
                    <input type="text" class="form-control" value="{{ $user->dni }}" disabled>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Colegiatura</label>
                    <input type="text" class="form-control" value="{{ $user->tuition ?? '—' }}" disabled>
                </div>
            </div>

            <p class="text-secondary small">El rol, el DNI y la colegiatura solo los puede modificar un administrador desde el módulo Usuarios.</p>

            <button type="submit" class="btn btn-primary text-white">Guardar</button>
            <a href="{{ route('dashboard.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection