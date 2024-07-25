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
}
