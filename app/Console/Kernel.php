<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Teste: Executar diariamente às 00:30
        $schedule->command('normas:atualizar-vigencia')
            ->dailyAt('00:30')
            ->timezone('America/Sao_Paulo')
            ->withoutOverlapping() // Evita execuções simultâneas
            ->runInBackground()
            ->onSuccess(function () {
                \Log::info('Automação de vigência das normas executada com sucesso', [
                    'timestamp' => now()->format('Y-m-d H:i:s'),
                    'timezone' => 'America/Sao_Paulo'
                ]);
            })
            ->onFailure(function () {
                \Log::error('Falha na automação de vigência das normas', [
                    'timestamp' => now()->format('Y-m-d H:i:s'),
                    'timezone' => 'America/Sao_Paulo'
                ]);
            });

        //Limpeza de logs antigos (manter apenas 30 dias)
        $schedule->command('log:clear')
            ->weekly()
            ->sundays()
            ->at('02:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}