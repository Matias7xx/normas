<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePalavraChaveRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'palavra_chave' => 'required|min:10|max:255',
    ];
  }

  public function messages()
  {
    return [
      'palavra_chave.required' => 'O campo (Palavra Chave) é obrigatório!',
      'palavra_chave.min' => 'Quantidade minima de 10 (dez) caracteres!',
      'palavra_chave.max' =>
        'Quantidade minima de 255 (duzentos e cinquenta e cinco) caracteres!',
    ];
  }
}
