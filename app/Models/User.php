<?php

namespace App\Models;

use App\Models\Rh\Servidor;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'matricula',
        'cargo_id',
        'cargo',
        'classe_funcional',
        'nivel_funcional',
        'email',
        'password',
        'role_id',
        'cpf',
        'sexo',
        'active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $appends = [
        'cargo'
    ];

    /**
     * Relations
     */
    public function role()
    {
      return $this->belongsTo(Role::class);
    }

    public function servidor(){
        return $this->belongsTo(Servidor::class, 'matricula', 'matricula');
    }

    /**
     * Acessors
     */
    public function getCargoAttribute()
    {
        switch ($this->attributes['cargo_id']) {
            case 1:
                return 'Delegado de Polícia';
                break;

            case 2:
                return 'Investigador de Polícia';
                break;

            case 3:
                return 'Escrivão de Polícia';
                break;

            case 4:
                return 'Agente Operacional de Polícia';
                break;

            default:
                return 'Cargo Indefinido';
                break;
        }
    }
}
