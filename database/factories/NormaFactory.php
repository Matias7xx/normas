<?php

namespace Database\Factories;

use App\Models\Norma;
use App\Models\Orgao;
use App\Models\Publicidade;
use App\Models\Tipo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NormaFactory extends Factory
{
    /**
     * O nome do modelo correspondente.
     *
     * @var string
     */
    protected $model = Norma::class;

    /**
     * Define o estado padrão do modelo.
     *
     * @return array
     */
    public function definition()
    {
        // Garanti que existem dados nas tabelas (tipo, órgão e publicidade)
        $usuario_id = User::inRandomOrder()->first()->id ?? 1;
        $publicidade_id = Publicidade::where('status', true)->inRandomOrder()->first()->id ?? 1;
        $tipo_id = Tipo::where('status', true)->inRandomOrder()->first()->id ?? 1;
        $orgao_id = Orgao::where('status', true)->inRandomOrder()->first()->id ?? 1;

        return [
            'usuario_id' => $usuario_id,
            'data' => $this->faker->dateTimeBetween('-3 years', 'now'),
            'descricao' => $this->faker->sentence(6, true),
            'resumo' => $this->faker->paragraph(3),
            'publicidade_id' => $publicidade_id,
            'tipo_id' => $tipo_id,
            'orgao_id' => $orgao_id,
            'anexo' => 'exemplo.pdf',
            'status' => true
        ];
    }

    /**
     * Indica que a norma é privada.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function privada()
    {
        return $this->state(function (array $attributes) {
            $publicidade_privada = Publicidade::where('publicidade', 'ilike', '%privad%')
                ->where('status', true)
                ->first();

            return [
                'publicidade_id' => $publicidade_privada ? $publicidade_privada->id : 
                    Publicidade::where('status', true)->inRandomOrder()->first()->id
            ];
        });
    }

    /**
     * Indica que a norma é pública.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function publica()
    {
        return $this->state(function (array $attributes) {
            $publicidade_publica = Publicidade::where('publicidade', 'ilike', '%public%')
                ->where('status', true)
                ->first();

            return [
                'publicidade_id' => $publicidade_publica ? $publicidade_publica->id : 
                    Publicidade::where('status', true)->inRandomOrder()->first()->id
            ];
        });
    }

    /**
     * Indica que a norma é um decreto.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function decreto()
    {
        return $this->state(function (array $attributes) {
            $tipo_decreto = Tipo::where('tipo', 'ilike', '%decreto%')
                ->where('status', true)
                ->first();

            return [
                'tipo_id' => $tipo_decreto ? $tipo_decreto->id : 
                    Tipo::where('status', true)->inRandomOrder()->first()->id
            ];
        });
    }

    /**
     * Indica que a norma é uma portaria.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function portaria()
    {
        return $this->state(function (array $attributes) {
            $tipo_portaria = Tipo::where('tipo', 'ilike', '%portaria%')
                ->where('status', true)
                ->first();

            return [
                'tipo_id' => $tipo_portaria ? $tipo_portaria->id : 
                    Tipo::where('status', true)->inRandomOrder()->first()->id
            ];
        });
    }

    /**
     * Define um órgão para a norma.
     *
     * @param string $nome Nome do órgão (parcial)
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function doOrgao(string $nome)
    {
        return $this->state(function (array $attributes) use ($nome) {
            $orgao = Orgao::where('orgao', 'ilike', "%$nome%")
                ->where('status', true)
                ->first();

            return [
                'orgao_id' => $orgao ? $orgao->id : 
                    Orgao::where('status', true)->inRandomOrder()->first()->id
            ];
        });
    }
}