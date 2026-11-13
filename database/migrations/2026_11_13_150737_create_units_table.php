<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
    
            $table->foreignId('network_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();
    
            $table->string('name');
            $table->string('code', 50)->nullable();
    
            $table->string('document', 20)->nullable();
    
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
    
            $table->string('postal_code', 10)->nullable();
            $table->string('street')->nullable();
            $table->string('number', 20)->nullable();
            $table->string('complement')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->char('state', 2)->nullable();
    
            $table->boolean('active')->default(true);
    
            $table->timestamps();
            $table->softDeletes();
    
            $table->unique(['network_id', 'code']);
            $table->index(['network_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
