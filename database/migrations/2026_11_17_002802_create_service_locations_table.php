<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_locations', function (Blueprint $table) {
            $table->id();

            /*
             * Todo local físico pertence a uma unidade.
             */
            $table->foreignId('unit_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Permite hierarquia:
             *
             * Bloco
             *   -> Andar
             *      -> Ala
             *         -> Consultório
             */
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('service_locations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Tipos iniciais:
             *
             * building
             * block
             * floor
             * wing
             * sector
             * room
             * office
             * bed
             * other
             */
            $table->string('type', 30);

            $table->string('name');

            /*
             * Código interno da unidade.
             *
             * Ex.:
             * BL-A
             * AND-02
             * CARDIO
             * CONS-201
             */
            $table->string('code', 50)->nullable();

            $table->text('description')->nullable();

            $table->boolean('active')
                ->default(true);

            $table->timestamps();
            $table->softDeletes();

            /*
             * O código precisa ser único somente dentro
             * da própria unidade.
             */
            $table->unique(
                ['unit_id', 'code'],
                'service_location_unit_code_unique'
            );

            $table->index(
                ['unit_id', 'type', 'active'],
                'service_location_unit_type_active_index'
            );

            $table->index(
                ['unit_id', 'parent_id'],
                'service_location_hierarchy_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_locations');
    }
};
