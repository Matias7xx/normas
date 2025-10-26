<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrgaoRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'nome_orgao' => 'required|min:10|max:255',
    ];
  }

  public function messages()
  {
    return [
      'nome_orgao.required' => 'O campo (Nome do Órgão) é obrigatório!',
      'nome_orgao.min' => 'Quantidade minima de 10 (dez) caracteres!',
      'nome_orgao.max' =>
        'Quantidade minima de 255 (duzentos e cinquenta e cinco) caracteres!',
    ];
  }
}
