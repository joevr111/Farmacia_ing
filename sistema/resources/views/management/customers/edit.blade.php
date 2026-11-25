@extends('template.administrador')

@section('title', 'Clientes')
@section('name_section', 'Clientes')
@section('description', 'Editar registro de cliente')
@section('item_section', 'Editar')
@section('route_section', route('customers.index'))
@section('section', 'Clientes')
@section('module', 'Gestión')

@section('content')
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
    <div class="card-body">
        <form action="{{ route('customers.update', $customer) }}" method="POST">
            @csrf
            @method('PUT')
            @include('management.customers._form')
        </form>
    </div>
</div>
@endsection