<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Permission;

class AuthServiceProvider extends ServiceProvider
{
  /**
   * The policy mappings for the application.
   *
   * @var array<class-string, class-string>
   */
  protected $policies = [
    // 'App\Models\Model' => 'App\Policies\ModelPolicy',
  ];

  /**
   * Register any authentication / authorization services.
   *
   * @return void
   */
  public function boot()
  {
    $this->registerPolicies();

    try {
      $permissions = Permission::with('roles')->get();
      foreach ($permissions as $permission) {
        Gate::define($permission->slug, function ($user) use ($permission) {
          return $permission->roles->contains($user->role);
        });
      }
    } catch (\Throwable $th) {
      //throw $th;
    }
  }
}
