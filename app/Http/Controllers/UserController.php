<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use App\Http\Requests\CreateUserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $users = User::orderBy('name')->get();
      return view('user.index')->with('users', $users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $user = new User;
      $roles = Role::where('id', '!=', 1)->get();
      return view('user.create')->with('roles', $roles)->with('user', $user);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateUserRequest $request)
    {
      try {
        // save the user to the database
        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'matricula'     => $request->matricula,
            'active'        => $request->active,
            'password'      => bcrypt($request->password),
            'role_id'       => $request->role_id,
            'cargo_id'      => $request->cargo_id,
            'cpf'           => $request->cpf,
            'telefone'      => $request->phone,
        ]);
        return redirect()->route('user.index')->withSuccess('Usuário criado com sucesso!');
      } catch (\Exception $e) {
        Log::error($e);
        return back()->withErrors(['Erro interno no servidor, informe o administrador do sistema!']);
      }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $user = User::find($id);
      $roles = Role::where('id', '!=', 1)->get();
      return view('user.edit')->with('user', $user)->with('roles', $roles);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $update_user = User::find($id) ;
      $update_user->name  = $request->name;
      $update_user->matricula = $request->matricula;
      $update_user->email = $request->email;
      $update_user->role_id = $request->role_id;
      $update_user->cargo_id = $request->cargo_id;
      $update_user->cpf = $request->cpf;
      $update_user->telefone = $request->phone;
      $update_user->active = $request->active;
      // update pass is available
      if ($request->has('password') && $request->password != null){
       $update_user->password = bcrypt($request->password);
      }
      $update_user->save() ;

      Session::flash('success', 'Usuário editado com sucesso!');
      return redirect()->route('user.index') ;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete_user = User::find($id) ;
        if ( $delete_user->id == 1 ) {
	        Session::flash('error', 'Erro, Usuário Root não pode ser apagado!') ;
	        return redirect()->back();
        }
        $delete_user->delete() ;
        Session::flash('success', 'Usuário apagado com sucesso!') ;
        return redirect()->back();
    }


    public function activate($id) {
        $user = User::find($id) ;
        $user->active = 1;
        $user->save() ;
        // return "USER WITH ID: $id  is now active"  ;
        return redirect()->back() ;
    }

    public function disable($id) {
        $user = User::find($id) ;
        if ( $user->id == 1 ) {
	        Session::flash('error', 'Erro, Usuário Root não pode ser desabilitado!') ;
	        return redirect()->back();
        }
        $user->active = 0;
        $user->save() ;

		Session::flash('success', 'Usuário desabilitado!') ;
        return redirect()->back() ;
    }

    public function userTaskList($id) {
        $username = User::find($id) ;
        return view('user.list', compact('username') ) ;
    }

    public function userAutocomplete(Request $request)
    {
      $users = User::select('id', 'name')
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

    public function managerAutocomplete(Request $request)
    {
      $users = User::with('role')->select('id', 'name')
                    ->where('name', 'ilike', '%'.$request->q.'%')
                    ->where('role_id', 3)
                    ->whereActive(1)->get();
      $data=array();
    foreach ($users as $user) {
      $data[]=array('id'=>$user->id, 'text'=>mb_strtoupper($user->name));
    }
    if(count($data))
         return response()->json($data);
    }

    public function supervisorAutocomplete(Request $request)
    {
      $users = User::with('role')->select('id', 'name')
                    ->where('name', 'ilike', '%'.$request->q.'%')
                    ->where('role_id', 2)
                    ->whereActive(1)->get();
      $data=array();
    foreach ($users as $user) {
      $data[]=array('id'=>$user->id, 'text'=>mb_strtoupper($user->name));
    }
    if(count($data))
         return response()->json($data);
    }
}
