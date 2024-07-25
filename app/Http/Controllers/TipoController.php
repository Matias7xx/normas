<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTipoRequest;
use App\Models\Tipo;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class TipoController extends Controller
{
    public function show()
    {
        $tipo = Tipo::orderBy('tipo')->get();
        return view('tipos.tipo_list', compact(['tipo']));
    }

    public function create()
    {
        return view('tipos.tipo_create');
    }

    public function store(CreateTipoRequest $request)
    {
        try {
            Tipo::create([
                'usuario_id' => auth()->user()->id,
                'tipo' => $request->nome_tipo,
                'status' => true
            ]);
            return redirect()->route('tipos.tipo_list')->withSuccess('Cadastro realizado com sucesso!');
        } catch (\Exception $e) {
            Log::error($e);
            return back()->withErrors(['Erro interno no servidor, informe o administrador do sistema!']);
        }
    }

    public function edit($id)
    {
        $tipo = Tipo::where('status', true)
            ->where('id', $id)
            ->orderBy('tipo')
            ->first();
        return view('tipos.tipo_edit', compact(['tipo']));
    }

    public function update(Request $request, $id)
    {
        try {
            $update_tipo = Tipo::find($id);
            $update_tipo->tipo  = $request->nome_tipo;
            $update_tipo->save();
            return redirect()->route('tipos.tipo_list')->withSuccess('Edição realizada com sucesso!');
        } catch (\Exception $e) {
            Log::error($e);
            return back()->withErrors(['Erro interno no servidor, informe o administrador do sistema!']);
        }
    }
}
