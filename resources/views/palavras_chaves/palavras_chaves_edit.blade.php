@extends('layouts.app')
@section('page-title')
    Editar Palavra Chave
@endsection

@section('header-content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">Editar Palavra Chave</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Editar Palavra Chave</li>
            </ol>
        </div><!-- /.col -->
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-dark">
                    <div class="card-header">
                        <h3>Editar Palavra Chave</h3>
                    </div>
                    <div class="card-body">
                        <form id="user_form" action="{{ route('palavras_chaves.palavras_chaves_update', [ 'id' => $palavra_chave->id ]) }}" method="POST">
                            @csrf
                            @include('palavras_chaves.palavras_chaves_edit_form')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

