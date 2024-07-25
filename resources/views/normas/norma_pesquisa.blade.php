@extends('layouts.app')
@section('page-title')
    Pesquisa de Normas
@endsection
@section('header-content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">Pesquisa de Normas</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Pesquisar normas</a></li>
            </ol>
        </div><!-- /.col -->
    </div>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-defalt">
                    <div class="card-header">
                        <h3>Pesquisar <b>NORMAS</b></h3>
                        <form action="{{ route('normas.norma_search') }}" method="GET">
                            @csrf
                            <div class="row">
                                <div class="col-2">
                                    <label class="section-form-label">Nome da norma</label>
                                    <input type="text" name="norma" class="section-form-input"
                                        value="{{ isset($_GET['norma']) ? $_GET['norma'] : '' }}">
                                </div>
                                <div class="col-2">
                                    <label class="section-form-label">Resumo</label>
                                    <input type="text" name="resumo" class="section-form-input">
                                </div>
                                <div class="col-2">
                                    <label class="section-form-label">Palavra chave</label>
                                    <input type="text" name="palavra_chave" class="section-form-input">
                                </div>
                                <div class="col-2">
                                    <label class="section-form-label">Órgão</label>
                                    <select name="orgao" class="section-form-select">
                                        <option value=""></option>
                                        @foreach ($orgao as $orgao_obj)
                                            <option value="{{ $orgao_obj->id }}">{{ $orgao_obj->orgao }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <label class="section-form-label">Tipo norma</label>
                                    <select name="tipo" class="section-form-select">
                                        <option value=""></option>
                                        @foreach ($tipo as $tipo_obj)
                                            <option value="{{ $tipo_obj->id }}">{{ $tipo_obj->tipo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <br><button type="submit" class="btn btn-primary"><i class="fas fa-search"></i>
                                        Pesquisar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <section class="content">
                            <div class="row">
                                <div class="col-12">
                                    <table id="example1" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Ord</th>
                                                <th>Norma</th>
                                                <th>Resumo</th>
                                                <th>Órgão</th>
                                                <th>**</th>
                                                <th>**</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($norma_pesquisa as $key => $norma)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>{{ $norma->descricao }}</td>
                                                    <td>{{ $norma->resumo }}</td>
                                                    <td>{{ $norma->orgao->orgao }}</td>
                                                    <td>
                                                        <a
                                                            href='javascript:abrirPagina("storage/normas/{{ $norma->anexo }}",600,600);'><button
                                                                class='btn btn-danger'>
                                                                <nobr><i class='fas fa-file-pdf'></i> Anexo</nobr>
                                                            </button></a>
                                                    </td>
                                                    <td>
                                                        <a
                                                            href="{{ route('normas.norma_edit', $norma->id) }}"><button
                                                                class='btn btn-success'>
                                                                <nobr><i class='fas fa-edit'></i> Editar</nobr>
                                                            </button></a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
@endsection
