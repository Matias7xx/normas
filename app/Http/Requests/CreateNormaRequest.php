<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateNormaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'data' => 'required',
            'publicidade' => 'required',
            'tipo' => 'required',
            'orgao' => 'required',
            'anexo' => 'required',
            'descricao' => 'required|min:10|max:255',
            'resumo' => 'required|min:10|max:255'
        ];
    }

    public function messages(){
        return[
            'data.required' => 'O campo (Data) é obrigatório!',
            'publicidade.required' => 'O campo (Publicidade) é obrigatório!',
            'tipo.required' => 'O campo (Tipos de normas) é obrigatório!',
            'orgao.required' => 'O campo (Órgãos) é obrigatório!',
            'anexo.required' => 'O campo (Anexo) é obrigatório!',
            'descricao.required' => 'O campo (Descrição) é obrigatório!',
            'descricao.min' => 'Quantidade minima de 10 (dez) caracteres!',
            'descricao.max' => 'Quantidade minima de 255 (duzentos e cinquenta e cinco) caracteres!',
            'resumo.required' => 'O campo (Resumo da norma) é obrigatório!',
            'resumo.min' => 'Quantidade minima de 10 (dez) caracteres!',
            'resumo.max' => 'Quantidade minima de 255 (duzentos e cinquenta e cinco) caracteres!',
        ];
    }
}
