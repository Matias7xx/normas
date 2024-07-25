<?php

namespace App\Models\Rh;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    use HasFactory;
    protected $connection = 'db_rh';
    protected $primaryKey = 'idcargo';
    protected $table = 'cargo';
}
