<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePalavraChaveRequest;
use App\Models\Norma;
use App\Models\NormaChave;
use App\Models\PalavraChave;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class PalavraChaveController extends Controller
{
    public function index()
    {
        $palavra_chave = PalavraChave::orderBy('palavra_chave')->get();
        return view('palavras_chaves.palavras_chaves_list', compact(['palavra_chave']));
    }

    public function create()
    {
        return view('palavras_chaves.palavras_chaves_create');
    }

    public function store(CreatePalavraChaveRequest $request)
    {
        try {
            PalavraChave::create([
                'usuario_id' => auth()->user()->id,
                'palavra_chave' => $request->palavra_chave,
                'status' => true
            ]);

            return redirect()->route('palavras_chaves.palavras_chaves_list')->withSuccess('Cadastro realizado com sucesso!');
        } catch (\Exception $e) {
            Log::error($e);
            return back()->withErrors(['Erro interno no servidor, informe o administrador do sistema!']);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $update_palavra_chave = PalavraChave::find($id);
            $update_palavra_chave->palavra_chave = $request->palavra_chave;
            $update_palavra_chave->save();
            return redirect()->route('palavras_chaves.palavras_chaves_list')->withSuccess('Edição realizada com sucesso!');
        } catch (\Exception $e) {
            Log::error($e);
            return back()->withErrors(['Erro interno no servidor, informe o administrador do sistema!']);
        }
    }

    public function edit($id)
    {
        $palavra_chave = PalavraChave::where('status', true)
            ->where('id', $id)
            ->orderBy('palavra_chave')
            ->first();
        return view('palavras_chaves.palavras_chaves_edit', compact(['palavra_chave']));
    }
}
