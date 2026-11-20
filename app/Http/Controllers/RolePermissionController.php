<?php

namespace App\Http\Controllers;

use App\Models\Network;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RolePermissionController extends Controller
{
    /**
     * Lista as funções disponíveis e permite selecionar
     * o contexto Rede + Unidade.
     */
    public function index(Request $request): View
    {
        $networkId = $request->integer('network_id') ?: null;
        $unitId = $request->integer('unit_id') ?: null;

        $networks = Network::query()
            ->where('active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        $units = Unit::query()
            ->with('network')
            ->where('active', true)
            ->when(
                $networkId,
                fn ($query) => $query->where(
                    'network_id',
                    $networkId
                )
            )
            ->orderBy('name')
            ->get([
                'id',
                'network_id',
                'name',
                'city',
                'state',
            ]);

        /*
         * O Administrador Vitallis é uma função de plataforma.
         * Não faz sentido aplicar overrides de unidade nele.
         *
         * São exibidas:
         * - funções globais;
         * - funções da rede selecionada;
         * - funções da unidade selecionada.
         */
        $roles = Role::query()
            ->where('active', true)
            ->where('slug', '!=', 'vitallis-admin')
            ->where(function ($query) use ($networkId, $unitId) {
                $query->where('scope_type', 'global');

                if ($networkId) {
                    $query->orWhere(function ($query) use ($networkId) {
                        $query
                            ->where('scope_type', 'network')
                            ->where('network_id', $networkId);
                    });
                }

                if ($unitId) {
                    $query->orWhere(function ($query) use (
                        $networkId,
                        $unitId
                    ) {
                        $query
                            ->where('scope_type', 'unit')
                            ->where('unit_id', $unitId);

                        if ($networkId) {
                            $query->where(
                                'network_id',
                                $networkId
                            );
                        }
                    });
                }
            })
            ->with([
                'network',
                'unit',
            ])
            ->withCount('permissions')
            ->orderByRaw(
                "CASE
                    WHEN scope_type = 'global' THEN 1
                    WHEN scope_type = 'network' THEN 2
                    WHEN scope_type = 'unit' THEN 3
                    ELSE 4
                END"
            )
            ->orderBy('name')
            ->get();

        return view(
            'roles.index',
            compact(
                'networks',
                'units',
                'roles',
                'networkId',
                'unitId'
            )
        );
    }

    /**
     * Exibe o formulário para criação de uma função.
     */
    public function create(Request $request): View
    {
        $networks = Network::query()
            ->where('active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        $units = Unit::query()
            ->with('network')
            ->where('active', true)
            ->whereHas(
                'network',
                fn ($query) => $query->where(
                    'active',
                    true
                )
            )
            ->orderBy('name')
            ->get([
                'id',
                'network_id',
                'name',
                'city',
                'state',
            ]);

        $permissions = Permission::query()
            ->orderBy('slug')
            ->get();

        $permissionGroups = $permissions->groupBy(
            fn (Permission $permission) =>
                explode('.', $permission->slug)[0]
        );

        return view(
            'roles.create',
            compact(
                'networks',
                'units',
                'permissions',
                'permissionGroups'
            )
        );
    }

    /**
     * Cria uma nova função global, de rede ou de unidade.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'scope_type' => [
                'required',
                Rule::in([
                    'global',
                    'network',
                    'unit',
                ]),
            ],

            'network_id' => [
                'nullable',
                'integer',
                'exists:networks,id',
            ],

            'unit_id' => [
                'nullable',
                'integer',
                'exists:units,id',
            ],

            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'integer',
                'exists:permissions,id',
            ],
        ]);

        /*
         * Escopo global não possui rede nem unidade.
         */
        if ($validated['scope_type'] === 'global') {
            $validated['network_id'] = null;
            $validated['unit_id'] = null;
        }

        /*
         * Escopo de rede exige uma rede ativa.
         */
        if ($validated['scope_type'] === 'network') {
            if (empty($validated['network_id'])) {
                return back()
                    ->withErrors([
                        'network_id' =>
                            'Selecione a rede desta função.',
                    ])
                    ->withInput();
            }

            $networkExists = Network::query()
                ->whereKey($validated['network_id'])
                ->where('active', true)
                ->exists();

            if (! $networkExists) {
                return back()
                    ->withErrors([
                        'network_id' =>
                            'A rede selecionada não está ativa.',
                    ])
                    ->withInput();
            }

            $validated['unit_id'] = null;
        }

        /*
         * Escopo de unidade exige rede e unidade.
         */
        if ($validated['scope_type'] === 'unit') {
            if (empty($validated['network_id'])) {
                return back()
                    ->withErrors([
                        'network_id' =>
                            'Selecione a rede desta função.',
                    ])
                    ->withInput();
            }

            if (empty($validated['unit_id'])) {
                return back()
                    ->withErrors([
                        'unit_id' =>
                            'Selecione a unidade desta função.',
                    ])
                    ->withInput();
            }

            /*
             * A unidade deve estar ativa e pertencer
             * à rede selecionada.
             */
            $validUnit = Unit::query()
                ->whereKey($validated['unit_id'])
                ->where(
                    'network_id',
                    $validated['network_id']
                )
                ->where('active', true)
                ->whereHas(
                    'network',
                    fn ($query) => $query->where(
                        'active',
                        true
                    )
                )
                ->exists();

            if (! $validUnit) {
                return back()
                    ->withErrors([
                        'unit_id' =>
                            'A unidade selecionada não pertence à rede informada ou está inativa.',
                    ])
                    ->withInput();
            }
        }

        /*
         * Gera o identificador da função.
         *
         * Funções locais recebem o ID do seu escopo para
         * evitar colisão entre redes e unidades diferentes.
         */
        $informedSlug = trim(
            (string) ($validated['slug'] ?? '')
        );

        if ($informedSlug === '') {
            $baseSlug = Str::slug(
                $validated['name']
            );

            $slug = match ($validated['scope_type']) {
                'network' =>
                    $baseSlug
                    .'-network-'
                    .$validated['network_id'],

                'unit' =>
                    $baseSlug
                    .'-unit-'
                    .$validated['unit_id'],

                default => $baseSlug,
            };
        } else {
            $slug = Str::slug($informedSlug);
        }

        if ($slug === '') {
            return back()
                ->withErrors([
                    'slug' =>
                        'Não foi possível gerar um identificador válido para a função.',
                ])
                ->withInput();
        }

        /*
         * O índice único do banco inclui inclusive funções
         * excluídas logicamente, portanto não reutilizamos slug.
         */
        if (
            Role::withTrashed()
                ->where('slug', $slug)
                ->exists()
        ) {
            return back()
                ->withErrors([
                    'slug' =>
                        'Já existe uma função com este identificador.',
                ])
                ->withInput();
        }

        $role = DB::transaction(
            function () use ($validated, $slug) {
                $role = Role::create([
                    'name' => $validated['name'],
                    'slug' => $slug,

                    'description' =>
                        $validated['description'] ?? null,

                    'scope_type' =>
                        $validated['scope_type'],

                    'network_id' =>
                        $validated['network_id'] ?? null,

                    'unit_id' =>
                        $validated['unit_id'] ?? null,

                    /*
                     * Funções criadas pelo painel não são
                     * funções internas protegidas do Vitallis.
                     */
                    'system' => false,
                    'active' => true,
                ]);

                $role->permissions()->sync(
                    $validated['permissions'] ?? []
                );

                return $role;
            }
        );

        /*
         * Uma função de unidade é aberta diretamente
         * na matriz daquela unidade.
         */
        if (
            $role->scope_type === 'unit'
            && $role->unit_id
        ) {
            return redirect()
                ->route(
                    'roles.edit',
                    [
                        'role' => $role->id,
                        'unit_id' => $role->unit_id,
                    ]
                )
                ->with(
                    'success',
                    'Função criada com sucesso.'
                );
        }

        /*
         * Função de rede retorna para a listagem
         * filtrada pela respectiva rede.
         */
        if (
            $role->scope_type === 'network'
            && $role->network_id
        ) {
            return redirect()
                ->route(
                    'roles.index',
                    [
                        'network_id' =>
                            $role->network_id,
                    ]
                )
                ->with(
                    'success',
                    'Função criada com sucesso.'
                );
        }

        return redirect()
            ->route('roles.index')
            ->with(
                'success',
                'Função criada com sucesso.'
            );
    }

    /**
     * Exibe a matriz de permissões da função
     * para uma determinada unidade.
     */
    public function edit(
        Request $request,
        Role $role
    ): View {
        if ($role->slug === 'vitallis-admin') {
            abort(
                403,
                'O Administrador Vitallis possui acesso global.'
            );
        }

        $validated = $request->validate([
            'unit_id' => [
                'required',
                'integer',
                'exists:units,id',
            ],
        ]);

        $unit = Unit::query()
            ->with('network')
            ->whereKey($validated['unit_id'])
            ->firstOrFail();

        /*
         * Uma função exclusiva de unidade só poderá
         * ser configurada em sua própria unidade.
         */
        if (
            $role->scope_type === 'unit'
            && (int) $role->unit_id !== (int) $unit->id
        ) {
            abort(
                403,
                'Esta função pertence a outra unidade.'
            );
        }

        /*
         * Uma função de rede só poderá ser aplicada
         * em unidades daquela mesma rede.
         */
        if (
            $role->scope_type === 'network'
            && (int) $role->network_id
                !== (int) $unit->network_id
        ) {
            abort(
                403,
                'Esta função pertence a outra rede.'
            );
        }

        /*
         * IDs das permissões padrão da função.
         */
        $defaultPermissionIds = DB::table(
            'permission_role'
        )
            ->where('role_id', $role->id)
            ->pluck('permission_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        /*
         * Overrides existentes para esta função
         * especificamente nesta unidade.
         */
        $overrides = DB::table(
            'unit_permission_overrides'
        )
            ->where('unit_id', $unit->id)
            ->where('role_id', $role->id)
            ->get([
                'permission_id',
                'allowed',
            ])
            ->keyBy('permission_id');

        /*
         * Todas as permissões disponíveis.
         */
        $permissions = Permission::query()
            ->orderBy('slug')
            ->get();

        /*
         * Agrupamento visual pelo primeiro segmento
         * do identificador da permissão.
         */
        $permissionGroups = $permissions->groupBy(
            fn (Permission $permission) =>
                explode('.', $permission->slug)[0]
        );

        return view(
            'roles.edit',
            compact(
                'role',
                'unit',
                'permissions',
                'permissionGroups',
                'defaultPermissionIds',
                'overrides'
            )
        );
    }

    /**
     * Salva os overrides da função na unidade.
     *
     * inherit -> remove override
     * allow   -> allowed = true
     * deny    -> allowed = false
     */
    public function update(
        Request $request,
        Role $role
    ): RedirectResponse {
        if ($role->slug === 'vitallis-admin') {
            abort(
                403,
                'O Administrador Vitallis possui acesso global.'
            );
        }

        $validated = $request->validate([
            'unit_id' => [
                'required',
                'integer',
                'exists:units,id',
            ],

            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                Rule::in([
                    'inherit',
                    'allow',
                    'deny',
                ]),
            ],
        ]);

        $unit = Unit::query()
            ->with('network')
            ->findOrFail(
                $validated['unit_id']
            );

        /*
         * Protege o escopo da função.
         */
        if (
            $role->scope_type === 'unit'
            && (int) $role->unit_id !== (int) $unit->id
        ) {
            abort(
                403,
                'Esta função pertence a outra unidade.'
            );
        }

        if (
            $role->scope_type === 'network'
            && (int) $role->network_id
                !== (int) $unit->network_id
        ) {
            abort(
                403,
                'Esta função pertence a outra rede.'
            );
        }

        /*
         * IDs válidos existentes no banco.
         */
        $validPermissionIds = Permission::query()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        DB::transaction(
            function () use (
                $validated,
                $role,
                $unit,
                $validPermissionIds
            ) {
                foreach (
                    $validated['permissions'] ?? []
                    as $permissionId => $state
                ) {
                    $permissionId = (int) $permissionId;

                    /*
                     * Impede IDs manipulados manualmente.
                     */
                    if (
                        ! in_array(
                            $permissionId,
                            $validPermissionIds,
                            true
                        )
                    ) {
                        abort(
                            422,
                            'Permissão inválida.'
                        );
                    }

                    /*
                     * Herdar significa ausência de override.
                     */
                    if ($state === 'inherit') {
                        DB::table(
                            'unit_permission_overrides'
                        )
                            ->where(
                                'unit_id',
                                $unit->id
                            )
                            ->where(
                                'role_id',
                                $role->id
                            )
                            ->where(
                                'permission_id',
                                $permissionId
                            )
                            ->delete();

                        continue;
                    }

                    /*
                     * Preserva created_at quando o registro
                     * já existir.
                     */
                    $existingOverride = DB::table(
                        'unit_permission_overrides'
                    )
                        ->where(
                            'unit_id',
                            $unit->id
                        )
                        ->where(
                            'role_id',
                            $role->id
                        )
                        ->where(
                            'permission_id',
                            $permissionId
                        )
                        ->first();

                    if ($existingOverride) {
                        DB::table(
                            'unit_permission_overrides'
                        )
                            ->where(
                                'id',
                                $existingOverride->id
                            )
                            ->update([
                                'allowed' =>
                                    $state === 'allow',
                                'updated_at' => now(),
                            ]);
                    } else {
                        DB::table(
                            'unit_permission_overrides'
                        )->insert([
                            'unit_id' => $unit->id,
                            'role_id' => $role->id,
                            'permission_id' =>
                                $permissionId,

                            'allowed' =>
                                $state === 'allow',

                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        );

        return redirect()
            ->route(
                'roles.edit',
                [
                    'role' => $role->id,
                    'unit_id' => $unit->id,
                ]
            )
            ->with(
                'success',
                'Permissões da função atualizadas para esta unidade.'
            );
    }

    /**
     * Atualiza as permissões padrão da função.
     *
     * Estas permissões serão utilizadas quando não existir
     * override de usuário ou unidade.
     */
    public function updateDefaults(
        Request $request,
        Role $role
    ): RedirectResponse {
        if ($role->slug === 'vitallis-admin') {
            abort(
                403,
                'As permissões globais do Administrador Vitallis não podem ser reduzidas.'
            );
        }

        $validated = $request->validate([
            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'integer',
                'exists:permissions,id',
            ],
        ]);

        $role->permissions()->sync(
            $validated['permissions'] ?? []
        );

        return back()->with(
            'success',
            'Permissões padrão da função atualizadas.'
        );
    }
}