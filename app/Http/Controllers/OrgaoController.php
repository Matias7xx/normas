<?php

namespace App\Http\Controllers;
use App\Http\Requests\CreateOrgaoRequest;
use App\Models\Orgao;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class OrgaoController extends Controller
{
    public function edit($id)
    {
        $orgao = Orgao::where('status', true)
            ->where('id', $id)
            ->orderBy('orgao')
            ->first();
        return view('orgaos.orgao_edit', compact(['orgao']));
    }

    public function create()
    {
        return view('orgaos.orgao_create');
    }

    public function show()
    {
        $orgao = Orgao::orderBy('orgao')->get();
        return view('orgaos.orgao_list', compact(['orgao']));
    }

    public function store(CreateOrgaoRequest $request)
    {
        try {
            Orgao::create([
                // 'rash' => $request->_token,
                'usuario_id' => auth()->user()->id,
                'orgao' => $request->nome_orgao,
                'status' => true
            ]);
            return redirect()->route('orgaos.orgao_list')->withSuccess('Cadastro realizado com sucesso!');
        } catch (\Exception $e) {
            Log::error($e);
            return back()->withErrors(['Erro interno no servidor, informe o administrador do sistema!']);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $update_orgao = Orgao::find($id);
            $update_orgao->orgao  = $request->nome_orgao;
            $update_orgao->save();
            return redirect()->route('orgaos.orgao_list')->withSuccess('Edição realizada com sucesso!');
        } catch (\Exception $e) {
            Log::error($e);
            return back()->withErrors(['Erro interno no servidor, informe o administrador do sistema!']);
        }
    }
}
