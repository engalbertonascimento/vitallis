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
     * Get the attributes that should be cast.
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
     * O vínculo também armazena o contexto:
     * rede, unidade e situação do acesso.
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
     */
    public function hasRole(
        string $roleSlug,
        ?int $networkId = null,
        ?int $unitId = null
    ): bool {
        return $this->roles()
            ->where('roles.slug', $roleSlug)
            ->wherePivot('active', true)
            ->when(
                $networkId !== null,
                fn ($query) => $query->wherePivot(
                    'network_id',
                    $networkId
                )
            )
            ->when(
                $unitId !== null,
                fn ($query) => $query->wherePivot(
                    'unit_id',
                    $unitId
                )
            )
            ->exists();
    }

    /**
     * Verifica se o usuário possui uma permissão ativa.
     *
     * Administradores globais Vitallis possuem todas
     * as permissões da plataforma.
     */
    public function hasPermission(
        string $permissionSlug,
        ?int $networkId = null,
        ?int $unitId = null
    ): bool {
        if ($this->hasRole('vitallis-admin')) {
            return true;
        }

        return $this->roles()
            ->wherePivot('active', true)
            ->when(
                $networkId !== null,
                fn ($query) => $query->wherePivot(
                    'network_id',
                    $networkId
                )
            )
            ->when(
                $unitId !== null,
                fn ($query) => $query->wherePivot(
                    'unit_id',
                    $unitId
                )
            )
            ->whereHas(
                'permissions',
                function ($query) use ($permissionSlug) {
                    $query->where(
                        'permissions.slug',
                        $permissionSlug
                    );
                }
            )
            ->exists();
    }

    /**
     * Verifica se o usuário é administrador global do Vitallis.
     */
    public function isVitallisAdmin(): bool
    {
        return $this->hasRole('vitallis-admin');
    }
}