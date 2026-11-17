<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {

            /*
             * Escopo da função:
             *
             * global  -> disponível em toda a plataforma
             * network -> disponível apenas em determinada rede
             * unit    -> disponível apenas em determinada unidade
             */
            $table->string('scope_type', 20)
                ->default('global')
                ->after('description');

            /*
             * Utilizado quando scope_type = network ou unit.
             */
            $table->foreignId('network_id')
                ->nullable()
                ->after('scope_type')
                ->constrained('networks')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Utilizado quando scope_type = unit.
             */
            $table->foreignId('unit_id')
                ->nullable()
                ->after('network_id')
                ->constrained('units')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index(
                ['scope_type', 'network_id', 'unit_id'],
                'roles_scope_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {

            $table->dropIndex('roles_scope_index');

            $table->dropForeign(['unit_id']);
            $table->dropForeign(['network_id']);

            $table->dropColumn([
                'scope_type',
                'network_id',
                'unit_id',
            ]);
        });
    }
};
