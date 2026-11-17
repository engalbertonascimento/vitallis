<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
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
             * Profissional responsável pela agenda.
             *
             * Neste momento usamos users porque o profissional
             * que opera no Vitallis já possui identidade no sistema.
             */
            $table->foreignId('professional_user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Identificação da agenda.
             */
            $table->string('name')->nullable();

            $table->string('specialty')->nullable();

            /*
             * Período de validade da grade.
             */
            $table->date('starts_on');

            $table->date('ends_on')->nullable();

            /*
             * Dia da semana:
             *
             * 0 = domingo
             * 1 = segunda
             * ...
             * 6 = sábado
             */
            $table->unsignedTinyInteger('weekday');

            /*
             * Horário da grade.
             */
            $table->time('starts_at');
            $table->time('ends_at');

            /*
             * Intervalo padrão de cada atendimento.
             */
            $table->unsignedSmallInteger('slot_duration_minutes')
                ->default(30);

            /*
             * Capacidade por horário.
             *
             * 1 para atendimento individual.
             * Valores maiores permitem futuramente
             * atendimentos coletivos.
             */
            $table->unsignedSmallInteger('slot_capacity')
                ->default(1);

            /*
             * Local textual por enquanto.
             *
             * Posteriormente podemos criar uma tabela própria
             * para consultórios, salas e recursos físicos.
             */
            $table->string('room')->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'unit_id',
                'professional_user_id',
                'active',
            ]);

            $table->index([
                'unit_id',
                'weekday',
                'starts_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
