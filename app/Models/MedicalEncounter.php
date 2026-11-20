<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalEncounter extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'network_id',
        'unit_id',
        'professional_user_id',
        'specialty_id',
        'service_location_id',
        'status',
        'started_at',
        'completed_at',
        'reason',
        'chief_complaint',
        'history',
        'physical_exam',
        'assessment',
        'plan',
        'created_by_user_id',
        'updated_by_user_id',
        'completed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Agendamento que originou o atendimento clínico.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(
            Appointment::class
        );
    }

    /**
     * Paciente deste atendimento.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(
            Patient::class
        );
    }

    /**
     * Rede onde o atendimento ocorreu.
     */
    public function network(): BelongsTo
    {
        return $this->belongsTo(
            Network::class
        );
    }

    /**
     * Unidade onde o atendimento ocorreu.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            Unit::class
        );
    }

    /**
     * Profissional responsável pelo atendimento.
     */
    public function professional(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'professional_user_id'
        );
    }

    /**
     * Especialidade registrada no atendimento.
     */
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(
            Specialty::class
        );
    }

    /**
     * Local onde o atendimento ocorreu.
     */
    public function serviceLocation(): BelongsTo
    {
        return $this->belongsTo(
            ServiceLocation::class
        );
    }

    /**
     * Usuário que criou o episódio clínico.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by_user_id'
        );
    }

    /**
     * Responsável pela última alteração.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by_user_id'
        );
    }

    /**
     * Usuário que finalizou o atendimento.
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'completed_by_user_id'
        );
    }

    /**
     * Atendimento ainda está acontecendo.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Atendimento já foi finalizado.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
