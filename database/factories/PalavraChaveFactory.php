<?php

namespace Database\Factories;

use App\Models\PalavraChave;
use Illuminate\Database\Eloquent\Factories\Factory;

class PalavraChaveFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = PalavraChave::class;

    /**
     * @return array
     */
    public function definition()
    {
        return [
            'usuario_id' => \App\Models\User::inRandomOrder()->first()->id ?? 1,
            'palavra_chave' => ucfirst($this->faker->unique()->words(mt_rand(1, 2), true)),
            'status' => true
        ];
    }
}