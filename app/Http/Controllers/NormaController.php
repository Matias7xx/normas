<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateNormaRequest;
use App\Models\Norma;
use App\Models\NormaChave;
use App\Models\Orgao;
use App\Models\PalavraChave;
use App\Models\Publicidade;
use App\Models\Rh\Servidor;
use App\Models\Tipo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// public function create($id)
//     {
//         $norma = Norma::with(['publicidade','orgao','tipo','palavrasChave'])
//             ->where('id', $id)
//             ->first();
//         return view('palavras_chaves.palavras_chaves_create', compact(['norma']));
//     }

class NormaController extends Controller
{
    public function edit($id)
    {
        $tipos = Tipo::orderBy('tipo')->get();
        $publicidades = Publicidade::orderBy('publicidade')->get();
        $orgaos = Orgao::where('status', true)
            ->orderBy('orgao')
            ->get();

        $palavra_chaves = PalavraChave::orderBy('palavra_chave')->get();

        $norma = Norma::with(['publicidade', 'orgao', 'tipo', 'palavrasChave'])
            ->where('id', $id)
            ->first();
        return view('normas.norma_edit', compact(['norma'], ['tipos'], ['orgaos'], ['publicidades'], ['palavra_chaves']));
    }

    public function create()
    {
        $tipos = Tipo::orderBy('tipo')->get();
        $publicidades = Publicidade::orderBy('publicidade')->get();
        $orgaos = Orgao::where('status', true)
            ->orderBy('orgao')
            ->get();
        return view('normas.norma_create', compact(['tipos'], ['orgaos'], ['publicidades']));
    }

    public function index()
    {   $servidor = Servidor::where('matricula', Auth::user()->matricula)->first();
        $normas_por_tipo = Norma::with(['publicidade', 'orgao', 'tipo', 'palavrasChave'])->orderBy('tipo_id')->get()->groupBy('tipo.tipo');
        return view('normas.norma_list', compact(['normas_por_tipo', 'servidor']));
    }


    public function search(Request $request)
    {
        $tipo = Tipo::where('status', true)
            ->orderBy('tipo')
            ->get();

        $orgao = Orgao::where('status', true)
            ->orderBy('orgao')
            ->get();

        $query = Norma::with(['publicidade', 'orgao', 'tipo', 'palavrasChave'])->orderBy('tipo_id');

        if ($request->norma) {
            $query->where('descricao', 'ILIKE', "%{$request->norma}%");
        }
        if ($request->orgao) {
            $query->where('orgao_id', '=', $request->orgao);
        }
        if ($request->palavra_chave) {
            $query->whereHas('palavrasChave', function ($query) use ($request) {
                $query->where('palavra_chave', 'ILIKE', "%{$request->palavra_chave}%");
            });
        }
        if ($request->descricao && $request->descricao != '') {
            $termos = explode(' ', $request->descricao);
            foreach ($termos as $key => $t) {
                $query->whereRaw("remove_acento(descricao) ILIKE remove_acento('%" . $t . "%')");
            }
        }
        // dd($query->toSql());
        $norma_pesquisa = $query->get();
        return view('normas.norma_pesquisa', compact(['norma_pesquisa'], ['tipo'], ['orgao']));
    }

    public function store(CreateNormaRequest $request)
    {
        try {
            /*função para salvar o arquivo*/
            if ($request->file('anexo')->isValid()) {
                /*cria um rash para renomear o arquivo*/
                $name_file = Str::uuid() . "." . $request->anexo->extension();
                /*salva o arquivo e renomeia automaticamente e renomeia com o valor informado */
                $request->file('anexo')->storeAs('public/normas', $name_file);
                Norma::create([
                    'usuario_id' => auth()->user()->id,
                    'data' => $request->data,
                    'descricao' => $request->descricao,
                    'anexo' => $name_file,
                    'resumo' => $request->resumo,
                    'publicidade_id' => $request->publicidade,
                    'tipo_id' => $request->tipo,
                    'orgao_id' => $request->orgao,
                    'status' => true
                ]);
                return redirect()->route('normas.norma_list')->withSuccess('Cadastro realizado com sucesso!');
            } else {
                return back()->withErrors(['Erro ao fazer o Upload do arquivo!']);
            }
        } catch (\Exception $e) {
            Log::error($e);
            return back()->withErrors(['Erro interno no servidor, informe o administrador do sistema!']);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $tipos = Tipo::orderBy('tipo')->get();
            $publicidades = Publicidade::orderBy('publicidade')->get();
            $orgaos = Orgao::where('status', true)
                ->orderBy('orgao')
                ->get();
            $palavra_chaves = NormaChave::where('norma_id', '=', $id)
                ->where('status', true)
                ->get();
            $norma = Norma::with(['publicidade', 'orgao', 'tipo', 'palavrasChave'])
                ->where('id', $id)
                ->first();

            if (isset($request->delete_palavra_chave)) {
                $norma_result = Norma::find($id);
                $norma_result->palavrasChave()->detach($request->delete_palavra_chave);
                return redirect()->route('normas.norma_edit', compact(['id'], ['norma'], ['tipos'], ['orgaos'], ['publicidades'], ['palavra_chaves']))->withSuccess('Palavra chave desvinculada com sucesso!');
            } elseif (isset($request->add_palavra_chave)) {
                $norma_result = Norma::find($id);
                $norma_result->palavrasChave()->attach($request->add_palavra_chave);
                return redirect()->route('normas.norma_edit', compact(['id'], ['norma'], ['tipos'], ['orgaos'], ['publicidades'], ['palavra_chaves']))->withSuccess('Palavra chave vinculada com sucesso!');
            } else {
                $update_norma = Norma::find($id);
                $update_norma->data  = $request->data;
                $update_norma->descricao = $request->descricao;
                if (($request->hasFile('anexo')) && ($request->file('anexo')->isValid())) {
                    /*excluir arquivo anterior*/
                    if(($update_norma->anexo != NULL)&&(file_exists(storage_path().'/app/public/normas/'.$update_norma->anexo))){
                        unlink(storage_path().'/app/public/normas/'.$update_norma->anexo);
                    }
                    /*cria um rash para renomear o arquivo*/
                    $name_file = Str::uuid() . "." . $request->anexo->extension();
                    /*salva o arquivo e renomeia automaticamente e renomeia com o valor informado */
                    $request->file('anexo')->storeAs('public/normas', $name_file);
                    $update_norma->anexo  = $name_file;
                }
                $update_norma->resumo = $request->resumo;
                $update_norma->publicidade_id  = $request->publicidade;
                $update_norma->tipo_id  = $request->tipo;
                $update_norma->orgao_id  = $request->orgao;
                $update_norma->save();
                return redirect()->route('normas.norma_list')->withSuccess('Edição realizada com sucesso!');
            }
        } catch (\Exception $e) {
            Log::error($e);
            return back()->withErrors(['Erro interno no servidor, informe o administrador do sistema!']);
        }
    }
}
