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
            $user = User::where('matricula', $request->matricula)->first();
            
            if (!$user) {
                // Usuário não existe localmente, tentar criar via API
                return $this->criarUsuarioViaAPI($request);
            }
            
            // Verificar se é usuário ROOT (não usar API)
            if ($this->isUsuarioAdministrativo($user)) {
                Log::info("Usuário administrativo {$user->matricula} - autenticação apenas local");
                return $this->autenticacaoLocal($user, $request->password);
            }
            
            // Para usuários comuns: tentar local primeiro, depois API
            return $this->autenticacaoHibrida($user, $request);
        });
    }

    /**
     * Verificar se é usuário ROOT (não deve usar API)
     */
    private function isUsuarioAdministrativo($user)
    {
        // Apenas usuário ROOT (role_id = 1)
        $matriculasRoot = ['000000'];
        
        return $user->role_id == 1 || in_array($user->matricula, $matriculasRoot);
    }

    /**
     * Autenticação local
     */
    private function autenticacaoLocal($user, $password)
    {
        if (Hash::check($password, $user->password)) {
            return $user;
        }
        
        return null;
    }

    /**
     * Autenticação para usuários
     */
    private function autenticacaoHibrida($user, Request $request)
    {
        // Tentar autenticação local
        if (Hash::check($request->password, $user->password)) {
            Log::info("Usuário {$user->matricula} autenticado via senha local");
            // Tentar atualizar dados via API
            $this->atualizarDadosViaAPI($user, $request);
            return $user;
        }

        // Se senha local falhou, tentar via API
        try {
            $response = Http::timeout(5)->withToken(env('API_TOKEN'))
                ->post(env('API_LOGIN_URL').'/api/servidor/login', [
                    'matricula' => $request->matricula,
                    'senha' => $request->password
                ]);

            if ($response->successful()) {
                $usuario = $response->json();
                
                Log::info("Usuário {$user->matricula} autenticado via API");
                
                // Sincronizar senha da API com banco local
                $user->password = Hash::make($request->password);
                
                // Atualizar outros dados
                $user->update([
                    'name' => $usuario['nome'],
                    'email' => $usuario['email'] ?: $usuario['matricula'].'@pc.pb.gov.br',
                    'status' => $usuario['status'],
                    'unidade_lotacao_id' => $usuario['lotacao_principal']['codigo_unidade_lotacao'] ?? $user->unidade_lotacao_id,
                    'srpc' => $usuario['lotacao_principal']['srpc'] ?? $user->srpc,
                    'dspc' => $usuario['lotacao_principal']['dspc'] ?? $user->dspc,
                    'unidade_lotacao' => $this->getLotacaoServidor($usuario),
                    'cargo_id' => $usuario['codigo_cargo'] ?? $user->cargo_id,
                    'cargo' => $usuario['cargo'] ?? $user->cargo,
                    'cpf' => $usuario['cpf'],
                    'sexo' => $usuario['sexo'],
                    'classe_funcional' => $usuario['classe_funcional'],
                    'nivel_funcional' => $usuario['nivel'],
                ]);
                
                return $user;
            }
        } catch (\Exception $e) {
            Log::warning("Falha na autenticação via API para {$user->matricula}: " . $e->getMessage());
        }

        // Falhou em ambas
        return null;
    }

    /**
     * Criar usuário via API quando não existe localmente
     */
    private function criarUsuarioViaAPI(Request $request)
    {
        try {
            $response = Http::timeout(5)->withToken(env('API_TOKEN'))
                ->post(env('API_LOGIN_URL').'/api/servidor/login', [
                    'matricula' => $request->matricula,
                    'senha' => $request->password
                ]);

            if ($response->successful()) {
                $usuario = $response->json();
                
                $user = User::create([
                    'name' => $usuario['nome'],
                    'email' => $usuario['email'] ?: $usuario['matricula'].'@pc.pb.gov.br',
                    'matricula' => $usuario['matricula'],
                    'active' => true,
                    'password' => Hash::make($request->password),
                    'role_id' => 4,
                    'cargo_id' => $usuario['codigo_cargo'],
                    'cargo' => $usuario['cargo'],
                    'cpf' => $usuario['cpf'],
                    'sexo' => $usuario['sexo'],
                    'unidade_lotacao' => $this->getLotacaoServidor($usuario),
                    'unidade_lotacao_id' => $usuario['lotacao_principal']['codigo_unidade_lotacao'] ?? 340,
                    'srpc' => $usuario['lotacao_principal']['srpc'] ?? 0,
                    'dspc' => $usuario['lotacao_principal']['dspc'] ?? 0,
                    'nivel' => $usuario['lotacao_principal']['nivel'] ?? 0,
                    'classe_funcional' => $usuario['classe_funcional'],
                    'nivel_funcional' => $usuario['nivel'],
                ]);

                Log::info("Novo usuário criado via API: {$user->matricula}");
                return $user;
            }
        } catch (\Exception $e) {
            Log::error("Erro ao criar usuário via API: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Atualizar dados funcionais via API
     */
    private function atualizarDadosViaAPI($user, Request $request)
    {
        try {
            $response = Http::timeout(3)->withToken(env('API_TOKEN'))
                ->post(env('API_LOGIN_URL').'/api/servidor/login', [
                    'matricula' => $request->matricula,
                    'senha' => $request->password
                ]);

            if ($response->successful()) {
                $usuario = $response->json();
                
                $user->update([
                    'status' => $usuario['status'],
                    'unidade_lotacao_id' => $usuario['lotacao_principal']['codigo_unidade_lotacao'] ?? $user->unidade_lotacao_id,
                    'srpc' => $usuario['lotacao_principal']['srpc'] ?? $user->srpc,
                    'dspc' => $usuario['lotacao_principal']['dspc'] ?? $user->dspc,
                    'unidade_lotacao' => $this->getLotacaoServidor($usuario),
                    'cargo_id' => $usuario['codigo_cargo'] ?? $user->cargo_id,
                    'cargo' => $usuario['cargo'] ?? $user->cargo,
                    'cpf' => $usuario['cpf'] ?? $user->cpf,
                    'sexo' => $usuario['sexo'] ?? $user->sexo,
                    'classe_funcional' => $usuario['classe_funcional'] ?? $user->classe_funcional,
                    'nivel_funcional' => $usuario['nivel'] ?? $user->nivel_funcional,
                ]);
                
                Log::info("Dados funcionais atualizados via API para usuário {$user->matricula}");
            }
        } catch (\Exception $e) {
            Log::debug("Não foi possível atualizar dados via API para usuário {$user->matricula}: " . $e->getMessage());
        }
    }

    /**
     * Obter lotação do servidor baseada nos dados da API
     */
    private function getLotacaoServidor($servidor)
    {
        if (isset($servidor['lotacao_principal']) && $servidor['lotacao_principal'] != null) {
            return $servidor['lotacao_principal']['unidade_lotacao'];
        }
        elseif (isset($servidor['orgao_cedido']) && isset($servidor['orgao_cedido']['nome'])) {
            return 'Servidor cedido a '.$servidor['orgao_cedido']['nome'];
        }
        
        return null;
    }
}