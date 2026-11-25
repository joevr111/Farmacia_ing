@extends('template.administrador')

@section('title', 'Categorías')
@section('name_section', 'Categorías')
@section('description', 'Nuevo registro de categorías')
@section('item_section', 'Nuevo')
@section('route_section', route('categories.index'))
@section('section', 'Categorías')
@section('module', 'Configuraciones')

@section('content')
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
    <div class="card-body">
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            @include('settings.categories._form')
        </form>
    </div>
</div>
@endsection