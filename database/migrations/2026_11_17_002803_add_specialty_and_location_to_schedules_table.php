
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {

            $table->foreignId('specialty_id')
                ->nullable()
                ->after('name')
                ->constrained('specialties')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('service_location_id')
                ->nullable()
                ->after('slot_capacity')
                ->constrained('service_locations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index(
                [
                    'unit_id',
                    'specialty_id',
                    'active',
                ],
                'schedule_unit_specialty_active_index'
            );

            $table->index(
                [
                    'unit_id',
                    'service_location_id',
                ],
                'schedule_unit_location_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {

            $table->dropIndex(
                'schedule_unit_specialty_active_index'
            );

            $table->dropIndex(
                'schedule_unit_location_index'
            );

            $table->dropForeign([
                'service_location_id',
            ]);

            $table->dropForeign([
                'specialty_id',
            ]);

            $table->dropColumn([
                'specialty_id',
                'service_location_id',
            ]);
        });
    }
};
