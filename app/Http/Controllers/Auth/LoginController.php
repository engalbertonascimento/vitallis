<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(
            $credentials,
            $request->boolean('remember')
        )) {
            throw ValidationException::withMessages([
                'email' => 'E-mail ou senha inválidos.',
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();

        /*
         * Administrador global não depende de rede/unidade
         * para operar na plataforma.
         */
        if (! $user->isVitallisAdmin()) {
            $this->initializeOperationalContext(
                $request,
                $user
            );
        }

        return redirect()->intended('/dashboard');
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Inicializa ou restaura o contexto operacional
     * do usuário após autenticação.
     */
    private function initializeOperationalContext(
        Request $request,
        User $user
    ): void {
        $availableUnits = $this->accessibleUnits($user);

        /*
         * Usuário sem unidade disponível:
         * não cria contexto inválido.
         *
         * O header indicará que não existe contexto
         * operacional disponível.
         */
        if ($availableUnits->isEmpty()) {
            DB::table('user_contexts')
                ->where('user_id', $user->id)
                ->delete();

            $request->session()->forget([
                'active_network_id',
                'active_unit_id',
            ]);

            return;
        }

        /*
         * Primeiro tentamos preservar o contexto utilizado
         * anteriormente, desde que o usuário ainda possua
         * acesso àquela unidade.
         */
        $storedContext = DB::table('user_contexts')
            ->where('user_id', $user->id)
            ->first();

        $selectedUnit = null;

        if (
            $storedContext
            && $storedContext->unit_id
        ) {
            $selectedUnit = $availableUnits->first(
                fn (Unit $unit) =>
                    (int) $unit->id
                    === (int) $storedContext->unit_id
            );
        }

        /*
         * Se não existir contexto anterior válido,
         * seleciona automaticamente a primeira
         * unidade acessível.
         *
         * Para um recepcionista com apenas uma unidade,
         * esta será necessariamente a unidade dele.
         */
        if (! $selectedUnit) {
            $selectedUnit = $availableUnits->first();
        }

        DB::table('user_contexts')->updateOrInsert(
            [
                'user_id' => $user->id,
            ],
            [
                'network_id' => $selectedUnit->network_id,
                'unit_id' => $selectedUnit->id,
                'selected_at' => now(),
                'updated_at' => now(),
                'created_at' => $storedContext?->created_at
                    ?? now(),
            ]
        );

        /*
         * Mantemos também na sessão para o contexto
         * atual da navegação.
         */
        $request->session()->put(
            'active_network_id',
            $selectedUnit->network_id
        );

        $request->session()->put(
            'active_unit_id',
            $selectedUnit->id
        );
    }

    /**
     * Recupera todas as unidades efetivamente
     * acessíveis ao usuário.
     *
     * role_user.unit_id preenchido:
     * acesso específico à unidade.
     *
     * role_user.unit_id NULL:
     * acesso às unidades da respectiva rede.
     */
    private function accessibleUnits(User $user): Collection
    {
        $links = DB::table('role_user')
            ->where('user_id', $user->id)
            ->where('active', true)
            ->whereNotNull('network_id')
            ->get([
                'network_id',
                'unit_id',
            ]);

        $networkWideIds = $links
            ->whereNull('unit_id')
            ->pluck('network_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $specificUnitIds = $links
            ->whereNotNull('unit_id')
            ->pluck('unit_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        /*
         * Nenhum vínculo operacional ativo.
         */
        if (
            $networkWideIds->isEmpty()
            && $specificUnitIds->isEmpty()
        ) {
            return collect();
        }

        return Unit::query()
            ->with('network')
            ->where('active', true)
            ->whereHas(
                'network',
                fn ($query) =>
                    $query->where('active', true)
            )
            ->where(function ($query) use (
                $networkWideIds,
                $specificUnitIds
            ) {
                if ($networkWideIds->isNotEmpty()) {
                    $query->whereIn(
                        'network_id',
                        $networkWideIds
                    );
                }

                if ($specificUnitIds->isNotEmpty()) {
                    if ($networkWideIds->isNotEmpty()) {
                        $query->orWhereIn(
                            'id',
                            $specificUnitIds
                        );
                    } else {
                        $query->whereIn(
                            'id',
                            $specificUnitIds
                        );
                    }
                }
            })
            ->orderBy('name')
            ->get();
    }
}