<!DOCTYPE html>
<html lang="pt_BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Biblioteca de Normas | PCPB</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{asset('/plugins/fontawesome-free/css/all.min.css')}}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{asset('/dist/css/adminlte.min.css')}}">
    <!-- Estilo local -->
    <link rel="stylesheet" href="{{asset('/dist/css/estilo.css')}}">
</head>
<body class="layout-top-nav">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-md main-navbar">
        <div class="container-fluid">
            <!-- Brand/Logo -->
            <a href="#" class="navbar-brand fade-in">
                <img src="/images/brasao_pcpb.png" alt="Logo PCPB" width="35px" class="mr-2" style="opacity: .9; brightness(1.1) flex-shrink: 0;"/>
                <div class="brand-text">
                    <h4 class="brand-title">Biblioteca de Normas</h4>
                    <small class="brand-subtitle">Polícia Civil da Paraíba</small>
                </div>
            </a>

            <!-- Toggler -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent" 
                    aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar content -->
            {{-- <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" title="Página Inicial">
                            <i class="fas fa-home mr-2"></i>
                            <span class="d-md-none d-lg-inline">Início</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" title="Consultar Normas">
                            <i class="fas fa-search mr-2"></i>
                            <span class="d-md-none d-lg-inline">Consultar</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" title="Ajuda">
                            <i class="fas fa-question-circle mr-2"></i>
                            <span class="d-md-none d-lg-inline">Ajuda</span>
                        </a>
                    </li>
                </ul>
            </div> --}}
        </div>
    </nav>

        <!-- Content Wrapper -->
        <div class="content-wrapper" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-12 text-center">
                            <h1 class="m-0" style="color: #2c3e50; font-weight: 600;">
                                <i class="fas fa-search mr-2" style="color: #bea55a;"></i>
                                Consulta Pública de Normas
                            </h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content - componente norma_list -->
            <section class="content">
                <div class="container-fluid">
                    <!-- conteúdo do norma_list adaptado para consulta pública -->
                    @include('components.norma-search-public', [
                        'tipos' => $tipos,
                        'orgaos' => $orgaos
                    ])
                </div>
            </section>
        </div>

        <!-- Footer -->
        <footer class="main-footer" style="background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%); border-top: 3px solid #bea55a; color: #495057;">
            <div class="container-fluid">
                <div class="row align-items-center py-3">
                    <!-- versão -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div>
                                <strong style="color: #343a40;">
                                    <a href="https://policiacivil.pb.gov.br" class="text-decoration-none" 
                                       style="color: #bea55a;" target="_blank" rel="noopener">
                                        Polícia Civil da Paraíba
                                    </a>
                                </strong>
                                <div class="text-muted small" style="color: #6c757d;">
                                    Biblioteca de Normas
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações técnicas e direitos -->
                    <div class="col-md-6 text-right">
                        <div class="mb-1">
                            <span class="badge badge-outline-light mr-2" style="border: 1px solid #bea55a; color: #bea55a;">
                                <i class="fas fa-code mr-1"></i> Versão 1.0.0
                            </span>
                            <span class="badge badge-outline-light" style="border: 1px solid #bea55a; color: #bea55a;">
                                <i class="fas fa-server mr-1"></i> DITI/DG-PCPB
                            </span>
                        </div>
                        <div class="text-muted small">
                            © {{ date('Y') }} - Todos os Direitos Reservados
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- jQuery -->
    <script src="{{asset('/plugins/jquery/jquery.min.js')}}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{asset('/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <!-- Select2 -->
    <script src="{{asset('plugins/select2/js/select2.full.min.js')}}"></script>
    <!-- AdminLTE App -->
    <script src="{{asset('/dist/js/adminlte.min.js')}}"></script>
    <!-- Normas JS customizado para consulta pública -->
    <script src="{{ asset('js/normas-public.js') }}"></script>

    <style>
        /* Estilos específicos para a consulta pública */
        .btn-primary {
            background: linear-gradient(135deg, #bea55a 0%, #d4b76a 100%);
            border: none;
            color: #1a1a1a;
            font-weight: bold;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #d4b76a 0%, #bea55a 100%);
            color: #1a1a1a;
            transform: translateY(-1px);
        }

        .badge-primary {
            background: linear-gradient(135deg, #bea55a 0%, #d4b76a 100%);
            color: #1a1a1a;
        }

        .form-control:focus {
            border-color: #bea55a;
            box-shadow: 0 0 0 0.2rem rgba(190, 165, 90, 0.25);
        }

        .content-wrapper {
            min-height: calc(100vh - 120px);
        }

        /* Esconder elementos de administração */
        .admin-only {
            display: none !important;
        }

        :root {
            --primary-color: #bea55a;
            --primary-dark: #a69348;
            --primary-light: #d4b76a;
            --dark-bg: #1a1a1a;
            --light-bg: #f8f9fa;
            --text-dark: #2c3e50;
            --text-muted: #6c757d;
        }

        body {
            font-family: 'Source Sans Pro', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }

        /* Navbar */
        .main-navbar {
            background: linear-gradient(135deg, var(--dark-bg) 0%, #1a1a1a 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 1030;
            backdrop-filter: blur(10px);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            text-decoration: none !important;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: translateY(-2px);
            text-decoration: none !important;
        }

        /* Logo melhorado */
        .brand-logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.1);
            padding: 4px;
            margin-right: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(190, 165, 90, 0.3);
        }

        .brand-logo:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 6px 20px rgba(190, 165, 90, 0.5);
        }

        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-title {
            color: #ffffff;
            font-size: 1.4rem;
            font-weight: 600;
            line-height: 1.2;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .brand-subtitle {
            color: var(--primary-color);
            font-size: 0.85rem;
            font-weight: 400;
            margin: 0;
            opacity: 0.9;
        }

        /* Navbar items */
        .navbar-nav .nav-item {
            margin: 0 0.25rem;
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary-color) !important;
            background: rgba(190, 165, 90, 0.1);
            transform: translateY(-2px);
        }

        .navbar-toggler {
            border: 2px solid var(--primary-color);
            border-radius: 8px;
            padding: 0.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(190, 165, 90, 0.25);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28190, 165, 90, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.875rem;
            }
        }
    </style>
</body>
</html>