<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTipoRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'nome_tipo' => 'required|min:5|max:255',
    ];
  }

  public function messages()
  {
    return [
      'nome_tipo.required' => 'O campo (Nome do Tipo) é obrigatório!',
      'nome_tipo.min' => 'Quantidade minima de 5 (cinco) caracteres!',
      'nome_tipo.max' =>
        'Quantidade minima de 255 (duzentos e cinquenta e cinco) caracteres!',
    ];
  }
}
