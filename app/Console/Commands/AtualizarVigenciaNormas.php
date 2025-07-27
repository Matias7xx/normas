<?php

namespace App\Console\Commands;

use App\Models\Norma;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AtualizarVigenciaNormas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'normas:atualizar-vigencia {--dry-run : Executar sem fazer alterações}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza automaticamente o status de vigência das normas baseado na data limite';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $hoje = Carbon::today();

        $this->info("Iniciando verificação de vigência das normas...");
        $this->info("Data de referência: " . $hoje->format('d/m/Y'));
        
        if ($dryRun) {
            $this->warn("MODO DRY-RUN ATIVO - Nenhuma alteração será feita!");
        }

        // Buscar normas que têm data limite definida e não são de vigência indeterminada
        $normasParaVerificar = Norma::where('status', true)
            ->where('vigencia_indeterminada', false)
            ->whereNotNull('data_limite_vigencia')
            ->whereDate('data_limite_vigencia', '<=', $hoje)
            ->get();

        if ($normasParaVerificar->isEmpty()) {
            $this->info("Nenhuma norma encontrada para atualização.");
            return 0;
        }

        $this->info("Encontradas {$normasParaVerificar->count()} normas para verificação:");

        $contadorAtualizacoes = 0;
        $errors = [];

        foreach ($normasParaVerificar as $norma) {
            try {
                $statusAtual = $norma->vigente;
                $novoStatus = $this->determinarNovoStatus($statusAtual);
                
                if ($novoStatus === $statusAtual) {
                    $this->line("ID {$norma->id}: {$norma->descricao} - Já está no status correto ({$statusAtual})");
                    continue;
                }

                $this->line("ID {$norma->id}: {$norma->descricao}");
                $this->line("   Data limite: " . $norma->data_limite_vigencia->format('d/m/Y'));
                $this->line("   {$statusAtual} → {$novoStatus}");

                if (!$dryRun) {
                    $norma->update([
                        'vigente' => $novoStatus,
                        'vigencia_indeterminada' => true,
                        'data_limite_vigencia' => null
                    ]);

                    Log::info("Vigência atualizada automaticamente", [
                        'norma_id' => $norma->id,
                        'descricao' => $norma->descricao,
                        'status_anterior' => $statusAtual,
                        'status_novo' => $novoStatus,
                        'data_limite' => $norma->data_limite_vigencia->format('Y-m-d'),
                        'data_atualizacao' => now()->format('Y-m-d H:i:s')
                    ]);

                    $this->info("   Atualizada com sucesso!");
                } else {
                    $this->warn("   Seria atualizada (dry-run)");
                }

                $contadorAtualizacoes++;

            } catch (\Exception $e) {
                $erro = "Erro ao atualizar norma ID {$norma->id}: " . $e->getMessage();
                $errors[] = $erro;
                $this->error("   ❌ " . $erro);
                
                Log::error("Erro na automação de vigência", [
                    'norma_id' => $norma->id,
                    'erro' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // Resumo
        $this->newLine();
        $this->info("   RESUMO DA EXECUÇÃO:");
        $this->info("   Normas verificadas: " . $normasParaVerificar->count());
        $this->info("   Normas atualizadas: " . $contadorAtualizacoes);
        $this->info("   Erros encontrados: " . count($errors));

        if (!empty($errors)) {
            $this->newLine();
            $this->error("❌ ERROS ENCONTRADOS:");
            foreach ($errors as $error) {
                $this->error("   • " . $error);
            }
        }

        if ($dryRun && $contadorAtualizacoes > 0) {
            $this->newLine();
            $this->warn("Para executar as alterações, execute o comando sem --dry-run:");
            $this->warn("php artisan normas:atualizar-vigencia");
        }

        return count($errors) > 0 ? 1 : 0;
    }

    /**
     * Determina o novo status baseado no status atual
     *
     * @param string $statusAtual
     * @return string
     */
    private function determinarNovoStatus($statusAtual)
    {
        switch ($statusAtual) {
            case Norma::VIGENTE_VIGENTE:
                return Norma::VIGENTE_NAO_VIGENTE;
            
            case Norma::VIGENTE_NAO_VIGENTE:
                return Norma::VIGENTE_VIGENTE;
            
            case Norma::VIGENTE_EM_ANALISE:
                // Por padrão, normas em análise ficam vigentes
                return Norma::VIGENTE_VIGENTE;
            
            default:
                return $statusAtual; // Não altera se não reconhecer o status
        }
    }
}