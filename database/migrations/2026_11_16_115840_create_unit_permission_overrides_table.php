<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_permission_overrides', function (Blueprint $table) {
            $table->id();

            $table->foreignId('unit_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('role_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('permission_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->boolean('allowed');

            $table->timestamps();

            $table->unique(
                ['unit_id', 'role_id', 'permission_id'],
                'unit_role_permission_unique'
            );

            $table->index(
                ['unit_id', 'role_id'],
                'unit_role_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_permission_overrides');
    }
};
