<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateNormaRequest;
use App\Http\Requests\UpdateNormaRequest;
use App\Models\Norma;
use App\Models\NormaChave;
use App\Models\Orgao;
use App\Models\PalavraChave;
use App\Models\Publicidade;
use App\Models\Rh\Servidor;
use App\Models\Tipo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class NormaController extends Controller
{
    /**
     * Exibe a listagem de normas agrupadas por tipo
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {   
        try {
            /* $servidor = Servidor::where('matricula', Auth::user()->matricula)->first(); */
            
            // Obter listas para filtros
            $tipos = Tipo::where('status', true)->orderBy('tipo')->get();
            $orgaos = Orgao::where('status', true)->orderBy('orgao')->get();
            
            // Verificar permissões do usuário
            $user = Auth::user();
            $userPermissions = [
                'canEdit' => $user->role_id == 1 || in_array($user->role_id, [1, 2, 3]),
                'canDelete' => $user->role_id == 1 || in_array($user->role_id, [1, 2, 3]),
                'canCreate' => $user->role_id == 1 || in_array($user->role_id, [1, 2, 3]),
                'isRoot' => $user->role_id == 1,
                'isAdmin' => in_array($user->role_id, [1, 2, 3])
            ];
            
            return view('normas.norma_list', compact( 'tipos', 'orgaos', 'userPermissions'));
            
        } catch (\Exception $e) {
            Log::error('Erro ao listar normas: ' . $e->getMessage());
            return back()->withErrors(['Erro ao carregar normas. Por favor, tente novamente.']);
        }
    }

    /**
     * Obtém normas com paginação via Ajax
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNormasAjax(Request $request)
{
    try {   
        // Base query com eager loading otimizado
        $query = Norma::with([
            'publicidade:id,publicidade', 
            'orgao:id,orgao', 
            'tipo:id,tipo', 
            'palavrasChave:id,palavra_chave',
            'usuario:id,name,matricula' // Necessário para auditoria
        ])
        ->ativas();
        
        // Aplicar filtros de pesquisa
        if ($request->filled('search_term')) {
        $searchTerm = trim($request->search_term);
        $query = $this->applySearchWithRelevance($query, $searchTerm);
        }
        
        // Filtro por tipo
        if ($request->filled('tipo_id')) {
            $query->where('tipo_id', $request->tipo_id);
        }
        
        // Filtro por órgão
        if ($request->filled('orgao_id')) {
            $query->where('orgao_id', $request->orgao_id);
        }

        // Filtro por vigência
        if ($request->filled('vigente')) {
            $query->where('vigente', $request->vigente);
        }
        
        if ($request->filled('data_inicio')) {
            try {
                $dataInicio = Carbon::createFromFormat('Y-m-d', $request->data_inicio)->startOfDay();
                $query->where('data', '>=', $dataInicio);
            } catch (\Exception $e) {
            }
        }
        
        if ($request->filled('data_fim')) {
            try {
                $dataFim = Carbon::createFromFormat('Y-m-d', $request->data_fim)->endOfDay();
                $query->where('data', '<=', $dataFim);
            } catch (\Exception $e) {
            }
        }
                    
        // Tratamento de ordenação
        $orderBy = $request->input('order_by', 'data');
        $orderDir = $request->input('order_dir', 'desc');
        
        // Validar direção de ordenação
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? 
            strtolower($orderDir) : 'desc';
        
        // Aplicar ordenação baseada no campo
        switch ($orderBy) {
            case 'id':
                $query->orderBy('id', $orderDir);
                break;
            case 'data':
                $query->orderBy('data', $orderDir)->orderBy('id', 'desc');
                break;
            case 'descricao':
                $query->orderBy('descricao', $orderDir);
                break;
            case 'resumo':
                $query->orderBy('resumo', $orderDir);
                break;
            case 'vigente':
                $query->orderByRaw(
                    "CASE 
                        WHEN vigente = 'VIGENTE' THEN 1 
                        WHEN vigente = 'EM ANÁLISE' THEN 2 
                        WHEN vigente = 'NÃO VIGENTE' THEN 3 
                        ELSE 4 
                    END " . $orderDir
                );
                break;
            case 'orgao':
                $query->join('orgaos', 'normas.orgao_id', '=', 'orgaos.id')
                      ->orderBy('orgaos.orgao', $orderDir)
                      ->select('normas.*');
                break;
            case 'tipo':
                $query->join('tipos', 'normas.tipo_id', '=', 'tipos.id')
                      ->orderBy('tipos.tipo', $orderDir)
                      ->select('normas.*');
                break;
            default:
                $query->orderBy('data', 'desc')->orderBy('id', 'desc');
                break;
        }
        
        // Paginação
        $perPage = $request->input('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50]) ? $perPage : 15;
        
        $normas = $query->paginate($perPage);
        
        // Verificar se o usuário atual tem role_id = 2 (gestor) ou 1 (root) para mostrar auditoria
        $showAudit = in_array(auth()->user()->role_id, [1, 2]);
        
        // Preparar dados para exibição
        $formattedNormas = $normas->map(function($norma) use ($showAudit) {
            $normaData = [
                'id' => $norma->id,
                'data' => $norma->data ? $norma->data->format('d/m/Y') : null,
                'descricao' => $norma->descricao,
                'resumo' => $norma->resumo,
                'orgao' => $norma->orgao->orgao ?? 'N/A',
                'tipo' => $norma->tipo->tipo ?? 'N/A',
                'vigente' => $norma->vigente,
                'vigente_class' => $norma->vigente_class,
                'vigente_icon' => $norma->vigente_icon,
                'anexo' => $norma->anexo,
                'anexo_url' => asset('storage/normas/' . $norma->anexo),
                'palavras_chave' => $norma->palavrasChave->take(3)->map(function($pc) {
                    return ['id' => $pc->id, 'palavra_chave' => $pc->palavra_chave];
                }),
                'palavras_chave_restantes' => $norma->palavrasChave->count() > 3 ? 
                    $norma->palavrasChave->count() - 3 : 0
            ];
            
            // Adicionar informações de auditoria apenas se o usuário tiver permissão
            if ($showAudit && $norma->usuario) {
                $normaData['auditoria'] = [
                    'usuario_nome' => $norma->usuario->name,
                    'usuario_matricula' => $norma->usuario->matricula,
                    'data_cadastro' => $norma->created_at ? $norma->created_at->format('d/m/Y H:i') : null
                ];
            }
            
            return $normaData;
        });
        
        return response()->json([
            'normas' => $formattedNormas,
            'pagination' => [
                'total' => $normas->total(),
                'per_page' => $normas->perPage(),
                'current_page' => $normas->currentPage(),
                'last_page' => $normas->lastPage(),
                'from' => $normas->firstItem(),
                'to' => $normas->lastItem()
            ],
            'filters_applied' => $this->getAppliedFiltersInfo($request),
            'show_audit' => $showAudit, // Informar ao frontend se deve mostrar auditoria
            'debug' => [
                'data_inicio' => $request->data_inicio,
                'data_fim' => $request->data_fim,
                'vigente' => $request->vigente,
                'total_encontrado' => $normas->total()
            ]
        ]);
        
    } catch (\Exception $e) {            
        return response()->json([
            'error' => 'Erro ao carregar normas: ' . $e->getMessage(),
            'trace' => config('app.debug') ? $e->getTraceAsString() : null
        ], 500);
    }
}
    
    /**
     * Retorna informações sobre os filtros aplicados
     */
    private function getAppliedFiltersInfo(Request $request)
    {
        $filters = [];
        
        if ($request->filled('search_term')) {
            $filters[] = 'Termo de busca: ' . $request->search_term;
        }
        
        if ($request->filled('tipo_id')) {
            $tipo = Tipo::find($request->tipo_id);
            if ($tipo) {
                $filters[] = 'Tipo: ' . $tipo->tipo;
            }
        }
        
        if ($request->filled('orgao_id')) {
            $orgao = Orgao::find($request->orgao_id);
            if ($orgao) {
                $filters[] = 'Órgão: ' . $orgao->orgao;
            }
        }

        if ($request->filled('vigente')) {
            $filters[] = 'Vigência: ' . $request->vigente;
        }
        
        if ($request->filled('data_inicio')) {
            $filters[] = 'A partir de: ' . Carbon::createFromFormat('Y-m-d', $request->data_inicio)->format('d/m/Y');
        }
        
        if ($request->filled('data_fim')) {
            $filters[] = 'Até: ' . Carbon::createFromFormat('Y-m-d', $request->data_fim)->format('d/m/Y');
        }
        
        return $filters;
    }

    /**
     * Exibe o formulário para criar uma nova norma
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            $tipos = Tipo::where('status', true)->orderBy('tipo')->get();
            $publicidades = Publicidade::where('status', true)->orderBy('publicidade')->get();
            $orgaos = Orgao::where('status', true)->orderBy('orgao')->get();
            $palavras_chave = PalavraChave::where('status', true)->orderBy('palavra_chave')->get();
                
            return view('normas.norma_create', compact('tipos', 'orgaos', 'publicidades', 'palavras_chave'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de criação: ' . $e->getMessage());
            return back()->withErrors(['Erro ao carregar o formulário. Por favor, tente novamente.']);
        }
    }

    /**
     * Exibe o formulário para editar uma norma
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            $norma = Norma::with([
                'publicidade:id,publicidade', 
                'orgao:id,orgao', 
                'tipo:id,tipo', 
                'palavrasChave'
            ])
            ->where('id', $id)
            ->ativas()
            ->firstOrFail();
            
            $tipos = Tipo::where('status', true)->orderBy('tipo')->get();
            $publicidades = Publicidade::where('status', true)->orderBy('publicidade')->get();
            $orgaos = Orgao::where('status', true)->orderBy('orgao')->get();
            $palavra_chaves = PalavraChave::where('status', true)->orderBy('palavra_chave')->get();

            return view('normas.norma_edit', compact('norma', 'tipos', 'orgaos', 'publicidades', 'palavra_chaves'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('normas.norma_list')->withErrors(['Norma não encontrada.']);
        } catch (\Exception $e) {
            Log::error('Erro ao editar norma: ' . $e->getMessage());
            return back()->withErrors(['Erro ao carregar formulário de edição. Por favor, tente novamente.']);
        }
    }

    /**
     * Armazena uma nova norma no banco de dados
     *
     * @param CreateNormaRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateNormaRequest $request)
    {
        DB::beginTransaction();
        try {
            // Validar arquivo
            if (!$request->hasFile('anexo') || !$request->file('anexo')->isValid()) {
                return back()
                    ->withInput()
                    ->withErrors(['anexo' => 'Erro ao fazer o upload do arquivo!']);
            }
            
            // Processar e armazenar o arquivo
            $file = $request->file('anexo');
            $nameFile = Str::uuid() . "." . $file->extension();
            $file->storeAs('public/normas', $nameFile);
            
            // Criar a norma
            $norma = Norma::create([
                'usuario_id' => auth()->user()->id,
                'data' => $request->data,
                'descricao' => $request->descricao,
                'anexo' => $nameFile,
                'resumo' => $request->resumo,
                'publicidade_id' => $request->publicidade,
                'tipo_id' => $request->tipo,
                'orgao_id' => $request->orgao,
                'vigente' => $request->vigente ?? Norma::VIGENTE_VIGENTE,
                'status' => true
            ]);
            
            // Array para armazenar IDs de palavras-chave para vincular
            $palavrasChaveIds = [];
            
            // Processar palavras-chave existentes selecionadas
            if ($request->has('palavras_chave') && is_array($request->palavras_chave)) {
                $palavrasChaveIds = array_merge($palavrasChaveIds, $request->palavras_chave);
            }
            
            // Processar novas palavras-chave
            if ($request->has('novas_palavras_chave') && !empty($request->novas_palavras_chave)) {
                try {
                    $novasPalavrasChave = json_decode($request->novas_palavras_chave, true);
                    
                    if (is_array($novasPalavrasChave) && count($novasPalavrasChave) > 0) {
                        foreach ($novasPalavrasChave as $palavraChave) {
                            // Limpar e validar a entrada
                            $palavraChave = trim($palavraChave);
                            if (empty($palavraChave)) continue;
                            
                            // Verificar se esta palavra-chave já existe
                            $existente = PalavraChave::where('palavra_chave', 'ILIKE', $palavraChave)
                                ->where('status', true)
                                ->first();
                            
                            if ($existente) {
                                // Se existir, adicione o ID ao array
                                $palavrasChaveIds[] = $existente->id;
                            } else {
                                // Se não existir, crie-a e adicione o ID ao array
                                $novaPalavraChave = PalavraChave::create([
                                    'usuario_id' => auth()->user()->id,
                                    'palavra_chave' => $palavraChave,
                                    'status' => true
                                ]);
                                
                                $palavrasChaveIds[] = $novaPalavraChave->id;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Erro ao processar novas palavras-chave: ' . $e->getMessage());
                }
            }
            
            // Remover duplicatas
            $palavrasChaveIds = array_unique($palavrasChaveIds);
            
            // Vincular todas as palavras-chave à norma
            if (!empty($palavrasChaveIds)) {
                foreach ($palavrasChaveIds as $palavraChaveId) {
                    NormaChave::create([
                        'norma_id' => $norma->id,
                        'palavra_chave_id' => $palavraChaveId,
                        'status' => true
                    ]);
                }
            }
            
            DB::commit();
            return redirect()->route('normas.norma_list')->with('success', 'Norma cadastrada com sucesso!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cadastrar norma: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['Erro ao cadastrar norma: ' . $e->getMessage()]);
        }
    }

    /**
     * Atualiza uma norma existente
     *
     * @param UpdateNormaRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateNormaRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            // Obter a norma
            $norma = Norma::where('id', $id)
                ->ativas()
                ->firstOrFail();
            
            $mensagens = []; // Para armazenar mensagens de sucesso

            // Processar exclusão de palavra-chave (se solicitado)
            if ($request->has('delete_palavra_chave')) {
                $normaChave = NormaChave::where('norma_id', $id)
                    ->where('palavra_chave_id', $request->delete_palavra_chave)
                    ->where('status', true)
                    ->first();
                    
                if ($normaChave) {
                    $normaChave->status = false;
                    $normaChave->save();
                    $mensagens[] = 'Palavra-chave desvinculada com sucesso!';
                }
            }
            
            // Processar adição de palavras-chave
            $adicionouPalavras = false;
            
            // Processar palavras-chave existentes selecionadas
            if ($request->has('add_palavra_chave') && is_array($request->add_palavra_chave) && count($request->add_palavra_chave) > 0) {
                foreach ($request->add_palavra_chave as $palavraChaveId) {
                    // Verificar se já existe um registro (mesmo que inativo)
                    $existente = NormaChave::where('norma_id', $id)
                        ->where('palavra_chave_id', $palavraChaveId)
                        ->first();
                        
                    if ($existente) {
                        // Se existir, apenas atualiza para ativo
                        if (!$existente->status) {
                            $existente->status = true;
                            $existente->save();
                            $adicionouPalavras = true;
                        }
                    } else {
                        // Se não existir, cria novo
                        NormaChave::create([
                            'norma_id' => $id,
                            'palavra_chave_id' => $palavraChaveId,
                            'status' => true
                        ]);
                        $adicionouPalavras = true;
                    }
                }
            }
            
            // Processar novas palavras-chave
            if ($request->has('novas_palavras_chave') && !empty($request->novas_palavras_chave)) {
                $novasPalavrasChave = json_decode($request->novas_palavras_chave, true);
                
                if (is_array($novasPalavrasChave) && count($novasPalavrasChave) > 0) {
                    foreach ($novasPalavrasChave as $palavraChave) {
                        // Limpar e validar a entrada
                        $palavraChave = trim($palavraChave);
                        if (empty($palavraChave)) continue;
                        
                        // Verificar se esta palavra-chave já existe
                        $existente = PalavraChave::where('palavra_chave', 'ILIKE', $palavraChave)
                            ->where('status', true)
                            ->first();
                        
                        if ($existente) {
                            // Verificar se já está vinculada à norma
                            $normaChaveExistente = NormaChave::where('norma_id', $id)
                                ->where('palavra_chave_id', $existente->id)
                                ->first();
                                
                            if ($normaChaveExistente) {
                                // Se existir, apenas atualiza para ativo
                                if (!$normaChaveExistente->status) {
                                    $normaChaveExistente->status = true;
                                    $normaChaveExistente->save();
                                    $adicionouPalavras = true;
                                }
                            } else {
                                // Se não existir, cria novo vínculo
                                NormaChave::create([
                                    'norma_id' => $id,
                                    'palavra_chave_id' => $existente->id,
                                    'status' => true
                                ]);
                                $adicionouPalavras = true;
                            }
                        } else {
                            // Se não existir, crie e vincule
                            $novaPalavraChave = PalavraChave::create([
                                'usuario_id' => auth()->user()->id,
                                'palavra_chave' => $palavraChave,
                                'status' => true
                            ]);
                            
                            NormaChave::create([
                                'norma_id' => $id,
                                'palavra_chave_id' => $novaPalavraChave->id,
                                'status' => true
                            ]);
                            $adicionouPalavras = true;
                        }
                    }
                }
            }
            
            if ($adicionouPalavras) {
                $mensagens[] = 'Palavras-chave vinculadas com sucesso!';
            }
            
            // Atualizar dados da norma
            $atualizouNorma = false;
            
            if ($request->has('data')) {
                $norma->data = $request->data;
                $atualizouNorma = true;
            }
            
            if ($request->has('descricao')) {
                $norma->descricao = $request->descricao;
                $atualizouNorma = true;
            }
            
            if ($request->has('resumo')) {
                $norma->resumo = $request->resumo;
                $atualizouNorma = true;
            }
            
            if ($request->has('publicidade')) {
                $norma->publicidade_id = $request->publicidade;
                $atualizouNorma = true;
            }
            
            if ($request->has('tipo')) {
                $norma->tipo_id = $request->tipo;
                $atualizouNorma = true;
            }
            
            if ($request->has('orgao')) {
                $norma->orgao_id = $request->orgao;
                $atualizouNorma = true;
            }

            // Atualizar status de vigência
            if ($request->has('vigente') && in_array($request->vigente, array_keys(Norma::getVigenteOptions()))) {
                $norma->vigente = $request->vigente;
                $atualizouNorma = true;
            }
            
            // Processar upload de arquivo (se enviado)
            if (($request->hasFile('anexo')) && ($request->file('anexo')->isValid())) {
                // Excluir arquivo anterior
                if ($norma->anexo && Storage::exists('public/normas/'.$norma->anexo)) {
                    Storage::delete('public/normas/'.$norma->anexo);
                }
                
                // Criar um hash para renomear o arquivo
                $nameFile = Str::uuid() . "." . $request->anexo->extension();
                
                // Salvar o arquivo com o novo nome
                $request->file('anexo')->storeAs('public/normas', $nameFile);
                $norma->anexo = $nameFile;
                $atualizouNorma = true;
            }
            
            // Salvar a norma se algo foi alterado
            if ($atualizouNorma) {
                $norma->save();
                $mensagens[] = 'Informações da norma atualizadas com sucesso!';
            }
            
            DB::commit();
            
            // Determinar mensagem de sucesso
            $mensagemFinal = count($mensagens) > 0 ? implode(' ', $mensagens) : 'Nenhuma alteração foi realizada.';

            // Redirecionar de acordo com o tipo de operação
            if ($request->has('delete_palavra_chave') || $request->has('add_palavra_chave') || $request->has('novas_palavras_chave')) {
                // Se foi uma operação em palavras-chave, redireciona de volta para a tela de edição
                return redirect()->route('normas.norma_edit', ['id' => $id])
                                ->with('success', $mensagemFinal);
            } else {
                // Se foi uma edição normal, redireciona para a lista
                return redirect()->route('normas.norma_list')
                                ->with('success', $mensagemFinal);
            }
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()->route('normas.norma_list')->withErrors(['Norma não encontrada.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar norma: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['Erro ao atualizar norma: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Executa soft delete em uma norma
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $norma = Norma::where('id', $id)
                ->ativas()
                ->firstOrFail();
            
            $norma->status = false;
            $norma->save();
            
            return redirect()->route('normas.norma_list')->with('success', 'Norma removida com sucesso!');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('normas.norma_list')->withErrors(['Norma não encontrada.']);
        } catch (\Exception $e) {
            Log::error('Erro ao remover norma: ' . $e->getMessage());
            return back()->withErrors(['Erro ao remover norma. Por favor, tente novamente.']);
        }
    }

    //Busca com relevância (pesquisas exatas aparecem primeiro)
    private function applySearchWithRelevance($query, $searchTerm)
{
    $searchTerm = trim($searchTerm);
    
    if (empty($searchTerm)) {
        return $query;
    }
    
    // Dividir em palavras e filtrar palavras muito pequenas
    $words = array_filter(array_map('trim', explode(' ', $searchTerm)), function($word) {
        return strlen($word) >= 2;
    });
    
    if (empty($words)) {
        return $query;
    }
    
    // Criar subquery para calcular relevância
    $relevanceSelect = $this->buildRelevanceScore($words, $searchTerm);
    
    return $query->select('normas.*')
                ->selectRaw("({$relevanceSelect}) as relevance_score")
                ->where(function($q) use ($words, $searchTerm) {
                    // Busca nos campos principais
                    foreach ($words as $word) {
                        $q->where(function($wordQuery) use ($word) {
                            $wordQuery->where('descricao', 'ILIKE', "%{$word}%")
                                     ->orWhere('resumo', 'ILIKE', "%{$word}%");
                        });
                    }
                    
                    // busca nas palavras-chave
                    $q->orWhereHas('palavrasChave', function($subq) use ($words) {
                        foreach ($words as $word) {
                            $subq->where('palavra_chave', 'ILIKE', "%{$word}%");
                        }
                    });
                })
                ->orderByRaw('relevance_score DESC')
                ->orderBy('data', 'desc');
}

/**
 * Constrói a query de score de relevância
 */
private function buildRelevanceScore($words, $fullTerm)
{
    $scoreQueries = [];
    
    // Score para frase exata (busca primeiro)
    $scoreQueries[] = "CASE WHEN descricao ILIKE '%{$fullTerm}%' THEN 10 ELSE 0 END";
    $scoreQueries[] = "CASE WHEN resumo ILIKE '%{$fullTerm}%' THEN 8 ELSE 0 END";
    
    // Score para palavras individuais
    foreach ($words as $index => $word) {
        $weight = max(1, 5 - $index); // Primeiras palavras têm peso maior
        $scoreQueries[] = "CASE WHEN descricao ILIKE '%{$word}%' THEN {$weight} ELSE 0 END";
        $scoreQueries[] = "CASE WHEN resumo ILIKE '%{$word}%' THEN " . ($weight - 1) . " ELSE 0 END";
    }
    
    return implode(' + ', $scoreQueries);
}

}