<?php

namespace App\Models\Rh;

use App\Models\User;
use App\Models\Rh\Cargo;
use App\Models\Rh\Unidade;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servidor extends Model
{
    use HasFactory;
    protected $connection = 'db_rh';
    protected $table = 'servidor';
    protected $primaryKey = 'id_servidor';

    public function unidades(){
        return $this->belongsToMany(Unidade::class, 'delegacia_servidor', 'matricula_servidor','id_delegacia');
    }

    public function cargo(){
        return $this->belongsTo(Cargo::class, 'cargo', 'codigo');
    }

    /* use HasFactory;
    
    //Usando a conexão padrão temporariamente. Trecho necessário para login sem API
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    //Método para mapear como se fosse um servidor
    public static function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column === 'matricula') {
            //Redireciona a consulta para a tabela users
            return User::where('matricula', $operator, $value);
        }
        
        return parent::where($column, $operator, $value, $boolean);
    }

    public function unidades(){
        //Retorna uma coleção vazia para não quebrar o código
        return collect([]);
    }

    public function cargo(){
        //Retorna null ou um mock do cargo
        return null;
    }
    
    //Método para retornar atributos padrão
    public function getAttribute($key)
    {
        $attributes = parent::getAttribute($key);
        
        //Se o atributo não existir, fornece um valor padrão para campos comuns
        if ($attributes === null) {
            switch ($key) {
                case 'nome':
                    return $this->name ?? 'Nome do Servidor';
                case 'cargo':
                    return 'Cargo Padrão';
                case 'matricula':
                    return $this->matricula ?? '000000';
                case 'foto':
                    return null;
                //Adicione outros campos conforme necessário
            }
        }
        
        return $attributes;
    } */
}
