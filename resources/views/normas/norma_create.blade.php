@extends('layouts.app')
@section('page-title')
    Nova Norma
@endsection
@section('header-content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">Nova Norma</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Nova Norma</li>
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
                        <h3>Criar uma nova <b>NORMA</b></h3>
                    </div>
                    <div class="card-body">
                        <form id="user_form" action="{{ route('normas.norma_store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @include('normas.norma_create_form')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

