@extends('layouts.app')
@section('page-title')
    Editar Órgão
@endsection

@section('header-content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">Editar Órgão</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Editar Órgão</li>
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
                        <h3>Editar Órgão</h3>
                    </div>
                    <div class="card-body">
                        <form id="user_form" action="{{ route('orgaos.orgao_update', [ 'id' => $orgao->id ]) }}" method="POST">
                            @csrf
                            @include('orgaos.orgao_edit_form')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

