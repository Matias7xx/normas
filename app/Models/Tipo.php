<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tipo extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'tipo',
        'status'
    ];

    /*criação de accessors*/
    // public function getCorAttribute(){
    //     switch ($this->tipo) {
    //         case 'value':
    //             # code...
    //             break;

    //         default:
    //             # code...
    //             break;
    //     }
    // }
}
