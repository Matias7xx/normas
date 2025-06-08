
class NormasManager {
    constructor() {
        this.currentPage = 1;
        this.perPage = 15;
        this.orderBy = 'data';
        this.orderDir = 'desc';
        this.isLoading = false;
        
        this.initializeComponents();
        this.bindEvents();
        this.updateSortIndicators(); // Inicializar o select com o valor padrão
        this.loadNormas();
    }
    
    initializeComponents() {
    // Inicializar Select2 para filtros
    this.initializeSelect2();
    
    // Inicializar date pickers
    this.initializeDatePickers();
    
    // Configurar controles de período
    this.setupPeriodControls();
    }
    
    initializeSelect2() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('#tipo_id, #orgao_id').select2({
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

    /**
     * Habilitar/desabilitar campos de data fim
     */
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

    /**
     * Validar se ambos mês e ano estão preenchidos
     */
    validateStartDateComplete() {
        const inicioMes = $('#data_inicio_mes').val();
        const inicioAno = $('#data_inicio_ano').val();
        
        return inicioMes && inicioAno;
    }

    /**
     * Validar se ambos mês e ano de fim estão preenchidos
     */
    validateEndDateComplete() {
        const fimMes = $('#data_fim_mes').val();
        const fimAno = $('#data_fim_ano').val();
        
        // Se um está preenchido, ambos devem estar
        if (fimMes || fimAno) {
            return fimMes && fimAno;
        }
        
        return true; // Se nenhum está preenchido, está válido
    }

    togglePeriodFilter() {
    const $content = $('#period-filter-content');
    const $icon = $('#period-filter-icon');
    const $button = $('#toggle-period-filter');
    
    if ($content.is(':visible')) {
        // Esconder
        $content.slideUp(300);
        $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        $button.removeClass('btn-primary').addClass('btn-outline-secondary');
        
        // Limpar filtros de período ao esconder
        this.clearPeriodFilters();
    } else {
        // Mostrar
        $content.slideDown(300);
        $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        $button.removeClass('btn-outline-secondary').addClass('btn-primary');
    }
}

    handleStartDateChange() {
        const startComplete = this.validateStartDateComplete();
        
        if (startComplete) {
            // Habilitar campos de fim
            this.toggleEndDateFields(true);
            this.showToast('Info', 'Campos de data fim liberados. Agora você pode definir o período final.', 'info');
        } else {
            // Desabilitar e limpar campos de fim
            this.toggleEndDateFields(false);
            
            // Mostrar aviso se apenas um campo está preenchido
            const inicioMes = $('#data_inicio_mes').val();
            const inicioAno = $('#data_inicio_ano').val();
            
            if (inicioMes && !inicioAno) {
                this.showToast('Atenção', 'Selecione também o ano para completar a data de início.', 'warning');
            } else if (!inicioMes && inicioAno) {
                this.showToast('Atenção', 'Selecione também o mês para completar a data de início.', 'warning');
            }
        }
    }

    handleEndDateChange() {
        const endComplete = this.validateEndDateComplete();
        
        if (!endComplete) {
            const fimMes = $('#data_fim_mes').val();
            const fimAno = $('#data_fim_ano').val();
            
            if (fimMes && !fimAno) {
                this.showToast('Atenção', 'Selecione também o ano para completar a data de fim.', 'warning');
            } else if (!fimMes && fimAno) {
                this.showToast('Atenção', 'Selecione também o mês para completar a data de fim.', 'warning');
            }
        }
    }
    
    initializeDatePickers() {
        // Configurar campos de data com valores padrão intuitivos
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = String(currentDate.getMonth() + 1).padStart(2, '0');
        
        // Data fim padrão: mês atual
        $('#data_fim_mes').val(currentMonth);
        $('#data_fim_ano').val(currentYear);
        
        // Configurar anos disponíveis
        this.populateYearOptions();
    }
    
    populateYearOptions() {
        const currentYear = new Date().getFullYear();
        const startYear = currentYear - 75;
        const endYear = currentYear;
        
        const yearSelects = ['#data_inicio_ano', '#data_fim_ano'];
        
        yearSelects.forEach(selector => {
            const $select = $(selector);
            $select.empty().append('<option value="">Qualquer</option>');
            
            for (let year = endYear; year >= startYear; year--) {
                $select.append(`<option value="${year}">${year}</option>`);
            }
        });
    }

    clearPeriodFilters() {
    $('#data_inicio_mes').val('');
    $('#data_inicio_ano').val('');
    $('#data_fim_mes').val('');
    $('#data_fim_ano').val('');
    
    // Desabilitar campos de fim
    this.toggleEndDateFields(false);
    }
    
    bindEvents() {
    // Eventos existentes...
    $('#btn-search').on('click', () => this.applyFilters());
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
            this.applyFilters();
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
    
    // Resto dos eventos existentes...
    $(document).on('change', '#sort-select', (e) => {
        const sortValue = $(e.target).val();
        const [field, direction] = sortValue.split('-');
        this.orderBy = field;
        this.orderDir = direction;
        this.currentPage = 1;
        this.updateSortIndicators();
        this.loadNormas();
    });
    
    $('.sortable').on('click', (e) => this.handleSort(e));
    
    $(document).on('click', '.pagination-btn', (e) => {
        e.preventDefault();
        const page = $(e.currentTarget).data('page');
        if (page && page !== this.currentPage) {
            this.currentPage = page;
            this.loadNormas();
        }
    });
    
    $(document).on('change', '#per-page-select', (e) => {
        this.perPage = parseInt($(e.target).val());
        this.currentPage = 1;
        this.loadNormas();
    });
    
    $(document).on('click', '.btn-delete', (e) => {
        e.preventDefault();
        const id = $(e.currentTarget).data('id');
        const descricao = $(e.currentTarget).data('descricao');
        this.showDeleteModal(id, descricao);
    });
}
    
    applyQuickFilter(period) {
    // Mostrar o filtro de período se não estiver visível
    if (!$('#period-filter-content').is(':visible')) {
        this.togglePeriodFilter();
    }
    
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const currentMonth = currentDate.getMonth() + 1;
    
    // Limpar outros filtros mas manter o período visível
    $('#search_term').val('');
    $('#tipo_id').val('').trigger('change');
    $('#orgao_id').val('').trigger('change');
    this.clearPeriodFilters();
    
    switch (period) {
        case 'all':
            // Esconder filtro de período novamente
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
            $('#sort-select').val('data-desc');
            this.updateSortIndicators();
            // Esconder filtro de período para "mais recentes"
            if ($('#period-filter-content').is(':visible')) {
                this.togglePeriodFilter();
            }
            break;
    }
    
    this.currentPage = 1;
    this.loadNormas();
}
    
    validateDateRange() {
        const inicioMes = $('#data_inicio_mes').val();
        const inicioAno = $('#data_inicio_ano').val();
        const fimMes = $('#data_fim_mes').val();
        const fimAno = $('#data_fim_ano').val();
        
        if (inicioMes && inicioAno && fimMes && fimAno) {
            const dataInicio = new Date(inicioAno, inicioMes - 1);
            const dataFim = new Date(fimAno, fimMes - 1);
            
            if (dataInicio > dataFim) {
                this.showToast('Atenção', 'A data de início não pode ser posterior à data de fim.', 'warning');
                return false;
            }
        }
        
        return true;
    }
    
        applyFilters() {
            if (!this.validateDateRange()) {
                return;
            }
            
            this.currentPage = 1;
            $('.quick-filter').removeClass('active');
            this.loadNormas();
        }
        
        clearFilters(reload = true) {
        $('#search_term').val('');
        $('#tipo_id').val('').trigger('change');
        $('#orgao_id').val('').trigger('change');
        
        // Limpar filtros de período
        this.clearPeriodFilters();
        
        $('.quick-filter').removeClass('active');
        
        this.currentPage = 1;
        this.orderBy = 'data';
        this.orderDir = 'desc';
        
        if (reload) {
            this.updateSortIndicators();
            this.loadNormas();
        }
    }
    
    getFilters() {
        const filters = {
            search_term: $('#search_term').val().trim(),
            tipo_id: $('#tipo_id').val(),
            orgao_id: $('#orgao_id').val(),
            page: this.currentPage,
            per_page: this.perPage,
            order_by: this.orderBy,
            order_dir: this.orderDir
        };
        
        // Construir filtros de data
        const inicioMes = $('#data_inicio_mes').val();
        const inicioAno = $('#data_inicio_ano').val();
        const fimMes = $('#data_fim_mes').val();
        const fimAno = $('#data_fim_ano').val();
        
        if (inicioMes && inicioAno) {
            filters.data_inicio = `${inicioAno}-${inicioMes.padStart(2, '0')}-01`;
        }
        
        if (fimMes && fimAno) {
            const ultimoDiaDoMes = new Date(parseInt(fimAno), parseInt(fimMes), 0).getDate();
            filters.data_fim = `${fimAno}-${fimMes.padStart(2, '0')}-${ultimoDiaDoMes}`;
        }
        
        return filters;
    }
    
    loadNormas() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading(true);
        
        const filters = this.getFilters();
        
        $.ajax({
            url: '/normas/ajax',
            method: 'GET',
            data: filters,
            success: (response) => {
                this.renderNormas(response.normas);
                this.renderPagination(response.pagination);
                this.updateInfo(response.pagination);
                
                if (response.normas.length === 0) {
                    this.showNoDataMessage();
                } else {
                    this.hideNoDataMessage();
                }
            },
            error: (xhr) => {
                console.error('Erro ao carregar normas:', xhr);
                this.showError('Erro ao carregar normas. Tente novamente.');
            },
            complete: () => {
                this.isLoading = false;
                this.showLoading(false);
            }
        });
    }
    
    renderNormas(normas) {
        const tbody = $('#normas-body');
        tbody.empty();
        
        if (normas.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="fas fa-search mr-2"></i>
                        Nenhuma norma encontrada com os critérios informados.
                    </td>
                </tr>
            `);
            return;
        }
        
        normas.forEach(norma => {
            const palavrasChaveHtml = this.renderPalavrasChave(norma.palavras_chave, norma.palavras_chave_restantes);
            const acoesHtml = this.renderAcoes(norma);
            
            const row = `
                <tr>
                    <td class="font-weight-bold text-primary">${norma.id}</td>
                    <td>
                        <span class="badge badge-secondary px-2 py-1">${norma.tipo}</span>
                    </td>
                    <td>
                        <span class="text-muted small">${norma.data || 'N/A'}</span>
                    </td>
                    <td>
                        <div class="text-truncate-custom" title="${norma.descricao}">
                            ${norma.descricao}
                        </div>
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
        });
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
    
    renderAcoes(norma) {
        let html = `
            <a href="/storage/normas/${norma.anexo}" target="_blank" 
               class="btn btn-danger btn-xs" title="Visualizar PDF">
                <i class="fas fa-file-pdf"></i>
            </a>
        `;
        
        // Verificar permissões do usuário
        if (window.userPermissions && window.userPermissions.canEdit) {
            html += `
                <a href="/normas/norma_edit/${norma.id}" 
                   class="btn btn-primary btn-xs" title="Editar">
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
        
        // Botão Anterior
        if (pagination.current_page > 1) {
            html += `
                <li class="page-item">
                    <a class="page-link pagination-btn" href="#" data-page="${pagination.current_page - 1}">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            `;
        }
        
        // Páginas
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
        
        // Botão Próximo
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
        
        // Adicionar seletor de itens por página
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
    
    updateInfo(pagination) {
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
        
        info.html(text);
    }
    
    getActiveFiltersCount() {
        let count = 0;
        
        if ($('#search_term').val().trim()) count++;
        if ($('#tipo_id').val()) count++;
        if ($('#orgao_id').val()) count++;
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
        
        this.updateSortIndicators();
        this.currentPage = 1;
        this.loadNormas();
    }
    
    updateSortIndicators() {
        // Atualizar o select de ordenação
        const sortValue = `${this.orderBy}-${this.orderDir}`;
        $('#sort-select').val(sortValue);
        
        // Atualizar indicadores visuais dos headers da tabela (se existirem)
        $('.sortable').removeClass('sort-asc sort-desc');
        $(`.sortable[data-field="${this.orderBy}"]`).addClass(`sort-${this.orderDir}`);
    }
    
    showDeleteModal(id, descricao) {
        if (window.userPermissions && window.userPermissions.canDelete) {
            $('#normaDesc').text(descricao);
            $('#deleteForm').attr('action', `/normas/norma_destroy/${id}`);
            $('#deleteModal').modal('show');
        }
    }
    
    showLoading(show) {
        if (show) {
            $('#normas-body').html(`
                <tr>
                    <td colspan="7" class="text-center py-4">
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
        if (typeof $.fn.Toasts !== 'undefined') {
            $(document).Toasts('create', {
                title: "Erro!",
                class: 'bg-danger',
                autohide: true,
                delay: 5000,
                position: 'topRight',
                body: message
            });
        } else {
            alert(message);
        }
    }
    
    showToast(title, message, type = 'info') {
        if (typeof $.fn.Toasts !== 'undefined') {
            const classMap = {
                'info': 'bg-info',
                'success': 'bg-success',
                'warning': 'bg-warning',
                'error': 'bg-danger'
            };
            
            $(document).Toasts('create', {
                title: title,
                class: classMap[type] || 'bg-info',
                autohide: true,
                delay: 3000,
                position: 'topRight',
                body: message
            });
        }
    }

    getLastDayOfMonth(year, month) {
    // month é 1-based (1-12), Date() usa 0-based (0-11)
    return new Date(year, month, 0).getDate();
}

/**
 * Função para validar e corrigir a data
 */
validateAndFixDate(year, month, day) {
    const lastDay = this.getLastDayOfMonth(year, month);
    if (day > lastDay) {
        return lastDay;
    }
    return day;
}

/**
 * Função atualizada para obter os filtros com datas corretas
 */
getFilters() {
    const filters = {
        search_term: $('#search_term').val().trim(),
        tipo_id: $('#tipo_id').val(),
        orgao_id: $('#orgao_id').val(),
        page: this.currentPage,
        per_page: this.perPage,
        order_by: this.orderBy,
        order_dir: this.orderDir
    };
    
    // Construir filtros de data CORRIGIDOS
    const inicioMes = $('#data_inicio_mes').val();
    const inicioAno = $('#data_inicio_ano').val();
    const fimMes = $('#data_fim_mes').val();
    const fimAno = $('#data_fim_ano').val();
    
    if (inicioMes && inicioAno) {
        // Para data de início, sempre use o dia 1
        filters.data_inicio = `${inicioAno}-${inicioMes.padStart(2, '0')}-01`;
    }
    
    if (fimMes && fimAno) {
        // Para data de fim, use o último dia válido do mês
        const ultimoDiaDoMes = this.getLastDayOfMonth(parseInt(fimAno), parseInt(fimMes));
        filters.data_fim = `${fimAno}-${fimMes.padStart(2, '0')}-${ultimoDiaDoMes.toString().padStart(2, '0')}`;
    }
    
    return filters;
}

/**
 * Função para aplicar filtros rápidos com datas corretas
 */
applyQuickFilter(period) {
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const currentMonth = currentDate.getMonth() + 1;
    
    // Limpar filtros existentes
    this.clearFilters(false);
    
    switch (period) {
        case 'all':
            // Não aplicar filtros de data
            break;
            
        case 'month':
            $('#data_inicio_mes').val(String(currentMonth).padStart(2, '0'));
            $('#data_inicio_ano').val(currentYear);
            $('#data_fim_mes').val(String(currentMonth).padStart(2, '0'));
            $('#data_fim_ano').val(currentYear);
            break;
            
        case 'quarter':
            const threeMonthsAgo = new Date();
            threeMonthsAgo.setMonth(threeMonthsAgo.getMonth() - 3);
            
            $('#data_inicio_mes').val(String(threeMonthsAgo.getMonth() + 1).padStart(2, '0'));
            $('#data_inicio_ano').val(threeMonthsAgo.getFullYear());
            $('#data_fim_mes').val(String(currentMonth).padStart(2, '0'));
            $('#data_fim_ano').val(currentYear);
            break;
            
        case 'year':
            $('#data_inicio_mes').val('01');
            $('#data_inicio_ano').val(currentYear);
            $('#data_fim_mes').val('12');
            $('#data_fim_ano').val(currentYear);
            break;
            
        case 'recent':
            this.orderBy = 'data';
            this.orderDir = 'desc';
            $('#sort-select').val('data-desc');
            this.updateSortIndicators();
            break;
    }
    
    this.currentPage = 1;
    this.loadNormas();
}

/**
 * Função para validação de período com melhor lógica
 */
validateDateRange() {
    const startComplete = this.validateStartDateComplete();
    const endComplete = this.validateEndDateComplete();
    
    // Se início não está completo mas tem algum campo preenchido
    if (!startComplete) {
        const inicioMes = $('#data_inicio_mes').val();
        const inicioAno = $('#data_inicio_ano').val();
        
        if (inicioMes || inicioAno) {
            this.showToast('Atenção', 'Complete a data de início informando mês e ano.', 'warning');
            return false;
        }
    }
    
    // Se fim não está completo mas tem algum campo preenchido
    if (!endComplete) {
        this.showToast('Atenção', 'Complete a data de fim informando mês e ano.', 'warning');
        return false;
    }
    
    // Se tem início mas não tem fim
    if (startComplete && !this.validateEndDateComplete()) {
        const fimMes = $('#data_fim_mes').val();
        const fimAno = $('#data_fim_ano').val();
        
        if (!fimMes && !fimAno) {
            this.showToast('Atenção', 'Informe também a data de fim para completar o período.', 'warning');
            return false;
        }
    }
    
    // Validar se a data de início não é posterior à data de fim
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
}

// Inicializar quando o documento estiver pronto
$(document).ready(function() {
    window.normasManager = new NormasManager();
});