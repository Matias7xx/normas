<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;

class AdminController extends Controller
{
  public function listUsers()
  {
    $users = User::with('role')->where('id', '!=', 1)->orderBy('name')->get();
    return view('admin.list-users')->with('users', $users);
  }
  public function listRoles()
  {
    $roles = Role::with('permissions')->orderBy('id', 'asc')->get();
    $permissions = Permission::orderBy('name')->get();
    return view('admin.list-roles')->with('roles', $roles)->with('permissions', $permissions);
  }

  public function addRole(Request $request)
  {
    $role = new Role;
    $role->name = mb_strtoupper($request->name, mb_internal_encoding());
    /* $role->display_name = $request->display_name; */ //Não existe na migration
    $role->description = mb_strtoupper($request->description, mb_internal_encoding());
    $role->save();
    // $permissions = $request->permissions;
    foreach ($request->permissions as $key => $p) {
      $role->permissions()->toggle([$p]);
    }
    return back()->withInput();
  }

  public function editRole($id)
  {
    $role = Role::find($id);
    $permissions = Permission::orderBy('name')->get();
    return view('admin.edit-role')->with('role', $role)->with('permissions', $permissions);
  }

  public function updateRole(Request $request, $id)
  {
    $role = Role::findOrFail($id);
    $role->name = mb_strtoupper($request->name, mb_internal_encoding());
    $role->description = mb_strtoupper($request->description, mb_internal_encoding());
    $role->save();
    $role->permissions()->sync($request->permissions);
    return redirect('/admin/list-roles')->withSuccess('Perfil atualizado com sucesso!');
  }

  public function listPermissions()
  {
    $permissions = Permission::orderBy('name')->get();
    return view('admin.list-permissions')->with('permissions', $permissions);
  }

  public function addPermission(Request $request)
  {
    $permission = new Permission;
    $permission->name = mb_strtoupper($request->name, mb_internal_encoding());
    $permission->slug = mb_strtolower($request->slug, mb_internal_encoding());
    $permission->save();

    return back()->withSucesso('Permissão '.$permission->name.' cadastrada com sucesso!');
  }

  public function deleteRole($id)
{
    try {
        $role = Role::findOrFail($id);
        
        // Verificar se é um role crítico (root ou admin principal)
        if (in_array($role->id, [1, 2, 3])) {
            return redirect()->back()->withErrors(['Não é possível excluir roles críticos do sistema (Root ou Admin principal).']);
        }
        
        // Verificar se há usuários usando este role
        $usersCount = User::where('role_id', $id)->count();
        if ($usersCount > 0) {
            return redirect()->back()->withErrors(["Não é possível excluir este perfil. Existem {$usersCount} usuário(s) utilizando este perfil."]);
        }
        
        // Armazenar o nome para a mensagem de sucesso
        $roleName = $role->name;
        
        // Remover associações com permissões
        $role->permissions()->detach();
        
        // Excluir o role
        $role->delete();
        
        return redirect()->route('admin.listrole')->with('success', "Perfil '{$roleName}' excluído com sucesso!");
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return redirect()->back()->withErrors(['Perfil não encontrado.']);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Erro ao excluir role: ' . $e->getMessage());
        return redirect()->back()->withErrors(['Erro interno no servidor. Tente novamente.']);
    }
}
}
