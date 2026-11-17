<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'name',
    'email',
    'password',
    'active',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Conversões dos atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    /**
     * Papéis atribuídos ao usuário.
     *
     * O vínculo armazena:
     * - função;
     * - rede;
     * - unidade;
     * - situação do vínculo.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot([
                'network_id',
                'unit_id',
                'active',
            ])
            ->withTimestamps();
    }

    /**
     * Verifica se o usuário possui determinado papel ativo.
     *
     * Quando rede e unidade forem informadas, considera:
     *
     * - vínculo específico da unidade;
     * - vínculo válido para toda a rede, com unit_id NULL.
     */
    public function hasRole(
        string $roleSlug,
        ?int $networkId = null,
        ?int $unitId = null
    ): bool {
        $query = DB::table('role_user')
            ->join(
                'roles',
                'roles.id',
                '=',
                'role_user.role_id'
            )
            ->where(
                'role_user.user_id',
                $this->id
            )
            ->where(
                'role_user.active',
                true
            )
            ->where(
                'roles.active',
                true
            )
            ->where(
                'roles.slug',
                $roleSlug
            )
            ->whereNull(
                'roles.deleted_at'
            );

        if ($networkId !== null) {
            $query->where(
                'role_user.network_id',
                $networkId
            );
        }

        if ($unitId !== null) {
            $query->where(function ($query) use ($unitId) {
                $query
                    ->where(
                        'role_user.unit_id',
                        $unitId
                    )
                    ->orWhereNull(
                        'role_user.unit_id'
                    );
            });
        }

        return $query->exists();
    }

    /**
     * Verifica se o usuário possui uma permissão efetiva.
     *
     * Ordem de precedência:
     *
     * 1. Administrador Vitallis;
     * 2. Override individual do usuário;
     * 3. Override da função na unidade;
     * 4. Permissão padrão da função;
     * 5. Negado.
     */
    public function hasPermission(
        string $permissionSlug,
        ?int $networkId = null,
        ?int $unitId = null
    ): bool {
        /*
         * Usuários inativos não possuem acesso.
         */
        if (! $this->active) {
            return false;
        }

        /*
         * Administrador global possui acesso irrestrito.
         */
        if ($this->isVitallisAdmin()) {
            return true;
        }

        /*
         * Quando o contexto não é informado explicitamente,
         * utiliza a unidade ativa da sessão ou user_contexts.
         */
        $context = $this->resolvePermissionContext(
            $networkId,
            $unitId
        );

        $networkId = $context['network_id'];
        $unitId = $context['unit_id'];

        /*
         * Usuário operacional sem contexto ativo não recebe
         * permissões de unidade por segurança.
         */
        if (
            $networkId === null
            || $unitId === null
        ) {
            return false;
        }

        /*
         * Confirma que a permissão existe.
         */
        $permission = DB::table('permissions')
            ->where(
                'slug',
                $permissionSlug
            )
            ->first([
                'id',
            ]);

        if (! $permission) {
            return false;
        }

        $permissionId = (int) $permission->id;

        /*
         * 1. Override individual do usuário.
         *
         * A regra mais específica tem precedência.
         */
        $userOverride = DB::table(
            'user_permission_overrides'
        )
            ->where(
                'user_id',
                $this->id
            )
            ->where(
                'unit_id',
                $unitId
            )
            ->where(
                'permission_id',
                $permissionId
            )
            ->first([
                'allowed',
            ]);

        if ($userOverride !== null) {
            return (bool) $userOverride->allowed;
        }

        /*
         * Localiza todas as funções ativas aplicáveis
         * ao contexto atual.
         *
         * Um papel pode estar:
         *
         * - vinculado especificamente à unidade;
         * - vinculado à rede inteira, com unit_id NULL.
         */
        $roleIds = $this->roleIdsForContext(
            $networkId,
            $unitId
        );

        if ($roleIds->isEmpty()) {
            return false;
        }

        /*
         * 2. Overrides da função na unidade.
         *
         * Cada override pertence a uma função específica.
         * Uma negação em uma função não elimina uma permissão
         * concedida por outra função ativa do usuário.
         */
        $roleOverrides = DB::table(
            'unit_permission_overrides'
        )
            ->where(
                'unit_id',
                $unitId
            )
            ->where(
                'permission_id',
                $permissionId
            )
            ->whereIn(
                'role_id',
                $roleIds
            )
            ->get([
                'role_id',
                'allowed',
            ])
            ->keyBy(
                fn ($override) => (int) $override->role_id
            );

        /*
         * Avalia cada papel individualmente.
         *
         * Para cada função:
         *
         * - se existir override, ele substitui o padrão;
         * - caso contrário, verifica permission_role.
         *
         * Como os papéis são cumulativos, basta uma função
         * efetivamente permitir a ação.
         */
        foreach ($roleIds as $roleId) {
            $roleId = (int) $roleId;

            $roleOverride = $roleOverrides->get(
                $roleId
            );

            if ($roleOverride !== null) {
                if ((bool) $roleOverride->allowed) {
                    return true;
                }

                /*
                 * Override negado para esta função.
                 * Continua verificando outras funções ativas.
                 */
                continue;
            }

            /*
             * 3. Permissão padrão da função.
             */
            $defaultAllowed = DB::table(
                'permission_role'
            )
                ->where(
                    'role_id',
                    $roleId
                )
                ->where(
                    'permission_id',
                    $permissionId
                )
                ->exists();

            if ($defaultAllowed) {
                return true;
            }
        }

        /*
         * Nenhuma função permitiu a ação.
         */
        return false;
    }

    /**
     * Verifica se o usuário é administrador global.
     */
    public function isVitallisAdmin(): bool
    {
        return $this->hasRole(
            'vitallis-admin'
        );
    }

    /**
     * Retorna os IDs das funções ativas aplicáveis
     * à rede e unidade atuais.
     */
    private function roleIdsForContext(
        int $networkId,
        int $unitId
    ): Collection {
        return DB::table('role_user')
            ->join(
                'roles',
                'roles.id',
                '=',
                'role_user.role_id'
            )
            ->where(
                'role_user.user_id',
                $this->id
            )
            ->where(
                'role_user.active',
                true
            )
            ->where(
                'role_user.network_id',
                $networkId
            )
            ->where(function ($query) use ($unitId) {
                $query
                    ->where(
                        'role_user.unit_id',
                        $unitId
                    )
                    ->orWhereNull(
                        'role_user.unit_id'
                    );
            })
            ->where(
                'roles.active',
                true
            )
            ->whereNull(
                'roles.deleted_at'
            )
            ->pluck(
                'roles.id'
            )
            ->map(
                fn ($id) => (int) $id
            )
            ->unique()
            ->values();
    }

    /**
     * Resolve a rede e unidade utilizadas na autorização.
     *
     * Prioridade:
     *
     * 1. IDs informados ao método;
     * 2. contexto da sessão;
     * 3. contexto persistido em user_contexts.
     *
     * @return array{
     *     network_id: int|null,
     *     unit_id: int|null
     * }
     */
    private function resolvePermissionContext(
        ?int $networkId,
        ?int $unitId
    ): array {
        /*
         * Contexto explicitamente informado.
         */
        if (
            $networkId !== null
            && $unitId !== null
        ) {
            return [
                'network_id' => $networkId,
                'unit_id' => $unitId,
            ];
        }

        /*
         * Contexto armazenado na sessão atual.
         */
        if (app()->bound('session')) {
            $sessionNetworkId = session(
                'active_network_id'
            );

            $sessionUnitId = session(
                'active_unit_id'
            );

            if (
                $networkId === null
                && $sessionNetworkId !== null
            ) {
                $networkId = (int) $sessionNetworkId;
            }

            if (
                $unitId === null
                && $sessionUnitId !== null
            ) {
                $unitId = (int) $sessionUnitId;
            }
        }

        /*
         * Se a sessão já resolveu tudo, não consulta o banco.
         */
        if (
            $networkId !== null
            && $unitId !== null
        ) {
            return [
                'network_id' => $networkId,
                'unit_id' => $unitId,
            ];
        }

        /*
         * Fallback para o contexto persistido.
         */
        $context = DB::table('user_contexts')
            ->where(
                'user_id',
                $this->id
            )
            ->first([
                'network_id',
                'unit_id',
            ]);

        if ($context) {
            if (
                $networkId === null
                && $context->network_id !== null
            ) {
                $networkId = (int) $context->network_id;
            }

            if (
                $unitId === null
                && $context->unit_id !== null
            ) {
                $unitId = (int) $context->unit_id;
            }
        }

        return [
            'network_id' => $networkId,
            'unit_id' => $unitId,
        ];
    }
}