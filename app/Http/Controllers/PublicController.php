<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Norma;
use App\Models\Tipo;
use App\Models\Orgao;
use App\Models\User;
use App\Models\PalavraChave;

class PublicController extends Controller
{
    /**
     * Página inicial da SPA
     */
    public function home()
    {
        $stats = $this->getSystemStats();
        
        return Inertia::render('Home', [
            'stats' => $stats,
            'page' => 'home'
        ]);
    }

    /**
     * Página de consulta de normas
     */
    public function consulta(Request $request)
    {
        $tipos = Tipo::where('status', true)->orderBy('tipo')->get();
        $orgaos = Orgao::where('status', true)->orderBy('orgao')->get();
        
        $filtros = $request->only([
            'search_term', 'tipo_id', 'orgao_id', 'vigente', 
            'data_inicio', 'data_fim', 'page'
        ]);

        $normas = null;
        
        // Se há parâmetros de busca, realizar a consulta
        if ($request->anyFilled(['search_term', 'tipo_id', 'orgao_id', 'vigente', 'data_inicio', 'data_fim'])) {
            $normas = $this->performSearch($request);
        }

        return Inertia::render('Consulta', [
            'tipos' => $tipos,
            'orgaos' => $orgaos,
            'normas' => $normas,
            'filtros' => $filtros,
            'stats' => $this->getSystemStats(),
            'page' => 'consulta'
        ]);
    }

    /**
     * Visualização de uma norma específica
     */
    public function normaView($id)
    {
        $norma = Norma::with(['tipo', 'orgao', 'palavrasChave'])
            ->where('status', true)
            ->findOrFail($id);

        // Buscar normas relacionadas
        $relacionadas = Norma::where('status', true)
            ->where('id', '!=', $id)
            ->where(function($query) use ($norma) {
                $query->where('tipo_id', $norma->tipo_id)
                      ->orWhere('orgao_id', $norma->orgao_id);
            })
            ->with(['tipo', 'orgao'])
            ->limit(5)
            ->get();

        return Inertia::render('NormaView', [
            'norma' => $norma,
            'relacionadas' => $relacionadas,
            'stats' => $this->getSystemStats(),
            'page' => 'norma'
        ]);
    }

    /**
     * Visualização de PDF da norma (para iframe)
     */
    public function viewNorma($id)
    {
        $norma = Norma::where('status', true)->findOrFail($id);
        
        if (!$norma->anexo) {
            abort(404, 'Arquivo não encontrado');
        }

        // Usar o storage disk public para localizar o arquivo
        $filePath = storage_path('app/public/normas/' . $norma->anexo);
        
        // Verificar se o arquivo existe
        if (!file_exists($filePath)) {
            abort(404, 'Arquivo PDF não encontrado: ' . $norma->anexo);
        }

        // Retornar o PDF com headers
        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $norma->anexo . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    /**
     * Download de arquivo da norma
     */
    public function downloadNorma($id)
    {
        $norma = Norma::where('status', true)->findOrFail($id);
        
        if (!$norma->anexo) {
            abort(404, 'Arquivo não encontrado');
        }

        // Usar o storage disk public para localizar o arquivo
        $filePath = storage_path('app/public/normas/' . $norma->anexo);
        
        // Verificar se o arquivo existe
        if (!file_exists($filePath)) {
            abort(404, 'Arquivo não encontrado no servidor: ' . $norma->anexo);
        }

        // Gerar nome do arquivo para download
        $fileName = $norma->numero_norma 
            ? sanitize_filename($norma->numero_norma) . '.pdf'
            : sanitize_filename($norma->descricao) . '.pdf';

        // Forçar download do arquivo
        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * API para busca de normas (AJAX)
     */
    public function searchApi(Request $request)
    {
        $normas = $this->performSearch($request);
        
        return response()->json([
            'success' => true,
            'data' => $normas
        ]);
    }

    /**
     * API para obter estatísticas
     */
    public function getStats()
    {
        return response()->json($this->getSystemStats());
    }

    /**
     * API para obter tipos
     */
    public function getTipos()
    {
        $tipos = Tipo::where('status', true)->orderBy('tipo')->get();
        return response()->json($tipos);
    }

    /**
     * API para obter órgãos
     */
    public function getOrgaos()
    {
        $orgaos = Orgao::where('status', true)->orderBy('orgao')->get();
        return response()->json($orgaos);
    }

    /**
     * Realizar busca de normas
     */
    private function performSearch(Request $request)
    {
        $query = Norma::query()
            ->with(['tipo', 'orgao', 'palavrasChave'])
            ->where('status', true);

        // Busca por termo
        if ($request->filled('search_term')) {
            $term = $request->search_term;
            $query->where(function($q) use ($term) {
                $q->where('descricao', 'LIKE', "%{$term}%")
                  ->orWhere('resumo', 'LIKE', "%{$term}%")
                  ->orWhere('numero_norma', 'LIKE', "%{$term}%")
                  ->orWhereHas('palavrasChave', function($pq) use ($term) {
                      $pq->where('palavra_chave', 'LIKE', "%{$term}%");
                  });
            });
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

        // Filtro por data
        if ($request->filled('data_inicio')) {
            $query->where('data', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->where('data', '<=', $request->data_fim);
        }

        // Ordenação
        $query->orderBy('data', 'desc');

        // Paginação
        $normas = $query->paginate(10);

        // Processar palavras-chave para exibição
        $normas->getCollection()->transform(function ($norma) {
            $palavrasChave = $norma->palavrasChave;
            
            if ($palavrasChave->count() > 3) {
                $norma->palavras_chave = $palavrasChave->take(3);
                $norma->palavras_chave_restantes = $palavrasChave->count() - 3;
            } else {
                $norma->palavras_chave = $palavrasChave;
                $norma->palavras_chave_restantes = 0;
            }
            
            return $norma;
        });

        return $normas;
    }

    /**
     * Obter estatísticas do sistema
     */
    private function getSystemStats()
    {
        return [
            'total_normas' => Norma::where('status', true)->count(),
            'normas_vigentes' => Norma::where('status', true)
                ->where('vigente', 'VIGENTE')->count(),
            'tipos_count' => Tipo::where('status', true)->count(),
            'orgaos_count' => Orgao::where('status', true)->count(),
            'usuarios_ativos' => User::where('active', true)->count(),
            'normas_cadastradas' => Norma::where('status', true)->count(),
            'em_analise' => Norma::where('status', true)
                ->where('vigente', 'EM ANÁLISE')->count(),
            'nao_vigentes' => Norma::where('status', true)
                ->where('vigente', 'NÃO VIGENTE')->count(),
        ];
    }
}

/**
 * Função auxiliar para sanitizar nome de arquivo
 */
if (!function_exists('sanitize_filename')) {
    function sanitize_filename($filename) {
        // Remove caracteres especiais e substitui espaços por underscores
        $filename = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $filename);
        $filename = preg_replace('/_+/', '_', $filename);
        return trim($filename, '_');
    }
}