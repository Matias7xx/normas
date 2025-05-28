@extends('layouts.app')
@section('page-title')
    Nova Palavra Chave
@endsection
@section('header-content')
    <div class="page-header fade-in mb-6">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 mb-2">
                        <i class="fas fa-plus-circle mr-3"></i>Nova Palavra-chave
                    </h1>
                    <p class="lead mb-0">Crie uma nova Palavra-chave</p>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-md-end">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}">
                                    <i class="fas fa-home mr-1"></i>Início
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('palavras_chaves.palavras_chaves_list') }}">Lista de Palavras-chave</a>
                            </li>
                            <li class="breadcrumb-item active">Nova Palavra-chave</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                    <div class="card-body">
                        <form id="user_form" action="{{ route('palavras_chaves.palavras_chaves_store') }}" method="POST">
                            @csrf
                            @include('palavras_chaves.palavras_chaves_create_form')
                        </form>
                    </div>
            </div>
        </div>
    </div>
@endsection

<style>
.page-header {
        background: linear-gradient(135deg, #404040 0%, #2c2c2c 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 1rem;
        border-radius: 8px;
    }
    
    .page-header h1 {
        margin: 0;
        font-weight: 300;
    }
    
    .page-header .breadcrumb {
        background: transparent;
        margin: 0;
    }
    
    .page-header .breadcrumb-item a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
    }
    
    .page-header .breadcrumb-item a:hover {
        color: white;
    }
    
    .page-header .breadcrumb-item.active {
        color: rgba(255,255,255,0.9);
    }
</style>