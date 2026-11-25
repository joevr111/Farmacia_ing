@extends('template.administrador')

@section('title', 'Medicamentos')
@section('name_section', 'Medicamentos')
@section('description', 'Editar registro de medicamento')
@section('item_section', 'Editar')
@section('route_section', route('medicines.index'))
@section('section', 'Medicamentos')
@section('module', 'Gestión')

@section('content')
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
    <div class="card-body">
        <form action="{{ route('medicines.update', $medicine) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('management.medicines._form')
        </form>
    </div>
</div>
@endsection