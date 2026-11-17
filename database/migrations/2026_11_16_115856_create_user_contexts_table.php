<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_contexts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('network_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamp('selected_at')
                ->nullable();

            $table->timestamps();

            /*
             * Um usuário possui somente um contexto
             * selecionado por vez.
             */
            $table->unique('user_id');

            $table->index(
                ['network_id', 'unit_id'],
                'user_context_network_unit_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_contexts');
    }
};
