<?php

namespace Database\Factories;

use App\Models\Norma;
use App\Models\NormaChave;
use App\Models\PalavraChave;
use Illuminate\Database\Eloquent\Factories\Factory;

class NormaChaveFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = NormaChave::class;

    /**
     * @return array
     */
    public function definition()
    {
        return [
            'norma_id' => Norma::factory(),
            'palavra_chave_id' => PalavraChave::factory(),
            'status' => true
        ];
    }
}