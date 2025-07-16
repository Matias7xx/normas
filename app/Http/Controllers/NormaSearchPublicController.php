<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Norma;
use App\Models\Orgao;
use App\Models\Tipo;
use Inertia\Inertia;

class NormaSearchPublicController extends Controller
{
    public function search(Request $request)
    {
        // Carregar dados para os filtros
        $orgaos = Orgao::where('status', true)->orderBy('orgao')->get();
        $tipos = Tipo::where('status', true)->orderBy('tipo')->get();
        
        // Inicializar query com relacionamentos
        $query = Norma::with(['orgao', 'tipo', 'palavrasChave'])
                     ->where('status', true); // Apenas normas ativas
        
        // Por padrão, mostrar apenas normas VIGENTES
        if (!$request->hasAny(['search_term', 'tipo_id', 'orgao_id', 'vigente'])) {
            $query->where('vigente', 'VIGENTE');
        } else {
            // Aplicar filtros apenas se fornecidos
            if ($request->filled('search_term')) {
                $searchTerm = $request->search_term;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('descricao', 'ILIKE', "%{$searchTerm}%")
                      ->orWhere('resumo', 'ILIKE', "%{$searchTerm}%")
                      ->orWhereHas('palavrasChave', function($subQuery) use ($searchTerm) {
                          $subQuery->where('palavra_chave', 'ILIKE', "%{$searchTerm}%")
                                   ->where('normas_chaves.status', true);
                      });
                });
            }
            
            if ($request->filled('tipo_id')) {
                $query->where('tipo_id', $request->tipo_id);
            }
            
            if ($request->filled('orgao_id')) {
                $query->where('orgao_id', $request->orgao_id);
            }
            
            if ($request->filled('vigente')) {
                $query->where('vigente', $request->vigente);
            }
        }
        
        // Debug: Contar total antes da paginação
        $totalCount = $query->count();
        
        // Executar busca com ordenação
        $norma_pesquisa = $query->orderBy('data', 'desc')
                               ->orderBy('id', 'desc')
                               ->paginate(15)
                               ->appends($request->query());        
        return view('public_search.norma_search', compact(
            'norma_pesquisa',
            'orgaos',
            'tipos'
        ));
    }

    public function inicio () {
    return Inertia::render('Inicio');
}
    
    /**
     * Método AJAX para carregar normas na consulta pública
     */
    public function searchAjax(Request $request)
    {
        try {
            // Base query para consulta pública
            $query = Norma::with(['orgao:id,orgao', 'tipo:id,tipo', 'palavrasChave:id,palavra_chave'])
                          ->where('status', true); // Apenas normas ativas
            
            // Por padrão, mostrar apenas normas VIGENTES
            if (!$request->hasAny(['search_term', 'tipo_id', 'orgao_id', 'vigente'])) {
                $query->where('vigente', 'VIGENTE');
            } else {
                // Aplicar filtros apenas se fornecidos
                if ($request->filled('search_term')) {
                    $searchTerm = $request->search_term;
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('descricao', 'ILIKE', "%{$searchTerm}%")
                          ->orWhere('resumo', 'ILIKE', "%{$searchTerm}%")
                          ->orWhereHas('palavrasChave', function($subQuery) use ($searchTerm) {
                              $subQuery->where('palavra_chave', 'ILIKE', "%{$searchTerm}%")
                                       ->where('normas_chaves.status', true);
                          });
                    });
                }
                
                if ($request->filled('tipo_id')) {
                    $query->where('tipo_id', $request->tipo_id);
                }
                
                if ($request->filled('orgao_id')) {
                    $query->where('orgao_id', $request->orgao_id);
                }
                
                if ($request->filled('vigente')) {
                    $query->where('vigente', $request->vigente);
                }
                
                // Filtros de data
                if ($request->filled('data_inicio')) {
                    $query->where('data', '>=', $request->data_inicio);
                }
                
                if ($request->filled('data_fim')) {
                    $query->where('data', '<=', $request->data_fim);
                }
            }
            
            // Ordenação
            $orderBy = $request->input('order_by', 'data');
            $orderDir = $request->input('order_dir', 'desc');
            $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? strtolower($orderDir) : 'desc';
            
            switch ($orderBy) {
                case 'id':
                    $query->orderBy('id', $orderDir);
                    break;
                case 'data':
                    $query->orderBy('data', $orderDir)->orderBy('id', $orderDir);
                    break;
                case 'descricao':
                    $query->orderBy('descricao', $orderDir);
                    break;
                case 'vigente':
                    $query->orderByRaw("
                        CASE vigente 
                            WHEN 'VIGENTE' THEN 1 
                            WHEN 'EM ANÁLISE' THEN 2 
                            WHEN 'NÃO VIGENTE' THEN 3 
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
            
            // Preparar dados para exibição (adaptado para consulta pública)
            $formattedNormas = $normas->map(function($norma) {
                return [
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
                    'anexo_url' => $norma->hasAnexo() ? $norma->anexo_url : null,
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
                ],
                'filters_applied' => $this->getAppliedFiltersInfo($request)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro na consulta pública AJAX: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erro ao carregar normas: ' . $e->getMessage(),
                'normas' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => 15,
                    'current_page' => 1,
                    'last_page' => 1,
                    'from' => null,
                    'to' => null
                ]
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
        
        if ($request->filled('data_inicio')) {
            $filters[] = 'A partir de: ' . \Carbon\Carbon::createFromFormat('Y-m-d', $request->data_inicio)->format('d/m/Y');
        }
        
        if ($request->filled('data_fim')) {
            $filters[] = 'Até: ' . \Carbon\Carbon::createFromFormat('Y-m-d', $request->data_fim)->format('d/m/Y');
        }
        
        return $filters;
    }
}