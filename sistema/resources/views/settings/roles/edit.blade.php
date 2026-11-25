@extends('template.administrador')

@section('title', 'Roles')
@section('name_section', 'Roles')
@section('description', 'Actualizar registro de roles')
@section('item_section', 'Editar')
@section('route_section', route('roles.index'))
@section('section', 'Roles')
@section('module', 'Configuraciones')

@section('content')
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
    <div class="card-body">
        <form action="{{ route('roles.update', $role) }}" method="POST">
            @csrf
            @method('PUT')
            @include('settings.roles._form')
        </form>
    </div>
</div>
@endsection