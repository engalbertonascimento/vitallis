<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'scope_type',
        'network_id',
        'unit_id',
        'system',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'system' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /**
     * Permissões padrão da função.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)
            ->withTimestamps();
    }

    /**
     * Rede da função, quando seu escopo for network ou unit.
     */
    public function network(): BelongsTo
    {
        return $this->belongsTo(Network::class);
    }

    /**
     * Unidade da função, quando seu escopo for unit.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Indica se a função possui escopo global.
     */
    public function isGlobal(): bool
    {
        return $this->scope_type === 'global';
    }

    /**
     * Indica se a função pertence a uma rede.
     */
    public function isNetworkScoped(): bool
    {
        return $this->scope_type === 'network';
    }

    /**
     * Indica se a função pertence a uma unidade.
     */
    public function isUnitScoped(): bool
    {
        return $this->scope_type === 'unit';
    }
}