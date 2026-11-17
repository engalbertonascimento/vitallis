<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceLocation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'unit_id',
        'parent_id',
        'type',
        'name',
        'code',
        'description',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            ServiceLocation::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            ServiceLocation::class,
            'parent_id'
        );
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
