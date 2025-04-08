<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateNormaRequest;
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

    class NormaController extends Controller
    {
        /**
         * Exibe a listagem de normas agrupadas por tipo
         * @return \Illuminate\View\View
         */
        public function index(Request $request)
    {   
        try {
            $servidor = Servidor::where('matricula', Auth::user()->matricula)->first();
            
            // Obter listas para filtros
            $tipos = Tipo::where('status', true)->orderBy('tipo')->get();
            $orgaos = Orgao::where('status', true)->orderBy('orgao')->get();
            
            // Para a primeira carga, é enviada a página vazia com os filtros
            return view('normas.norma_list', compact('servidor', 'tipos', 'orgaos'));
            
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
            'palavrasChave:id,palavra_chave'
        ])
        ->ativas();
        
        // Aplicar filtros de pesquisa
        if ($request->filled('search_term')) {
            $query->pesquisaGeral($request->search_term);
        }
        
        if ($request->filled('tipo_id')) {
            $query->porTipo($request->tipo_id);
        }
        
        if ($request->filled('orgao_id')) {
            $query->porOrgao($request->orgao_id);
        }
        
        // Tratamento de ordenação
        $orderBy = $request->input('order_by', 'data');
        $orderDir = $request->input('order_dir', 'desc');
        
        // Aplicar ordenação em órgão e tipo de norma
        switch ($orderBy) {
            case 'id':
                // Ordenação específica por ID
                $query->orderBy('id', $orderDir);
                break;
            case 'orgao':
                // Ordenar pelo nome do órgão usando subconsulta
                $query->orderBy(function($query) {
                    $query->select('orgao')
                          ->from('orgaos')
                          ->whereColumn('orgaos.id', 'normas.orgao_id')
                          ->limit(1);
                }, $orderDir);
                break;
            case 'tipo':
                // Ordenar pelo nome do tipo usando subconsulta
                $query->orderBy(function($query) {
                    $query->select('tipo')
                          ->from('tipos')
                          ->whereColumn('tipos.id', 'normas.tipo_id')
                          ->limit(1);
                }, $orderDir);
                break;
            default:
                // Ordenação padrão para campos diretos (data, norma, resumo)
                $query->orderBy($orderBy, $orderDir);
                break;
        }
        
        // Paginação
        $perPage = $request->input('per_page', 15);
        $normas = $query->paginate($perPage);
        
        // Preparar dados para exibição
        $formattedNormas = $normas->map(function($norma) {
            return [
                'id' => $norma->id,
                'data' => $norma->data ? $norma->data->format('d/m/Y') : null,
                'descricao' => $norma->descricao,
                'resumo' => $norma->resumo,
                'orgao' => $norma->orgao->orgao ?? 'N/A',
                'tipo' => $norma->tipo->tipo ?? 'N/A',
                'anexo' => $norma->anexo,
                'palavras_chave' => $norma->palavrasChave->take(3)->map(function($pc) {
                    return ['id' => $pc->id, 'palavra_chave' => $pc->palavra_chave];
                }),
                'palavras_chave_restantes' => $norma->palavrasChave->count() > 3 ? 
                    $norma->palavrasChave->count() - 3 : 0
            ];
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
            ]
        ]);
    } catch (\Exception $e) {
        \Log::error('Erro ao listar normas via Ajax: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        return response()->json([
            'error' => 'Erro ao carregar normas: ' . $e->getMessage(),
            'trace' => config('app.debug') ? $e->getTraceAsString() : null
        ], 500);
    }
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
                return back()->withErrors(['anexo' => 'Erro ao fazer o upload do arquivo!']);
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
            return back()->withInput()->withErrors(['Erro ao cadastrar norma: ' . $e->getMessage()]);
        }
    }

    /**
     * Atualiza uma norma existente
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Obter a norma
            $norma = Norma::where('id', $id)
                ->ativas()
                ->firstOrFail();
            
            $mensagens = []; // Para armazenar mensagens de sucesso

            // Processar exclusão de palavra-chave (se solicitado)
            if (isset($request->delete_palavra_chave)) {
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
        if (isset($request->delete_palavra_chave) || isset($request->add_palavra_chave) || !empty($request->novas_palavras_chave)) {
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
            return back()->withErrors(['Erro ao atualizar norma: ' . $e->getMessage()]);
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

    /**
     * Exibe a interface de pesquisa de normas
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        try {
            $tipo = Tipo::where('status', true)
                ->orderBy('tipo')
                ->get();

            $orgao = Orgao::where('status', true)
                ->orderBy('orgao')
                ->get();

            // Iniciar a query base
            $query = Norma::with(['publicidade', 'orgao', 'tipo', 'palavrasChave'])
                ->ativas()
                ->orderBy('data', 'desc');

            // Aplicar filtros
            if ($request->filled('norma')) {
                $query->where('descricao', 'ILIKE', "%{$request->norma}%");
            }
            
            if ($request->filled('resumo')) {
                $query->where('resumo', 'ILIKE', "%{$request->resumo}%");
            }
            
            if ($request->filled('orgao')) {
                $query->porOrgao($request->orgao);
            }
            
            if ($request->filled('tipo')) {
                $query->porTipo($request->tipo);
            }
            
            if ($request->filled('palavra_chave')) {
                $query->porPalavraChave($request->palavra_chave);
            }
            
            if ($request->filled('descricao')) {
                $termos = explode(' ', $request->descricao);
                foreach ($termos as $termo) {
                    $termo = trim($termo);
                    if (!empty($termo)) {
                        $query->where('descricao', 'ILIKE', "%{$termo}%");
                    }
                }
            }
            
            // Para conjuntos muito grandes, podemos usar paginação
            $temFiltros = $request->filled('norma') || $request->filled('resumo') || 
                        $request->filled('orgao') || $request->filled('tipo') || 
                        $request->filled('palavra_chave') || $request->filled('descricao');
                        
            if ($temFiltros) {
                $norma_pesquisa = $query->get();
            } else {
                $norma_pesquisa = $query->paginate(100);
            }
            
            return view('normas.norma_pesquisa', compact('norma_pesquisa', 'tipo', 'orgao'));
        } catch (\Exception $e) {
            Log::error('Erro na pesquisa de normas: ' . $e->getMessage());
            return back()->withErrors(['Erro ao realizar pesquisa. Por favor, tente novamente.']);
        }
    }
}