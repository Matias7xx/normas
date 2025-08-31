<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use App\Http\Requests\CreateUserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
        DB::beginTransaction();
        
        $user = User::create([
            'name'          => trim($request->name),
            'email'         => $request->email ? trim($request->email) : null,
            'matricula'     => $request->matricula,
            'active'        => (bool) $request->active,
            'password'      => Hash::make($request->password),
            'role_id'       => $request->role_id,
            'cargo_id'      => $request->cargo_id,
            'cpf'           => $request->cpf,
            'telefone'      => $request->telefone,
        ]);
        
        DB::commit();
        
        Log::info("Usuário criado com sucesso", [
            'id' => $user->id,
            'matricula' => $user->matricula,
            'name' => $user->name,
            'created_by' => auth()->user()->name ?? 'Sistema'
        ]);
        
        return redirect()->route('user.index')->withSuccess('Usuário criado com sucesso!');
        
      } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erro ao criar usuário: ' . $e->getMessage());
        return back()->withInput()->withErrors(['Erro interno no servidor, informe o administrador do sistema!']);
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
     */
    public function update(UpdateUserRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $user = User::findOrFail($id);
            
            // Verificar se não é o usuário root sendo editado por outro usuário
            if ($user->id == 1 && auth()->user()->id != 1) {
                return back()->withErrors(['Apenas o usuário root pode editar seus próprios dados.']);
            }
            
            $user->fill([
                'name'      => trim($request->name),
                'matricula' => $request->matricula,
                'email'     => $request->email ? trim($request->email) : null,
                'role_id'   => $request->role_id,
                'cargo_id'  => $request->cargo_id,
                'cpf'       => $request->cpf,
                'telefone'  => $request->telefone,
                'active'    => (bool) $request->active,
            ]);
            
            // Atualizar senha apenas se fornecida
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
                
                Log::info("Senha atualizada para usuário", [
                    'user_id' => $user->id,
                    'matricula' => $user->matricula,
                    'updated_by' => auth()->user()->name ?? 'Sistema'
                ]);
            }
            
            $user->save();
            
            DB::commit();
            
            return redirect()->route('user.index')
                ->with('success', 'Usuário editado com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar usuário: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->withErrors(['Erro interno no servidor, informe o administrador do sistema!']);
        }
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