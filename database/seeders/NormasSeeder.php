<?php

namespace Database\Seeders;

use App\Models\Norma;
use App\Models\NormaChave;
use App\Models\PalavraChave;
use Illuminate\Database\Seeder;

class NormasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Criar palavras-chave
        $palavrasChave = PalavraChave::factory()->count(30)->create();

        // Criar normas de diferentes tipos
        // Decretos
        Norma::factory()
            ->count(15)
            ->decreto()
            ->publica()
            ->create()
            ->each(function ($norma) use ($palavrasChave) {
                // Associar 2-5 palavras-chave aleatórias para cada norma
                $palavras_ids = $palavrasChave->random(mt_rand(2, 5))->pluck('id');
                foreach ($palavras_ids as $palavra_id) {
                    NormaChave::create([
                        'norma_id' => $norma->id,
                        'palavra_chave_id' => $palavra_id,
                        'status' => true
                    ]);
                }
            });

        // Portarias
        Norma::factory()
            ->count(10)
            ->portaria()
            ->create()
            ->each(function ($norma) use ($palavrasChave) {
                // Associar 1-3 palavras-chave aleatórias para cada norma
                $palavras_ids = $palavrasChave->random(mt_rand(1, 3))->pluck('id');
                foreach ($palavras_ids as $palavra_id) {
                    NormaChave::create([
                        'norma_id' => $norma->id,
                        'palavra_chave_id' => $palavra_id,
                        'status' => true
                    ]);
                }
            });

        // Normas da Polícia Civil
        Norma::factory()
            ->count(8)
            ->doOrgao('Polícia Civil')
            ->create()
            ->each(function ($norma) use ($palavrasChave) {
                // Associar 2-4 palavras-chave aleatórias para cada norma
                $palavras_ids = $palavrasChave->random(mt_rand(2, 4))->pluck('id');
                foreach ($palavras_ids as $palavra_id) {
                    NormaChave::create([
                        'norma_id' => $norma->id,
                        'palavra_chave_id' => $palavra_id,
                        'status' => true
                    ]);
                }
            });

        // Adicionar normas privadas
        Norma::factory()
            ->count(5)
            ->privada()
            ->create()
            ->each(function ($norma) use ($palavrasChave) {
                // Associar 1-2 palavras-chave aleatórias para cada norma
                $palavras_ids = $palavrasChave->random(mt_rand(1, 2))->pluck('id');
                foreach ($palavras_ids as $palavra_id) {
                    NormaChave::create([
                        'norma_id' => $norma->id,
                        'palavra_chave_id' => $palavra_id,
                        'status' => true
                    ]);
                }
            });
    }
}