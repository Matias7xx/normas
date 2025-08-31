<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBoletimRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Verificar se o usuário tem role 1 (root) ou 7
        return in_array(auth()->user()->role_id, [1, 7]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
            'data_publicacao' => 'required|date',
            'arquivo' => 'nullable|file|mimes:pdf|max:20480' // Opcional na edição, 20MB
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do boletim é obrigatório.',
            'nome.string' => 'O nome deve ser um texto válido.',
            'nome.max' => 'O nome não pode ter mais de 255 caracteres.',
            
            'descricao.string' => 'A descrição deve ser um texto válido.',
            'descricao.max' => 'A descrição não pode ter mais de 1000 caracteres.',
            
            'data_publicacao.required' => 'A data de publicação é obrigatória.',
            'data_publicacao.date' => 'A data de publicação deve ser uma data válida.',
            
            'arquivo.file' => 'O campo arquivo deve conter um arquivo válido.',
            'arquivo.mimes' => 'O arquivo deve ser do tipo PDF.',
            'arquivo.max' => 'O arquivo não pode ser maior que 20MB.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nome' => 'nome do boletim',
            'descricao' => 'descrição',
            'data_publicacao' => 'data de publicação',
            'arquivo' => 'arquivo PDF'
        ];
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function failedAuthorization()
    {
        abort(403, 'Acesso negado. Apenas usuários autorizados podem editar boletins.');
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validação adicional se necessário
            if ($this->hasFile('arquivo')) {
                $file = $this->file('arquivo');
                
                // Verificar se é realmente um PDF
                if ($file && $file->getMimeType() !== 'application/pdf') {
                    $validator->errors()->add('arquivo', 'O arquivo enviado não é um PDF válido.');
                }
                
                // Verificar tamanho adicional em bytes (20MB = 20971520 bytes)
                if ($file && $file->getSize() > 20971520) {
                    $validator->errors()->add('arquivo', 'O arquivo é muito grande. Tamanho máximo permitido: 20MB.');
                }
            }
        });
    }
}