<?php

namespace App\Http\Controllers;

use App\Models\Network;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Lista os usuários cadastrados.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search'));

        $users = User::query()
            ->with([
                'roles' => function ($query) {
                    $query->wherePivot('active', true);
                },
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users', 'search'));
    }

    /**
     * Exibe o formulário de cadastro.
     */
    public function create(): View
    {
        $roles = Role::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $networks = Network::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $units = Unit::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('users.create', compact(
            'roles',
            'networks',
            'units'
        ));
    }

    /**
     * Cadastra usuário e seu primeiro vínculo.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'role_id' => [
                'required',
                'exists:roles,id',
            ],

            'network_id' => [
                'nullable',
                'exists:networks,id',
            ],

            'unit_id' => [
                'nullable',
                'exists:units,id',
            ],
        ]);

        $role = Role::findOrFail($validated['role_id']);

        /*
         * Somente Administrador Vitallis pode existir
         * sem contexto de rede/unidade.
         */
        if ($role->slug !== 'vitallis-admin' && empty($validated['network_id'])) {
            return back()
                ->withErrors([
                    'network_id' => 'Selecione a rede em que o usuário atuará.',
                ])
                ->withInput();
        }

        /*
         * Se uma unidade foi informada, ela obrigatoriamente
         * precisa pertencer à rede selecionada.
         */
        if (! empty($validated['unit_id'])) {
            $unitBelongsToNetwork = Unit::query()
                ->whereKey($validated['unit_id'])
                ->where('network_id', $validated['network_id'])
                ->exists();

            if (! $unitBelongsToNetwork) {
                return back()
                    ->withErrors([
                        'unit_id' => 'A unidade selecionada não pertence à rede informada.',
                    ])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($validated, $role) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'active' => true,
            ]);

            DB::table('role_user')->insert([
                'user_id' => $user->id,
                'role_id' => $role->id,

                'network_id' => $role->slug === 'vitallis-admin'
                    ? null
                    : $validated['network_id'],

                'unit_id' => $role->slug === 'vitallis-admin'
                    ? null
                    : ($validated['unit_id'] ?? null),

                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuário cadastrado com sucesso.');
    }

    /**
     * Exibe o formulário de edição.
     */
    public function edit(User $user): View
    {
        $user->load('roles');

        $roles = Role::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $networks = Network::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $units = Unit::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        /*
         * Neste primeiro CRUD editaremos o vínculo ativo principal.
         *
         * Depois criaremos a administração de múltiplos vínculos
         * separadamente, sem mudar a arquitetura.
         */
        $currentRole = $user->roles
            ->first(fn ($role) => (bool) $role->pivot->active);

        return view('users.edit', compact(
            'user',
            'roles',
            'networks',
            'units',
            'currentRole'
        ));
    }

    /**
     * Atualiza usuário e vínculo principal.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'role_id' => [
                'required',
                'exists:roles,id',
            ],

            'network_id' => [
                'nullable',
                'exists:networks,id',
            ],

            'unit_id' => [
                'nullable',
                'exists:units,id',
            ],
        ]);

        $role = Role::findOrFail($validated['role_id']);

        if ($role->slug !== 'vitallis-admin' && empty($validated['network_id'])) {
            return back()
                ->withErrors([
                    'network_id' => 'Selecione a rede em que o usuário atuará.',
                ])
                ->withInput();
        }

        if (! empty($validated['unit_id'])) {
            $unitBelongsToNetwork = Unit::query()
                ->whereKey($validated['unit_id'])
                ->where('network_id', $validated['network_id'])
                ->exists();

            if (! $unitBelongsToNetwork) {
                return back()
                    ->withErrors([
                        'unit_id' => 'A unidade selecionada não pertence à rede informada.',
                    ])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($validated, $user, $role) {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if (! empty($validated['password'])) {
                $userData['password'] = $validated['password'];
            }

            $user->update($userData);

            /*
             * Desativa os vínculos atuais.
             * Não apagamos histórico.
             */
            DB::table('role_user')
                ->where('user_id', $user->id)
                ->where('active', true)
                ->update([
                    'active' => false,
                    'updated_at' => now(),
                ]);

            /*
             * Cria o novo vínculo ativo.
             */
            DB::table('role_user')->insert([
                'user_id' => $user->id,
                'role_id' => $role->id,

                'network_id' => $role->slug === 'vitallis-admin'
                    ? null
                    : $validated['network_id'],

                'unit_id' => $role->slug === 'vitallis-admin'
                    ? null
                    : ($validated['unit_id'] ?? null),

                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    /**
     * Ativa ou desativa o usuário.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        /*
         * Evita que o próprio usuário desative sua conta
         * enquanto está autenticado.
         */
        if (auth()->id() === $user->id) {
            return redirect()
                ->route('users.index')
                ->withErrors([
                    'user' => 'Você não pode desativar o próprio usuário.',
                ]);
        }

        $user->update([
            'active' => ! $user->active,
        ]);

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                $user->active
                    ? 'Usuário ativado com sucesso.'
                    : 'Usuário desativado com sucesso.'
            );
    }
}