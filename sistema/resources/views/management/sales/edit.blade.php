@extends('template.administrador')

@section('title', 'Ventas')
@section('name_section', 'Ventas')
@section('description', 'Editar venta')
@section('item_section', 'Editar')
@section('route_section', route('sales.index'))
@section('section', 'Ventas')
@section('module', 'Gestión')

@section('content')
    @include('management.sales._form')
@endsection
