<?php

namespace App\Http\Controllers;

use App\Models\Rh\Servidor;
use App\Http\Requests\StoreServidorRequest;
use App\Http\Requests\UpdateServidorRequest;
use Illuminate\Http\Request;

class ServidorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function servidorAutocomplete(Request $request)
    {
      $users = Servidor::select()
                    ->where('name', 'ilike', '%'.$request->q.'%')
                    ->where('id', '!=', 1)
                    ->whereActive(1)->get();
      $data=array();
    foreach ($users as $user) {
      $data[]=array('id'=>$user->id, 'text'=>mb_strtoupper($user->name));
    }
    if(count($data))
         return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreServidorRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreServidorRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Servidor  $servidor
     * @return \Illuminate\Http\Response
     */
    public function show(Servidor $servidor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Servidor  $servidor
     * @return \Illuminate\Http\Response
     */
    public function edit(Servidor $servidor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateServidorRequest  $request
     * @param  \App\Models\Servidor  $servidor
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateServidorRequest $request, Servidor $servidor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Servidor  $servidor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Servidor $servidor)
    {
        //
    }
}
