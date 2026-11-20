<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'social_name',
        'cpf',
        'cns',
        'birth_date',
        'sex',
        'mother_name',
        'father_name',
        'email',
        'phone',
        'mobile_phone',
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
            'birth_date' => 'date',
            'active' => 'boolean',
        ];
    }

    /**
     * Usuário associado a este paciente, quando existir.
     *
     * Este vínculo identifica que User e Patient representam
     * a mesma pessoa. Ele não concede acesso ao prontuário.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'patient_user')
            ->withTimestamps();
    }

    /**
     * Redes nas quais este paciente possui vínculo.
     */
    public function networks(): BelongsToMany
    {
        return $this->belongsToMany(Network::class, 'patient_network')
            ->withPivot([
                'origin_unit_id',
                'active',
                'first_registered_at',
            ])
            ->withTimestamps();
    }
    
    /**
     * Histórico longitudinal de atendimentos clínicos.
     */
    public function medicalEncounters(): HasMany
    {
        return $this->hasMany(
            MedicalEncounter::class
        );
    }
}
