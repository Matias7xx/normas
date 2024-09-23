<?php

namespace App\Http\Controllers;

use App\Models\Norma;
use App\Models\Orgao;
use App\Models\Tipo;
use Illuminate\Http\Request;

class NormaSearchPublicController extends Controller
{
    public function search(Request $request)
    {
        $tipo = Tipo::where('status', true)
            ->orderBy('tipo')
            ->get();

        $orgao = Orgao::where('status', true)
            ->orderBy('orgao')
            ->get();

        $query = Norma::with(['publicidade', 'orgao', 'tipo', 'palavrasChave'])
            ->where('publicidade_id', '=', '1')
            ->orderBy('tipo_id');

        if ($request->norma) {
            $query->where('descricao', 'ILIKE', "%{$request->norma}%");
        }
        if ($request->resumo) {
            $query->where('resumo', 'ILIKE', "%{$request->resumo}%");
        }
        if ($request->orgao) {
            $query->where('orgao_id', '=', $request->orgao);
        }
        if ($request->tipo) {
            $query->where('tipo_id', '=', $request->tipo);
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
        //dd($query->toSql());
        $norma_pesquisa = $query->get();
        return view('public_search.norma_search', compact(['norma_pesquisa'], ['tipo'], ['orgao']));
    }
}
