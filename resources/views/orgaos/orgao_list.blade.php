@extends('layouts.app')
@section('page-title')
    Lista de Órgãos
@endsection
@section('header-content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">Lista de Órgãos</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Lista de Órgãos</li>
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
                        <h3>Lista de <b>ÓRGÃOS</b> cadastrados</h3>
                    </div>
                    <div class="card-body">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Ord</th>
                                    <th>Nome do Órgão</th>
                                    <th>**</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orgao as $orgao_obj)
                                    <tr>
                                        <td>{{ $orgao_obj->id }}</td>
                                        <td>{{ $orgao_obj->orgao }}</td>
                                        <td>
                                            <nobr>
                                                <a
                                                    href="{{ route('normas.orgao_edit', $orgao_obj->id) }}"><button
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
