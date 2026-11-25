@extends('template.administrador')

@section('title', 'Usuarios')
@section('name_section', 'Usuarios')
@section('description', 'Nuevo registro de usuario')
@section('item_section', 'Nuevo')
@section('route_section', route('users.index'))
@section('section', 'Usuarios')
@section('module', 'Configuraciones')

@section('content')
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            @include('settings.users._form')
        </form>
    </div>
</div>
@endsection