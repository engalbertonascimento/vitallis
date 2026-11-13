<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'network_id',
        'name',
        'code',
        'document',
        'email',
        'phone',
        'postal_code',
        'street',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(Network::class);
    }
}