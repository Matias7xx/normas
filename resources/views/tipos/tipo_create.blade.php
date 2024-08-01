@extends('layouts.app')
@section('page-title')
    Novo Tipo de Norma
@endsection
@section('header-content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">Novo Tipo de Norma</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Novo Tipo de Norma</li>
            </ol>
        </div><!-- /.col -->
    </div>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3>Criar um novo <b>TIPO</b></h3>
                    </div>
                    <div class="card-body">
                        <form id="user_form" action="{{ route('tipos.tipo_store') }}" method="POST">
                            @csrf
                            @include('tipos.tipo_create_form')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

