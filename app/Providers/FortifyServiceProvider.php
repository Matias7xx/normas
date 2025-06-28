<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::loginView(function(){
            return view('auth.login');
        });
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $matricula = (string) $request->matricula;

            return Limit::perMinute(5)->by($matricula.$request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        Fortify::authenticateUsing(function(Request $request){

              //Buscar usuário pela matrícula. Trecho necessário para Login sem API
        $user = User::where('matricula', $request->matricula)->first();
        
        //Verificar se o usuário existe e se a senha está correta
        if ($user && Hash::check($request->password, $user->password)) {
            return $user;
        }
        
        return null;
        
             /* try {
                $response = Http::withToken(env('API_TOKEN'))
                ->post(env('API_LOGIN_URL').'/api/servidor/login', [
                    'matricula' => $request->matricula,
                    'senha' => $request->password
                ]);
                // Log::debug($response->json());
                if ($response->successful()) {
                    $usuario = $response->json();

                    if (User::where('matricula', $usuario['matricula'])->exists()) {
                        $user = User::where('matricula', $usuario['matricula'])->first();
                        $user->status = $usuario['status'];
                        $user->unidade_lotacao_id = $usuario['lotacao_principal'] != null ? $usuario['lotacao_principal']['codigo_unidade_lotacao'] : 340;
                        $user->srpc = $usuario['lotacao_principal'] != null ? $usuario['lotacao_principal']['srpc'] : 0;
                        $user->dspc = $usuario['lotacao_principal'] != null ? $usuario['lotacao_principal']['dspc'] : 0;
                        $user->unidade_lotacao = $this->getLotacaoServidor($usuario);
                        $user->save();
                    } else {
                        //dd($usuario);
                        $user = User::create([
                            'name'               => $usuario['nome'],
                            'email'              => $usuario['email'] ? $usuario['email'] : $usuario['matricula'].'@pc.pb.gov.br',
                            'matricula'          => $usuario['matricula'],
                            'active'             => true,
                            'password'           => bcrypt($request->password),
                            'role_id'            => 3,
                            'cargo_id'           => $usuario['codigo_cargo'],
                            'cargo'              => $usuario['cargo'],
                            'cpf'                => $usuario['cpf'],
                            'sexo'               => $usuario['sexo'],
                            'unidade_lotacao'    => $this->getLotacaoServidor($usuario),
                            'unidade_lotacao_id' => $usuario['lotacao_principal'] != null ? $usuario['lotacao_principal']['codigo_unidade_lotacao'] : 340,
                            'srpc'               => $usuario['lotacao_principal'] != null ? $usuario['lotacao_principal']['srpc'] : 0,
                            'dspc'               => $usuario['lotacao_principal'] != null ? $usuario['lotacao_principal']['dspc'] : 0,
                            'nivel'              => $usuario['lotacao_principal'] != null ? $usuario['lotacao_principal']['nivel'] : 0,
                            'classe_funcional'   => $usuario['classe_funcional'],
                            'nivel_funcional'    => $usuario['nivel'],
                        ]);
                    }

                    return $user;
                } else {
                    $user = User::where('matricula',$request->matricula)->first();
                    if($user && Hash::check($request->password,$user->password)){
                        return $user;
                    }
                }
            } catch (\Throwable $th) {
                Log::error($th);
                $user = User::where('matricula',$request->matricula)->first();
                if($user && Hash::check($request->password,$user->password)){
                    return $user;
                }
            } */
        });

    }

    public function getLotacaoServidor($servidor)
    {
        if ($servidor['lotacao_principal'] != null) {
            return $servidor['lotacao_principal']['unidade_lotacao'];
        }
        elseif (isset($servidor['orgao_cedido']) && isset($servidor['orgao_cedido']['nome'])) {
            return 'Servidor cedido a '.$servidor['orgao_cedido']['nome'];
        } else {
            return null;
        }
    }
}
