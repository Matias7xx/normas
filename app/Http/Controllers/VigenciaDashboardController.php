<?php

namespace App\Http\Controllers;

use App\Models\Norma;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VigenciaDashboardController extends Controller
{
  /**
   * Exibe o dashboard de vigência das normas
   */
  public function index()
  {
    // Estatísticas gerais
    $stats = Norma::obterEstatisticasVigenciaProgramada();

    // Normas que devem ser atualizadas hoje
    $normasParaHoje = Norma::with(['tipo', 'orgao'])
      ->ativas()
      ->paraAtualizarHoje()
      ->orderBy('data_limite_vigencia')
      ->get();

    // Normas vencendo nos próximos 7 dias
    $normasProximos7Dias = Norma::with(['tipo', 'orgao'])
      ->ativas()
      ->vencendoEm(7)
      ->orderBy('data_limite_vigencia')
      ->get();

    // Normas vencendo nos próximos 30 dias
    $normasProximos30Dias = Norma::with(['tipo', 'orgao'])
      ->ativas()
      ->vencendoEm(30)
      ->orderBy('data_limite_vigencia')
      ->get();

    // Normas atrasadas (que deveriam ter mudado de status)
    $normasAtrasadas = Norma::with(['tipo', 'orgao'])
      ->ativas()
      ->comVigenciaProgramada(-365)
      ->whereDate('data_limite_vigencia', '<', now())
      ->orderBy('data_limite_vigencia')
      ->get();

    return view(
      'vigencia.dashboard',
      compact(
        'stats',
        'normasParaHoje',
        'normasProximos7Dias',
        'normasProximos30Dias',
        'normasAtrasadas',
      ),
    );
  }

  /**
   * Executa manualmente a atualização de vigência
   */
  public function executarAtualizacao(Request $request)
  {
    $dryRun = $request->input('dry_run', false);

    try {
      // Executar o comando via Artisan
      \Artisan::call('normas:atualizar-vigencia', [
        '--dry-run' => $dryRun,
      ]);

      $output = \Artisan::output();

      if ($dryRun) {
        return response()->json([
          'success' => true,
          'message' => 'Simulação executada com sucesso',
          'output' => $output,
        ]);
      } else {
        return response()->json([
          'success' => true,
          'message' => 'Atualização executada com sucesso',
          'output' => $output,
        ]);
      }
    } catch (\Exception $e) {
      return response()->json(
        [
          'success' => false,
          'message' => 'Erro ao executar atualização: ' . $e->getMessage(),
        ],
        500,
      );
    }
  }

  /**
   * Retorna dados para gráficos via Ajax
   */
  public function getDadosGraficos()
  {
    // Dados para o próximo mês
    $proximoMes = [];
    for ($i = 0; $i < 30; $i++) {
      $data = now()->addDays($i);
      $count = Norma::ativas()
        ->where('vigencia_indeterminada', false)
        ->whereDate('data_limite_vigencia', $data)
        ->count();

      $proximoMes[] = [
        'data' => $data->format('d/m'),
        'count' => $count,
      ];
    }

    // Distribuição por status atual
    $distribuicaoPorStatus = [
      'vigente' => Norma::ativas()->vigentes()->count(),
      'nao_vigente' => Norma::ativas()->naoVigentes()->count(),
      'em_analise' => Norma::ativas()->emAnalise()->count(),
    ];

    // Normas com vigência programada por órgão
    $porOrgao = Norma::ativas()
      ->join('orgaos', 'normas.orgao_id', '=', 'orgaos.id')
      ->where('vigencia_indeterminada', false)
      ->whereNotNull('data_limite_vigencia')
      ->selectRaw('orgaos.orgao, COUNT(*) as total')
      ->groupBy('orgaos.id', 'orgaos.orgao')
      ->orderByDesc('total')
      ->limit(10)
      ->get();

    return response()->json([
      'proximo_mes' => $proximoMes,
      'distribuicao_status' => $distribuicaoPorStatus,
      'por_orgao' => $porOrgao,
    ]);
  }

  /**
   * Atualiza uma norma específica manualmente
   */
  public function atualizarNormaEspecifica(Request $request, $normaId)
  {
    try {
      $norma = Norma::findOrFail($normaId);

      if (!$norma->temVigenciaProgramada()) {
        return response()->json(
          [
            'success' => false,
            'message' => 'Esta norma não possui vigência programada',
          ],
          400,
        );
      }

      $statusAnterior = $norma->vigente;
      $novoStatus = $norma->proximo_status;

      $norma->update([
        'vigente' => $novoStatus,
        'vigencia_indeterminada' => true,
        'data_limite_vigencia' => null,
      ]);

      return response()->json([
        'success' => true,
        'message' => "Status alterado de '{$statusAnterior}' para '{$novoStatus}'",
      ]);
    } catch (\Exception $e) {
      return response()->json(
        [
          'success' => false,
          'message' => 'Erro ao atualizar norma: ' . $e->getMessage(),
        ],
        500,
      );
    }
  }
}
