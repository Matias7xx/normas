@extends('layouts.app')
@section('page-title')
    Lista de Palavras Chave
@endsection
@section('header-content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">Lista de Palavras Chave</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Lista de Palavras Chave</li>
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
                        <h3>Lista de <b>PALAVRAS CHAVE</b> cadastradas</h3>
                    </div>
                    <div class="card-body">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Ord</th>
                                    <th>Id</th>
                                    <th>Nome do Órgão</th>
                                    <th>**</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($palavra_chave as $key => $palavra_chave_obj)
                                    <tr>
                                        <td>{{ ($key+1) }}</td>
                                        <td>{{ $palavra_chave_obj->id }}</td>
                                        <td>{{ $palavra_chave_obj->palavra_chave }}</td>
                                        <td>
                                            <nobr>
                                                <a
                                                    href="{{ route('palavras_chaves.palavras_chaves_edit', $palavra_chave_obj->id) }}"><button
                                                        type='button' class='btn btn-primary'>
                                                        <nobr><i class='fas fa-edit'></i> Editar</nobr>
                                                    </button></a>

                                            </nobr>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
