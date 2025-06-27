<?php

namespace Database\Factories;

use App\Models\Norma;
use App\Models\Orgao;
use App\Models\Publicidade;
use App\Models\Tipo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        // Buscar dados existentes ou criar automaticamente se não existirem
        $usuario_id = $this->getOrCreateUser();
        $publicidade_id = $this->getOrCreatePublicidade();
        $tipo_id = $this->getOrCreateTipo();
        $orgao_id = $this->getOrCreateOrgao();

        return [
            'usuario_id' => $usuario_id,
            'data' => $this->faker->dateTimeBetween('-3 years', 'now'),
            'descricao' => $this->faker->sentence(6, true),
            'resumo' => $this->faker->paragraph(3),
            'publicidade_id' => $publicidade_id,
            'tipo_id' => $tipo_id,
            'orgao_id' => $orgao_id,
            'anexo' => 'exemplo.pdf',
            'vigente' => $this->faker->randomElement(['VIGENTE', 'NÃO VIGENTE', 'EM ANÁLISE']),
            'status' => true
        ];
    }

    /**
     * Busca usuário existente ou usa ID padrão
     */
    private function getOrCreateUser()
    {
        $user = User::inRandomOrder()->first();
        return $user ? $user->id : 1;
    }

    /**
     * Busca publicidade existente ou cria uma
     */
    private function getOrCreatePublicidade()
    {
        $publicidade = Publicidade::where('status', true)->inRandomOrder()->first();
        
        if (!$publicidade) {
            $publicidade = Publicidade::create([
                'publicidade' => 'Público',
                'status' => true,
                'usuario_id' => 1
            ]);
        }
        
        return $publicidade->id;
    }

    /**
     * Busca tipo existente ou cria um
     */
    private function getOrCreateTipo()
    {
        $tipo = Tipo::where('status', true)->inRandomOrder()->first();
        
        if (!$tipo) {
            $tipos = ['Decreto', 'Portaria', 'Resolução', 'Instrução Normativa'];
            $tipo = Tipo::create([
                'tipo' => $this->faker->randomElement($tipos),
                'status' => true,
                'usuario_id' => 1
            ]);
        }
        
        return $tipo->id;
    }

    /**
     * Busca órgão existente ou cria um
     */
    private function getOrCreateOrgao()
    {
        $orgao = Orgao::where('status', true)->inRandomOrder()->first();
        
        if (!$orgao) {
            $orgaos = [
                'Polícia Civil da Paraíba',
                'Secretaria de Segurança Pública',
                'Delegacia Geral',
                'Corregedoria'
            ];
            $orgao = Orgao::create([
                'orgao' => $this->faker->randomElement($orgaos),
                'status' => true,
                'usuario_id' => 1
            ]);
        }
        
        return $orgao->id;
    }

    /**
     * Indica que a norma é privada.
     */
    public function privada()
    {
        return $this->state(function (array $attributes) {
            // Busca publicidade privada ou cria uma
            $publicidade_privada = Publicidade::where('publicidade', 'ilike', '%privad%')
                ->where('status', true)
                ->first();
            
            if (!$publicidade_privada) {
                $publicidade_privada = Publicidade::create([
                    'publicidade' => 'Privado',
                    'status' => true,
                    'usuario_id' => 1
                ]);
            }

            return [
                'publicidade_id' => $publicidade_privada->id
            ];
        });
    }

    /**
     * Indica que a norma é pública.
     */
    public function publica()
    {
        return $this->state(function (array $attributes) {
            // Busca publicidade pública ou cria uma
            $publicidade_publica = Publicidade::where('publicidade', 'ilike', '%public%')
                ->where('status', true)
                ->first();
            
            if (!$publicidade_publica) {
                $publicidade_publica = Publicidade::create([
                    'publicidade' => 'Público',
                    'status' => true,
                    'usuario_id' => 1
                ]);
            }

            return [
                'publicidade_id' => $publicidade_publica->id
            ];
        });
    }

    /**
     * Indica que a norma é um decreto.
     */
    public function decreto()
    {
        return $this->state(function (array $attributes) {
            // Busca tipo decreto ou cria um
            $tipo_decreto = Tipo::where('tipo', 'ilike', '%decreto%')
                ->where('status', true)
                ->first();
            
            if (!$tipo_decreto) {
                $tipo_decreto = Tipo::create([
                    'tipo' => 'Decreto',
                    'status' => true,
                    'usuario_id' => 1
                ]);
            }

            return [
                'tipo_id' => $tipo_decreto->id
            ];
        });
    }

    /**
     * Indica que a norma é uma portaria.
     */
    public function portaria()
    {
        return $this->state(function (array $attributes) {
            // Busca tipo portaria ou cria um
            $tipo_portaria = Tipo::where('tipo', 'ilike', '%portaria%')
                ->where('status', true)
                ->first();
            
            if (!$tipo_portaria) {
                $tipo_portaria = Tipo::create([
                    'tipo' => 'Portaria',
                    'status' => true,
                    'usuario_id' => 1
                ]);
            }

            return [
                'tipo_id' => $tipo_portaria->id
            ];
        });
    }

    /**
     * Define um órgão para a norma.
     */
    public function doOrgao(string $nome)
    {
        return $this->state(function (array $attributes) use ($nome) {
            // Busca órgão específico ou cria um
            $orgao = Orgao::where('orgao', 'ilike', "%$nome%")
                ->where('status', true)
                ->first();
            
            if (!$orgao) {
                $orgao = Orgao::create([
                    'orgao' => $nome,
                    'status' => true,
                    'usuario_id' => 1
                ]);
            }

            return [
                'orgao_id' => $orgao->id
            ];
        });
    }
}