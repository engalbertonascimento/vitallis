<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_encounters', function (Blueprint $table) {
            $table->id();

            /*
             * Atendimento administrativo que originou
             * este episódio clínico.
             *
             * Um agendamento poderá originar no máximo
             * um atendimento clínico.
             */
            $table->foreignId('appointment_id')
                ->unique()
                ->constrained('appointments')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Paciente atendido.
             */
            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Contexto organizacional preservado
             * diretamente no episódio clínico.
             */
            $table->foreignId('network_id')
                ->constrained('networks')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->constrained('units')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Profissional responsável pelo episódio.
             */
            $table->foreignId('professional_user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Especialidade e local são preservados
             * no atendimento clínico.
             */
            $table->foreignId('specialty_id')
                ->nullable()
                ->constrained('specialties')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('service_location_id')
                ->nullable()
                ->constrained('service_locations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Estado do atendimento clínico.
             *
             * in_progress
             * completed
             * cancelled
             */
            $table->string('status', 30)
                ->default('in_progress');

            /*
             * Momento real do início e encerramento.
             */
            $table->timestamp('started_at');

            $table->timestamp('completed_at')
                ->nullable();

            /*
             * Motivo administrativo que trouxe
             * o paciente ao atendimento.
             *
             * Não substitui evolução clínica.
             */
            $table->string('reason')
                ->nullable();

            /*
             * Campos clínicos iniciais.
             *
             * Eles representam a ficha principal
             * daquele episódio.
             *
             * Evoluções adicionais terão tabela
             * própria posteriormente.
             */
            $table->text('chief_complaint')
                ->nullable();

            $table->longText('history')
                ->nullable();

            $table->longText('physical_exam')
                ->nullable();

            $table->longText('assessment')
                ->nullable();

            $table->longText('plan')
                ->nullable();

            /*
             * Controle do profissional que efetivamente
             * criou o registro clínico.
             */
            $table->foreignId('created_by_user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Profissional responsável pela última alteração.
             */
            $table->foreignId('updated_by_user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Quem encerrou o atendimento.
             */
            $table->foreignId('completed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamps();
            $table->softDeletes();

            /*
             * Consultas frequentes do prontuário.
             */
            $table->index(
                [
                    'patient_id',
                    'started_at',
                ],
                'medical_encounters_patient_started_index'
            );

            $table->index(
                [
                    'unit_id',
                    'status',
                    'started_at',
                ],
                'medical_encounters_unit_status_index'
            );

            $table->index(
                [
                    'professional_user_id',
                    'status',
                ],
                'medical_encounters_professional_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_encounters');
    }
};