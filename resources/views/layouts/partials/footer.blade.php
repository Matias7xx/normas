<footer class="main-footer" style="background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%); border-top: 3px solid #bea55a; color: #495057;">
    <div class="container-fluid">
        <div class="row align-items-center py-3">
            <!-- Informações da versão -->
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
                        <i class="fas fa-code mr-1"></i> Versão 3.1.4
                    </span>
                    <span class="badge badge-outline-light" style="border: 1px solid #bea55a; color: #bea55a;">
                        <i class="fas fa-server mr-1"></i> DITI/DG-PCPB
                    </span>
                </div>
                <div class="text-muted small" style="color: #6c757d;">
                    © {{ date('Y') }} - Todos os Direitos Reservados
                </div>
            </div>
        </div>

        <!-- informações -->
        {{-- <div class="row border-top" style="border-color: #dee2e6 !important;">
            <div class="col-12 py-2">
                <div class="d-flex justify-content-between align-items-center flex-wrap">

                    <div class="d-flex align-items-center">
                        <span class="text-muted small mr-3" style="color: #6c757d;">
                            <i class="fas fa-users mr-1" style="color: #bea55a;"></i>
                            {{ \App\Models\User::where('active', true)->count() }} Usuários Ativos
                        </span>
                        <span class="text-muted small mr-3" style="color: #6c757d;">
                            <i class="fas fa-file-alt mr-1" style="color: #bea55a;"></i>
                            {{ \App\Models\Norma::where('status', true)->count() }} Normas Cadastradas
                        </span>
                        <span class="text-muted small" style="color: #6c757d;">
                            <i class="fas fa-clock mr-1" style="color: #bea55a;"></i>
                            Última Atualização: {{ date('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
</footer>

<style>
/* Footer specific styling */
.main-footer {
    margin-left: 0;
    z-index: 1000;
}

.main-footer a:hover {
    color: #d4b86a !important;
    transition: color 0.3s ease;
}

.badge-outline-light {
    background-color: transparent;
    font-size: 0.7rem;
    padding: 4px 8px;
}

/* Control sidebar styling */
.control-sidebar {
    border-left: 3px solid #bea55a;
}

.custom-control-input:checked ~ .custom-control-label::before {
    background-color: #bea55a;
    border-color: #bea55a;
}

.custom-control-input:focus ~ .custom-control-label::before {
    box-shadow: 0 0 0 0.2rem rgba(190, 165, 90, 0.25);
    border-color: #bea55a;
}

.info-box {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    padding: 15px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .main-footer .row {
        text-align: center;
    }

    .main-footer .col-md-6:last-child {
        text-align: center !important;
        margin-top: 10px;
    }

    .main-footer .d-flex {
        flex-direction: column;
        align-items: center;
    }

    .main-footer .d-flex > span {
        margin: 2px 0 !important;
    }
}

/* Animation for stats */
.info-box-content .col-4:hover {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}

.info-box-content .font-weight-bold {
    font-size: 1.2rem;
}

/* Professional hover effects */
.badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(190, 165, 90, 0.3);
    transition: all 0.2s ease;
}
</style>
