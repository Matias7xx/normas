@extends('layouts.guest')

@section('content')
<div class="login-container">
    <!-- Logo Section - Fora do card -->
    <div class="logo-section">
        <div class="logo-wrapper">
            <img src="{{asset('images/logo-pc.png')}}" alt="Logo PCPB" class="logo-img"/>
        </div>
        <h1 class="app-title">Biblioteca de Normas</h1>
        <p class="app-subtitle">Assessoria Técnico-Normativa</p>
    </div>

    <!-- Login Card -->
    <div class="login-card">
        <!-- Login Form -->
        <div class="login-form-section">
            <div class="login-header">
                <h2>Acesso ao Sistema</h2>
            </div>

            <!-- Mensagens de erro -->
            @if ($errors->any())
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            <form id="loginForm" action="{{route('login')}}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="matricula" class="form-label">Matrícula</label>
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="matricula" 
                            name="matricula" 
                            class="form-control {{ $errors->has('matricula') ? 'error' : '' }}"
                            placeholder="Informe sua matrícula"
                            value="{{ old('matricula') }}"
                            required
                            autocomplete="username"
                        >
                        <i class="fas fa-id-card input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Senha</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control {{ $errors->has('password') ? 'error' : '' }}"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        >
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">Lembrar-me</label>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    Entrar no Sistema
                </button>
            </form>
        </div>
    </div>

    <!-- Rodapé -->
    <div class="footer">
        <p>&copy; {{ date('Y') }} Polícia Civil - Todos os direitos reservados</p>
    </div>
</div>
@endsection

@section('styles')
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: relative;
    }

    /* Sobrescrever estilos do AdminLTE */
    .login-page {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        background-color: transparent !important;
        background-image: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    }

    /* Forçar background em todos os containers */
    .login-page * {
        background-color: transparent !important;
    }

    .login-page body {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    }

    .login-container {
        width: 100%;
        max-width: 450px;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: calc(100vh - 40px);
        justify-content: center;
        position: relative;
    }

    /* Logo Section - Fora do card */
    .logo-section {
        text-align: center;
        margin-bottom: 15px;
        z-index: 10;
        position: relative;
    }

    .logo-wrapper {
        margin-bottom: 20px;
    }

    .logo-img {
        height: 160px;
        width: auto;
        filter: brightness(1.1) drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
    }

    .app-title {
        font-size: 28px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .app-subtitle {
        font-size: 16px;
        color: #34495e;
        font-weight: 400;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    /* Login Card */
    .login-card {
        background: white !important;
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        width: 100%;
        padding: 40px 35px;
        border: 1px solid rgba(255, 255, 255, 0.8);
        position: relative;
        margin-bottom: 15px;
    }

    .login-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: #bea55a;
    }

    .login-form-section {
        color: #2c3e50;
    }

    .login-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .login-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }

    .form-group {
        margin-bottom: 24px;
        position: relative;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        color: #2c3e50;
        font-weight: 500;
        font-size: 14px;
    }

    .input-wrapper {
        position: relative;
    }

    .form-control {
        width: 100%;
        padding: 16px 16px 16px 50px;
        border: 1px solid #ddd;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 400;
        color: #2c3e50;
        background: rgba(255, 255, 255, 0.9);
        transition: all 0.3s ease;
    }

    .form-control::placeholder {
        color: #999;
    }

    .form-control:focus {
        outline: none;
        border-color: #bea55a;
        box-shadow: 0 0 0 1px rgba(44, 62, 80, 0.15);
        background: rgba(255, 255, 255, 1);
    }

    .form-control.error {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
    }

    .input-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        font-size: 18px;
        transition: color 0.3s ease;
        pointer-events: none;
    }

    .form-control:focus + .input-icon {
        color: #bea55a;
    }

    .password-toggle {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #999;
        cursor: pointer;
        font-size: 18px;
        padding: 4px;
        transition: color 0.3s ease;
    }

    .password-toggle:hover {
        color: #bea55a;
    }

    .form-options {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        margin-bottom: 32px;
    }

    .checkbox-wrapper {
        display: flex;
        align-items: center;
    }

    .checkbox-wrapper input {
        margin-right: 8px;
        transform: scale(1.1);
        accent-color: #2c3e50;
    }

    .checkbox-wrapper label {
        color: #7f8c8d;
        font-size: 14px;
        font-weight: 400;
        cursor: pointer;
    }

    .btn-login {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #bea55a 0%, #d4c47a 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(44, 62, 80, 0.4);
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(190, 165, 90, 0.4);
        background: linear-gradient(135deg, #d4c47a 0%, #bea55a 100%);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .btn-login::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s;
    }

    .btn-login:hover::before {
        left: 100%;
    }

    .error-message {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 20px;
        border-left: 4px solid #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    /* Rodapé */
    .footer {
        text-align: center;
        margin-top: 10px;
        padding: 10px;
        color: #4d4e4e;
        font-size: 14px;
        font-weight: 500;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    /* Loading animation */
    .btn-loading {
        position: relative;
        color: transparent;
    }

    .btn-loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        margin-left: -10px;
        margin-top: -10px;
        width: 20px;
        height: 20px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top: 2px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Responsive */
    @media (max-width: 480px) {
        body {
            padding: 15px;
        }
        
        .login-card {
            padding: 30px 25px;
        }

        .app-title {
            font-size: 24px;
        }

        .login-header h2 {
            font-size: 20px;
        }

        .form-control {
            padding: 14px 14px 14px 45px;
            font-size: 16px;
        }

        .input-icon {
            left: 14px;
            font-size: 16px;
        }

        .password-toggle {
            right: 14px;
            font-size: 16px;
        }

        .logo-img {
            height: 120px;
        }

        .footer {
            font-size: 12px;
            color: #2c3e50;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    // Toggle de visibilidade da senha
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordField = document.getElementById('password');
        const toggleIcon = this.querySelector('i');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    });

    // Animação de loading no botão
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const loginBtn = document.getElementById('loginBtn');
        loginBtn.classList.add('btn-loading');
        loginBtn.disabled = true;
        
        // Remove loading após timeout para permitir retorno em caso de erro
        setTimeout(() => {
            loginBtn.classList.remove('btn-loading');
            loginBtn.disabled = false;
        }, 10000);
    });

    // Auto-focus no primeiro campo
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('matricula').focus();
    });

    // Enter para navegar entre campos
    document.getElementById('matricula').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('password').focus();
        }
    });

    // Efeito de digitação suave nos campos
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.length > 0) {
                this.style.background = 'rgba(255, 255, 255, 1)';
                this.style.borderColor = '#2c3e50';
            } else {
                this.style.background = 'rgba(255, 255, 255, 0.9)';
                this.style.borderColor = '#ddd';
            }
        });
    });
</script>
@endsection