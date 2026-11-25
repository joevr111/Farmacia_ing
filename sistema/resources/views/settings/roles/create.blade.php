@extends('template.administrador')

@section('title', 'Roles')
@section('name_section', 'Roles')
@section('description', 'Nuevo registro de roles')
@section('item_section', 'Nuevo')
@section('route_section', route('roles.index'))
@section('section', 'Roles')
@section('module', 'Configuraciones')

@section('content')
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
    <div class="card-body">
        <form action="{{ route('roles.store') }}" method="POST">
            @csrf
            @include('settings.roles._form')
        </form>
    </div>
</div>
@endsection