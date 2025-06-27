class NormasManager {
    constructor() {
        this.currentPage = 1;
        this.perPage = 15;
        this.orderBy = 'data';
        this.orderDir = 'desc';
        this.isLoading = false;
        this.lastRequest = null;
        this.autoSearchEnabled = false; // propriedade para controlar busca automática. Ao selecioanr um filtro ele busca automaticamente
        
        // Estado dos filtros
        this.currentFilters = {
            search_term: '',
            tipo_id: '',
            orgao_id: '',
            vigente: '',
            data_inicio: '',
            data_fim: '',
            order_by: 'data',
            order_dir: 'desc',
            per_page: 15,
            page: 1
        };
        
        this.initializeComponents();
        this.bindEvents();
        this.updateSortIndicators();
        this.loadNormas();
    }
    
    initializeComponents() {
        // Inicializar Select2 para filtros
        this.initializeSelect2();
        
        // Inicializar date pickers
        this.initializeDatePickers();
        
        // Configurar controles de período
        this.setupPeriodControls();
        
        // Configurar ordenação
        this.setupSorting();
        
        // Remover qualquer barra de progresso que possa existir
        this.removeProgressBars();
    }
    
    removeProgressBars() {
        // Remover qualquer elemento de progresso
        $('.progress, .progress-bar, .loading-bar, .ajax-progress').remove();
        
        // Esconder elementos de loading do navegador
        if (typeof NProgress !== 'undefined') {
            NProgress.done();
        }
        
        // Remover barras de carregamento
        $('.preloader, .overlay').remove();
        
        // Esconder indicador de loading global
        $('body').removeClass('loading');
    }
    
    initializeSelect2() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('#tipo_id, #orgao_id, #vigente').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%'
            });
        }
    }

    setupPeriodControls() {
        // Inicializar com filtro escondido
        $('#period-filter-content').hide();
        
        // Desabilitar campos de fim inicialmente
        this.toggleEndDateFields(false);
        
        // Limpar campos de fim inicialmente
        $('#data_fim_mes, #data_fim_ano').val('');
    }

    toggleEndDateFields(enable) {
        $('#data_fim_mes, #data_fim_ano').prop('disabled', !enable);
        
        if (enable) {
            $('#data_fim_mes, #data_fim_ano').removeClass('disabled-field');
            $('#end-date-label').removeClass('text-muted').addClass('font-weight-bold');
        } else {
            $('#data_fim_mes, #data_fim_ano').addClass('disabled-field').val('');
            $('#end-date-label').addClass('text-muted').removeClass('font-weight-bold');
        }
    }

    validateStartDateComplete() {
        const inicioMes = $('#data_inicio_mes').val();
        const inicioAno = $('#data_inicio_ano').val();
        
        return inicioMes && inicioAno;
    }

    validateEndDateComplete() {
        const fimMes = $('#data_fim_mes').val();
        const fimAno = $('#data_fim_ano').val();
        
        if (fimMes || fimAno) {
            return fimMes && fimAno;
        }
        
        return true;
    }

    togglePeriodFilter() {
        const $content = $('#period-filter-content');
        const $icon = $('#period-filter-icon');
        const $button = $('#toggle-period-filter');
        
        if ($content.is(':visible')) {
            $content.slideUp(300);
            $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            $button.removeClass('btn-primary').addClass('btn-outline-secondary');
            this.clearPeriodFilters();
        } else {
            $content.slideDown(300);
            $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            $button.removeClass('btn-outline-secondary').addClass('btn-primary');
        }
    }

    handleStartDateChange() {
        const startComplete = this.validateStartDateComplete();
        
        if (startComplete) {
            this.toggleEndDateFields(true);
            this.showToast('Info', 'Campos de data fim liberados. Agora você pode definir o período final.', 'info');
        } /* else {
            this.toggleEndDateFields(false);
            
            const inicioMes = $('#data_inicio_mes').val();
            const inicioAno = $('#data_inicio_ano').val();
            
            if (inicioMes && !inicioAno) {
                this.showToast('Atenção', 'Selecione também o ano para completar a data de início.', 'warning');
            } else if (!inicioMes && inicioAno) {
                this.showToast('Atenção', 'Selecione também o mês para completar a data de início.', 'warning');
            }
        } */
        
        this.updateDateFilters();
    }

    handleEndDateChange() {
        const endComplete = this.validateEndDateComplete();
        
        if (!endComplete) {
            const fimMes = $('#data_fim_mes').val();
            const fimAno = $('#data_fim_ano').val();
            
           /*  if (fimMes && !fimAno) {
                this.showToast('Atenção', 'Selecione também o ano para completar a data de fim.', 'warning');
            } else if (!fimMes && fimAno) {
                this.showToast('Atenção', 'Selecione também o mês para completar a data de fim.', 'warning');
            } */
        }
        
        this.updateDateFilters();
    }
    
    initializeDatePickers() {
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        
        this.populateYearOptions();
    }
    
    populateYearOptions() {
        const currentYear = new Date().getFullYear();
        const startYear = currentYear - 75;
        const endYear = currentYear;
        
        const yearSelects = ['#data_inicio_ano', '#data_fim_ano'];
        
        yearSelects.forEach(selector => {
            const $select = $(selector);
            if ($select.length && $select.children().length <= 1) {
                $select.empty().append('<option value="">Ano</option>');
                
                for (let year = endYear; year >= startYear; year--) {
                    $select.append(`<option value="${year}">${year}</option>`);
                }
            }
        });
    }

    clearPeriodFilters() {
        $('#data_inicio_mes').val('');
        $('#data_inicio_ano').val('');
        $('#data_fim_mes').val('');
        $('#data_fim_ano').val('');
        
        this.toggleEndDateFields(false);
        
        this.currentFilters.data_inicio = '';
        this.currentFilters.data_fim = '';
    }

    setupSorting() {
        this.updateSortIndicators();
    }

    updateDateFilters() {
        const dataInicioMes = $('#data_inicio_mes').val();
        const dataInicioAno = $('#data_inicio_ano').val();
        const dataFimMes = $('#data_fim_mes').val();
        const dataFimAno = $('#data_fim_ano').val();

        this.currentFilters.data_inicio = '';
        this.currentFilters.data_fim = '';

        if (dataInicioMes && dataInicioAno) {
            this.currentFilters.data_inicio = `${dataInicioAno}-${dataInicioMes.padStart(2, '0')}-01`;
        }

        if (dataFimMes && dataFimAno) {
            const lastDay = this.getLastDayOfMonth(parseInt(dataFimAno), parseInt(dataFimMes));
            this.currentFilters.data_fim = `${dataFimAno}-${dataFimMes.padStart(2, '0')}-${lastDay.toString().padStart(2, '0')}`;
        }
    }

    getLastDayOfMonth(year, month) {
        return new Date(year, month, 0).getDate();
    }
    
    bindEvents() {
        // Eventos de pesquisa e filtros
        $('#btn-search').on('click', () => this.performSearch());
        $('#clear-filters').on('click', () => this.clearFilters());
        $('#inline-clear-filters').on('click', (e) => {
            e.preventDefault();
            this.clearFilters();
        });
        
        // Toggle do filtro de período
        $('#toggle-period-filter').on('click', (e) => {
            e.preventDefault();
            this.togglePeriodFilter();
        });
        
        // Filtros rápidos
        $(document).on('click', '.quick-filter', (e) => {
            $('.quick-filter').removeClass('active');
            $(e.currentTarget).addClass('active');
            this.applyQuickFilter($(e.currentTarget).data('period'));
        });
        
        // Enter para pesquisar
        $('#search_term').on('keypress', (e) => {
            if (e.which === 13) {
                this.performSearch();
            }
        });
        
        // Limpar campo de busca individual
        $('#clear-search').on('click', () => {
            $('#search_term').val('').focus();
        });
            
        // Monitorar mudanças na data de início
        $('#data_inicio_mes, #data_inicio_ano').on('change', () => {
            this.handleStartDateChange();
        });
        
        // Monitorar mudanças na data de fim
        $('#data_fim_mes, #data_fim_ano').on('change', () => {
            this.handleEndDateChange();
        });
        
        $('#tipo_id, #orgao_id, #vigente').on('change', () => {
            // Apenas resetar a página, mas não fazer busca automática
            this.currentPage = 1;
        });
        
        // Ordenação via select
        $(document).on('change', '#sort-select', (e) => {
            const sortValue = $(e.target).val();
            const [field, direction] = sortValue.split('-');
            this.orderBy = field;
            this.orderDir = direction;
            this.currentFilters.order_by = field;
            this.currentFilters.order_dir = direction;
            this.currentPage = 1;
            this.currentFilters.page = 1;
            this.updateSortIndicators();
            this.loadNormas();
        });
        
        // Ordenação via clique nos cabeçalhos
        $('.sortable').on('click', (e) => this.handleSort(e));
        
        // Paginação
        $(document).on('click', '.pagination-btn', (e) => {
            e.preventDefault();
            const page = $(e.currentTarget).data('page');
            if (page && page !== this.currentPage) {
                this.currentPage = page;
                this.currentFilters.page = page;
                this.loadNormas();
            }
        });
        
        // Itens por página
        $(document).on('change', '#per-page-select', (e) => {
            this.perPage = parseInt($(e.target).val());
            this.currentFilters.per_page = this.perPage;
            this.currentPage = 1;
            this.currentFilters.page = 1;
            this.loadNormas();
        });
        
        // Exclusão de normas
        $(document).on('click', '.btn-delete', (e) => {
            e.preventDefault();
            const id = $(e.currentTarget).data('id');
            const descricao = $(e.currentTarget).data('descricao');
            this.showDeleteModal(id, descricao);
        });

        // Remover barras de progresso quando AJAX for concluído
        $(document).ajaxComplete(() => {
            this.removeProgressBars();
        });
    }

// Modificar a função performSearch para funcionar normalmente
    performSearch() {
        if (!this.validateDateRange()) {
            return;
        }
        
        // Atualizar filtros com valores dos campos
        this.currentFilters.search_term = $('#search_term').val().trim();
        this.currentFilters.tipo_id = $('#tipo_id').val();
        this.currentFilters.orgao_id = $('#orgao_id').val();
        this.currentFilters.vigente = $('#vigente').val();
        this.currentFilters.page = 1;
        this.currentPage = 1;
        
        this.updateDateFilters();
        $('.quick-filter').removeClass('active');
        this.loadNormas();
    }
    
    applyQuickFilter(period) {
        if (!$('#period-filter-content').is(':visible')) {
            this.togglePeriodFilter();
        }
        
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth() + 1;
        
        $('#search_term').val('');
        $('#tipo_id').val('').trigger('change');
        $('#orgao_id').val('').trigger('change');
        $('#vigente').val('').trigger('change');
        this.clearPeriodFilters();
        
        switch (period) {
            case 'all':
                if ($('#period-filter-content').is(':visible')) {
                    this.togglePeriodFilter();
                }
                break;
                
            case 'month':
                $('#data_inicio_mes').val(String(currentMonth).padStart(2, '0'));
                $('#data_inicio_ano').val(currentYear);
                this.toggleEndDateFields(true);
                $('#data_fim_mes').val(String(currentMonth).padStart(2, '0'));
                $('#data_fim_ano').val(currentYear);
                break;
                
            case 'quarter':
                const threeMonthsAgo = new Date();
                threeMonthsAgo.setMonth(threeMonthsAgo.getMonth() - 3);
                
                $('#data_inicio_mes').val(String(threeMonthsAgo.getMonth() + 1).padStart(2, '0'));
                $('#data_inicio_ano').val(threeMonthsAgo.getFullYear());
                this.toggleEndDateFields(true);
                $('#data_fim_mes').val(String(currentMonth).padStart(2, '0'));
                $('#data_fim_ano').val(currentYear);
                break;
                
            case 'year':
                $('#data_inicio_mes').val('01');
                $('#data_inicio_ano').val(currentYear);
                this.toggleEndDateFields(true);
                $('#data_fim_mes').val('12');
                $('#data_fim_ano').val(currentYear);
                break;
                
            case 'recent':
                this.orderBy = 'data';
                this.orderDir = 'desc';
                this.currentFilters.order_by = 'data';
                this.currentFilters.order_dir = 'desc';
                $('#sort-select').val('data-desc');
                this.updateSortIndicators();
                if ($('#period-filter-content').is(':visible')) {
                    this.togglePeriodFilter();
                }
                break;
        }
        
        this.currentPage = 1;
        this.currentFilters.page = 1;
        this.updateDateFilters();
        // Filtros rápidos aplicam automaticamente
        this.loadNormas();
    }
    
    validateDateRange() {
        const startComplete = this.validateStartDateComplete();
        const endComplete = this.validateEndDateComplete();
        
        if (!startComplete) {
            const inicioMes = $('#data_inicio_mes').val();
            const inicioAno = $('#data_inicio_ano').val();
            
            if (inicioMes || inicioAno) {
                this.showToast('Atenção', 'Complete a data de início informando mês e ano.', 'warning');
                return false;
            }
        }
        
        if (!endComplete) {
            this.showToast('Atenção', 'Complete a data de fim informando mês e ano.', 'warning');
            return false;
        }
        
        if (startComplete && !this.validateEndDateComplete()) {
            const fimMes = $('#data_fim_mes').val();
            const fimAno = $('#data_fim_ano').val();
            
            if (!fimMes && !fimAno) {
                this.showToast('Atenção', 'Informe também a data de fim para completar o período.', 'warning');
                return false;
            }
        }
        
        if (startComplete && endComplete) {
            const inicioMes = $('#data_inicio_mes').val();
            const inicioAno = $('#data_inicio_ano').val();
            const fimMes = $('#data_fim_mes').val();
            const fimAno = $('#data_fim_ano').val();
            
            const dataInicio = new Date(parseInt(inicioAno), parseInt(inicioMes) - 1, 1);
            const ultimoDiaFim = this.getLastDayOfMonth(parseInt(fimAno), parseInt(fimMes));
            const dataFim = new Date(parseInt(fimAno), parseInt(fimMes) - 1, ultimoDiaFim);
            
            if (dataInicio > dataFim) {
                this.showToast('Atenção', 'A data de início não pode ser posterior à data de fim.', 'warning');
                return false;
            }
        }
        
        return true;
    }
        
    clearFilters(reload = true) {
        $('#search_term').val('');
        $('#tipo_id').val('').trigger('change');
        $('#orgao_id').val('').trigger('change');
        $('#vigente').val('').trigger('change');
        
        this.clearPeriodFilters();
        
        $('.quick-filter').removeClass('active');
        
        this.currentPage = 1;
        this.orderBy = 'data';
        this.orderDir = 'desc';
        
        this.currentFilters = {
            search_term: '',
            tipo_id: '',
            orgao_id: '',
            vigente: '',
            data_inicio: '',
            data_fim: '',
            order_by: 'data',
            order_dir: 'desc',
            per_page: this.perPage,
            page: 1
        };
        
        if (reload) {
            this.updateSortIndicators();
            this.loadNormas();
        }

        if ($('#period-filter-content').is(':visible')) {
            this.togglePeriodFilter();
        }
    }
    
    getFilters() {
        this.currentFilters.search_term = $('#search_term').val().trim();
        this.currentFilters.tipo_id = $('#tipo_id').val();
        this.currentFilters.orgao_id = $('#orgao_id').val();
        this.currentFilters.vigente = $('#vigente').val();
        this.currentFilters.page = this.currentPage;
        this.currentFilters.per_page = this.perPage;
        this.currentFilters.order_by = this.orderBy;
        this.currentFilters.order_dir = this.orderDir;
        
        this.updateDateFilters();
        
        return this.currentFilters;
    }
    
    loadNormas() {
        if (this.isLoading) {
            if (this.lastRequest) {
                this.lastRequest.abort();
            }
        }
        
        this.isLoading = true;
        this.showLoading(true);
        
        // Remover barras de progresso antes de iniciar
        this.removeProgressBars();
        
        const filters = this.getFilters();
        
        const cleanFilters = {};
        Object.keys(filters).forEach(key => {
            if (filters[key] !== '' && filters[key] !== null && filters[key] !== undefined) {
                cleanFilters[key] = filters[key];
            }
        });
        
        this.lastRequest = $.ajax({
            url: '/normas/ajax',
            method: 'GET',
            data: cleanFilters,
            // Configurações adicionais para evitar barras de progresso
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                // Remover eventos de progresso
                xhr.upload.addEventListener("progress", function(evt) {
                    // Não fazer nada para evitar barras de progresso
                }, false);
                return xhr;
            },
            success: (response) => {
                this.renderNormas(response.normas);
                this.renderPagination(response.pagination);
                this.updateInfo(response.pagination, response.filters_applied);
                
                if (response.normas.length === 0) {
                    this.showNoDataMessage();
                } else {
                    this.hideNoDataMessage();
                }
            },
            error: (xhr) => {
                if (xhr.statusText !== 'abort') {
                    console.error('Erro ao carregar normas:', xhr);
                    this.showError('Erro ao carregar normas. Tente novamente.');
                }
            },
            complete: () => {
                this.isLoading = false;
                this.showLoading(false);
                this.lastRequest = null;
                // Garantir que barras de progresso sejam removidas
                setTimeout(() => {
                    this.removeProgressBars();
                }, 100);
            }
        });
    }
    
    renderNormas(normas) {
        const tbody = $('#normas-body');
        tbody.empty();
        
        if (normas.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fas fa-search mr-2"></i>
                        Nenhuma norma encontrada com os critérios informados.
                    </td>
                </tr>
            `);
            return;
        }
        
        normas.forEach(norma => {
            const palavrasChaveHtml = this.renderPalavrasChave(norma.palavras_chave, norma.palavras_chave_restantes);
            const auditoriaHtml = norma.auditoria ? this.renderAuditoriaBadge(norma.auditoria) : '';
            const acoesHtml = this.renderAcoes(norma);
            const vigenciaHtml = this.renderVigencia(norma.vigente);
            
            let rowClass = '';
            switch (norma.vigente) {
                case 'VIGENTE':
                    rowClass = 'norma-vigente';
                    break;
                case 'NÃO VIGENTE':
                    rowClass = 'norma-nao-vigente';
                    break;
                case 'EM ANÁLISE':
                    rowClass = 'norma-em-analise';
                    break;
            }
            
            const row = `
                <tr class="${rowClass}">
                    <td class="font-weight-bold text-primary">${norma.id}</td>
                    <td>
                        <span class="badge badge-secondary px-2 py-1">${norma.tipo}</span>
                    </td>
                    <td>
                        <span class="text-muted small">${norma.data || 'N/A'}</span>
                    </td>
                    <td>
                        ${vigenciaHtml}
                    </td>
                    <td>
                        <div class="text-truncate-custom" title="${norma.descricao}">
                            ${norma.descricao}
                        </div>
                        ${auditoriaHtml}
                    </td>
                    <td>
                        <div class="text-truncate-custom" title="${norma.resumo}">
                            ${norma.resumo}
                        </div>
                        ${palavrasChaveHtml}
                    </td>
                    <td>
                        <small class="text-muted">${norma.orgao}</small>
                    </td>
                    <td class="text-center">
                        <div class="action-buttons">
                            ${acoesHtml}
                        </div>
                    </td>
                </tr>
            `;
            
            tbody.append(row);
            
            // Inicializar tooltips após renderizar as normas
            setTimeout(() => {
                $('[data-toggle="tooltip"]').tooltip();
            }, 100);
        });
    }
    
    renderVigencia(vigente) {
        let badgeClass, icon;
        
        switch (vigente) {
            case 'VIGENTE':
                badgeClass = 'badge-success';
                icon = 'fas fa-check-circle';
                break;
            case 'NÃO VIGENTE':
                badgeClass = 'badge-danger';
                icon = 'fas fa-times-circle';
                break;
            case 'EM ANÁLISE':
                badgeClass = 'badge-warning';
                icon = 'fas fa-clock';
                break;
            case 'REVOGADA':
                badgeClass = 'badge-dark';
                icon = 'fas fa-ban';
                break;
            case 'SUSPENSA':
                badgeClass = 'badge-secondary';
                icon = 'fas fa-pause-circle';
                break;
            default:
                badgeClass = 'badge-light';
                icon = 'fas fa-question-circle';
                vigente = 'NÃO INFORMADO';
        }
        
        return `
            <span class="badge ${badgeClass} vigencia-badge">
                <i class="${icon}"></i>
                ${vigente}
            </span>
        `;
    }
    
    renderPalavrasChave(palavrasChave, restantes) {
        if (!palavrasChave || palavrasChave.length === 0) {
            return '';
        }

        let html = '<div class="mt-1">';
        
        palavrasChave.forEach(pc => {
            html += `<span class="badge badge-light mr-1 mb-1" style="font-size: 0.7rem;">${pc.palavra_chave}</span>`;
        });
        
        if (restantes > 0) {
            html += `<span class="badge badge-info" style="font-size: 0.7rem;">+${restantes} mais</span>`;
        }
        
        html += '</div>';
        return html;
    }

    renderAuditoriaBadge(auditoria) {
    if (!auditoria) {
        return '';
    }

    const tooltipText = `Cadastrado por: ${auditoria.usuario_nome} (Mat: ${auditoria.usuario_matricula}) em ${auditoria.data_cadastro}`;
    
    return `
        <div class="mt-1">
            <span class="badge badge-info auditoria-badge" 
                  title="${tooltipText}" 
                  data-toggle="tooltip" 
                  data-placement="top">
                <i class="fas fa-user-edit"></i>
                ${auditoria.usuario_nome}
            </span>
        </div>
    `;
}
    
    renderAcoes(norma) {
        let html = `
        <a href="${norma.anexo_url}" 
           target="_blank" 
           class="btn btn-secondary btn-xs" 
           title="Visualizar PDF">
            <i class="fas fa-file-pdf"></i>
        </a>
    `;
        
        if (window.userPermissions && window.userPermissions.canEdit) {
            html += `
                <a href="/normas/norma_edit/${norma.id}" 
                   class="btn btn-secondary btn-xs" title="Editar">
                    <i class="fas fa-edit"></i>
                </a>
            `;
        }
        
        if (window.userPermissions && window.userPermissions.canDelete) {
            html += `
                <button type="button" class="btn btn-danger btn-xs btn-delete" 
                        data-id="${norma.id}" data-descricao="${norma.descricao}" title="Excluir">
                    <i class="fas fa-trash"></i>
                </button>
            `;
        }
        
        return html;
    }
    
    renderPagination(pagination) {
        const container = $('#pagination-controls');
        container.empty();
        
        if (pagination.last_page <= 1) {
            return;
        }
        
        let html = '<nav aria-label="Navegação de páginas"><ul class="pagination pagination-sm justify-content-center mb-0">';
        
        if (pagination.current_page > 1) {
            html += `
                <li class="page-item">
                    <a class="page-link pagination-btn" href="#" data-page="${pagination.current_page - 1}">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            `;
        }
        
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.last_page, pagination.current_page + 2);
        
        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link pagination-btn" href="#" data-page="1">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === pagination.current_page ? 'active' : '';
            html += `
                <li class="page-item ${activeClass}">
                    <a class="page-link pagination-btn" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }
        
        if (endPage < pagination.last_page) {
            if (endPage < pagination.last_page - 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            html += `<li class="page-item"><a class="page-link pagination-btn" href="#" data-page="${pagination.last_page}">${pagination.last_page}</a></li>`;
        }
        
        if (pagination.current_page < pagination.last_page) {
            html += `
                <li class="page-item">
                    <a class="page-link pagination-btn" href="#" data-page="${pagination.current_page + 1}">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            `;
        }
        
        html += '</ul></nav>';
        
        html += `
            <div class="d-flex justify-content-center align-items-center mt-2">
                <small class="text-muted mr-2">Itens por página:</small>
                <select id="per-page-select" class="form-control form-control-sm" style="width: auto;">
                    <option value="10" ${this.perPage == 10 ? 'selected' : ''}>10</option>
                    <option value="15" ${this.perPage == 15 ? 'selected' : ''}>15</option>
                    <option value="25" ${this.perPage == 25 ? 'selected' : ''}>25</option>
                    <option value="50" ${this.perPage == 50 ? 'selected' : ''}>50</option>
                </select>
            </div>
        `;
        
        container.html(html);
    }
    
    updateInfo(pagination, filtersApplied = []) {
        const info = $('#info-container');
        let text = '';
        
        if (pagination.total === 0) {
            text = 'Nenhuma norma encontrada';
        } else {
            text = `Mostrando ${pagination.from} a ${pagination.to} de ${pagination.total} normas`;
        }
        
        const activeFilters = this.getActiveFiltersCount();
        if (activeFilters > 0) {
            text += ` <span class="badge badge-primary ml-2">${activeFilters} filtro(s) ativo(s)</span>`;
        }
        
        if (filtersApplied && filtersApplied.length > 0) {
            text += `<br><small class="text-muted">Filtros: ${filtersApplied.join(', ')}</small>`;
        }
        
        info.html(text);
    }
    
    getActiveFiltersCount() {
        let count = 0;
        
        if ($('#search_term').val().trim()) count++;
        if ($('#tipo_id').val()) count++;
        if ($('#orgao_id').val()) count++;
        if ($('#vigente').val()) count++;
        if ($('#data_inicio_mes').val() && $('#data_inicio_ano').val()) count++;
        if ($('#data_fim_mes').val() && $('#data_fim_ano').val()) count++;
        
        return count;
    }
    
    handleSort(e) {
        const $target = $(e.currentTarget);
        const field = $target.data('field');
        
        if (this.orderBy === field) {
            this.orderDir = this.orderDir === 'asc' ? 'desc' : 'asc';
        } else {
            this.orderBy = field;
            this.orderDir = 'asc';
        }
        
        this.currentFilters.order_by = this.orderBy;
        this.currentFilters.order_dir = this.orderDir;
        
        this.updateSortIndicators();
        this.currentPage = 1;
        this.currentFilters.page = 1;
        this.loadNormas();
    }
    
    updateSortIndicators() {
        const sortValue = `${this.orderBy}-${this.orderDir}`;
        $('#sort-select').val(sortValue);
        
        $('.sortable').removeClass('sort-asc sort-desc');
        $(`.sortable[data-field="${this.orderBy}"]`).addClass(`sort-${this.orderDir}`);
        
        $('.sortable .fa-sort').removeClass('text-primary').addClass('text-muted');
        $(`.sortable[data-field="${this.orderBy}"] .fa-sort`).removeClass('text-muted').addClass('text-primary');
    }
    
    showDeleteModal(id, descricao) {
        if (window.userPermissions && window.userPermissions.canDelete) {
            $('#normaDesc').text(descricao);
            $('#deleteForm').attr('action', `/normas/norma_destroy/${id}`);
            $('#deleteModal').modal('show');
        }
    }
    
    showLoading(show = true) {
        const tbody = $('#normas-body');
        if (show) {
            tbody.html(`
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Carregando normas...
                    </td>
                </tr>
            `);
        }
    }
    
    showNoDataMessage() {
        $('#no-data-message').removeClass('d-none');
    }
    
    hideNoDataMessage() {
        $('#no-data-message').addClass('d-none');
    }
    
    showError(message) {
        const tbody = $('#normas-body');
        tbody.html(`
            <tr>
                <td colspan="8" class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i> ${message}
                </td>
            </tr>
        `);
    }
    
    showToast(title, message, type = 'info') {
        if (typeof $(document).Toasts === 'function') {
            let toastClass = 'bg-info';
            switch (type) {
                case 'success':
                    toastClass = 'bg-success';
                    break;
                case 'warning':
                    toastClass = 'bg-warning';
                    break;
                case 'error':
                case 'danger':
                    toastClass = 'bg-danger';
                    break;
                default:
                    toastClass = 'bg-info';
            }
            
            $(document).Toasts('create', {
                title: title,
                class: toastClass,
                autohide: true,
                delay: 3000,
                position: 'topRight',
                body: message
            });
        } else {
            alert(`${title}: ${message}`);
        }
    }
}

/**
 * Funções globais para compatibilidade
 */
window.changePage = function(page) {
    if (window.normasManager) {
        window.normasManager.currentPage = page;
        window.normasManager.currentFilters.page = page;
        window.normasManager.loadNormas();
    }
};

window.confirmDelete = function(id, descricao) {
    if (window.normasManager) {
        window.normasManager.showDeleteModal(id, descricao);
    }
};

/**
 * Inicialização quando o DOM estiver pronto
 */
$(document).ready(function() {
    // Verificar se estamos na página correta
    if ($('#normas-table').length > 0) {
        // Remover qualquer barra de progresso inicial
        $('.progress, .progress-bar, .loading-bar, .ajax-progress').remove();
        $('body').removeClass('loading');
        
        // Configurar AJAX para não mostrar barras de progresso
        $.ajaxSetup({
            beforeSend: function(xhr, settings) {
                // Remover barras de progresso
                $('.progress, .progress-bar, .loading-bar').remove();
            },
            complete: function(xhr, status) {
                // Garantir que barras de progresso sejam removidas
                setTimeout(() => {
                    $('.progress, .progress-bar, .loading-bar, .ajax-progress').remove();
                    $('.preloader, .overlay').remove();
                    $('body').removeClass('loading');
                }, 50);
            }
        });
        
        // Inicializar o gerenciador de normas
        window.normasManager = new NormasManager();
        
        console.log('Sistema de Gestão de Normas iniciado com sucesso!');
        console.log('Busca manual ativada - use o botão Pesquisar');
    }
});