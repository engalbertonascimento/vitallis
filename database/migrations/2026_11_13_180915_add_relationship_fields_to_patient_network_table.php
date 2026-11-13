<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_network', function (Blueprint $table) {

            $table->foreignId('patient_id')
                ->after('id')
                ->constrained('patients')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('network_id')
                ->after('patient_id')
                ->constrained('networks')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('origin_unit_id')
                ->nullable()
                ->after('network_id')
                ->constrained('units')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->boolean('active')
                ->default(true)
                ->after('origin_unit_id');

            $table->timestamp('first_registered_at')
                ->nullable()
                ->after('active');

            $table->unique(
                ['patient_id', 'network_id'],
                'patient_network_patient_network_unique'
            );

            $table->index(
                ['network_id', 'active'],
                'patient_network_network_active_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('patient_network', function (Blueprint $table) {

            $table->dropIndex(
                'patient_network_network_active_index'
            );

            $table->dropUnique(
                'patient_network_patient_network_unique'
            );

            $table->dropForeign(['origin_unit_id']);
            $table->dropForeign(['network_id']);
            $table->dropForeign(['patient_id']);

            $table->dropColumn([
                'patient_id',
                'network_id',
                'origin_unit_id',
                'active',
                'first_registered_at',
            ]);
        });
    }
};
