<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_user', function (Blueprint $table) {
            $table->foreignId('patient_id')
                ->after('id')
                ->constrained('patients')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->after('patient_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unique(['patient_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('patient_user', function (Blueprint $table) {
            $table->dropUnique(['patient_id', 'user_id']);

            $table->dropForeign(['patient_id']);
            $table->dropForeign(['user_id']);

            $table->dropColumn([
                'patient_id',
                'user_id',
            ]);
        });
    }
};
