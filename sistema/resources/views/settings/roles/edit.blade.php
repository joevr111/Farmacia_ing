@extends('template.administrador')

@section('title','ROLES')
@section('name-section','ROLES')

@section('description','Actualizar registro de categoria')
@section('item-breadcrumb','Actualizar')
@section('section-breadcrumb','Categorias')
@section('module-breadcrumb','Configuraciones')

@section('content')
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
                
                <div class="card-body">
                    <form action="{{ route('roles.update' , $role->id) }}" method="POST">
                        @csrf
                        @method('put')
                        @include('settings.roles._form')
                    </form>
                </div>
            </div>

@endsection