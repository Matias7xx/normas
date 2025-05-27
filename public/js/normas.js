/**
 * Script para gerenciamento de normas com paginação AJAX
 */
$(function () {
    // Estado da aplicação ORDENADO PELO ID do menor para o maior
    let state = {
        currentPage: 1,
        perPage: 15,
        orderBy: 'id',
        orderDir: 'asc',
        filters: {
            search_term: '',
            tipo_id: '',
            orgao_id: ''
        },
        loading: false
    };
    
    // Verificar permissões do usuário (passadas do backend)
    const userPermissions = window.userPermissions || {};
    const canEdit = userPermissions.canEdit === true;
    const canDelete = userPermissions.canDelete === true;
    
    // Debug - verificar se as permissões estão sendo carregadas corretamente
    console.log('User Permissions:', userPermissions);
    console.log('Can Edit:', canEdit);
    console.log('Can Delete:', canDelete);
    
    // Inicializar Select2
    $('.select2-sm').select2({
        theme: 'bootstrap4',
        width: '100%',
        allowClear: true,
        minimumResultsForSearch: 6,
        language: {
            noResults: function() {
                return "Nenhum resultado encontrado";
            },
            searching: function() {
                return "Pesquisando...";
            }
        }
    });

    // Cores diferentes para cada tipo de norma. Cores: info, primary, danger, warning, secondary, success, dark (preto), light (cinza), white
    const tipoColors = {
        'DECRETO': 'primary',
        'PORTARIA': 'success',
        'LEI ORDINÁRIA': 'danger',
        'MEDIDA PROVISÓRIA': 'dark',
        'REGIMENTO': 'warning',
        'default': 'secondary' // Cor para tipos não especificados
    };

    //Pegar a cor
    function getTipoColor(tipo) {
        const tipoUpperCase = tipo.toUpperCase();
        return tipoColors[tipoUpperCase] || tipoColors.default;
    }
    
    // Função para gerar botões de ação baseado nas permissões
    function generateActionButtons(norma) {
        let buttons = '';
        
        // Debug
        console.log('Generating buttons for norma:', norma.id);
        console.log('Can Edit:', canEdit, 'Can Delete:', canDelete);
        
        // Botão para visualizar PDF (sempre disponível)
        buttons += `
            <a href="javascript:abrirPagina('${window.location.origin}/storage/normas/${norma.anexo}',800,600);" 
                class="btn btn-xs btn-default" data-toggle="tooltip" title="Visualizar PDF">
                <i class="fas fa-file-pdf"></i>
            </a>
        `;
        
        // Botão de editar (apenas para admins)
        if (canEdit) {
            buttons += `
                <a href="/normas/norma_edit/${norma.id}" 
                    class="btn btn-xs btn-primary" data-toggle="tooltip" title="Editar">
                    <i class="fas fa-edit"></i>
                </a>
            `;
        }
        
        // Botão de excluir (apenas para admins)
        if (canDelete) {
            buttons += `
                <button type="button" class="btn btn-xs btn-danger delete-norma" 
                        data-toggle="modal" data-target="#deleteModal" 
                        data-norma-id="${norma.id}"
                        data-norma-desc="${norma.descricao}"
                        title="Remover">
                    <i class="fas fa-trash"></i>
                </button>
            `;
        }
        
        console.log('Generated buttons:', buttons);
        return buttons;
    }
    
    // Carregar dados
    function loadNormas() {
        if (state.loading) return;
        state.loading = true;
        
        // Mostrar loading Colspan era 5 e ficou 7 após acrescentar o ID
        $('#normas-body').html(`
            <tr>
                <td colspan="7" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin mr-2"></i> Carregando normas...
                </td>
            </tr>
        `);

        // Exibir feedback visual de carregamento
        $('#info-container').html(`
            <i class="fas fa-spinner fa-spin"></i> Carregando informações...
        `);
        
        $.ajax({
            url: "/normas/ajax", // URL absoluta
            type: "GET",
            data: {
                page: state.currentPage,
                per_page: state.perPage,
                order_by: state.orderBy,
                order_dir: state.orderDir,
                search_term: state.filters.search_term,
                tipo_id: state.filters.tipo_id,
                orgao_id: state.filters.orgao_id
            },
            success: function(response) {
                state.loading = false;
                
                // Atualizar informações
                updateInfoContainer(response.pagination);
                
                // Verificar se há normas
                if (response.normas.length === 0) {
                    $('#normas-table').addClass('d-none');
                    $('#no-data-message').removeClass('d-none');
                    return;
                }
                
                // Exibir tabela e esconder mensagem de sem dados
                $('#normas-table').removeClass('d-none');
                $('#no-data-message').addClass('d-none');
                
                // Limpar e preencher tabela //Acrescentado o ID
                let html = '';
                response.normas.forEach(function(norma) {
                    html += `
                    <tr>
                        <td><strong>${norma.id}</strong></td>
                        <td>
                            <span class="badge badge-${getTipoColor(norma.tipo)}" style="font-size: 90%;">
                                ${norma.tipo}
                            </span>
                        </td>
                        <td>${norma.data}</td>
                        <td>
                            <div class="text-truncate-custom" data-toggle="tooltip" title="${norma.descricao}">
                                ${norma.descricao}
                            </div>
                            ${renderPalavrasChave(norma.palavras_chave, norma.palavras_chave_restantes)}
                        </td>
                        <td>
                            <div class="text-truncate-custom" data-toggle="tooltip" title="${norma.resumo}">
                                ${norma.resumo}
                            </div>
                        </td>
                        <td>${norma.orgao}</td>
                        <td class="text-center">
                            <div class="action-buttons">
                                ${generateActionButtons(norma)}
                            </div>
                        </td>
                    </tr>
                    `;
                });
                
                $('#normas-body').html(html);
                
                // Atualizar paginação
                updatePagination(response.pagination);
                
                // Inicializar tooltips
                $('[data-toggle="tooltip"]').tooltip({
                    delay: { show: 500, hide: 100 },
                    boundary: 'window',
                    container: 'body'
                });
            },
            error: function(xhr, status, error) {
                state.loading = false;
                
                // Exibir mensagem de erro com detalhes. Colspan era 5 e ficou 7 após acrescentar o ID
                $('#normas-body').html(`
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-exclamation-circle mr-2"></i> 
                            Erro ao carregar dados: ${error}
                            <br>
                            <small class="mt-2 d-block">Por favor, tente novamente ou contate o suporte.</small>
                            <button class="btn btn-sm btn-outline-secondary mt-3" onclick="loadNormas()">
                                <i class="fas fa-sync-alt"></i> Tentar novamente
                            </button>
                        </td>
                    </tr>
                `);
                
                console.error('Erro ao carregar normas:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
            }
        });
    }
    
    // Renderizar palavras-chave
    function renderPalavrasChave(palavrasChave, restantes) {
        if (!palavrasChave || palavrasChave.length === 0) return '';
        
        let html = '<div class="mt-1">';
        
        palavrasChave.forEach(function(pc) {
            html += `<span class="badge badge-info mr-1">${pc.palavra_chave}</span>`;
        });
        
        if (restantes > 0) {
            html += `<span class="badge badge-light palavra-chave-more" 
                          data-toggle="tooltip" 
                          title="Possui mais ${restantes} palavra(s)-chave">
                        +${restantes}
                    </span>`;
        }
        
        html += '</div>';
        return html;
    }
    
    // Atualizar informações de total e filtros
    function updateInfoContainer(pagination) {
        let filterText = '';
        if (state.filters.search_term || state.filters.tipo_id || state.filters.orgao_id) {
            filterText = '<span class="ml-2 badge badge-info"><i class="fas fa-filter"></i> Filtros aplicados</span>';
        }
        
        $('#info-container').html(`
            <i class="fas fa-info-circle"></i> 
            Total: <span class="font-weight-bold">${pagination.total}</span> normas
            ${filterText}
        `);
    }
    
    // Atualizar controles de paginação
    function updatePagination(pagination) {
        // Atualizar informações de paginação
        $('#pagination-info').text(`Mostrando ${pagination.from || 0} a ${pagination.to || 0} de ${pagination.total} normas`);
        
        // Se não houver páginas, esconder controles
        if (pagination.last_page <= 1) {
            $('#pagination-controls').html('');
            return;
        }
        
        // Criar controles de paginação
        let html = '<ul class="pagination pagination-sm m-0">';
        
        // Botão anterior
        html += `
            <li class="page-item ${pagination.current_page <= 1 ? 'disabled' : ''}">
                <a class="page-link" href="javascript:void(0)" data-page="${pagination.current_page - 1}">
                    <i class="fas fa-angle-left"></i>
                </a>
            </li>
        `;
        
        // Definir quantas páginas mostrar antes e depois da atual
        const maxPagesToShow = 5;
        const halfMaxPages = Math.floor(maxPagesToShow / 2);
        
        let startPage = Math.max(1, pagination.current_page - halfMaxPages);
        let endPage = Math.min(pagination.last_page, startPage + maxPagesToShow - 1);
        
        if (endPage - startPage + 1 < maxPagesToShow) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }
        
        // Primeira página
        if (startPage > 1) {
            html += `
                <li class="page-item">
                    <a class="page-link" href="javascript:void(0)" data-page="1">1</a>
                </li>
            `;
            
            if (startPage > 2) {
                html += `
                    <li class="page-item disabled">
                        <a class="page-link" href="javascript:void(0)">...</a>
                    </li>
                `;
            }
        }
        
        // Páginas intermediárias
        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                    <a class="page-link" href="javascript:void(0)" data-page="${i}">${i}</a>
                </li>
            `;
        }
        
        // Última página
        if (endPage < pagination.last_page) {
            if (endPage < pagination.last_page - 1) {
                html += `
                    <li class="page-item disabled">
                        <a class="page-link" href="javascript:void(0)">...</a>
                    </li>
                `;
            }
            
            html += `
                <li class="page-item">
                    <a class="page-link" href="javascript:void(0)" data-page="${pagination.last_page}">${pagination.last_page}</a>
                </li>
            `;
        }
        
        // Botão próximo
        html += `
            <li class="page-item ${pagination.current_page >= pagination.last_page ? 'disabled' : ''}">
                <a class="page-link" href="javascript:void(0)" data-page="${pagination.current_page + 1}">
                    <i class="fas fa-angle-right"></i>
                </a>
            </li>
        `;
        
        html += '</ul>';
        
        $('#pagination-controls').html(html);
    }
    
    // Event handlers para controle de paginação
    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        
        if ($(this).parent().hasClass('disabled')) return;
        
        const page = $(this).data('page');
        if (page) {
            state.currentPage = page;
            loadNormas();
            // Scroll to top smoothly
            $('html, body').animate({ scrollTop: 0 }, 'fast');
        }
    });
    
    // Pesquisa ao clicar no botão
    $('#btn-search').on('click', function() {
        state.filters.search_term = $('#search_term').val().trim();
        state.filters.tipo_id = $('#tipo_id').val();
        state.filters.orgao_id = $('#orgao_id').val();
        state.currentPage = 1; // Resetar para primeira página
        loadNormas();
    });
    
    // Pesquisa ao pressionar Enter no campo de busca
    $('#search_term').on('keypress', function(e) {
        if (e.which === 13) {
            $('#btn-search').click();
        }
    });
    
    // Limpar filtros
    $('#clear-filters, #inline-clear-filters').on('click', function(e) {
        e.preventDefault();
        
        // Limpar campos
        $('#search_term').val('');
        $('#tipo_id').val('').trigger('change');
        $('#orgao_id').val('').trigger('change');
        
        // Resetar estado
        state.filters.search_term = '';
        state.filters.tipo_id = '';
        state.filters.orgao_id = '';
        state.currentPage = 1;
        
        // Recarregar dados
        loadNormas();
    });
    
    // Ordenação
    $('.sortable').on('click', function() {
        const field = $(this).data('field');
        
        // Alternar direção se o mesmo campo foi clicado
        if (state.orderBy === field) {
            state.orderDir = state.orderDir === 'asc' ? 'desc' : 'asc';
        } else {
            state.orderBy = field;
            state.orderDir = 'asc';
        }
        
        // Atualizar ícones
        $('.sortable').removeClass('sort-asc sort-desc');
        $(this).addClass(state.orderDir === 'asc' ? 'sort-asc' : 'sort-desc');
        
        // Recarregar dados
        loadNormas();
    });
    
    // Modal de exclusão (só funciona se o usuário tiver permissão)
    $(document).on('click', '.delete-norma', function() {
        if (!canDelete) {
            alert('Você não tem permissão para excluir normas.');
            return;
        }
        
        const normaId = $(this).data('norma-id');
        const normaDesc = $(this).data('norma-desc');
        
        $('#normaDesc').text(normaDesc);
        $('#deleteForm').attr('action', `/normas/norma_destroy/${normaId}`);
    });

    // Função para abrir documentos em nova janela
    window.abrirPagina = function(url, largura, altura) {
        const left = (screen.width - largura) / 2;
        const top = (screen.height - altura) / 2;
        const opcoes = 'width=' + largura + ',height=' + altura + ',top=' + top + ',left=' + left + ',scrollbars=yes,resizable=yes,status=no,location=no';
        
        const win = window.open(url, '_blank', opcoes);
        if (win) {
            win.focus();
        } else {
            alert('Por favor, permita popups para este site para visualizar o documento.');
        }
        return false;
    };
    
    // Expor loadNormas globalmente
    window.loadNormas = loadNormas;
    
    // Carregar dados quando a página for carregada
    loadNormas();
    
    // Evitar que tooltips fiquem presos na tela ao exibir modais
    $('.modal').on('show.bs.modal', function () {
        $('.tooltip').remove();
    });
});