<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            /*
             * Contexto organizacional.
             */
            $table->foreignId('network_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Grade utilizada.
             */
            $table->foreignId('schedule_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Paciente.
             */
            $table->foreignId('patient_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Profissional responsável.
             *
             * É armazenado também no agendamento para preservar
             * explicitamente quem realizará o atendimento.
             */
            $table->foreignId('professional_user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Horário reservado.
             */
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            /*
             * Estados iniciais:
             *
             * scheduled
             * confirmed
             * checked_in
             * waiting
             * in_service
             * completed
             * cancelled
             * no_show
             */
            $table->string('status', 30)
                ->default('scheduled');

            /*
             * Motivo/observação administrativa do agendamento.
             *
             * Não utilizar este campo para evolução clínica.
             */
            $table->string('reason')->nullable();

            $table->text('administrative_notes')->nullable();

            /*
             * Quem criou o agendamento.
             */
            $table->foreignId('created_by_user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Auditoria básica das mudanças de estado.
             */
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->foreignId('cancelled_by_user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('cancellation_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'unit_id',
                'starts_at',
                'status',
            ]);

            $table->index([
                'professional_user_id',
                'starts_at',
            ]);

            $table->index([
                'patient_id',
                'starts_at',
            ]);

            /*
             * Evita que o mesmo paciente seja duplicado
             * exatamente no mesmo início da mesma agenda.
             *
             * A validação de capacidade e sobreposição continuará
             * sendo feita também no backend dentro de transação.
             */
            $table->unique(
                [
                    'schedule_id',
                    'patient_id',
                    'starts_at',
                ],
                'appointment_schedule_patient_start_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
