<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'network_id',
        'unit_id',
        'professional_user_id',
        'name',

        /*
         * Novos relacionamentos normalizados.
         */
        'specialty_id',
        'service_location_id',

        /*
         * Campos legados temporários.
         *
         * Serão removidos somente depois que toda
         * a aplicação estiver usando as FKs acima.
         */
        'specialty',
        'room',

        'starts_on',
        'ends_on',
        'weekday',
        'starts_at',
        'ends_at',
        'slot_duration_minutes',
        'slot_capacity',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'weekday' => 'integer',
            'slot_duration_minutes' => 'integer',
            'slot_capacity' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * Rede à qual a grade pertence.
     */
    public function network(): BelongsTo
    {
        return $this->belongsTo(Network::class);
    }

    /**
     * Unidade à qual a grade pertence.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Profissional responsável pela agenda.
     */
    public function professional(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'professional_user_id'
        );
    }

    /**
     * Especialidade vinculada à grade.
     */
    public function specialtyRelation(): BelongsTo
    {
        return $this->belongsTo(
            Specialty::class,
            'specialty_id'
        );
    }

    /**
     * Local físico onde os atendimentos ocorrerão.
     */
    public function serviceLocation(): BelongsTo
    {
        return $this->belongsTo(
            ServiceLocation::class,
            'service_location_id'
        );
    }

    /**
     * Agendamentos realizados nesta grade.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
